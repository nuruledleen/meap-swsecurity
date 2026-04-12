<?php
require_once 'security.php';
require_once 'connect.php';

requireLogin();

$flash = getFlash();
$selectedDate = '';
$startTime = '';
$endTime = '';
$availableSlots = array();
$searched = false;

if (isset($_GET['parking_date'])) {
    $selectedDate = trim($_GET['parking_date']);
}

if (isset($_GET['start_time'])) {
    $startTime = trim($_GET['start_time']);
}

if (isset($_GET['end_time'])) {
    $endTime = trim($_GET['end_time']);
}

if (isset($_GET['search'])) {
    $searched = true;

    if (!validDate($selectedDate) || !validTime($startTime) || !validTime($endTime)) {
        $flash = ['type'=>'error','message'=>'Please enter valid date and time'];
    }
    elseif ($endTime <= $startTime) {
        $flash = ['type'=>'error','message'=>'End time must be after start time'];
    }
    elseif ($selectedDate < date("Y-m-d") || 
          ($selectedDate == date("Y-m-d") && $endTime <= date("H:i"))) {
        $flash = ['type'=>'error','message'=>'Please choose a future time range.'];
    }
    else {
        $bookedSlots = array();
        $bookedInfo = [];

        $slotsResult = $conn->query("SELECT slot_number FROM parking_slots");

        $allSlots = [];
        while ($row = $slotsResult->fetch_assoc()) {
            $allSlots[] = $row['slot_number'];
        }

        $stmt = $conn->prepare("
          SELECT p.slot_number, b.start_time, b.end_time
          FROM bookings b
          JOIN parking_slots p ON b.slot_id = p.id
          WHERE b.date = ?
          AND b.status = 'ACTIVE'
          AND (
                TIME(?) < b.end_time
                AND TIME(?) > b.start_time
              )
        ");

        $stmt->bind_param("sss", $selectedDate, $startTime, $endTime);

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $bookedSlots[] = $row['slot_number'];

            $bookedInfo[$row['slot_number']] =
                "Booked from " . date("h:i A", strtotime($row['start_time'])) . 
                " to " . date("h:i A", strtotime($row['end_time']));
        }

        $availableSlots = $allSlots; // show ALL slots
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
    .booked {background: grey !important; cursor: not-allowed; opacity: 0.8;}
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
              <label for="start_time">Start Time</label>
              <input type="time" id="start_time" name="start_time" required>
            </div>
            <div>
              <label for="end_time">End Time</label>
              <input type="time" id="end_time" name="end_time" required>
            </div>
        </div>
        <div style="margin-top:18px;">
          <button class="btn" type="submit" name="search" value="1">Search Slot</button>
        </div>
      </form>
    </div>

    <?php if ($searched && validDate($selectedDate) && validTime($startTime) && validTime($endTime)): ?>
      <div class="card">
        <h2>
        Available Slots on 
        <?php echo date("d/m/Y", strtotime($selectedDate)); ?> 
        from <?php echo date("h:i A", strtotime($startTime)); ?> 
        to <?php echo date("h:i A", strtotime($endTime)); ?>
        </h2>
        <?php if ($availableSlots): ?>
          <div class="slots">
            <?php foreach ($availableSlots as $slot): ?>
              <div class="slot-card">
                <h3><?php echo e($slot); ?></h3>

                <?php if (isset($bookedInfo[$slot])): ?>
                  <p style="color:red; font-weight:600;">Unavailable</p>
                  <button class="slot-btn booked" disabled>Booked</button>
                <?php else: ?>
                  <p style="color:green;">Available</p>
                  <a class="slot-btn" href="book_slot.php?slot=<?php echo urlencode($slot); ?>&parking_date=<?php echo urlencode($selectedDate); ?>&start_time=<?php echo urlencode($startTime); ?>&end_time=<?php echo urlencode($endTime); ?>">
                      Book Now
                  </a>
              <?php endif; ?>
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
