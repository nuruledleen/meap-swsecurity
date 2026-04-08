<?php

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function csrfToken(): string
{
    return $_SESSION['csrf_token'] ?? '';
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!$token || !hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        exit('Invalid request token.');
    }
}

function requireLogin(): void
{
    startSecureSession();

    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function validEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validName(string $name): bool
{
    return (bool) preg_match("/^[A-Za-z' @.-]{3,60}$/", $name);
}

function validPhone(string $phone): bool
{
    return (bool) preg_match('/^01[0-9]{8,9}$/', $phone);
}

function validPlate(string $plate): bool
{
    return (bool) preg_match('/^[A-Z0-9]{1,10}$/', $plate);
}

function validDate(string $date): bool
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}

function validTime(string $time): bool
{
    $parsed = DateTime::createFromFormat('H:i', $time);
    return $parsed && $parsed->format('H:i') === $time;
}

function bookingDateTimeIsFuture(string $date, string $time): bool
{
    $booking = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
    return $booking instanceof DateTime && $booking >= new DateTime();
}

function allowedSlots(): array
{
    return ['A1', 'A2', 'A3', 'A4', 'B1', 'B2', 'B3', 'B4'];
}

function validSlot(string $slot): bool
{
    return in_array($slot, allowedSlots(), true);
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

