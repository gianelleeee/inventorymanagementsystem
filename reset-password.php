<?php
session_start();
include('database/connection.php'); // Ensure the connection file path is correct

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    // Verify the token and get user
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = :reset_token AND token_expiration > NOW()");
    $stmt->bindParam(':reset_token', $token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (isset($_POST['submit'])) {
            $new_password = trim($_POST['new_password']);
            $confirm_password = trim($_POST['confirm_password']);

            if ($new_password === $confirm_password) {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                
                // Update user password and clear reset token
                $stmt = $conn->prepare("UPDATE users SET password = :password, reset_token = NULL, token_expiration = NULL WHERE reset_token = :reset_token");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':reset_token', $token);
                $stmt->execute();
                
                echo '<script>
                        alert("Password successfully updated.");
                        window.location.replace("index.php");
                      </script>';
            } else {
                echo '<script>alert("Passwords do not match.");</script>';
            }
        }
    } else {
        echo '<script>alert("Invalid or expired token.");
                window.location.replace("forgot-password.php");
                </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
                <h2 class="text-center">Reset Password</h2>
                <div class="loginInputsContainer">
                    <label for="new_password">New Password:</label>
                    <input type="password" class="appFormInput" name="new_password" id="new_password" required>
                </div>
                <div class="loginInputsContainer">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" class="appFormInput" name="confirm_password" id="confirm_password" required>
                </div>
                <div class="loginButtonContainer">
                    <button type="submit" name="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
