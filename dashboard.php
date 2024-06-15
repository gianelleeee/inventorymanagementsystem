<?php
    //start the session
    session_start();
    if(!isset($_SESSION['user'])) header('location: index.php');



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
    <?php include('partials/sidebar.php') ?>
        <div class="dashboard_content_container" id="dashboard_content_container">
        <?php include('partials/topnav.php') ?>
            <div class="dashboard_content">
                <div class="dashboard_content_main"></div>
            </div>
        </div>
    </div>

    <script src="js/script.js"> </script>
</body>
</html>