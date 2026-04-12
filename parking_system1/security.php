<?php

function startSecureSession()
{
    if (session_status() == PHP_SESSION_NONE) {
        $secure = false;

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $secure = true;
        }

        session_set_cookie_params(0, '/', '', $secure, true);
        session_start();

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}

function csrfToken()
{
    if (isset($_SESSION['csrf_token'])) {
        return $_SESSION['csrf_token'];
    }

    return '';
}

function verifyCsrf()
{
    $token = '';

    if (isset($_POST['csrf_token'])) {
        $token = $_POST['csrf_token'];
    }

    if ($token == '' || !hash_equals(csrfToken(), $token)) {
        exit('Invalid request token.');
    }
}

function requireLogin()
{
    startSecureSession();

    if (isset($_SESSION['user_id'])) {
        return;
    }

    if (isset($_SESSION['temp_user_id'])) {
        header('Location: otp.php');
        exit();
    }

    header('Location: login.php');
    exit();
}

function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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

    if ($check && $check->format('Y-m-d') == $date) {
        return true;
    }

    return false;
}

function validTime($time)
{
    $check = DateTime::createFromFormat('H:i', $time);

    if ($check && $check->format('H:i') == $time) {
        return true;
    }

    return false;
}

function validSlot($slot)
{
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM parking_slots WHERE slot_number = ?");
    $stmt->bind_param("s", $slot);
    $stmt->execute();

    return $stmt->get_result()->num_rows > 0;
}

function setFlash($type, $message)
{
    $_SESSION['flash'] = array(
        'type' => $type,
        'message' => $message
    );
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
