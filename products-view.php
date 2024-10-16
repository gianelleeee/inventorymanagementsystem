<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

include('database/connection.php');
$user = $_SESSION['user'];

// Check user permissions
if (!in_array('product_view', explode(',', $user['permissions']))) {
    $accessDenied = true; // Set access denied flag
} else {
    $show_table = 'products';
    $products = include('database/show.php');

    $show_table = 'category';
    $category_list = include('database/show.php');

    $category_arr = [];

    foreach ($category_list as $category) {
        $category_arr[$category['id']] = $category['category_name'];
    }

    $category_arr_json = json_encode($category_arr);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>IMS View Products</title>
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
                            <h1 class="section_header"><i class="fa fa-list"></i> List of Products</h1>
                            <div class="section_content">
                                <?php if (isset($accessDenied) && $accessDenied): ?>
                                    <div style="margin: 50px;">
                                        <h2>You have no access to this page.</h2>
                                    </div>
                                <?php else: ?>
                                    <div class="filter_section">
                                        <div class="category_filter">
                                            <label for="category_filter">Filter by Category:</label>
                                            <select id="category_filter">
                                                <option value="all">All Categories</option>
                                                <?php foreach ($category_list as $category) { ?>
                                                    <option value="<?= $category['id'] ?>"><?= $category['category_name'] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="search_box">
                                            <label for="product_search">Search Products:</label>
                                            <input type="text" id="product_search" placeholder="Search by product name or description">
                                            <button id="search_button">Search</button>
                                        </div>
                                    </div>

                                    <div class="users">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Product Name</th>
                                                    <th>Description</th>
                                                    <th width="10%">Category</th>
                                                    <th>Available Stock</th>
                                                    <th>Stocks Used</th>
                                                    <th>Created By</th>
                                                    <th>Created At</th>
                                                    <th>Updated At</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($products as $index => $product) { 
                                                    // Fetch total stocks used for this product
                                                    $pid = $product['id'];
                                                    $stmt = $conn->prepare("SELECT SUM(sales_product.sales) as total_sales FROM sales_product 
                                                                            INNER JOIN productscategory ON sales_product.product = productscategory.product 
                                                                            WHERE productscategory.product = :product_id");
                                                    $stmt->bindParam(':product_id', $pid, PDO::PARAM_INT);
                                                    $stmt->execute();
                                                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                                    $stocks_used = $result['total_sales'] ? $result['total_sales'] : 0;
                                                    
                                                    // Fetch product categories
                                                    $stmt = $conn->prepare("SELECT category.id, category.category_name FROM category 
                                                                            INNER JOIN productscategory ON productscategory.category = category.id 
                                                                            WHERE productscategory.product = :product_id");
                                                    $stmt->bindParam(':product_id', $pid, PDO::PARAM_INT);
                                                    $stmt->execute();
                                                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                ?>
                                                    <tr data-category="<?= implode(',', array_column($categories, 'id')) ?>">
                                                        <td><?= $index + 1 ?></td>
                                                        <td class="productName"><?= $product['product_name'] ?></td>
                                                        <td class="productDescription"><?= $product['description'] ?></td>
                                                        <td class="productCategory"><?= implode(', ', array_column($categories, 'category_name')) ?></td>
                                                        <td class="productName"><?= number_format($product['stock']) ?></td>
                                                        <td><?= number_format($stocks_used) ?></td>
                                                        <td>
                                                            <?php
                                                            $created_by_id = $product['created_by'];
                                                            $stmt = $conn->prepare("SELECT * FROM users WHERE id = :created_by_id");
                                                            $stmt->bindParam(':created_by_id', $created_by_id, PDO::PARAM_INT);
                                                            $stmt->execute();
                                                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                                            if ($row) {
                                                                $created_by_name = $row['first_name'] . ' ' . $row['last_name'];
                                                            } else {
                                                                $created_by_name = 'User Deleted';
                                                            }
                                                            echo $created_by_name;
                                                            ?>
                                                        </td>
                                                        <td><?= date('M d, Y @ h:i:s A', strtotime($product['created_at'])) ?></td>
                                                        <td><?= date('M d, Y @ h:i:s A', strtotime($product['updated_at'])) ?></td>
                                                        <td>
                                                            <a href="#" class="updateProduct" data-pid="<?= $product['id'] ?>"><i class="fa fa-pencil"></i> Edit</a>
                                                            <a href="#" class="deleteProduct" data-name="<?= $product['product_name'] ?>" data-pid="<?= $product['id'] ?>"><i class="fa fa-trash"></i> Delete</a>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                        <p class="product_count"><?= count($products) ?> Products</p>
                                    </div>
                                    <div id="no_products_message" style="display: none; color: red;">There are no products added to this category.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('partials/scripts.php'); ?>
    <script>
        var categoryList = <?= $category_arr_json ?>;
        
        $(document).ready(function () {
            // Function to handle deletion
            function attachDeleteEvent() {
                $('.deleteProduct').off('click').on('click', function (e) {
                    e.preventDefault();
                    var pId = $(this).data('pid');
                    var pName = $(this).data('name');

                    // Check if the user has delete permissions
                    if (!<?= json_encode(in_array('product_delete', explode(',', $user['permissions']))) ?>) {
                        BootstrapDialog.alert({
                            type: BootstrapDialog.TYPE_WARNING,
                            message: 'Access Denied: You do not have permission to delete products.'
                        });
                        return; // Exit the function if the user does not have permission
                    }

                    BootstrapDialog.confirm({
                        type: BootstrapDialog.TYPE_DANGER,
                        title: 'Delete Product',
                        message: 'Are you sure to delete <strong>' + pName + '</strong>?',
                        callback: function (isDelete) {
                            if (isDelete) {
                                $.ajax({
                                    method: 'POST',
                                    data: {
                                        id: pId,
                                        table: 'products'
                                    },
                                    url: 'database/delete.php',
                                    dataType: 'json',
                                    success: function (data) {
                                        var message = data.success ? pName + ' successfully deleted!' : 'Error Processing Your Request.';

                                        BootstrapDialog.alert({
                                            type: data.success ? BootstrapDialog.TYPE_SUCCESS : BootstrapDialog.TYPE_DANGER,
                                            message: message,
                                            callback: function () {
                                                if (data.success) {
                                                    // Reload and update the table
                                                    location.reload();
                                                }
                                            }
                                        });
                                    },
                                    error: function (jqXHR, textStatus, errorThrown) {
                                        BootstrapDialog.alert({
                                            type: BootstrapDialog.TYPE_DANGER,
                                            message: 'An error occurred: ' + textStatus
                                        });
                                    }
                                });
                            }
                        }
                    });
                });
            }


            // Function to handle update
            function attachUpdateEvent() {
                $('.updateProduct').off('click').on('click', function (e) {
                    e.preventDefault();
                    var pId = $(this).data('pid');

                    // Check if the user has edit permissions
                    if (!<?= json_encode(in_array('product_edit', explode(',', $user['permissions']))) ?>) {
                        BootstrapDialog.alert({
                            type: BootstrapDialog.TYPE_WARNING,
                            message: 'Access Denied: You do not have permission to edit products.'
                        });
                        return; // Exit the function if the user does not have permission
                    }

                    // Call function to show update dialog
                    showEditDialog(pId);
                });
            }

            // Function to show edit dialog
            function showEditDialog(id) {
                $.get('database/get-product.php', { id: id }, function (productDetails) {
                    let curCategory = productDetails['category']; // Array of selected category IDs
                    let categoryOption = '';

                    for (const [catId, catName] of Object.entries(categoryList)) {
                        // Check if the category ID is in the list of selected categories
                        let selected = curCategory.includes(parseInt(catId)) ? 'selected' : '';
                        categoryOption += `<option ${selected} value='${catId}'>${catName}</option>`;
                    }

                    BootstrapDialog.confirm({
                        title: `Update <strong>${productDetails.product_name}</strong>`,
                        message: `<form id="editProductForm">\
                            <div class="appFormInputContainer">\
                                <label for="product_name">Product Name</label>\
                                <input type="text" class="appFormInput" name="product_name" value="${productDetails.product_name}" placeholder="Enter product name..." id="product_name" required>\
                            </div>\
                            <div class="appFormInputContainer">\
                                <label for="description">Description</label>\
                                <textarea class="appFormInput productTextAreaInput" name="description" placeholder="Enter product description..." id="description">${productDetails.description}</textarea>\
                            </div>\
                            <div class="appFormInputContainer">\
                                <label for="categorySelect">Category</label>\
                                <select name="category[]" id="categorySelect" multiple="">\
                                    ${categoryOption}\
                                </select>\
                            </div>\
                            <input type="hidden" name="pid" value="${id}">\
                            <input type="hidden" name="table" value="products">\
                            <input type="hidden" name="operation" value="update">\
                        </form>`,
                        callback: function (isUpdate) {
                            if (isUpdate) {
                                var form = $('#editProductForm').serializeArray();
                                var productName = form.find(input => input.name === 'product_name').value;

                                // Check if any changes were made
                                if (productName === productDetails.product_name &&
                                    $('#description').val() === productDetails.description &&
                                    JSON.stringify($('#categorySelect').val().map(Number)) === JSON.stringify(curCategory)) {
                                    BootstrapDialog.alert({
                                        type: BootstrapDialog.TYPE_INFO,
                                        message: 'No changes were made.'
                                    });
                                    return; // Exit without submitting
                                }

                                // If changes were made, submit the form
                                $.post('database/update-product.php', form, function (data) {
                                    var message = data.success ? productName + ' successfully updated!' : 'Error Processing Your Request.';

                                    BootstrapDialog.alert({
                                        type: data.success ? BootstrapDialog.TYPE_SUCCESS : BootstrapDialog.TYPE_DANGER,
                                        message: message,
                                        callback: function () {
                                            if (data.success) {
                                                // Reload and update the table
                                                location.reload();
                                            }
                                        }
                                    });
                                }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
                                    BootstrapDialog.alert({
                                        type: BootstrapDialog.TYPE_DANGER,
                                        message: 'An error occurred: ' + textStatus
                                    });
                                });
                            }
                        }
                    });
                }, 'json');
}

            // Filter by category
            $('#category_filter').on('change', function () {
                var selectedCategory = $(this).val();
                filterProducts(selectedCategory);
            });

            // Filter products function
            function filterProducts(categoryId) {
                if (categoryId === 'all') {
                    $('tbody tr').show();
                } else {
                    $('tbody tr').each(function () {
                        var rowCategory = $(this).data('category').toString();
                        $(this).toggle(rowCategory.includes(categoryId));
                    });
                }
                updateProductCount();
            }

            // Update product count
            function updateProductCount() {
                var visibleProducts = $('tbody tr:visible').length;
                $('.product_count').text(visibleProducts + ' Products');
                if (visibleProducts === 0) {
                    $('#no_products_message').show();
                } else {
                    $('#no_products_message').hide();
                }
            }

            // Attach events on load
            attachDeleteEvent();
            attachUpdateEvent();

            // Search products by name or description
            $('#search_button').on('click', function () {
                var searchQuery = $('#product_search').val().toLowerCase();
                searchProducts(searchQuery);
            });

            // Search products function
            function searchProducts(query) {
                $('tbody tr').each(function () {
                    var productName = $(this).find('.productName').text().toLowerCase();
                    var productDescription = $(this).find('.productDescription').text().toLowerCase();
                    $(this).toggle(productName.includes(query) || productDescription.includes(query));
                });
                updateProductCount();
            }
        });
    </script>
</body>
</html>
