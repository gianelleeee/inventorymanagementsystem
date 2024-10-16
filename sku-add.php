<?php
// Start the session
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$_SESSION['table'] = 'category';
$_SESSION['redirect_to'] = 'sku-add.php';

$user = $_SESSION['user'];

// Check if the user has the 'category_add' permission
$hasAddPermission = in_array('category_add', explode(',', $user['permissions']));

?>

<!DOCTYPE html>
<html>
<head>
    <title>IMS Add Category</title>
    <?php include('partials/header-script.php'); ?>
</head>
<body>
    <div id="dashboardMainContainer">
        <?php include('partials/sidebar.php'); ?>
        <div class="dashboard_content_container" id="dashboard_content_container">
            <?php include('partials/topnav.php'); ?>
            <div class="dashboard_content">
                <div class="dashboard_content_main">
                    <div class="row">
                        <div class="column column-12">
                            <h1 class="section_header"><i class="fa fa-plus"></i> Add Category</h1>
                            
                            <?php if ($hasAddPermission): ?>
                                <div id="userAddFormContainer">
                                    <form action="database/add-category.php" method="POST" class="appForm" id="userAddForm">
                                        <div class="appFormInputContainer">
                                            <label for="category_name">Category Name</label>
                                            <input type="text" class="appFormInput" name="category_name" placeholder="Enter category name..." id="category_name" required>
                                        </div>
                                        <button type="submit" class="appBtn"><i class="fa fa-add"></i> Add Category</button>
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
                            <?php else: ?>
                                <div style="margin: 50px;">
                                    <h2>You have no access to this page.</h2>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('partials/scripts.php'); ?>
</body>
</html>
