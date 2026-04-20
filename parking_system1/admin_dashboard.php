<?php
require_once 'security.php';
require_once 'connect.php';

requireAdmin();
$flash = getFlash();

$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'")->fetch_assoc()['total'];
$totalAdmins = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'")->fetch_assoc()['total'];
$totalBookings = $conn->query("SELECT COUNT(*) AS total FROM bookings")->fetch_assoc()['total'];
$activeBookings = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status = 'ACTIVE'")->fetch_assoc()['total'];
$cancelledBookings = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status = 'CANCELLED'")->fetch_assoc()['total'];

$latest = $conn->query("
    SELECT b.id, u.name, p.slot_number, b.plate_number, b.date, b.start_time, b.end_time, b.status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN parking_slots p ON b.slot_id = p.id
    ORDER BY b.id DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <style>
    @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { min-height:100vh; background:#eef4ff; padding:30px; color:#1f2d3d; }
    .container { max-width:1180px; margin:0 auto; }
    .card { background:#fff; border-radius:14px; padding:28px; box-shadow:0 10px 30px rgba(64,112,244,0.12); margin-bottom:24px; }
    .top-links { display:flex; gap:12px; flex-wrap:wrap; margin-top:18px; }
    .btn {
      display:inline-block; text-decoration:none; border:none; background:#4070f4; color:#fff;
      padding:12px 18px; border-radius:8px; cursor:pointer; font-size:14px;
    }
    .danger { background:#d64545; }
    .stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-top:16px; }
    .stat { padding:18px; border-radius:12px; background:#f7f9fe; border:1px solid #e4eaf5; }
    .stat strong { display:block; margin-bottom:8px; }
    .stat .number { font-size:28px; font-weight:700; color:#365ad8; }
    table { width:100%; border-collapse:collapse; margin-top:16px; }
    th, td { padding:12px; border-bottom:1px solid #e4eaf5; text-align:left; }
    th { background:#f7f9fe; }
    .message { padding:14px 16px; border-radius:10px; margin-bottom:16px; }
    .message.success { background:#e9f8ef; color:#216e39; }
    .message.error { background:#fff1f1; color:#b42318; }
    .badge {
        display:inline-block; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600;
    }
    .ACTIVE { background:#e9f8ef; color:#216e39; }
    .CANCELLED { background:#fff1f1; color:#b42318; }
    .COMPLETED { background:#edf2ff; color:#365ad8; }
  </style>
</head>
<body>
<div class="container">
  <div class="card">
    <h1>Admin Dashboard</h1>
    <p style="color:#5f6f81;margin:8px 0 18px;">Manage users, monitor bookings, and control parking records.</p>

    <?php if ($flash): ?>
      <div class="message <?php echo e($flash['type']); ?>">
        <?php echo e($flash['message']); ?>
      </div>
    <?php endif; ?>

    <div class="top-links">
      <a class="btn" href="manage_users.php">Manage Users</a>
      <a class="btn" href="manage_bookings.php">Manage Bookings</a>
      <a class="btn danger" href="logout.php">Logout</a>
    </div>

    <div class="stats">
      <div class="stat">
        <strong>Total Users</strong>
        <div class="number"><?php echo e($totalUsers); ?></div>
      </div>
      <div class="stat">
        <strong>Total Admins</strong>
        <div class="number"><?php echo e($totalAdmins); ?></div>
      </div>
      <div class="stat">
        <strong>Total Bookings</strong>
        <div class="number"><?php echo e($totalBookings); ?></div>
      </div>
      <div class="stat">
        <strong>Active Bookings</strong>
        <div class="number"><?php echo e($activeBookings); ?></div>
      </div>
      <div class="stat">
        <strong>Cancelled Bookings</strong>
        <div class="number"><?php echo e($cancelledBookings); ?></div>
      </div>
    </div>
  </div>

  <div class="card">
    <h2>Latest Booking Records</h2>
    <table style="font-size:14px;">
      <thead>
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Plate</th>
          <th>Slot</th>
          <th>Date</th>
          <th>Time</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($row = $latest->fetch_assoc()): ?>
        <tr>
          <td><?php echo e($row['id']); ?></td>
          <td><?php echo e($row['name']); ?></td>
          <td><?php echo e($row['plate_number']); ?></td>
          <td><?php echo e($row['slot_number']); ?></td>
          <td><?php echo date("d/m/Y", strtotime($row['date'])); ?></td>
          <td>
            <?php 
              echo date("h:i A", strtotime($row['start_time'])) . 
              " - " . 
              date("h:i A", strtotime($row['end_time']));
            ?>
          </td>
          <td>
            <span class="badge <?php echo e($row['status']); ?>">
              <?php echo e($row['status']); ?>
            </span>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>