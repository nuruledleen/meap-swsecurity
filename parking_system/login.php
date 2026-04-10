<?php
require_once 'security.php';
require_once 'connect.php';

startSecureSession();

if (isset($_POST['login'])) {
    verifyCsrf();

    $email = '';
    $password = '';

    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }

    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }

    if ($email == '' || $password == '') {
        echo "<script>alert('All fields are required!');</script>";
    } elseif (!validEmail($email)) {
        echo "<script>alert('Please enter a valid email address!');</script>";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['temp_user_id'] = $user['id'];
            $_SESSION['temp_name'] = $user['name'];

            $otp = rand(100000, 999999);
            $_SESSION['generated_otp] = $otp;

            echo "<script>
                    alert('MFA Required! Your code is: $otp');
                    window.location.href='otp.php';
                  </script>";
            exit();
        }

        echo "<script>alert('Invalid email or password!');</script>";
    }
}
?>

<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
      @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
      *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
      body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#4070f4;}
      .wrapper{position:relative;max-width:500px;width:100%;background:#fff;padding:34px;border-radius:6px;box-shadow:0 5px 10px rgba(0,0,0,0.2);}
      .wrapper h1{font-size:30px;font-weight:600;color:#333;text-align:center;}
      .wrapper img{display:block;margin:10px auto 20px;}
      .wrapper h2{font-size:22px;font-weight:600;color:#333;text-align:center;}
      .wrapper form{margin-top:20px;}
      .wrapper form .input-box{height:40px;margin:15px 0;display:flex;justify-content:center;}
      form .input-box input{height:100%;width:90%;outline:none;padding:0 15px;font-size:14px;color:#333;border:1.5px solid #C7BEBE;border-bottom-width:2.5px;border-radius:6px;transition:all 0.3s ease;}
      .input-box input:focus,.input-box input:valid{border-color:#4070f4;}
      .input-box.button input{color:#fff;letter-spacing:1px;border:none;background:#4070f4;cursor:pointer;}
      .input-box.button input:hover{background:#0e4bf1;}
      form .text h3{color:#333;width:100%;text-align:center;font-size:14px;font-weight:500;}
      form .text h3 a{color:#4070f4;text-decoration:none;}
      form .text h3 a:hover{text-decoration:underline;}
    </style>
  </head>
<body>
  <div class="wrapper">
    <h1>Smart Parking<br>Reservation System</h1>
    <img src="parking.png" width="100" alt="Parking">
    <h2>LOGIN</h2>
    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
      <div class="input-box">
        <input type="email" name="email" placeholder="Enter your email" required>
      </div>
      <div class="input-box">
        <input type="password" name="password" placeholder="Enter your password" required>
      </div>
      <div class="input-box button">
        <input type="submit" name="login" value="Login">
      </div>
      <div class="text">
        <h3>Don't have an account? <a href="register.php">Register now</a></h3>
      </div>
    </form>
  </div>
</body>
</html>
