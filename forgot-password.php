<?php
session_start();
include('database/connection.php'); // Ensure the connection file path is correct
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set default timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']); // Retrieve email from form input

    if (!empty($email)) {
        // Check if the email exists and is verified
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['status'] == 1) { // Check if the account is verified
                // Generate a reset token
                $reset_token = bin2hex(random_bytes(32));
                $_SESSION['reset_email'] = $email; // Store email in session
                $_SESSION['reset_token'] = $reset_token; // Optionally store token

                // Update database with the reset token and expiration time
                $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
                $stmt = $conn->prepare("UPDATE users SET reset_token = :reset_token, token_expiration = :token_expiration WHERE email = :email");
                $stmt->bindParam(':reset_token', $reset_token);
                $stmt->bindParam(':token_expiration', $expiry_time);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                // Send the email with the reset link
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->Port = 587;
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = 'tls';
                    $mail->Username = 'gianelleeee@gmail.com'; // Replace with your email
                    $mail->Password = 'wylkejnrpmrctsyy'; // Replace with your email password

                    $mail->setFrom('gianelleeee@gmail.com', 'Password Recovery');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Recover your password';
                    $mail->Body = "<b>Dear User</b>
                    <h3>We received a request to reset your password.</h3>
                    <p>Kindly click the below link to reset your password:</p>
                    <a href='http://localhost/inventorymanagementsystem/reset-password.php?token=$reset_token'>Reset Password</a>";

                    if ($mail->send()) {
                        echo '<script>
                                alert("Email sent! Kindly check your inbox.");
                                window.location.replace("forgot-pass-sent.php");
                              </script>';
                    } else {
                        echo '<script>alert("Failed to send email. Please try again.");</script>';
                    }
                } catch (Exception $e) {
                    echo '<script>alert("Error: ' . $e->getMessage() . '");</script>';
                }
            } else {
                echo '<script>
                        alert("Sorry, your account must be verified before you can recover your password.");
                        window.location.replace("verification.php");
                      </script>';
            }
        } else {
            echo '<script>alert("Sorry, no email exists.");</script>';
        }
    } else {
        echo '<script>alert("Please provide your email address.");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
                <h2 class="text-center">Forgot Password</h2>
                <div class="loginInputsContainer">
                    <label for="email">Enter Your Email Address:</label>
                    <input type="email" class="appFormInput" name="email" id="email" required autofocus>
                </div>
                <div class="loginButtonContainer">
                    <button type="submit" name="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
