<?php
require_once 'security.php';
require_once 'connect.php';

requireLogin();

$flash = getFlash();

// Updated query to fetch slot number and time fields
$stmt = $conn->prepare("SELECT b.id, b.vehicle_plate, p.slot_number, b.parking_date, b.start_time, b.end_time, b.status, b.created_at
                        FROM bookings b
                        JOIN parking_slots p ON b.slot_id = p.id
                        WHERE b.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings</title>
  <style>/* Styling omitted for brevity */</style>
</head>
<body>
  <div>
    <h1>My Booking Details</h1>

    <?php if ($flash): ?>
      <div class="message <?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
    <?php endif; ?>

    <?php if ($bookings->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Vehicle Plate</th>
            <th>Slot</th>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($booking = $bookings->fetch_assoc()): ?>
            <tr>
              <td><?php echo e($booking['id']); ?></td>
              <td><?php echo e($booking['vehicle_plate']); ?></td>
              <td><?php echo e($booking['slot_number']); ?></td>
              <td><?php echo e($booking['parking_date']); ?></td>
              <td><?php echo e(substr($booking['start_time'], 0, 5)); ?></td>
              <td><?php echo e(substr($booking['end_time'], 0, 5)); ?></td>
              <td class="status <?php echo strtolower(e($booking['status'])); ?>"><?php echo e($booking['status']); ?></td>
              <td>
                <?php if ($booking['status'] == 'ACTIVE'): ?>
                  <form method="post" action="cancel_booking.php">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                    <input type="hidden" name="booking_id" value="<?php echo e($booking['id']); ?>">
                    <button type="submit">Cancel</button>
                  </form>
                <?php else: ?>
                  <span>-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div>No bookings found.</div>
    <?php endif; ?>
  </div>
</body>
</html>
