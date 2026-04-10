<?php
require_once 'security.php';
startSecureSession();

if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['verify_otp'])) {
    $user_input = $_POST['otp_code'];
    
    if ($user_input == $_SESSION['generated_otp']) {
        $_SESSION['user_id'] = $_SESSION['temp_user_id'];
        $_SESSION['name'] = $_SESSION['temp_name'];

        unset($_SESSION['temp_user_id'], $_SESSION['temp_name'], $_SESSION['generated_otp']);

        echo "<script>alert('MFA Verified!'); window.location.href='dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Invalid OTP code!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="wrapper">
        <h2>Two-Factor Authentication</h2>
        <p style="text-align:center; margin-top:10px;">Please enter the 6-digit code sent to your email.</p>
        <form method="post">
            <div class="input-box">
                <input type="text" name="otp_code" placeholder="Enter OTP" required maxlength="6">
            </div>
            <div class="input-box button">
                <input type="submit" name="verify_otp" value="Verify Code">
            </div>
        </form>
    </div>
</body>
</html>
