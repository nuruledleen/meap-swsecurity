<?php
require_once 'security.php';
require_once 'connect.php';

startSecureSession();

if (isset($_SESSION['user_id'])) {
    redirectDashboardByRole();
}

if (isset($_POST['register'])) {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $phone = preg_replace('/\s+/', '', $_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_pass'] ?? '';

    if ($name === '' || $phone === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'All fields are required.';
    } elseif (!validName($name)) {
        $error = 'Name should be 3 to 60 characters and contain only valid letters.';
    } elseif (!validPhone($phone)) {
        $error = 'Invalid Malaysian phone number.';
    } elseif (!validEmail($email)) {
        $error = 'Invalid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!validPasswordStrength($password)) {
        $error = 'Password must contain uppercase, lowercase, number and symbol.';
    } else {
        $check = $conn->prepare('SELECT id FROM users WHERE email = ? OR phone = ?');
        $check->bind_param('ss', $email, $phone);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = 'Email or phone already exists.';
        } else {
            $otp = random_int(100000, 999999);
            $_SESSION['registration_otp'] = (string)$otp;
            $_SESSION['registration_otp_expiry'] = time() + 300;
            $_SESSION['pending_registration'] = [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ];

            echo "<script>alert('Registration OTP: {$otp}'); window.location.href='otp.php?purpose=register';</script>";
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <style>
    @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#4070f4;padding:20px;}
    .wrapper{position:relative;max-width:500px;width:100%;background:#fff;padding:34px;border-radius:6px;box-shadow:0 5px 10px rgba(0,0,0,0.2);}
    .wrapper h1,.wrapper h2{text-align:center;color:#333;}
    .wrapper h1{font-size:30px;}
    .wrapper h2{font-size:22px;margin-top:12px;}
    .wrapper img{display:block;margin:10px auto 20px;}
    .input-box{height:44px;margin:15px 0;display:flex;justify-content:center;}
    .input-box input{height:100%;width:90%;outline:none;padding:0 15px;font-size:14px;color:#333;border:1.5px solid #C7BEBE;border-bottom-width:2.5px;border-radius:6px;}
    .input-box.button input{color:#fff;border:none;background:#4070f4;cursor:pointer;}
    .message{width:90%;margin:0 auto 12px;background:#fff1f1;color:#b42318;padding:12px;border-radius:8px;font-size:14px;}
    .text h3{text-align:center;font-size:14px;font-weight:500;}
    .text a{color:#4070f4;text-decoration:none;}
  </style>
</head>
<body>
<div class="wrapper">
  <h1>Smart Parking<br>Reservation System</h1>
  <img src="parking.png" width="100" alt="Parking">
  <h2>REGISTRATION</h2>
  <?php if (!empty($error)): ?><div class="message"><?php echo e($error); ?></div><?php endif; ?>
  <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
    <div class="input-box"><input type="text" name="name" placeholder="Enter your name" required></div>
    <div class="input-box"><input type="tel" name="phone" placeholder="Enter your phone number" pattern="^01[0-9]{8,9}$" required></div>
    <div class="input-box"><input type="email" name="email" placeholder="Enter your email" required></div>
    <div class="input-box"><input type="password" name="password" placeholder="Create password" required></div>
    <div class="input-box"><input type="password" name="confirm_pass" placeholder="Confirm password" required></div>
    <div class="input-box button"><input type="submit" name="register" value="Register Now"></div>
    <div class="text"><h3>Already have an account? <a href="login.php">Login now</a></h3></div>
  </form>
</div>
</body>
</html>
