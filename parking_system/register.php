<?php
include 'connect.php';

if(isset($_POST['register'])){

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_pass'];

    // Validation
    if(empty($name) || empty($phone) || empty($email) || empty($password)){
        echo "All fields are required!";
        exit();
    }

    if($password !== $confirm){
        echo "<script>alert('Passwords do not match!');</script>";
        exit();
    }

    if(strlen($password) < 6){
        echo "<script>alert('Password must be at least 6 characters!');</script>";
        exit();
    }

    // Malaysia phone validation
    if(!preg_match("/^01[0-9]{8,9}$/", $phone)){
        echo "<script>alert('Invalid Malaysian phone number!');</script>";
        exit();
    }

    // check duplicate email or phone
    $check = $conn->prepare("SELECT * FROM users WHERE email=? OR phone=?");
    $check->bind_param("ss", $email, $phone);
    $check->execute();
    $result = $check->get_result();

    if($result->num_rows > 0){
        echo "<script>alert('Email or phone already exists!');</script>";
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);

    if($stmt->execute()){
    echo "<script>
            alert('Registration successful!');
            window.location.href='login.php';
          </script>";
    } else {
        echo "<script>alert('Error occurred!');</script>";
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
*{
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}
body{
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #4070f4;
}
.wrapper{
  position: relative;
  max-width: 500px;
  width: 100%;
  background: #fff;
  padding: 34px;
  border-radius: 6px;
  box-shadow: 0 5px 10px rgba(0,0,0,0.2);
}
.wrapper h1{
  position: relative;
  font-size: 30px;
  font-weight: 600;
  color: #333;
  text-align: center;
}

.wrapper img{
    display: block;
    margin: 0 auto;
    margin-top: 10px;
    margin-bottom: 20px;
}

.wrapper h2{
  position: relative;
  font-size: 22px;
  font-weight: 600;
  color: #333;
  text-align: center;
}

.wrapper form{
  margin-top: 20px;
}
.wrapper form .input-box{
  height: 40px;
  margin: 15px 0;
  display: flex;
  justify-content: center;
  
}
form .input-box input{
  height: 100%;
  width: 90%;
  outline: none;
  padding: 0 15px;
  font-size: 14px;
  font-weight: 400;
  color: #333;
  border: 1.5px solid #C7BEBE;
  border-bottom-width: 2.5px;
  border-radius: 6px;
  transition: all 0.3s ease;
}
.input-box input:focus,
.input-box input:valid{
  border-color: #4070f4;
}
form .policy{
  display: flex;
  align-items: center;
}
form h3{
  color: #707070;
  font-size: 14px;
  font-weight: 500;
  margin-left: 10px;
}
.input-box.button input{
  color: #fff;
  letter-spacing: 1px;
  border: none;
  background: #4070f4;
  cursor: pointer;
}
.input-box.button input:hover{
  background: #0e4bf1;
}
form .text h3{
 color: #333;
 width: 100%;
 text-align: center;
}
form .text h3 a{
  color: #4070f4;
  text-decoration: none;
}
form .text h3 a:hover{
  text-decoration: underline;
}
    </style>
   </head>
<body>
  <div class="wrapper">
  <h1>Smart Parking<br>Reservation System</h1>
  <img src="parking.png" width=100>  
  <h2>REGISTRATION</h2>
    <form method="post" action="">
      <div class="input-box">
        <input type="text" name="name" placeholder="Enter your name" required>
      </div>
        <div class="input-box">
        <input type="tel" name="phone" placeholder="Enter your phone number" pattern="^01[0-9]{8,9}$" required>
      </div>
      <div class="input-box">
        <input type="text" name="email" placeholder="Enter your email" required>
      </div>
      <div class="input-box">
        <input type="password" name="password" placeholder="Create password" required>
      </div>
      <div class="input-box">
        <input type="password" name="confirm_pass" placeholder="Confirm password" required>
      </div>
      <div class="input-box button">
        <input type="Submit" name="register" value="Register Now">
      </div>
      <div class="text">
        <h3>Already have an account? <a href="login.php">Login now</a></h3>
      </div>
    </form>
  </div>
</body>
</html>