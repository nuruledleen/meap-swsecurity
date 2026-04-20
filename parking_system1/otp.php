<?php
require_once 'security.php';
require_once 'connect.php';

startSecureSession();
$purpose = $_GET['purpose'] ?? 'register';

if ($purpose !== 'register' || !isset($_SESSION['pending_registration'])) {
    header('Location: register.php');
    exit();
}

if (isset($_POST['verify_otp'])) {
    verifyCsrf();
    $userInput = trim($_POST['otp_code'] ?? '');

    if (time() > ($_SESSION['registration_otp_expiry'] ?? 0)) {
        $error = 'OTP expired. Please register again.';
    } elseif ($userInput === ($_SESSION['registration_otp'] ?? '')) {
        $pending = $_SESSION['pending_registration'];
        $role = 'user';

        $stmt = $conn->prepare('INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $pending['name'], $pending['email'], $pending['phone'], $pending['password'], $role);
        $stmt->execute();

        unset($_SESSION['pending_registration'], $_SESSION['registration_otp'], $_SESSION['registration_otp_expiry']);
        echo "<script>alert('Registration successful. Please login now.'); window.location.href='login.php';</script>";
        exit();
    } else {
        $error = 'Invalid OTP code.';
    }
}

if (isset($_POST['resend_otp'])) {
    verifyCsrf();
    $newOtp = (string)random_int(100000, 999999);
    $_SESSION['registration_otp'] = $newOtp;
    $_SESSION['registration_otp_expiry'] = time() + 300;
    echo "<script>alert('New registration OTP: {$newOtp}'); window.location.href='otp.php?purpose=register';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration OTP</title>
  <style>
    @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#4070f4;}
    .wrapper{position:relative;max-width:500px;width:100%;background:#fff;padding:34px;border-radius:6px;box-shadow:0 5px 10px rgba(0,0,0,0.2);}
    .wrapper h1,.wrapper h2,.wrapper p{text-align:center;color:#333;}
    .wrapper h2{margin-top:10px;}
    .wrapper p{font-size:14px;margin-top:10px;color:#555;}
    .input-box{height:44px;margin:15px 0;display:flex;justify-content:center;}
    .input-box input{height:100%;width:90%;outline:none;padding:0 15px;font-size:14px;color:#333;border:1.5px solid #C7BEBE;border-bottom-width:2.5px;border-radius:6px;text-align:center;letter-spacing:4px;}
    .input-box.button input,.btn-alt{color:#fff;border:none;background:#4070f4;cursor:pointer;letter-spacing:1px;}
    .message{width:90%;margin:12px auto;background:#fff1f1;color:#b42318;padding:12px;border-radius:8px;font-size:14px;}
    .btn-alt{display:block;width:90%;margin:0 auto;padding:12px;border-radius:6px;}
  </style>
</head>
<body>
<div class="wrapper">
  <h1>Registration Verification</h1>
  <h2>Enter OTP</h2>
  <p>This OTP is only used during account registration.</p>
  <?php if (!empty($error)): ?><div class="message"><?php echo e($error); ?></div><?php endif; ?>
  <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
    <div class="input-box"><input type="text" name="otp_code" placeholder="000000" pattern="\d{6}" maxlength="6" required autofocus></div>
    <div class="input-box button"><input type="submit" name="verify_otp" value="Verify Registration"></div>
  </form>
  <form method="post" action="" style="margin-top:10px;">
    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
    <button type="submit" name="resend_otp" class="btn-alt">Resend OTP</button>
  </form>
</div>
</body>
</html>
