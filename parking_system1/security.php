<?php
function startSecureSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}

function csrfToken()
{
    return $_SESSION['csrf_token'] ?? '';
}

function verifyCsrf()
{
    $token = $_POST['csrf_token'] ?? '';
    if ($token === '' || !hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        exit('Invalid request token.');
    }
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function validEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validName($name)
{
    return preg_match("/^[A-Za-z' @.-]{3,60}$/", $name);
}

function validPhone($phone)
{
    return preg_match('/^01[0-9]{8,9}$/', $phone);
}

function validPlate($plate)
{
    return preg_match('/^[A-Z0-9]{1,10}$/', $plate);
}

function validDate($date)
{
    $check = DateTime::createFromFormat('Y-m-d', $date);
    return $check && $check->format('Y-m-d') === $date;
}

function validTime($time)
{
    $check = DateTime::createFromFormat('H:i', $time);
    return $check && $check->format('H:i') === $time;
}

function validPasswordStrength($password)
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        && preg_match('/[^A-Za-z0-9]/', $password);
}

function validSlot($slot)
{
    global $conn;
    $stmt = $conn->prepare('SELECT id FROM parking_slots WHERE slot_number = ?');
    $stmt->bind_param('s', $slot);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function requireLogin()
{
    startSecureSession();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin()
{
    requireLogin();
    if (($_SESSION['role'] ?? 'user') !== 'admin') {
        http_response_code(403);
        exit('Access denied. Admin only.');
    }
}

function redirectDashboardByRole()
{
    if (($_SESSION['role'] ?? 'user') === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

function loginAttemptsExceeded()
{
    $maxAttempts = 5;
    $lockSeconds = 300;

    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }

    if (!isset($_SESSION['login_locked_until'])) {
        $_SESSION['login_locked_until'] = 0;
    }

    if ($_SESSION['login_locked_until'] > time()) {
        return true;
    }

    if ($_SESSION['login_attempts'] >= $maxAttempts) {
        $_SESSION['login_locked_until'] = time() + $lockSeconds;
        $_SESSION['login_attempts'] = 0;
        return true;
    }

    return false;
}

function recordFailedLogin()
{
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
}

function clearLoginAttempts()
{
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_locked_until'] = 0;
}
