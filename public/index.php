<?php
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $file = __DIR__ . '/../' . lcfirst(str_replace('\\', '/', $class)) . '.php';
        if (file_exists($file)) require $file;
    }
});

use App\Models\User;
use App\Models\Request as VacationRequest;
use App\Models\RequestComment;
use App\Models\Department;
use App\Core\I18n;

session_start();

$action = $_GET['action'] ?? null;

// Handle Language Switch
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = in_array($_GET['lang'], ['en', 'de']) ? $_GET['lang'] : 'en';
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?')); // strip query to avoid reload loop
    exit;
}

// Ensure database creates everything on first boot
\App\Core\Database::getConnection();

// --- Handle Non-Logged In Actions ---
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'login') {
            $user = User::authenticate($_POST['login'], $_POST['password']);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: /");
            } else {
                header("Location: /?error=login_failed");
            }
            exit;
        }

        if ($action === 'forgot_password' || $action === 'do_reset_password') {
            header("Location: /?error=invalid_request");
            exit;
        }
    }

    // Default View: Login
    include __DIR__ . '/../app/Views/login.php';
    exit;
}

// --- Logged In Actions ---

if ($action === 'logout') {
    session_destroy();
    header("Location: /");
    exit;
}

// Fetch current logged in user
$currentUser = User::getById($_SESSION['user_id']);
if (!$currentUser) {
    session_destroy();
    header("Location: /");
    exit;
}

$currentRole = $currentUser['role'];
$isAdmin = in_array($currentRole, ['CEO', 'Admin'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'change_password') {
    $newPw = $_POST['password'] ?? '';
    if ($newPw !== '') {
        User::updatePassword($currentUser['id'], $newPw, true);
    }
    header("Location: /?success=action_success");
    exit;
}
$requirePasswordChange = false;

if ($action === 'calendar_ics') {
    $requestsForCalendar = ($isAdmin)
        ? VacationRequest::getAll()
        : VacationRequest::getByUserId($currentUser['id']);
    $blockedForCalendar = VacationRequest::getBlockedPeriods();

    $filterStart = $_GET['export_start'] ?? null;
    $filterEnd = $_GET['export_end'] ?? null;
    $includeApproved = isset($_GET['include_approved']) ? (bool) $_GET['include_approved'] : true;
    $includePending = isset($_GET['include_pending']) ? (bool) $_GET['include_pending'] : true;
    $includeStorno = isset($_GET['include_storno']) ? (bool) $_GET['include_storno'] : true;
    $includeBlocked = isset($_GET['include_blocked']) ? (bool) $_GET['include_blocked'] : false;

    $statusAllow = [];
    if ($includeApproved) $statusAllow[] = 'approved';
    if ($includePending) $statusAllow[] = 'pending';
    if ($includeStorno) $statusAllow[] = 'storno_requested';
    if (empty($statusAllow)) {
        $statusAllow = ['approved', 'pending', 'storno_requested'];
    }

    $lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//ZenTime//Vacation Calendar//EN',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH'
    ];

    foreach ($requestsForCalendar as $requestItem) {
        if (in_array($requestItem['status'], ['rejected', 'cancelled'], true)) {
            continue;
        }
        if (!in_array($requestItem['status'], $statusAllow, true)) {
            continue;
        }
        if ($filterStart && $requestItem['end_date'] < $filterStart) {
            continue;
        }
        if ($filterEnd && $requestItem['start_date'] > $filterEnd) {
            continue;
        }

        $title = ($isAdmin)
            ? ($requestItem['firstname'] . ' ' . $requestItem['lastname'] . ' - Vacation')
            : 'Vacation';

        $start = date('Ymd', strtotime($requestItem['start_date']));
        $endExclusive = date('Ymd', strtotime($requestItem['end_date'] . ' +1 day'));
        $created = gmdate('Ymd\THis\Z', strtotime($requestItem['created_at'] ?? 'now'));
        $uid = 'request-' . $requestItem['id'] . '@zentime.local';

        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'UID:' . $uid;
        $lines[] = 'DTSTAMP:' . $created;
        $lines[] = 'DTSTART;VALUE=DATE:' . $start;
        $lines[] = 'DTEND;VALUE=DATE:' . $endExclusive;
        $lines[] = 'SUMMARY:' . str_replace([',', ';'], ['\,', '\;'], $title);
        $lines[] = 'DESCRIPTION:Status ' . $requestItem['status'];
        $lines[] = 'END:VEVENT';
    }

    if ($isAdmin && $includeBlocked) {
        foreach ($blockedForCalendar as $blockedItem) {
            if ($filterStart && $blockedItem['end_date'] < $filterStart) {
                continue;
            }
            if ($filterEnd && $blockedItem['start_date'] > $filterEnd) {
                continue;
            }

            $start = date('Ymd', strtotime($blockedItem['start_date']));
            $endExclusive = date('Ymd', strtotime($blockedItem['end_date'] . ' +1 day'));
            $created = gmdate('Ymd\THis\Z', strtotime($blockedItem['created_at'] ?? 'now'));
            $uid = 'blocked-' . $blockedItem['id'] . '@zentime.local';
            $label = $blockedItem['label'] ?: 'Booking blocked';

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTAMP:' . $created;
            $lines[] = 'DTSTART;VALUE=DATE:' . $start;
            $lines[] = 'DTEND;VALUE=DATE:' . $endExclusive;
            $lines[] = 'SUMMARY:' . str_replace([',', ';'], ['\,', '\;'], $label);
            $lines[] = 'DESCRIPTION:Blocked booking period';
            $lines[] = 'END:VEVENT';
        }
    }

    $lines[] = 'END:VCALENDAR';

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="zentime-calendar.ics"');
    echo implode("\r\n", $lines);
    exit;
}

