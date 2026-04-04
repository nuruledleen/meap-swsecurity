<?php
session_start();
include 'connect.php';

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    // 🔐 Validation
    if(empty($email) || empty($password)){
        echo "<script>alert('All fields are required!');</script>";
        exit();
    }

    // Get user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check password
    if($user && password_verify($password, $user['password'])){

        // ✅ Store session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];

        // ✅ Redirect to dashboard
        echo "<script>
                alert('Login successful!');
                window.location.href='dashboard.php';
              </script>";

    } else {
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
  <h2>LOGIN</h2>
    <form method="post" action="">
      <div class="input-box">
        <input type="text" name="email" placeholder="Enter your email" required>
      </div>
      <div class="input-box">
        <input type="password" name="password" placeholder="Enter your password" required>
      </div>
      <div class="input-box button">
        <input type="Submit" name="login" value="Login">
      </div>
    </form>
  </div>
</body>
</html>