<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

include('database/connection.php'); // Assuming this file contains database connection initialization
$show_table = 'products';
$user = $_SESSION['user'];
$products = include('database/show.php'); // Assuming this fetches products from the database

$show_table = 'category';
$category_list = include('database/show.php'); // Assuming this fetches categories from the database

$category_arr = [];

foreach($category_list as $category){
    $category_arr[$category['id']] = $category['category_name'];
}

$category_arr_json = json_encode($category_arr);
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
                            <h1 class="section_header"> <i class="fa fa-list"></i> List of Products</h1>
                            <div class="section_content">
                                <div class="users">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product Name</th>
                                                <th>Stock</th>
                                                <th>Description</th>
                                                <th width=10%>Category</th>
                                                <th>Created By</th>
                                                <th>Created At</th>
                                                <th>Updated At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($products as $index => $product){ ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td class="productName"><?= $product['product_name'] ?></td>
                                                    <td class="productName"><?= number_format($product['stock']) ?></td>
                                                    <td class="productDescription"><?= $product['description'] ?></td>
                                                    <td class="productCategory">
                                                        <?php
                                                        $pid = $product['id'];
                                                        $stmt = $conn->prepare("SELECT category.category_name FROM category, productscategory WHERE productscategory.product=$pid AND productscategory.category=category.id");
                                                        $stmt->execute();
                                                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                        if ($categories) {
                                                            $category_names = array_column($categories, 'category_name');
                                                            echo implode(', ', $category_names);
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $pid = $product['created_by'];
                                                        $stmt = $conn->prepare("SELECT * FROM users WHERE id=$pid");
                                                        $stmt->execute();
                                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                                        $created_by_name = $row['first_name'] . ' ' . $row['last_name'];
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
                                            <?php }?>
                                        </tbody>
                                    </table>
                                    <p class="product_count"><?= count($products) ?> Products</p>
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
        var categoryList = <?= $category_arr_json ?>;
        
        $(document).ready(function () {
            // Function to handle deletion
            $('.deleteProduct').on('click', function (e) {
                e.preventDefault();
                var pId = $(this).data('pid');
                var pName = $(this).data('name');
    
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
    
            // Function to handle update
            $('.updateProduct').on('click', function (e) {
                e.preventDefault();
                var pId = $(this).data('pid');
    
                // Call function to show update dialog
                showEditDialog(pId);
            });
    
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
                                    <option value="">Select Category</option>\
                                    ${categoryOption}\
                                </select>\
                            </div>\
                            <input type="hidden" name="pid" value="${productDetails.id}"/>\
                        </form>`,
                        callback: function (isUpdate) {
                            if (isUpdate) {
                                // Perform AJAX update
                                $.ajax({
                                    method: 'POST',
                                    data: $('#editProductForm').serialize(), // Serialize form data
                                    url: 'database/update-product.php',
                                    dataType: 'json',
                                    success: function (data) {
                                        const message = data.success ? data.message : 'Error updating product.';
                                        BootstrapDialog.alert({
                                            type: data.success ? BootstrapDialog.TYPE_SUCCESS : BootstrapDialog.TYPE_DANGER,
                                            message: message,
                                            callback: function () {
                                                if (data.success) {
                                                    // Reload the page to update the table
                                                    location.reload();
                                                }
                                            }
                                        });
                                    },
                                    error: function (jqXHR, textStatus, errorThrown) {
                                        BootstrapDialog.alert({
                                            type: BootstrapDialog.TYPE_DANGER,
                                            message: `An error occurred: ${textStatus}`
                                        });
                                    }
                                });
                            }
                        }
                    });
                }, 'json');
            }
});
    </script>
</body>
</html>