// Handle Data Manipulating Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create_request' && $currentRole === 'Employee') {
        $start = $_POST['start_date'] ?? null;
        $end = $_POST['end_date'] ?? null;
        $netDays = $_POST['net_days'] ?? null;
        if ($start && $end && $netDays) {
            $today = date('Y-m-d');
            if ($start < $today || $end < $today) {
                header("Location: /?error=past_date");
                exit;
            }
            $created = VacationRequest::create($currentUser['id'], $start, $end, $netDays);
            if (!$created) {
                header("Location: /?error=request_conflict");
                exit;
            }
            header("Location: /?success=created");
            exit;
        }
    }
    
    if ($action === 'withdraw_request' && $currentRole === 'Employee') {
        if (!empty($_POST['request_id'])) {
            VacationRequest::withdrawRequest($_POST['request_id'], $currentUser['id']);
            header("Location: /?success=action_success");
            exit;
        }
    }

    if ($action === 'request_storno' && $currentRole === 'Employee') {
        if (!empty($_POST['request_id'])) {
            VacationRequest::requestStorno($_POST['request_id'], $currentUser['id']);
            header("Location: /?success=action_success");
            exit;
        }
    }

    if ($action === 'decide_request' && $isAdmin) {
        $requestId = $_POST['request_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $comment = trim((string) ($_POST['admin_comment'] ?? ''));
        if ($requestId && $status) {
            $ok = VacationRequest::decide($requestId, $currentUser['id'], $status, $comment);
            if (!$ok) {
                header("Location: /?error=coverage_conflict");
                exit;
            }
            if ($comment !== '') {
                RequestComment::create((int) $requestId, (int) $currentUser['id'], $comment);
            }
            header("Location: /?success=decided");
            exit;
        }
    }

    if ($action === 'create_employee' && $isAdmin) {
        if (!isset($_POST['mnr']) || trim((string) $_POST['mnr']) === '') {
            header("Location: /?error=invalid_mnr");
            exit;
        }

        $success = User::createEmployee(
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['email'],
            $_POST['mnr'],
            $_POST['password'],
            $_POST['role'] ?? 'Employee',
            ($_POST['department_id'] ?? '') !== '' ? $_POST['department_id'] : null,
            null,
            isset($_POST['vacation_entitlement_days']) ? (int) $_POST['vacation_entitlement_days'] : 25,
            isset($_POST['overtime_hours']) ? (float) $_POST['overtime_hours'] : 0
        );
        header("Location: /?success=" . ($success ? "employee_created" : "employee_failed"));
        exit;
    }

    if ($action === 'edit_employee' && $isAdmin) {
        if (!isset($_POST['mnr']) || trim((string) $_POST['mnr']) === '') {
            header("Location: /?error=invalid_mnr");
            exit;
        }
        User::updateEmployee(
            $_POST['emp_id'],
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['email'],
            $_POST['mnr'],
            $_POST['password'] ?? null,
            $_POST['role'] ?? 'Employee',
            ($_POST['department_id'] ?? '') !== '' ? $_POST['department_id'] : null,
            null,
            isset($_POST['vacation_entitlement_days']) ? (int) $_POST['vacation_entitlement_days'] : 25,
            isset($_POST['overtime_hours']) ? (float) $_POST['overtime_hours'] : 0
        );
        header("Location: /?success=action_success");
        exit;
    }

    if ($action === 'delete_employee' && $isAdmin) {
        $employeeIdToDelete = isset($_POST['emp_id']) ? (int) $_POST['emp_id'] : 0;
        if ($employeeIdToDelete === (int) $currentUser['id']) {
            header("Location: /?error=self_delete_forbidden");
            exit;
        }
        User::deleteEmployee($employeeIdToDelete);
        header("Location: /?success=action_success");
        exit;
    }

    if ($action === 'create_blocked_period' && $isAdmin) {
        $start = $_POST['start_date'] ?? null;
        $end = $_POST['end_date'] ?? null;
        $label = trim($_POST['label'] ?? '');
        if ($start && $end && $end >= $start) {
            $createdBlocked = VacationRequest::createBlockedPeriod($start, $end, $label ?: null, $currentUser['id']);
            if (!$createdBlocked) {
                header("Location: /?error=blocked_exists");
                exit;
            }
            header("Location: /?success=action_success");
            exit;
        }
        header("Location: /?error=invalid_request");
        exit;
    }

    if ($action === 'admin_create_vacation' && $isAdmin) {
        $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $start = $_POST['start_date'] ?? null;
        $end = $_POST['end_date'] ?? null;
        $comment = trim($_POST['admin_comment'] ?? '');
        if ($userId > 0 && $start && $end && $end >= $start) {
            $today = date('Y-m-d');
            if ($start < $today || $end < $today) {
                header("Location: /?error=past_date");
                exit;
            }
            $netDays = (int) ((strtotime($end) - strtotime($start)) / 86400) + 1;
            if ($netDays <= 0) {
                header("Location: /?error=invalid_request");
                exit;
            }
            $created = VacationRequest::createAdminVacation($userId, $currentUser['id'], $start, $end, $netDays, $comment ?: null);
            if (!$created) {
                header("Location: /?error=request_conflict");
                exit;
            }
            if ($comment !== '') {
                RequestComment::create((int) $created, (int) $currentUser['id'], $comment);
            }
            header("Location: /?success=action_success");
            exit;
        }
        header("Location: /?error=invalid_request");
        exit;
    }

    if ($action === 'delete_blocked_period' && $isAdmin) {
        $blockedId = $_POST['blocked_id'] ?? null;
        if ($blockedId) {
            VacationRequest::deleteBlockedPeriod($blockedId);
            header("Location: /?success=action_success");
            exit;
        }
        header("Location: /?error=invalid_request");
        exit;
    }

    if ($action === 'add_request_comment') {
        $requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
        $comment = trim((string) ($_POST['comment'] ?? ''));
        $request = $requestId > 0 ? VacationRequest::getById($requestId) : false;
        $canComment = $request && ($isAdmin || (int) $request['user_id'] === (int) $currentUser['id']);
        if ($canComment && $comment !== '') {
            RequestComment::create($requestId, (int) $currentUser['id'], $comment);
            header("Location: /?success=action_success");
            exit;
        }
        header("Location: /?error=invalid_request");
        exit;
    }

    if ($action === 'update_min_staff' && $isAdmin) {
        header("Location: /?success=action_success");
        exit;
    }

    header("Location: /?error=invalid_request");
    exit;
}

