<?php
require_once 'security.php';
require_once 'connect.php';

requireAdmin();
startSecureSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $action = $_POST['action'] ?? '';
    $bookingId = (int)($_POST['booking_id'] ?? 0);

    if ($bookingId <= 0) {
        setFlash('error', 'Invalid booking selected.');
        header('Location: manage_bookings.php');
        exit();
    }

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $bookingId);

        if ($stmt->execute()) {
            setFlash('success', 'Booking deleted successfully.');
        } else {
            setFlash('error', 'Failed to delete booking.');
        }

        header('Location: manage_bookings.php');
        exit();
    }

    if ($action === 'update_status') {
        $newStatus = strtoupper(trim($_POST['status'] ?? ''));

        if (!in_array($newStatus, ['ACTIVE', 'CANCELLED', 'COMPLETED'], true)) {
            setFlash('error', 'Invalid booking status.');
            header('Location: manage_bookings.php');
            exit();
        }

        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $bookingId);

        if ($stmt->execute()) {
            setFlash('success', 'Booking status updated successfully.');
        } else {
            setFlash('error', 'Failed to update booking status.');
        }

        header('Location: manage_bookings.php');
        exit();
    }
}

$flash = getFlash();
$bookings = $conn->query("
    SELECT b.id, u.name, b.plate_number, p.slot_number, b.date, b.start_time, b.end_time, b.status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN parking_slots p ON b.slot_id = p.id
    ORDER BY b.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background:#eef4ff; padding:30px; color:#1f2d3d; }
        .container { max-width:1280px; margin:0 auto; }
        .card { background:#fff; border-radius:14px; padding:28px; box-shadow:0 10px 30px rgba(64,112,244,0.12); }
        h1 { margin-bottom:10px; }
        p { color:#5f6f81; margin-bottom:18px; }
        .topbar { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
        .btn, button {
            display:inline-block; text-decoration:none; border:none; cursor:pointer;
            background:#4070f4; color:#fff; padding:10px 16px; border-radius:8px; font-size:14px;
        }
        .danger { background:#d64545; }
        .secondary { background:#5f6f81; }
        .success { background:#1f9d55; }
        table { width:100%; border-collapse:collapse; margin-top:18px; }
        th, td { padding:12px; border-bottom:1px solid #e4eaf5; text-align:left; vertical-align:middle; }
        th { background:#f7f9fe; }
        .message { padding:14px 16px; border-radius:10px; margin-bottom:16px; }
        .message.success { background:#e9f8ef; color:#216e39; }
        .message.error { background:#fff1f1; color:#b42318; }
        .action-group { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        select { padding:8px 10px; border:1px solid #d6deeb; border-radius:8px; }
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
        <h1>Manage Bookings</h1>
        <p>Review bookings, update status, and remove records if needed.</p>

        <?php if ($flash): ?>
            <div class="message <?php echo e($flash['type']); ?>">
                <?php echo e($flash['message']); ?>
            </div>
        <?php endif; ?>

        <div class="topbar">
            <a class="btn secondary" href="admin_dashboard.php">Back to Dashboard</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Plate Number</th>
                    <th>Slot</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th width="320">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $bookings->fetch_assoc()): ?>
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
                        <td>
                            <div class="action-group">
                                <form method="POST" style="display:flex; gap:8px; align-items:center;">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo e($row['id']); ?>">
                                    <select name="status">
                                        <option value="ACTIVE" <?php echo $row['status'] === 'ACTIVE' ? 'selected' : ''; ?>>ACTIVE</option>
                                        <option value="CANCELLED" <?php echo $row['status'] === 'CANCELLED' ? 'selected' : ''; ?>>CANCELLED</option>
                                        <option value="COMPLETED" <?php echo $row['status'] === 'COMPLETED' ? 'selected' : ''; ?>>COMPLETED</option>
                                    </select>
                                    <button type="submit" class="success">Update Status</button>
                                </form>

                                <form method="POST" onsubmit="return confirm('Delete this booking?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="booking_id" value="<?php echo e($row['id']); ?>">
                                    <button type="submit" class="danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>