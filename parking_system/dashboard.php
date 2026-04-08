<?php
require_once 'security.php';
require_once 'connect.php';

requireLogin();

$flash = getFlash();
$selectedDate = trim($_GET['parking_date'] ?? '');
$selectedTime = trim($_GET['parking_time'] ?? '');
$availableSlots = [];
$searched = false;

if (isset($_GET['search'])) {
    $searched = true;

    if (!validDate($selectedDate) || !validTime($selectedTime)) {
        $flash = ['type' => 'error', 'message' => 'Please enter a valid date and time.'];
    } elseif (!bookingDateTimeIsFuture($selectedDate, $selectedTime)) {
        $flash = ['type' => 'error', 'message' => 'Please choose a current or future time slot.'];
    } else {
        $bookedSlots = [];
        $stmt = $conn->prepare("SELECT slot_number FROM bookings WHERE parking_date = ? AND parking_time = ? AND status = 'ACTIVE'");
        $stmt->bind_param("ss", $selectedDate, $selectedTime);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $bookedSlots[] = $row['slot_number'];
        }

        $availableSlots = array_values(array_diff(allowedSlots(), $bookedSlots));
    }
}
?>

<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <style>
    @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{min-height:100vh;background:#eef4ff;padding:30px;color:#1f2d3d;}
    .container{max-width:980px;margin:0 auto;}
    .card{background:#fff;border-radius:14px;padding:28px;box-shadow:0 10px 30px rgba(64,112,244,0.12);margin-bottom:24px;}
    h1{font-size:30px;margin-bottom:8px;}
    .subtitle{color:#5f6f81;margin-bottom:18px;}
    .top-links{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px;}
    .btn,.slot-btn{display:inline-block;text-decoration:none;border:none;background:#4070f4;color:#fff;padding:12px 18px;border-radius:8px;cursor:pointer;font-size:14px;}
    .btn.secondary{background:#1f2d3d;}
    .btn.danger{background:#d64545;}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;}
    label{font-weight:600;font-size:14px;display:block;margin-bottom:8px;}
    input{width:100%;padding:12px;border:1px solid #c8d2e1;border-radius:8px;}
    .message{padding:14px 16px;border-radius:10px;margin-bottom:16px;}
    .message.success{background:#e9f8ef;color:#216e39;}
    .message.error{background:#fff1f1;color:#b42318;}
    .slots{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-top:18px;}
    .slot-card{border:1px solid #d8e1f0;border-radius:12px;padding:18px;text-align:center;background:#f9fbff;}
    .slot-card h3{margin-bottom:8px;}
    .slot-card p{font-size:13px;color:#5f6f81;margin-bottom:12px;}
    .empty{padding:16px;background:#f8fbff;border:1px dashed #b8c7e6;border-radius:10px;color:#5f6f81;}
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h1>Welcome, <?php echo e($_SESSION['name']); ?></h1>
      <p class="subtitle">Search available parking slots, reserve a slot, and manage your bookings securely.</p>
      <?php if ($flash): ?>
        <div class="message <?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
      <?php endif; ?>
      <div class="top-links">
        <a class="btn secondary" href="view_booking.php">View My Bookings</a>
        <a class="btn danger" href="logout.php">Logout</a>
      </div>
    </div>

    <div class="card">
      <h2 style="margin-bottom:18px;">Search Available Parking Slot</h2>
      <form method="get" action="">
        <div class="grid">
          <div>
            <label for="parking_date">Parking Date</label>
            <input type="date" id="parking_date" name="parking_date" value="<?php echo e($selectedDate); ?>" required>
          </div>
          <div>
            <label for="parking_time">Parking Time</label>
            <input type="time" id="parking_time" name="parking_time" value="<?php echo e($selectedTime); ?>" required>
          </div>
        </div>
        <div style="margin-top:18px;">
          <button class="btn" type="submit" name="search" value="1">Search Slot</button>
        </div>
      </form>
    </div>

    <?php if ($searched && validDate($selectedDate) && validTime($selectedTime) && bookingDateTimeIsFuture($selectedDate, $selectedTime)): ?>
      <div class="card">
        <h2>Available Slots on <?php echo e($selectedDate); ?> at <?php echo e($selectedTime); ?></h2>
        <?php if ($availableSlots): ?>
          <div class="slots">
            <?php foreach ($availableSlots as $slot): ?>
              <div class="slot-card">
                <h3><?php echo e($slot); ?></h3>
                <p>Ready for reservation</p>
                <a class="slot-btn" href="book_slot.php?slot=<?php echo urlencode($slot); ?>&parking_date=<?php echo urlencode($selectedDate); ?>&parking_time=<?php echo urlencode($selectedTime); ?>">Book Now</a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty">No parking slots are available for the selected time. Please choose another time.</div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>

