<?php
require_once 'security.php';
require_once 'connect.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: view_booking.php');
    exit();
}

verifyCsrf();

$bookingId = 0;

if (isset($_POST['booking_id'])) {
    $bookingId = (int) $_POST['booking_id'];
}

if (!$bookingId) {
    setFlash('error', 'Invalid booking selected.');
    header('Location: view_booking.php');
    exit();
}

$checkTime = $conn->prepare("
    SELECT date, start_time FROM bookings 
    WHERE id = ? AND user_id = ?
");
$checkTime->bind_param("ii", $bookingId, $_SESSION['user_id']);
$checkTime->execute();
$data = $checkTime->get_result()->fetch_assoc();

if ($data) {
    $bookingDateTime = strtotime($data['date'] . ' ' . $data['start_time']);
    if ($bookingDateTime < time()) {
        setFlash('error', 'Cannot cancel past bookings.');
        header('Location: view_booking.php');
        exit();
    }
}

// Cancel the booking
$stmt = $conn->prepare("UPDATE bookings SET status = 'CANCELLED' WHERE id = ? AND user_id = ? AND status = 'ACTIVE'");
$stmt->bind_param("ii", $bookingId, $_SESSION['user_id']);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    setFlash('success', 'Booking cancelled successfully.');
} else {
    setFlash('error', 'Booking could not be cancelled.');
}

header('Location: view_booking.php');
exit();
?>
