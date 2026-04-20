<?php
require_once 'security.php';
startSecureSession();

// 1. Handle OTP Verification
if (isset($_POST['verify_otp'])) {
    $user_input = trim($_POST['otp_code']);
    
    if ($user_input == $_SESSION['generated_otp']) {
        // Promote temporary session to official session
        $_SESSION['user_id'] = $_SESSION['temp_user_id'];
        $_SESSION['name'] = $_SESSION['temp_name'];

        // Clear MFA-related session data
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_name']);
        unset($_SESSION['generated_otp']);

        echo "<script>
                alert('MFA Verified! Welcome to our Smart Parking Reservation System.');
                window.location.href='index.php';
              </script>";
        exit();
    } else {
        echo "<script>alert('Invalid OTP code. Please try again.');</script>";
    }
}

// 2. Handle Resend Request
if (isset($_POST['resend_otp'])) {
    $new_otp = rand(100000, 999999);
    $_SESSION['generated_otp'] = $new_otp;

    echo "<script>
            alert('A new MFA code has been generated: $new_otp');
            window.location.href='otp.php';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFA Verification</title>
    <style>
      @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
      *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
      body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#4070f4;}
      .wrapper{position:relative;max-width:500px;width:100%;background:#fff;padding:34px;border-radius:6px;box-shadow:0 5px 10px rgba(0,0,0,0.2);}
      .wrapper h1{font-size:30px;font-weight:600;color:#333;text-align:center;}
      .wrapper h2{font-size:22px;font-weight:600;color:#333;text-align:center;margin-top:10px;}
      .wrapper p{font-size:14px;color:#555;text-align:center;margin-top:10px;}
      .wrapper form{margin-top:20px;}
      .wrapper form .input-box{height:40px;margin:15px 0;display:flex;justify-content:center;}
      form .input-box input{height:100%;width:90%;outline:none;padding:0 15px;font-size:14px;color:#333;border:1.5px solid #C7BEBE;border-bottom-width:2.5px;border-radius:6px;transition:all 0.3s ease;text-align:center;letter-spacing:4px;}
      .input-box input:focus{border-color:#4070f4;}
      .input-box.button input{color:#fff;letter-spacing:1px;border:none;background:#4070f4;cursor:pointer; font-weight: 500;}
      .input-box.button input:hover{background:#0e4bf1;}
      
      /* Timer & Resend Styles */
      .timer-section { text-align: center; margin-top: 20px; font-size: 14px; color: #333; }
      #resend-btn { background: none; border: none; color: #4070f4; font-weight: 600; cursor: pointer; text-decoration: none; display: none; margin: 0 auto; }
      #resend-btn:hover { text-decoration: underline; }
    </style>
</head>
<body>
  <div class="wrapper">
    <h1>Verification</h1>
    <h2>Enter OTP</h2>
    <p>A 6-digit verification code has been generated for your session.</p>

    <form method="post" action="">
      <div class="input-box">
        <input type="text" name="otp_code" placeholder="000000" pattern="\d{6}" maxlength="6" required autofocus>
      </div>
      <div class="input-box button">
        <input type="submit" name="verify_otp" value="Verify & Login">
      </div>
    </form>

    <div class="timer-section">
        <span id="timer-label">Resend code in: <b id="seconds">15</b>s</span>
        <form method="post" style="margin-top: 0;">
            <button type="submit" name="resend_otp" id="resend-btn">Resend Code</button>
        </form>
    </div>
  </div>

  <script>
    let timeLeft = 15;
    const secondsDisplay = document.getElementById('seconds');
    const timerLabel = document.getElementById('timer-label');
    const resendBtn = document.getElementById('resend-btn');

    const countdown = setInterval(() => {
        timeLeft--;
        secondsDisplay.textContent = timeLeft;

        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerLabel.style.display = 'none'; // Hide the "Resend in..." text
            resendBtn.style.display = 'block'; // Show the clickable button
        }
    }, 1000);
  </script>
</body>
</html>