// Data fetching for Views
if ($isAdmin) {
    $requests = VacationRequest::getAll();
    $blockedPeriods = VacationRequest::getBlockedPeriods();
    $employees = User::getAll(); // To show in the team dashboard
    $departments = Department::getAll();

    $selectedTeamUserId = isset($_GET['team_user']) ? (int) $_GET['team_user'] : 0;
    if ($selectedTeamUserId <= 0 && !empty($employees)) {
        $selectedTeamUserId = (int) $employees[0]['id'];
    }
    $selectedTeamUser = null;
    foreach ($employees as $empCandidate) {
        if ((int) $empCandidate['id'] === $selectedTeamUserId) {
            $selectedTeamUser = $empCandidate;
            break;
        }
    }
    if (!$selectedTeamUser && !empty($employees)) {
        $selectedTeamUser = $employees[0];
        $selectedTeamUserId = (int) $selectedTeamUser['id'];
    }

    $selectedTeamUserRequests = [];
    $selectedTeamUserUsedDays = 0;
    if ($selectedTeamUser) {
        foreach ($requests as $reqRow) {
            if ((int) $reqRow['user_id'] !== (int) $selectedTeamUser['id']) {
                continue;
            }
            $selectedTeamUserRequests[] = $reqRow;
            if ($reqRow['status'] === 'approved') {
                $selectedTeamUserUsedDays += (int) $reqRow['net_days'];
            }
        }
    }
} else {
    $requests = VacationRequest::getByUserId($currentUser['id']);
    $blockedPeriods = VacationRequest::getBlockedPeriods();
}

