<?php
// Start the session
session_start();
if (isset($_SESSION['user'])) header('location: dashboard.php');

$error_message = '';
$redirect_to_verification = false;
if ($_POST) {
    include('database/connection.php');

    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = 'SELECT * FROM users WHERE users.email = :email LIMIT 1';
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $user = $stmt->fetch();

        // Check if the user's email is verified
        if ($user['status'] == 1) {
            // Verify the hashed password
            if (password_verify($password, $user['password'])) {
                // Captures data of the currently logged-in user
                $_SESSION['user'] = $user;
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error_message = 'Password is incorrect. Please try again.';
            }
        } else {
            // Check if the password is correct
            if (password_verify($password, $user['password'])) {
                // Generate an OTP and store it in the session
                $otp = rand(100000, 999999); // Generate a 6-digit OTP
                $_SESSION['otp'] = $otp;
                $_SESSION['mail'] = $user['email'];

                // Set a flag to handle redirection
                $redirect_to_verification = true;
            } else {
                $error_message = 'Password is incorrect. Please try again.';
            }
        }
    } else {
        $error_message = 'Email not found. Please make sure that username and password are correct.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>IMS Login</title>

    <link rel="stylesheet" type="text/css" href="css/stylesheet.css">
    <style>
        .link a {
            color: white;
            margin-top: 10px;
            font-size: 20px;
        }
        .text-center {
            text-align: center;
        }

        h2, p {
            color: white;
        }

        .link a:hover {
            color: #649037;
        }
    </style>
    <script>
        // Display an alert if there's an error message or if redirect is needed
        <?php if (!empty($error_message)) { ?>
            alert('<?= addslashes($error_message) ?>');
        <?php } ?>

        <?php if ($redirect_to_verification) { ?>
            alert('Email not yet Verified!');
            window.location.replace('verification.php');
        <?php } ?>
    </script>
</head>
<body id="loginBody">
    <div class="container">
        <div class="loginHeader">
            <h1>IMS</h1>
            <p>INVENTORY MANAGEMENT SYSTEM</p>
        </div>
        <div class="loginBody">
            <form action="index.php" method="POST">
                <h2 class="text-center">Login Form</h2>
                <p class="text-center">Login with your email and password.</p>
                <div class="loginInputsContainer">
                    <label for="">Email</label>
                    <input placeholder="email" name="email" type="text" />
                </div>
                <div class="loginInputsContainer">
                    <label for="">Password</label>
                    <input placeholder="password" name="password" type="password" />
                </div>
                <div class="link forget-pass text-left" style="padding-top: 6px;"><a href="forgot-password.php">Forgot password?</a></div>
                <div class="loginButtonContainer">
                    <button class="button">Login</button>
                </div>
                <div class="link login-link text-center" style="padding-top: 10px;"><a href="register.php">Register here</a></div>
            </form>
        </div>
    </div>
</body>
</html>
