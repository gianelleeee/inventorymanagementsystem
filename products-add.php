<?php
// Start the session
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$user = $_SESSION['user'];

// Check if the user has the 'product_add' permission
$hasPermission = in_array('product_add', explode(',', $user['permissions']));
?>

<!DOCTYPE html>
<html>
<head>
    <title>IMS Add Product</title>
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
                            <h1 class="section_header"> <i class="fa fa-plus"></i> Add Product</h1>
                            
                            <?php if ($hasPermission): ?>
                                <div id="userAddFormContainer">
                                    <form action="database/add-products.php" method="POST" class="appForm" id="userAddForm">
                                        <div class="appFormInputContainer">
                                            <label for="product_name">Product Name</label>
                                            <input type="text" class="appFormInput" name="product_name" placeholder="Enter product name..." id="product_name" required>
                                        </div>
                                        <div class="appFormInputContainer">
                                            <label for="description">Description</label>
                                            <textarea class="appFormInput productTextAreaInput" name="description" placeholder="Enter product description..." id="description"></textarea>
                                        </div>
                                        <div class="appFormInputContainer">
                                            <label for="categorySelect">Category</label>
                                            <select name="category[]" id="categorySelect" required>
                                                <option value="">Select Category</option>
                                                <?php
                                                    $show_table = 'category';
                                                    $category = include('database/show.php');

                                                    foreach ($category as $cat) {
                                                        echo "<option value='" . $cat['id'] . "'>" . $cat['category_name'] . "</option>";
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="appBtn"><i class="fa fa-add"></i> Add Product</button>
                                    </form>
                                    <?php 
                                        if (isset($_SESSION['response'])) { 
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
