<?php
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $file = __DIR__ . '/../' . lcfirst(str_replace('\\', '/', $class)) . '.php';
        if (file_exists($file)) require $file;
    }
});

use App\Models\User;
use App\Models\Request as VacationRequest;

session_start();

$action = $_GET['action'] ?? null;

// Handle Auth Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $user = User::authenticate($_POST['login'], $_POST['password']);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: /");
    } else {
        header("Location: /?error=login_failed");
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    header("Location: /");
    exit;
}

// Ensure database creates everything on first boot
\App\Core\Database::getConnection();

// Not Logged In View
if (!isset($_SESSION['user_id'])) {
    include __DIR__ . '/../app/Views/login.php';
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

// Handle Data Manipulating Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create_request' && $currentRole === 'Employee') {
        $start = $_POST['start_date'] ?? null;
        $end = $_POST['end_date'] ?? null;
        $netDays = $_POST['net_days'] ?? null;
        if ($start && $end && $netDays) {
            VacationRequest::create($currentUser['id'], $start, $end, $netDays);
            header("Location: /?success=created");
            exit;
        }
    }
    
    if ($action === 'decide_request' && $currentRole === 'CEO') {
        $requestId = $_POST['request_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $comment = $_POST['admin_comment'] ?? null;
        if ($requestId && in_array($status, ['approved', 'rejected'])) {
            VacationRequest::decide($requestId, $currentUser['id'], $status, $comment);
            header("Location: /?success=decided");
            exit;
        }
    }

    if ($action === 'create_employee' && $currentRole === 'CEO') {
        $success = User::createEmployee(
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['email'],
            $_POST['mnr'],
            $_POST['password']
        );
        header("Location: /?success=" . ($success ? "employee_created" : "employee_failed"));
        exit;
    }

    header("Location: /?error=invalid_request");
    exit;
}

// Data fetching for Views
if ($currentRole === 'CEO') {
    $requests = VacationRequest::getAll();
    $employees = User::getAll(); // To show in the team dashboard
} else {
    $requests = VacationRequest::getByUserId($currentUser['id']);
}

include __DIR__ . '/../app/Views/layout.php';
