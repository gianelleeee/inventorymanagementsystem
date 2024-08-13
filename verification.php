<?php 
session_start();
include('database/connection.php'); // Ensure the connection file path is correct
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to generate and send OTP using PHPMailer
function generateAndSendOTP($email) {
    $otp = rand(100000, 999999); // Generate a new 6-digit OTP
    $_SESSION['otp'] = $otp;

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = 'gianelleeee@gmail.com'; // Replace with your email
        $mail->Password = 'wylkejnrpmrctsyy'; // Replace with your email password

        // Recipients
        $mail->setFrom('gianelleeee@gmail.com', 'OTP Verification'); // Replace with your email
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your verification code';
        $mail->Body = "<p>Dear user, </p> <h3>Your new verification OTP code is $otp</h3>";

        // Send email
        if ($mail->send()) {
            echo '<script>
                    alert("A new OTP has been sent to ' . $email . '");
                  </script>';
        } else {
            echo '<script>
                    alert("Failed to send OTP. Please try again later.");
                  </script>';
        }

    } catch (Exception $e) {
        echo '<script>
                alert("Mailer Error: ' . $mail->ErrorInfo . '");
              </script>';
    }
}

// Handle form submission for OTP verification
if (isset($_POST["verify"])) {
    $otp = trim($_SESSION['otp'] ?? '');
    $email = $_SESSION['mail'] ?? null;
    $otp_code = trim($_POST['otp_code'] ?? '');

    if ($otp === $otp_code) {
        try {
            // Update user's status to verified
            $stmt = $conn->prepare("UPDATE users SET status = 1 WHERE email = :email");
            $stmt->bindParam(':email', $email);
    
            if ($stmt->execute()) {
                // Clear OTP session variables
                unset($_SESSION['otp']);
                unset($_SESSION['mail']);
    
                echo '<script>
                        alert("Account verified successfully, you may sign in now.");
                        window.location.replace("index.php");
                      </script>';
            } else {
                echo '<script>alert("Failed to verify account. Please try again.");</script>';
            }
        } catch (PDOException $e) {
            echo '<script>alert("Database error: ' . $e->getMessage() . '");</script>';
        }
    } else {
        echo '<script>alert("Invalid OTP code");</script>';
    }
}

// Handle Resend OTP request through the link
if (isset($_GET['resend_otp'])) {
    $email = $_SESSION['mail'] ?? null;
    if ($email) {
        generateAndSendOTP($email);
    } else {
        echo '<script>alert("No email found. Please try again.");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification</title>

    <link rel="stylesheet" href="css/stylesheet.css">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>

    <style>
        .text-center {
            text-align: center;
        }

        .form-container {
            background: none;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }

        .loginButtonContainer {
            margin-top: 20px;
        }

        h2, p {
            color: white;
        }

        .register-link a:hover {
            color: #649037;
        }

        .resend-link {
            color: #649037;
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
</head>
<body id="loginBody">
    <div class="container">
        <div class="loginHeader">
            <h1>IMS</h1>
            <p>INVENTORY MANAGEMENT SYSTEM</p>
        </div>

        <div class="registerBody form-container" id="userAddFormContainer">
            <form action="" method="POST" class="appForm">
                <h2 class="text-center">OTP Verification</h2>
                <div class="loginInputsContainer">
                    <label for="otp">Enter OTP Code:</label>
                    <input type="text" class="appFormInput" name="otp_code" id="otp" required autofocus>
                </div>

                <div class="loginButtonContainer">
                    <button type="submit" name="verify">Verify</button>
                </div>
                
                <div class="text-center" style="margin-top: 10px;">
                    <a href="?resend_otp=1" class="resend-link">Resend OTP</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
