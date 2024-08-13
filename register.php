<?php
session_start();
include('database/connection.php');

// Ensure Composer's autoloader is included first
require 'vendor/autoload.php';

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars($_POST["password"]);
    $confirm_password = htmlspecialchars($_POST["confirm_password"]);
    $first_name = htmlspecialchars($_POST["first_name"]);
    $last_name = htmlspecialchars($_POST["last_name"]);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['response'] = ['message' => 'Invalid email format', 'success' => false];
        header('Location: register.php');
        exit;
    }

    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['response'] = ['message' => 'Passwords do not match', 'success' => false];
        header('Location: register.php');
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['response'] = ['message' => 'User with email already exists!', 'success' => false];
            header('Location: register.php');
            exit;
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, status, created_at, updated_at) VALUES (:email, :password, :first_name, :last_name, 0, NOW(), NOW())");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $result = $stmt->execute();

            if ($result) {
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['mail'] = $email;

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->Port = 587;
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = 'tls';
                    $mail->Username = 'gianelleeee@gmail.com'; // Replace with your email
                    $mail->Password = 'wylkejnrpmrctsyy'; // Replace with your email password

                    $mail->setFrom('gianelleeee@gmail.com', 'OTP Verification'); // Replace with your email
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your verification code';
                    $mail->Body = "<p>Dear user, </p> <h3>Your verification OTP code is $otp</h3>";

                    if ($mail->send()) {
                        ?>
                        <script>
                            alert("Register Successfully, OTP sent to <?php echo $email; ?>");
                            window.location.replace('verification.php');
                        </script>
                        <?php
                    } else {
                        throw new Exception('Failed to send email.');
                    }
                } catch (Exception $e) {
                    $_SESSION['response'] = ['message' => 'Email sending failed: ' . $e->getMessage(), 'success' => false];
                    header('Location: register.php');
                    exit;
                }
            } else {
                $_SESSION['response'] = ['message' => 'Registration failed', 'success' => false];
                header('Location: register.php');
                exit;
            }
        }
    } catch (PDOException $e) {
        $_SESSION['response'] = ['message' => 'Database error: ' . $e->getMessage(), 'success' => false];
        header('Location: register.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>IMS Register</title>
    <link rel="stylesheet" type="text/css" href="css/stylesheet.css">
    <style>
        .link a {
            color: white;
            margin-top: 10px;
        }
        .text-center {
            text-align: center;
        }
        h2, p {
            color: white;
        }
        .register-link a:hover {
            color: #649037;
        }
    </style>
    <script>
    window.onload = function() {
        <?php if (isset($_SESSION['response'])): ?>
            var message = "<?php echo addslashes($_SESSION['response']['message']); ?>";
            var success = <?php echo $_SESSION['response']['success'] ? 'true' : 'false'; ?>;

            if (success) {
                alert("Success: " + message);
            } else {
                alert("Error: " + message);
            }

            <?php unset($_SESSION['response']); ?>
        <?php endif; ?>
    }
    </script>
</head>
<body id="loginBody">
    <div class="container">
        <div class="loginHeader">
            <h1>IMS</h1>
            <p>INVENTORY MANAGEMENT SYSTEM</p>
        </div>
        <div class="registerBody" id="userAddFormContainer">
            <form action="register.php" method="POST" class="appForm" id="userAddForm">
                <h2 class="text-center">Register Form</h2>
                <div class="loginInputsContainer">
                    <label for="first_name">First Name</label>
                    <input type="text" class="appFormInput" name="first_name" id="first_name" placeholder="First Name" required>
                </div>
                <div class="loginInputsContainer">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="appFormInput" name="last_name" id="last_name" placeholder="Last Name" required>
                </div>
                <div class="loginInputsContainer">
                    <label for="email">Email</label>
                    <input type="email" class="appFormInput" name="email" id="email" placeholder="Email" required>
                </div>
                <div class="loginInputsContainer">
                    <label for="password">Password</label>
                    <input type="password" class="appFormInput" name="password" id="password" placeholder="Password" required>
                </div>
                <div class="loginInputsContainer">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" class="appFormInput" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                </div>
                <div class="loginButtonContainer">
                    <button type="submit">Register</button>
                </div>
                <div class="link register-link text-center" style="padding-top: 10px; font-size: 20px;">
                    <a href="index.php">Login here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
