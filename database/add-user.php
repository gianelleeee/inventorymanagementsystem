<?php
session_start();
include('connection.php');

// Ensure Composer's autoloader is included first
require '../vendor/autoload.php';

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["first_name"]) && isset($_POST["last_name"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];

    // Sanitize user input
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars($password);
    $first_name = htmlspecialchars($first_name);
    $last_name = htmlspecialchars($last_name);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['response'] = ['message' => 'Invalid email format', 'success' => false];
        header('Location: ../register.php');
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $rowCount = $stmt->rowCount();

        if (!empty($email) && !empty($password) && !empty($first_name) && !empty($last_name)) {
            if ($rowCount > 0) {
                $_SESSION['response'] = ['message' => 'User with email already exists!', 'success' => false];
                header('Location: ../register.php');
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
                        $mail->Username = 'gianelleeee@gmail.com'; // Hardcoded email
                        $mail->Password = 'wylkejnrpmrctsyy'; // Hardcoded password

                        $mail->setFrom('gianelleeee@gmail.com', 'OTP Verification'); // Hardcoded email
                        $mail->addAddress($email);

                        $mail->isHTML(true);
                        $mail->Subject = 'Your verification code';
                        $mail->Body = "<p>Dear user, </p> <h3>Your verification OTP code is $otp</h3>";

                        $mail->send();
                        $_SESSION['response'] = ['message' => 'Registration successful. OTP sent to '.$email, 'success' => true];
                        header('Location: ../verification.php');
                        exit;
                    } catch (Exception $e) {
                        $_SESSION['response'] = ['message' => 'Registration failed. Mail could not be sent. Mailer Error: '.$mail->ErrorInfo, 'success' => false];
                        header('Location: ../register.php');
                        exit;
                    }
                }
            }
        }
    } catch (PDOException $e) {
        $_SESSION['response'] = ['message' => 'Database error: ' . $e->getMessage(), 'success' => false];
        header('Location: ../register.php');
        exit;
    }
}
?>
