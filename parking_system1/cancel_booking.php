<?php
require_once 'security.php';
require_once 'connect.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: view_booking.php');
    exit();
}

verifyCsrf();

$bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;

if (!$bookingId) {
    setFlash('error', 'Invalid booking.');
    header('Location: view_booking.php');
    exit();
}

// Get booking info
$stmt = $conn->prepare("
    SELECT date, end_time, status 
    FROM bookings 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $bookingId, $_SESSION['user_id']);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    setFlash('error', 'Booking not found.');
    header('Location: view_booking.php');
    exit();
}

// Check expiry (use END TIME)
$endDateTime = strtotime($data['date'] . ' ' . $data['end_time']);

if ($endDateTime < time()) {
    setFlash('error', 'Cannot cancel expired bookings.');
    header('Location: view_booking.php');
    exit();
}

// Cancel booking
$update = $conn->prepare("
    UPDATE bookings 
    SET status = 'CANCELLED' 
    WHERE id = ? AND user_id = ? AND status = 'ACTIVE'
");
$update->bind_param("ii", $bookingId, $_SESSION['user_id']);
$update->execute();

if ($update->affected_rows > 0) {
    setFlash('success', 'Booking cancelled successfully.');
} else {
    setFlash('error', 'Booking could not be cancelled.');
}

header('Location: view_booking.php');
exit();