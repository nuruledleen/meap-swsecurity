<?php
require_once 'security.php';
require_once 'connect.php';

startSecureSession();

if (isset($_SESSION['user_id'])) {
    redirectDashboardByRole();
}

if (isset($_POST['login'])) {
    verifyCsrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (loginAttemptsExceeded()) {
        $error = 'Too many failed login attempts. Please wait 5 minutes.';
    } elseif ($email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!validEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            clearLoginAttempts();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            redirectDashboardByRole();
        }

        recordFailedLogin();
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
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
  <h2>LOGIN</h2>
  <?php if (!empty($error)): ?><div class="message"><?php echo e($error); ?></div><?php endif; ?>
  <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
    <div class="input-box"><input type="email" name="email" placeholder="Enter your email" required></div>
    <div class="input-box"><input type="password" name="password" placeholder="Enter your password" required></div>
    <div class="input-box button"><input type="submit" name="login" value="Login"></div>
    <div class="text"><h3>Don't have an account? <a href="register.php">Register now</a></h3></div>
  </form>
</div>
</body>
</html>
