<?php
session_start();
include('database/connection.php');

// Ensure Composer's autoloader is included first
require 'vendor/autoload.php';

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize form variables
$email = $_SESSION['form_data']['email'] ?? '';
$first_name = $_SESSION['form_data']['first_name'] ?? '';
$last_name = $_SESSION['form_data']['last_name'] ?? '';
$password = ''; // Password fields should remain empty for security reasons
$confirm_password = '';

unset($_SESSION['form_data']); // Clear form data after it's used

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
        $_SESSION['form_data'] = $_POST;
        header('Location: register.php');
        exit;
    }

    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['response'] = ['message' => 'Passwords do not match', 'success' => false];
        $_SESSION['form_data'] = $_POST;
        header('Location: register.php');
        exit;
    }

    try {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // If the email doesn't exist in the database
            $_SESSION['response'] = ['message' => 'Email is not found in the system. You cannot register with this email.', 'success' => false];
            $_SESSION['form_data'] = $_POST;
            header('Location: register.php');
            exit;
        } else {
            // If email exists, check if first_name and last_name are not null
            if (!empty($user['first_name']) && !empty($user['last_name'])) {
                $_SESSION['response'] = ['message' => 'Email already registered!', 'success' => false];
                $_SESSION['form_data'] = $_POST;
                header('Location: register.php');
                exit;
            }
        }

        // Fetch current permissions to preserve it
        $stmt = $conn->prepare("SELECT permissions FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['response'] = ['message' => 'Email not found in the system.', 'success' => false];
            $_SESSION['form_data'] = $_POST;
            header('Location: register.php');
            exit;
        }

        $current_permissions = $user['permissions']; // Preserve the current permissions value

        // Fetch existing passwords to compare with the new password
        $stmt = $conn->prepare("SELECT password FROM users");
        $stmt->execute();
        $existingPasswords = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Check if the new password matches any existing passwords
        foreach ($existingPasswords as $existingPassword) {
            if (password_verify($password, $existingPassword)) {
                $_SESSION['response'] = ['message' => 'Please choose a stronger password that is unique.', 'success' => false];
                $_SESSION['form_data'] = $_POST;
                header('Location: register.php');
                exit;
            }
        }

        // Insert or update user registration data (including permissions)
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("
            UPDATE users 
            SET password = :password, first_name = :first_name, last_name = :last_name, status = 0, updated_at = NOW(), permissions = :permissions
            WHERE email = :email
        ");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':permissions', $current_permissions); // Preserve current permissions value
        $result = $stmt->execute();

        if ($result) {
            // OTP generation
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
                $_SESSION['form_data'] = $_POST;
                header('Location: register.php');
                exit;
            }
        } else {
            $_SESSION['response'] = ['message' => 'Registration failed', 'success' => false];
            $_SESSION['form_data'] = $_POST;
            header('Location: register.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['response'] = ['message' => 'Database error: ' . $e->getMessage(), 'success' => false];
        $_SESSION['form_data'] = $_POST;
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
                    <input type="text" class="appFormInput" name="first_name" id="first_name" value="<?php echo htmlspecialchars($first_name); ?>" placeholder="First Name" required>
                </div>
                <div class="loginInputsContainer">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="appFormInput" name="last_name" id="last_name" value="<?php echo htmlspecialchars($last_name); ?>" placeholder="Last Name" required>
                </div>
                <div class="loginInputsContainer">
                    <label for="email">Email</label>
                    <input type="email" class="appFormInput" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email" required>
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
                    <button class="button" type="submit">Register</button>
                </div>
                <div class="link register-link text-center" style="padding-top: 10px; font-size: 20px;">
                    <a href="index.php">Login here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
