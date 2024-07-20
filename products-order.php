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
                                        <button class="orderBtn orderProductBtn" id="orderProductBtn">Add New Product Order</button>
                                    </div>

                                    <div id="orderProductLists">
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

    <?php include('partials/scripts.php'); ?>

    <script>
        var products = <?= $products ?>;
        var counter = 0;

        function Script() {
            var vm = this;

            this.productOptions = '<div>\
                                        <label for="product_name">PRODUCT NAME</label>\
                                            <select name="product_name" class="productNameSelect" id="product_name">\
                                                <option value="">Select Product</option>\
                                                INSERTPRODUCTHERE\
                                            </select> \
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
                //add new product order event
                document.addEventListener('click', (e) => {
                    var targetElement = e.target;
                    if (targetElement.id === 'orderProductBtn') {
                        let orderProductListsContainer = document.getElementById('orderProductLists');

                        orderProductLists.innerHTML += '\
                            <div class="orderProductRow">\
                                '+ this.productOptions +'\
                                <div class="categoryRows" id="categoryRows_'+ counter+'" data-counter="'+ counter +'"></div>\
                            </div>';

                        
                        counter++;
                    }
                });

                // Add category row on product options change event
                document.addEventListener('change', (e) => {
                    var targetElement = e.target;
                    if (targetElement.classList.contains('productNameSelect')) {
                        let pid = targetElement.value;

                        
                        let counterId = targetElement.closest('div.orderProductRow').querySelector('.categoryRows').dataset.counter;

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
                                <input type="number" name="quantity" class="orderProductQty" placeholder="Enter quantity..." id="quantity" required>\
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
