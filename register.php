
<!DOCTYPE html>
<?php
// Start the session
    session_start();
    $_SESSION['table']='users';
?>
<html lang="en">
<head>
    <title>IMS Register</title>

    <link rel="stylesheet" type="text/css" href="css/stylesheet.css">
    <style>
        .link a {
            color: white;
            margin-top: 10px;

        }
        .text-center{
            text-align: center;
        }

        h2, p{
            color: white;
        }

        .register-link a:hover{
            color: #649037;
        }
    </style>
</head>
<body id="loginBody">
    <?php
        if(!empty($error_message)){ ?>
        <div style="background: #fff; text-align: center; color: red; font-size: 20px; padding: 11px;">
            <strong>Error:</strong><p><?= $error_message ?></p>
        </div>
    <?php } ?>
    <div class="container">
        <div class="loginHeader">
            <h1>IMS</h1>
            <p>INVENTORY MANAGEMENT SYSTEM</p>
        </div>
        <div class="registerBody" id="userAddFormContainer">
            <form action="database/add-user.php" method="POST" class="appForm" id="userAddForm">
                <h2 class="text-center">Register Form</h2>
                <div class="loginInputsContainer">
                    <label for="first_name">First Name</label>
                    <input type="text" class="appFormInput" name="first_name" id="first_name" placeholder="first name" required>
                </div>
                <div class="loginInputsContainer">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="appFormInput" name="last_name" id="last_name" placeholder="last name" required>
                </div>
                <div class="loginInputsContainer">
                    <label for="email">Email</label>
                    <input type="email" class="appFormInput" name="email" id="email" placeholder="email" required>
                </div>
                <div class="loginInputsContainer">
                    <label for="password">Password</label>
                    <input type="password" class="appFormInput" name="password" id="password" placeholder="password" required>
                </div>
                <!-- <div class="loginInputsContainer">
                    <label for="cpassword">Confirm Password</label>
                    <input type="password" class="appFormInput" name="cpassword" id="cpassword" placeholder="confirm password" required>
                </div> -->
                <div class="loginButtonContainer">
                    <!-- <input type="hidden" name="table" value="users"> -->
                    <button type="submit">Register</button>
                </div>
                <div class="link register-link text-center" style="padding-top: 10px; font-size: 20px;"><a href="index.php">Login here</a></div>
            </form>
                <?php if (isset($_SESSION['response'])) { 
                    $response_message = $_SESSION['response']['message'];
                    $is_success = $_SESSION['response']['success'];
                ?>
                <div class="responseRegisterMessage">
                    <p class="responseMessage<?= $is_success ? 'responseRegisterMessage_success' : 'responseRegisterMessage_error' ?>">
                        <?= $response_message ?>
                    </p>
                </div>
                <?php unset($_SESSION['response']); } ?>
            <div>

            </div>
        </div>
    </div>

    
</body>
</html>