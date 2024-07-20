<?php
// Start the session
    session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit();
    }
    $user = $_SESSION['user'];

    //get all products
    $show_table = 'products';
    $products = include('database/show.php');
    $products = json_encode($products);
?>

<!DOCTYPE html>
<html>
<head>
    <title>IMS Order Product</title>

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
                            <h1 class="section_header"> <i class="fa fa-plus"></i> Order Product</h1>
                                <div>
                                    <div class="alignRight">
                                        <button class="orderBtn orderProductBtn">Add New Product Order</button>
                                    </div>
                                    <div id="orderProductLists">
                                        <div class="orderProductRow">
                                            <div>
                                                <label for="product_name">PRODUCT NAME</label>
                                                <select name="product_name" class="productNameSelect" id="product_name">
                                                    <option value="">Product 1</option>
                                                </select> 
                                            </div>
                                            <div class="categoryRows">
                                                <div class="row">
                                                    <div style="width: 50%;">
                                                        <p class="categoryName">Category 1</p>
                                                    </div>
                                                    <div style="width: 50%;">
                                                        <label for="product_name">Quantity</label>
                                                        <input type="number" name="quantity" placeholder="Enter quantity..." id="quantity" required>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div style="width: 50%;">
                                                        <p class="categoryName">Category 2</p>
                                                    </div>
                                                    <div style="width: 50%;">
                                                        <label for="product_name">Quantity</label>
                                                        <input type="number" name="quantity" placeholder="Enter quantity..." id="quantity" required>
                                                    </div>
                                                </div>
                                                    <div class="row">
                                                        <div style="width: 50%;">
                                                            <p class="categoryName">Category 3</p>
                                                        </div>
                                                    <div style="width: 50%;">
                                                        <label for="product_name">Quantity</label>
                                                        <input type="number" name="quantity" placeholder="Enter quantity..." id="quantity" required>
                                                    </div>
                                                </div>
                                            </div>       
                                        </div>
                                        <div class="alignRight marginTop20">
                                                <button class=" orderBtn submitOrderProductBtn">Submit Order</button>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('partials/scripts.php'); ?>

    <script>
        var products = <?= $products  ?>;
        console.log(products);

    </script>

</body>
</html>
