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
    <title>IMS Sales</title>

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
                            <h1 class="section_header"> <i class="fa fa-plus"></i> Add Sales</h1>
                                <div>
                                    <form action="database/save-sales.php" method="POST">
                                        <div class="alignRight">
                                            <button type="button" class="salesBtn salesProductBtn" id="salesProductBtn">Add New Product Sales</button>
                                        </div>

                                        <div id="salesProductLists">
                                            <p id="noData" style="color: #9f9f9f;">No Products Selected</p>
                                        </div>
                                            
                                        <div class="alignRight marginTop20">
                                            <button type="submit" class="submitSalesProductBtn">Submit Sales</button>
                                        </div>
                                    </form>
                                </div>
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('partials/scripts.php'); ?>

    <script>
        var products = <?= $products ?>;
        var counter = 0;

        function Script() {
            var vm = this;

            this.productOptions = '<div>\
                                        <div class="salesDate">\
                                            <label for="date">SELECT DATE</label>\
                                            <input type="date" name="date" class="dateSelect" id="date">\
                                        </div>\
                                        <label for="product_name">PRODUCT NAME</label>\
                                            <select name="products[]" class="productNameSelect" id="product_name">\
                                                <option value="">Select Product</option>\
                                                INSERTPRODUCTHERE\
                                            </select> \
                                                <button class="removeSalesBtn">Remove</button>\
                                        </div>';

            this.initialize = function() {
                this.renderProductOptions();
                this.registerEvents();
            };

            this.renderProductOptions = function() {
                let optionHtml = '';
                products.forEach((product) => {
                    optionHtml += '<option value="'+ product.id+'">'+ product.product_name +'</option>';
                });

                this.productOptions = this.productOptions.replace('INSERTPRODUCTHERE', optionHtml);
            };

            
            this.registerEvents = function() {
                //add new product sales event
                document.addEventListener('click', (e) => {
                    var targetElement = e.target;
                    if (targetElement.id === 'salesProductBtn') {
                        document.getElementById('noData').style.display = 'none';
                        let salesProductListsContainer = document.getElementById('salesProductLists');

                        salesProductLists.innerHTML += '\
                            <div class="salesProductRow">\
                                '+ this.productOptions +'\
                                <div class="categoryRows" id="categoryRows_'+ counter+'" data-counter="'+ counter +'"></div>\
                            </div>';

                        
                        counter++;
                    }


                    // If remove button is clicked
                    if (targetElement.classList.contains('removeSalesBtn')) {
                        let salesRow = targetElement.closest('div.salesProductRow');

                        // Remove element
                        if (salesRow) {
                            salesRow.remove();
                        }
                    }
                });

                // Add category row on product options change event
                document.addEventListener('change', (e) => {
                    var targetElement = e.target;
                    if (targetElement.classList.contains('productNameSelect')) {
                        let pid = targetElement.value;

                        
                        let counterId = targetElement.closest('div.salesProductRow').querySelector('.categoryRows').dataset.counter;

                            $.get('database/get-product-sku.php', {id: pid}, function(category){
                                vm.renderCategoryRows(category, counterId);
                            }, 'json');
                    }
                });
            },

            this.renderCategoryRows = function(category, counterId){
                let categoryRows = '';

                category.forEach((category)=> {
                    categoryRows += '\
                        <div class="row">\
                            <div style="width: 50%;">\
                                <p class="categoryName">'+ category.category_name+'</p>\
                            </div>\
                            <div style="width: 50%;">\
                                <label for="product_name">Quantity</label>\
                                <input type="number" name="quantity['+ counterId +']['+ category.id +']" class="salesProductQty" placeholder="Enter quantity..." id="quantity" required>\
                            </div>\
                        </div>';

                });

                //append to container
                let supplierRowContainer = document.getElementById('categoryRows_' + counterId);
                supplierRowContainer.innerHTML = categoryRows;
            }
        }

        (new Script()).initialize();
    </script>


</body>
</html>
