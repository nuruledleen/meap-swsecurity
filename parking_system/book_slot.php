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

// Retrieve values from GET or POST
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

// Validate the form input
if (!validSlot($slot) || !validDate($parkingDate) || !validTime($startTime) || !validTime($endTime)) {
    setFlash('error', 'Invalid booking details were submitted.');
    header('Location: dashboard.php');
    exit();
}

// Ensure booking time is in the future
if (!bookingDateTimeIsFuture($parkingDate, $startTime)) {
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
        // Check if slot is already booked for the same time
        $check = $conn->prepare("SELECT id FROM bookings WHERE slot_id = ? AND parking_date = ? AND start_time = ? AND status = 'ACTIVE'");
        $check->bind_param("iss", $slot, $parkingDate, $startTime);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();

        if ($existing) {
            $error = 'This parking slot has already been reserved for the selected time.';
        } else {
            // Insert booking into the database
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, vehicle_plate, slot_id, parking_date, start_time, end_time, status) 
                                    VALUES (?, ?, ?, ?, ?, ?, 'ACTIVE')");
            $stmt->bind_param("isssss", $_SESSION['user_id'], $vehiclePlate, $slot, $parkingDate, $startTime, $endTime);

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

<!-- Form to book a slot -->
<html>
<head>
  <title>Reserve Slot</title>
  <style>/* Styling omitted for brevity */</style>
</head>
<body>
  <div>
    <h1>Smart Parking</h1>
    <h2>Reserve Parking Slot</h2>

    <?php if (!empty($error)): ?>
      <div class="message"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
      <input type="hidden" name="slot" value="<?php echo e($slot); ?>">
      <input type="hidden" name="parking_date" value="<?php echo e($parkingDate); ?>">
      <input type="hidden" name="start_time" value="<?php echo e($startTime); ?>">
      <input type="hidden" name="end_time" value="<?php echo e($endTime); ?>">

      <!-- Vehicle plate input -->
      <div>
        <input type="text" name="vehicle_plate" maxlength="10" placeholder="Enter vehicle plate number" value="<?php echo e($vehiclePlate); ?>" required>
      </div>

      <div>
        <button type="submit" name="reserve">Confirm Booking</button>
        <a href="dashboard.php">Back</a>
      </div>
    </form>
  </div>
</body>
</html>
