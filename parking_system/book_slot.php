<?php
require_once 'security.php';
require_once 'connect.php';

requireLogin();

$slot = '';
$parkingDate = '';
$parkingTime = '';
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

if (isset($_GET['parking_time'])) {
    $parkingTime = trim($_GET['parking_time']);
} elseif (isset($_POST['parking_time'])) {
    $parkingTime = trim($_POST['parking_time']);
}

if (isset($_POST['vehicle_plate'])) {
    $vehiclePlate = trim($_POST['vehicle_plate']);
}

if (!validSlot($slot) || !validDate($parkingDate) || !validTime($parkingTime)) {
    setFlash('error', 'Invalid booking details were submitted.');
    header('Location: dashboard.php');
    exit();
}

if (!bookingDateTimeIsFuture($parkingDate, $parkingTime)) {
    setFlash('error', 'Please choose a current or future booking time.');
    header('Location: dashboard.php');
    exit();
}

if (isset($_POST['reserve'])) {
    verifyCsrf();

    $vehiclePlate = strtoupper(str_replace(' ', '', $vehiclePlate));

    if (!validPlate($vehiclePlate)) {
        $error = 'Vehicle plate number is invalid. Use letters and numbers only.';
    } else {
        $check = $conn->prepare("SELECT id FROM bookings WHERE slot_number = ? AND parking_date = ? AND parking_time = ? AND status = 'ACTIVE'");
        $check->bind_param("sss", $slot, $parkingDate, $parkingTime);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();

        if ($existing) {
            $error = 'This parking slot has already been reserved for the selected time.';
        } else {
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, vehicle_plate, slot_number, parking_date, parking_time, status) VALUES (?, ?, ?, ?, ?, 'ACTIVE')");
            $stmt->bind_param("issss", $_SESSION['user_id'], $vehiclePlate, $slot, $parkingDate, $parkingTime);

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
      <p><strong>Date:</strong> <?php echo e($parkingDate); ?></p>
      <p><strong>Time:</strong> <?php echo e($parkingTime); ?></p>
    </div>

    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
      <input type="hidden" name="slot" value="<?php echo e($slot); ?>">
      <input type="hidden" name="parking_date" value="<?php echo e($parkingDate); ?>">
      <input type="hidden" name="parking_time" value="<?php echo e($parkingTime); ?>">

      <div class="input-box">
        <input type="text" name="vehicle_plate" maxlength="10" placeholder="Enter vehicle plate number" value="<?php echo e($vehiclePlate); ?>" required>
      </div>

      <div class="button-row">
        <button class="btn" type="submit" name="reserve">Confirm Booking</button>
        <a class="btn secondary" href="dashboard.php?parking_date=<?php echo urlencode($parkingDate); ?>&parking_time=<?php echo urlencode($parkingTime); ?>&search=1">Back</a>
      </div>
    </form>
  </div>
</body>
</html>
