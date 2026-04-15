<?php
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $file = __DIR__ . '/../' . lcfirst(str_replace('\\', '/', $class)) . '.php';
        if (file_exists($file)) require $file;
    }
});

use App\Models\User;
use App\Models\Request as VacationRequest;
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

        if ($action === 'forgot_password') {
            $email = $_POST['email'] ?? '';
            $token = User::generateResetToken($email);
            if ($token) {
                $resetLink = "http://localhost:8000/?action=reset_password&token=" . $token;
                // Since this runs locally without postfix normally, we write to error_log as simulation too
                error_log("RESET LINK: " . $resetLink);
                @mail($email, "Reset your ZenTime password", "Click here to reset your password: $resetLink\n\nIf you did not request this, please ignore.");
            }
            header("Location: /?success=password_reset_sent");
            exit;
        }

        if ($action === 'do_reset_password') {
            $token = $_POST['token'] ?? '';
            $newPassword = $_POST['password'] ?? '';
            $userId = User::verifyResetToken($token);
            if ($userId && $newPassword) {
                User::updatePassword($userId, $newPassword, true);
                User::clearResetToken($userId);
                header("Location: /?success=action_success");
            } else {
                header("Location: /?error=invalid_token");
            }
            exit;
        }
    }

    if ($action === 'reset_password') {
        // Show reset password view
        include __DIR__ . '/../app/Views/login.php';
        exit;
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

// Fetch the full credentials to check must_change_password
$db = \App\Core\Database::getConnection();
$stmt = $db->prepare("SELECT must_change_password FROM user_credentials WHERE user_id = ?");
$stmt->execute([$currentUser['id']]);
$creds = $stmt->fetch();

if ($creds['must_change_password']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'change_password') {
        $newPw = $_POST['password'] ?? '';
        if ($newPw) {
            User::updatePassword($currentUser['id'], $newPw, true);
            header("Location: /");
            exit;
        }
    }
    // Must change password view inside layout
    $requirePasswordChange = true;
    include __DIR__ . '/../app/Views/layout.php';
    exit;
}

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

    if ($action === 'decide_request' && $currentRole === 'CEO') {
        $requestId = $_POST['request_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $comment = $_POST['admin_comment'] ?? null;
        // Allows approving or rejecting a request, or approving/rejecting a Storno request
        // If Storno is approved -> cancelled. If Storno is rejected -> stays approved.
        if ($requestId && $status) {
            VacationRequest::decide($requestId, $currentUser['id'], $status, $comment);
            header("Location: /?success=decided");
            exit;
        }
    }

    if ($action === 'create_employee' && $currentRole === 'CEO') {
        if (!ctype_digit($_POST['mnr'])) {
            header("Location: /?error=invalid_mnr");
            exit;
        }

        $success = User::createEmployee(
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['email'],
            $_POST['mnr'],
            $_POST['password']
        );
        if ($success) {
            $msg = "Hello ".$_POST['firstname'].", your account has been created.\nLogin with Email: ".$_POST['email']." or MNR: ".$_POST['mnr']."\nPassword: ".$_POST['password'];
            @mail($_POST['email'], 'Welcome to ZenTime', $msg);
            error_log("SENT MAIL TO: " . $_POST['email'] . "\n" . $msg);
        }
        header("Location: /?success=" . ($success ? "employee_created" : "employee_failed"));
        exit;
    }

    if ($action === 'edit_employee' && $currentRole === 'CEO') {
        if (!ctype_digit($_POST['mnr'])) {
            header("Location: /?error=invalid_mnr");
            exit;
        }
        User::updateEmployee($_POST['emp_id'], $_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['mnr'], $_POST['password'] ?? null);
        header("Location: /?success=action_success");
        exit;
    }

    if ($action === 'delete_employee' && $currentRole === 'CEO') {
        User::deleteEmployee($_POST['emp_id']);
        header("Location: /?success=action_success");
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
