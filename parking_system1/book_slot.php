<?php
require_once 'security.php';
require_once 'connect.php';

requireLogin();

$slot = '';
$parkingDate = '';
$startTime = '';
$endTime = '';
$vehiclePlate = '';
$error = '';

if (isset($_GET['slot'])) {
    $slot = trim($_GET['slot']);
} elseif (isset($_POST['slot'])) {
    $slot = trim($_POST['slot']);
}

if (isset($_GET['parking_date'])) {
    $parkingDate = trim($_GET['parking_date']);
} elseif (isset($_POST['parking_date'])) {
    $parkingDate = trim($_POST['parking_date']);
}

if (isset($_GET['start_time'])) {
    $startTime = trim($_GET['start_time']);
} elseif (isset($_POST['start_time'])) {
    $startTime = trim($_POST['start_time']);
}

if (isset($_GET['end_time'])) {
    $endTime = trim($_GET['end_time']);
} elseif (isset($_POST['end_time'])) {
    $endTime = trim($_POST['end_time']);
}

if (isset($_POST['vehicle_plate'])) {
    $vehiclePlate = trim($_POST['vehicle_plate']);
}

if (!validSlot($slot) || !validDate($parkingDate) || !validTime($startTime) || !validTime($endTime)) {
    setFlash('error', 'Invalid booking details were submitted.');
    header('Location: index.php');
    exit();
}

if ($endTime <= $startTime) {
    setFlash('error', 'End time must be after start time.');
    header('Location: index.php');
    exit();
}

if ($parkingDate < date("Y-m-d") || 
   ($parkingDate == date("Y-m-d") && $startTime < date("H:i"))) {

    setFlash('error', 'Start time must be in the future.');
    header('Location: index.php');
    exit();
}

if (isset($_POST['reserve'])) {
    verifyCsrf();

    $vehiclePlate = strtoupper(str_replace(' ', '', $vehiclePlate));

    if (!validPlate($vehiclePlate)) {
        $error = 'Vehicle plate number is invalid. Use letters and numbers only.';
    } else {
        $check = $conn->prepare("
            SELECT b.id 
            FROM bookings b
            JOIN parking_slots p ON b.slot_id = p.id
            WHERE p.slot_number = ?
            AND b.date = ?
            AND b.status = 'ACTIVE'
            AND (? < b.end_time AND ? > b.start_time)
        ");
        $check->bind_param("ssss", $slot, $parkingDate, $endTime, $startTime);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = 'This parking slot has already been reserved for the selected time.';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO bookings (user_id, slot_id, plate_number, date, start_time, end_time, status) 
                VALUES (?, (SELECT id FROM parking_slots WHERE slot_number=?), ?, ?, ?, ?, 'ACTIVE')
            ");

            $stmt->bind_param(
                "isssss",
                $_SESSION['user_id'],
                $slot,
                $vehiclePlate,
                $parkingDate,
                $startTime,
                $endTime
            );

            if ($stmt->execute()) {
                setFlash('success', 'Parking slot reserved successfully.');
                header('Location: view_booking.php');
                exit();
            }

            $error = 'Unable to complete the reservation right now.';
        }
    }
}
?>

<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reserve Slot</title>
  <style>
    @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#4070f4;padding:24px;}
    .wrapper{max-width:560px;width:100%;background:#fff;padding:34px;border-radius:12px;box-shadow:0 12px 24px rgba(0,0,0,0.18);}
    h1{text-align:center;color:#333;font-size:30px;margin-bottom:8px;}
    h2{text-align:center;color:#333;font-size:22px;margin-bottom:18px;}
    .summary{background:#f4f7ff;border:1px solid #dbe4ff;border-radius:10px;padding:16px;margin-bottom:18px;color:#344054;}
    .summary p{margin-bottom:8px;}
    .input-box{margin:15px 0;}
    .input-box input{height:46px;width:100%;outline:none;padding:0 15px;font-size:14px;color:#333;border:1.5px solid #C7BEBE;border-bottom-width:2.5px;border-radius:6px;transition:all 0.3s ease;}
    .input-box input:focus,.input-box input:valid{border-color:#4070f4;}
    .button-row{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px;}
    .btn{flex:1;text-align:center;padding:12px 16px;border:none;border-radius:8px;text-decoration:none;background:#4070f4;color:#fff;cursor:pointer;}
    .btn.secondary{background:#5f6f81;}
    .message{padding:12px 14px;border-radius:10px;margin-bottom:14px;background:#fff1f1;color:#b42318;}
  </style>
</head>
<body>
  <div class="wrapper">
    <h1>Smart Parking</h1>
    <h2>Reserve Parking Slot</h2>

    <?php if (!empty($error)): ?>
      <div class="message"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="summary">
      <p><strong>Slot:</strong> <?php echo e($slot); ?></p>
      <p><strong>Date:</strong> <?php echo date("d/m/Y", strtotime($parkingDate)); ?></p>
      <p><strong>Time:</strong> <?php echo date("h:i A", strtotime($startTime)); ?> - <?php echo date("h:i A", strtotime($endTime)); ?></p>
    </div>

    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
      <input type="hidden" name="slot" value="<?php echo e($slot); ?>">
      <input type="hidden" name="parking_date" value="<?php echo e($parkingDate); ?>">
      <input type="hidden" name="start_time" value="<?php echo e($startTime); ?>">
    <input type="hidden" name="end_time" value="<?php echo e($endTime); ?>">

      <div class="input-box">
        <input type="text" name="vehicle_plate" maxlength="10" placeholder="Enter vehicle plate number" value="<?php echo e($vehiclePlate); ?>" required>
      </div>

      <div class="button-row">
        <button class="btn" type="submit" name="reserve">Confirm Booking</button>
        <a class="btn secondary" href="index.php?parking_date=<?php echo urlencode($parkingDate); ?>&start_time=<?php echo urlencode($startTime); ?>&end_time=<?php echo urlencode($endTime); ?>&search=1">Back</a>
      </div>
    </form>
  </div>
</body>
</html>