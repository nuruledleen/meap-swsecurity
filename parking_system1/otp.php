<?php
require_once 'security.php';
startSecureSession();

if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}

$flash = null;

// RESEND OTP
if (isset($_POST['resend_otp'])) {
    $newOtp = rand(100000, 999999);
    $_SESSION['generated_otp'] = $newOtp;

    $flash = array('type' => 'success', 'message' => 'New OTP generated!');
}

// VERIFY OTP
if (isset($_POST['verify_otp'])) {
    $user_input = $_POST['otp_code'];

    if ($user_input == $_SESSION['generated_otp']) {
        $_SESSION['user_id'] = $_SESSION['temp_user_id'];
        $_SESSION['name'] = $_SESSION['temp_name'];

        unset($_SESSION['temp_user_id'], $_SESSION['temp_name'], $_SESSION['generated_otp']);

        echo "<script>alert('MFA Verified!'); window.location.href='index.php';</script>";
        exit();
    } else {
        $flash = array('type' => 'error', 'message' => 'Invalid OTP code!');
    }
}
?>

<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify OTP</title>
  <style>
    @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{min-height:100vh;background:#eef4ff;padding:30px;color:#1f2d3d;display:flex;align-items:center;justify-content:center;}
    .container{max-width:500px;width:100%;}
    .card{background:#fff;border-radius:14px;padding:28px;box-shadow:0 10px 30px rgba(64,112,244,0.12);}
    h2{text-align:center;margin-bottom:10px;}
    .subtitle{text-align:center;color:#5f6f81;margin-bottom:20px;}
    .input-box{margin:15px 0;}
    input{width:100%;padding:12px;border:1px solid #c8d2e1;border-radius:8px;}
    .btn{width:100%;background:#4070f4;color:#fff;border:none;padding:12px;border-radius:8px;cursor:pointer;}
    .btn:hover{background:#2f5be3;}
    .message{padding:14px;border-radius:10px;margin-bottom:16px;text-align:center;}
    .message.error{background:#fff1f1;color:#b42318;}
  </style>
</head>

<body>
  <div class="container">
    <div class="card">
      <h2>Two-Factor Authentication</h2>
      <p class="subtitle">Enter your 6-digit verification code</p>

      <?php if ($flash): ?>
        <div class="message <?php echo $flash['type']; ?>">
          <?php echo $flash['message']; ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="input-box">
          <input type="text" name="otp_code" placeholder="Enter OTP" required maxlength="6">
        </div>

        <button class="btn" type="submit" name="verify_otp">Verify Code</button>
        
      </form>
    </div>
  </div>
</body>
</html>