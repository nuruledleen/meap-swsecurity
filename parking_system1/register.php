<?php
require_once 'security.php';
require_once 'connect.php';

startSecureSession();

if (isset($_POST['register'])) {
    verifyCsrf();

    $name = '';
    $phone = '';
    $email = '';
    $password = '';
    $confirm = '';

    if (isset($_POST['name'])) {
        $name = trim($_POST['name']);
    }

    if (isset($_POST['phone'])) {
        $phone = preg_replace('/\s+/', '', $_POST['phone']);
    }

    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }

    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }

    if (isset($_POST['confirm_pass'])) {
        $confirm = $_POST['confirm_pass'];
    }

    if ($name == '' || $phone == '' || $email == '' || $password == '' || $confirm == '') {
        echo "<script>alert('All fields are required!');</script>";
    } elseif (!validName($name)) {
        echo "<script>alert('Name should be 3 to 60 characters and contain only valid letters.');</script>";
    } elseif (!validPhone($phone)) {
        echo "<script>alert('Invalid Malaysian phone number!');</script>";
    } elseif (!validEmail($email)) {
        echo "<script>alert('Invalid email address!');</script>";
    } elseif ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!');</script>";
    } elseif (!validPassword($password)) {
        echo "<script>alert('Password must be at least 8 characters long and include at least one letter and one symbol (e.g., @, #, $)!');</script>";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $check->bind_param("ss", $email, $phone);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Email or phone already exists!');</script>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);

            if ($stmt->execute()) {
                echo "<script>
                        alert('Registration successful!');
                        window.location.href='login.php';
                      </script>";
                exit();
            }

            echo "<script>alert('Error occurred! Please try again.');</script>";
        }
    }
}
?>

<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
    <h2>REGISTRATION</h2>
    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
      <div class="input-box">
        <input type="text" name="name" placeholder="Enter your name" required>
      </div>
      <div class="input-box">
        <input type="tel" name="phone" placeholder="Enter your phone number" pattern="^01[0-9]{8,9}$" required>
      </div>
      <div class="input-box">
        <input type="email" name="email" placeholder="Enter your email" required>
      </div>
      <div class="input-box">
        <input type="password" name="password" placeholder="Create password" required>
      </div>
      <div class="input-box">
        <input type="password" name="confirm_pass" placeholder="Confirm password" required>
      </div>
      <div class="input-box button">
        <input type="submit" name="register" value="Register Now">
      </div>
      <div class="text">
        <h3>Already have an account? <a href="login.php">Login now</a></h3>
      </div>
    </form>
  </div>
</body>
</html>
