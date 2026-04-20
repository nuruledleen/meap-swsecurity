<?php
require_once 'security.php';
require_once 'connect.php';

requireLogin();

$flash = getFlash();

// Updated query to fetch slot number and time fields
$stmt = $conn->prepare("SELECT b.id, b.plate_number, p.slot_number, b.date, b.start_time, b.end_time, b.status
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
  <style>
    @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{min-height:100vh;background:#eef4ff;padding:30px;color:#1f2d3d;}
    .container{max-width:1100px;margin:0 auto;}
    .card{background:#fff;border-radius:14px;padding:28px;box-shadow:0 10px 30px rgba(64,112,244,0.12);}
    h1{font-size:30px;margin-bottom:10px;}
    .subtitle{color:#5f6f81;margin-bottom:18px;}
    .message{padding:14px 16px;border-radius:10px;margin-bottom:16px;}
    .message.success{background:#e9f8ef;color:#216e39;}
    .message.error{background:#fff1f1;color:#b42318;}
    .actions{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:22px;}
    .btn{display:inline-block;text-decoration:none;border:none;background:#4070f4;color:#fff;padding:12px 18px;border-radius:8px;cursor:pointer;font-size:14px;}
    .btn.secondary{background:#d64545;}
    table{width:100%;border-collapse:collapse;overflow:hidden;}
    th,td{padding:14px 12px;border-bottom:1px solid #e4eaf5;text-align:left;font-size:14px;}
    th{background:#f7f9fe;}
    .status{font-weight:600;}
    .status.active{color:#216e39;}
    .status.cancelled{color:#b42318;}
    .cancel-btn{background:#d64545;color:#fff;border:none;border-radius:6px;padding:10px 12px;cursor:pointer;}
    .cancel-btn.disabled{background:gray;color:#fff;border:none;border-radius:6px;padding:10px 12px;cursor:default;}
    .empty{padding:18px;border:1px dashed #b8c7e6;border-radius:10px;color:#5f6f81;background:#f8fbff;}
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h1>My Booking Details</h1>
      <p class="subtitle">View your reservation details and cancel any active reservation when needed.</p>

      <?php if ($flash): ?>
        <div class="message <?php echo e($flash['type']); ?>">
          <?php echo e($flash['message']); ?>
        </div>
      <?php endif; ?>

      <div class="actions">
        <a class="btn" href="index.php">Book Another Slot</a>
        <a class="btn secondary" href="logout.php">Logout</a>
      </div>

      <?php if ($bookings->num_rows > 0): ?>
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Vehicle Plate</th>
              <th>Slot</th>
              <th>Date</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Duration</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; ?>
            <?php while ($booking = $bookings->fetch_assoc()): ?>
              
              <?php
                $start = strtotime($booking['start_time']);
                $end = strtotime($booking['end_time']);

                $minutes = ($end - $start) / 60;
                $hours = floor($minutes / 60);
                $mins = $minutes % 60;

                $durationText = $hours . "h " . $mins . "m";

                // Expiry check
                $currentTime = time();
                $endDateTime = strtotime($booking['date'] . ' ' . $booking['end_time']);

                $isExpired = $currentTime > $endDateTime;

                if ($booking['status'] == 'ACTIVE' && $isExpired) {
                    $booking['status'] = 'EXPIRED';
                }

              ?>

              <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo e($booking['plate_number']); ?></td>
                <td><?php echo e($booking['slot_number']); ?></td>
                <td><?php echo date("d/m/Y", strtotime($booking['date'])); ?></td>
                <td><?php echo date("h:i A", strtotime($booking['start_time'])); ?></td>
                <td><?php echo date("h:i A", strtotime($booking['end_time'])); ?></td>
                <td><?php echo $durationText; ?></td>

                
                <td class="status <?php echo strtolower(e($booking['status'])); ?>">
                  <?php echo e($booking['status']); ?>
                </td>

                <td>
                  <?php if ($booking['status'] == 'ACTIVE' && !$isExpired): ?>
                    <form method="post" action="cancel_booking.php" onsubmit="return confirm('Cancel this booking?');">
                      <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                      <input type="hidden" name="booking_id" value="<?php echo e($booking['id']); ?>">
                      <button class="cancel-btn" type="submit">Cancel</button>
                    </form>
                  <?php else: ?>
                    <button class="cancel-btn disabled" disabled>Cancel</button>
                  <?php endif; ?>
                </td>
              </tr>

            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="empty">You do not have any bookings yet.</div>
      <?php endif; ?>

    </div>
  </div>
</body>
</html>
