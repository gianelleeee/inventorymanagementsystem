<?php
// Start the session
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$_SESSION['table'] = 'users';
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>IMS Dashboard</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
</head>
<body>
    <div id="dashboardMainContainer">
        <?php include('partials/sidebar.php'); ?>
        <div class="dashboard_content_container" id="dashboard_content_container">
            <?php include('partials/topnav.php'); ?>
            <div class="dashboard_content">
                <div class="dashboard_content_main">
                    <div id="userAddFormContainer">
                        <form action="database/add.php" method="POST" class="appForm" id="userAddForm">
                            <div class="appFormInputContainer">
                                <label for="first_name">First Name</label>
                                <input type="text" class="appFormInput" name="first_name" id="first_name" required>
                            </div>
                            <div class="appFormInputContainer">
                                <label for="last_name">Last Name</label>
                                <input type="text" class="appFormInput" name="last_name" id="last_name" required>
                            </div>
                            <div class="appFormInputContainer">
                                <label for="email">Email</label>
                                <input type="email" class="appFormInput" name="email" id="email" required>
                            </div>
                            <div class="appFormInputContainer">
                                <label for="password">Password</label>
                                <input type="password" class="appFormInput" name="password" id="password" required>
                            </div>
                            <input type="hidden" name="table" value="users">
                            <button type="submit" class="appBtn"><i class="fa fa-add"></i> Add User</button>
                        </form>
                        <?php if (isset($_SESSION['response'])) { 
                            $response_message = $_SESSION['response']['message'];
                            $is_success = $_SESSION['response']['success'];
                        ?>
                        <div class="responseMessage">
                            <p class="responseMessage<?= $is_success ? 'responseMessage_success' : 'responseMessage_error' ?>">
                                <?= $response_message ?>
                            </p>
                        </div>
                        <?php unset($_SESSION['response']); } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="js/script.js"></script>
</body>
</html>