$notificationList = [];
$notificationUnreadCount = 0;
$userVacationStats = VacationRequest::calculateUserVacationStats($currentUser['id']);
$minStaffAvailable = 1;
$requestCommentsById = RequestComment::getByRequestIds(array_column($requests, 'id'));
$recentAuditLogs = [];
$capacitySummary = $isAdmin ? VacationRequest::getCapacitySummary(date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))) : null;

// Prepare FullCalendar events
$fcEvents = [];
foreach ($requests as $r) {
    if ($r['status'] === 'rejected' || $r['status'] === 'cancelled') continue;
    
    $title = ($isAdmin) ? $r['firstname'] . ' ' . $r['lastname'] : I18n::get('emp.plan');
    if ($r['status'] === 'pending') $title .= ' (' . I18n::get('emp.status_pending') . ')';
    if ($r['status'] === 'storno_requested') $title .= ' (' . I18n::get('emp.status_storno_requested') . ')';
    
    // FullCalendar end bounds are exclusive
    $endDateStr = date('Y-m-d', strtotime($r['end_date'] . ' +1 day'));
    
    $color = '#E8007D';
    if ($r['status'] === 'pending') $color = '#FFD600';
    if ($r['status'] === 'storno_requested') $color = '#1a1a1a';
    
    $fcEvents[] = [
        'id' => $r['id'],
        'title' => $title,
        'start' => $r['start_date'],
        'end' => $endDateStr,
        'backgroundColor' => $color,
        'borderColor' => $color,
        'textColor' => ($r['status'] === 'pending') ? '#1a1a1a' : '#fff',
        'allDay' => true,
        'extendedProps' => [
            'status' => $r['status'],
            'requestId' => $r['id']
        ]
    ];
}

foreach ($blockedPeriods as $b) {
    $endDateStr = date('Y-m-d', strtotime($b['end_date'] . ' +1 day'));
    $fcEvents[] = [
        'id' => 'blocked-' . $b['id'],
        'title' => $b['label'] ?: 'Booking blocked',
        'start' => $b['start_date'],
        'end' => $endDateStr,
        'display' => 'background',
        'backgroundColor' => 'rgba(232, 0, 125, 0.16)',
        'borderColor' => 'rgba(26, 26, 26, 0.35)',
        'allDay' => true,
        'extendedProps' => [
            'isBlocked' => true,
            'blockedId' => $b['id'],
            'blockedLabel' => $b['label'] ?? ''
        ]
    ];
}

include __DIR__ . '/../app/Views/layout.php';
