<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

include('database/connection.php');
$user = $_SESSION['user'];

// Check if the user has the 'category_view' permission
$hasPermission = in_array('category_view', explode(',', $user['permissions']));

// Only include the database queries if the user has permission
if ($hasPermission) {
    $show_table = 'category';
    $category = include('database/show.php'); 

    $show_table = 'products';
    $products = include('database/show.php');

    $products_arr = [];

    foreach($products as $product){
        $products_arr[$product['id']] = $product['product_name'];
    }

    $products_arr = json_encode($products_arr);
} else {
    $category = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>IMS View Category</title>
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
                            <h1 class="section_header"><i class="fa fa-list"></i> List of Categories</h1>
                            <div class="section_content">
                                <?php if ($hasPermission): ?>
                                    <div class="users">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Category Name</th>
                                                    <th>Products</th>
                                                    <th>Created By</th>
                                                    <th>Created At</th>
                                                    <th>Updated At</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($category as $index => $cat): ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td class="categoryName"><?= htmlspecialchars($cat['category_name']) ?></td>
                                                        <td class="products_category">
                                                            <?php
                                                            $cid = $cat['id'];
                                                            $stmt = $conn->prepare("SELECT product_name FROM products, productscategory WHERE productscategory.category=:cid AND productscategory.product=products.id");
                                                            $stmt->bindParam(':cid', $cid, PDO::PARAM_INT);
                                                            $stmt->execute();
                                                            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                            if ($products) {
                                                                echo '<ul>';
                                                                foreach ($products as $product) {
                                                                    echo '<li>' . htmlspecialchars($product['product_name']) . '</li>';
                                                                }
                                                                echo '</ul>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="created_by">
                                                            <?php
                                                            $created_by = $cat['created_by'];
                                                            $stmt = $conn->prepare("SELECT * FROM users WHERE id=:created_by");
                                                            $stmt->bindParam(':created_by', $created_by, PDO::PARAM_INT);
                                                            $stmt->execute();
                                                            $user = $stmt->fetch(PDO::FETCH_ASSOC);

                                                            if ($user) {
                                                                $created_by_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                                                                echo $created_by_name;
                                                            } else {
                                                                echo "User Deleted";
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?= date('M d, Y @ h:i:s A', strtotime($cat['created_at'])) ?></td>
                                                        <td><?= date('M d, Y @ h:i:s A', strtotime($cat['updated_at'])) ?></td>
                                                        <td>
                                                            <a href="#" class="updateCategory" data-cid="<?= $cat['id'] ?>"><i class="fa fa-pencil"></i> Edit</a>
                                                            <a href="#" class="deleteCategory" data-name="<?= htmlspecialchars($cat['category_name']) ?>" data-cid="<?= $cat['id'] ?>"><i class="fa fa-trash"></i> Delete</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <p class="product_count"><?= count($category) ?> Categories</p>
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
    </div>

    <?php include('partials/scripts.php'); ?>


    <script>
    var productsList = <?= $products_arr ?>;

    $(document).ready(function () {
        // Handle deletion
        $('.deleteCategory').on('click', function (e) {
            e.preventDefault();
            var cId = $(this).data('cid');
            var categoryName = $(this).data('name');

            // Check if the user has the 'category_delete' permission
            var hasDeletePermission = <?= json_encode(in_array('category_delete', explode(',', $_SESSION['user']['permissions']))) ?>;

            if (!hasDeletePermission) {
                BootstrapDialog.alert({
                     type: BootstrapDialog.TYPE_WARNING,
                    message: 'Access Denied: You do not have permission to delete products.'
                });
                return; // Exit the function if the user does not have permission
            }

            BootstrapDialog.confirm({
                type: BootstrapDialog.TYPE_DANGER,
                title: 'Delete Category',
                message: 'Are you sure to delete <strong>' + categoryName + '</strong>?',
                callback: function (isDelete) {
                    if (isDelete) {
                        $.ajax({
                            method: 'POST',
                            data: { id: cId, table: 'category' },
                            url: 'database/delete.php',
                            dataType: 'json',
                            success: function (data) {
                                var message = data.success ? categoryName + ' successfully deleted!' : 'Error Processing Your Request.';
                                BootstrapDialog.alert({
                                    type: data.success ? BootstrapDialog.TYPE_SUCCESS : BootstrapDialog.TYPE_DANGER,
                                    message: message,
                                    callback: function () {
                                        if (data.success) location.reload();
                                    }
                                });
                            },
                            error: function (jqXHR, textStatus) {
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

        // Handle update
        $('.updateCategory').on('click', function (e) {
            e.preventDefault();
            var cId = $(this).data('cid');

            // Check if the user has the 'category_edit' permission
            var hasEditPermission = <?= json_encode(in_array('category_edit', explode(',', $_SESSION['user']['permissions']))) ?>;

            if (!hasEditPermission) {
                BootstrapDialog.alert({
                    type: BootstrapDialog.TYPE_WARNING,
                    message: 'Access Denied: You do not have permission to edit categories.'
                });
                return; // Exit the function if the user does not have permission
            }

            showEditDialog(cId);
        });

        // Show edit dialog
        function showEditDialog(id) {
            $.get('database/get-sku.php', { id: id }, function (categoryDetails) {
                let curProducts = categoryDetails['products'].map(Number); // Ensure curProducts are numbers
                let originalCategoryName = categoryDetails.category_name;
                let originalProducts = [...curProducts]; // Copy of current products for comparison
                let productOption = '';

                for (const [pId, pName] of Object.entries(productsList)) {
                    let selected = curProducts.includes(parseInt(pId)) ? 'selected' : '';
                    productOption += `<option ${selected} value='${pId}'>${pName}</option>`;
                }

                BootstrapDialog.confirm({
                    title: 'Update <strong>' + categoryDetails.category_name + '</strong>',
                    message: `<form id="editCategoryForm">\
                        <div class="appFormInputContainer">\
                            <label for="category_name">Category Name</label>\
                            <input type="text" class="appFormInput" name="category_name" value="${categoryDetails.category_name}" placeholder="Enter category name..." id="category_name">\
                        </div>\
                        <div class="appFormInputContainer">\
                            <label for="productSelect">Products</label>\
                            <select name="products[]" id="productSelect" multiple>\
                            <option value="">Select Product</option>\
                                ${productOption}\
                            </select>\
                        </div>\
                        <input type="hidden" name="cid" value="${categoryDetails.id}"/>\
                    </form>`,
                    callback: function (isUpdate) {
                        if (isUpdate) {
                            let newCategoryName = $('#category_name').val();
                            let newProducts = $('#productSelect').val().map(Number);

                            // Check if there are any changes
                            if (newCategoryName === originalCategoryName && arraysEqual(newProducts, originalProducts)) {
                                BootstrapDialog.alert({
                                    type: BootstrapDialog.TYPE_INFO,
                                    message: 'No changes were made.'
                                });
                                return;
                            }

                            $.ajax({
                                method: 'POST',
                                data: $('#editCategoryForm').serialize(),
                                url: 'database/update-sku.php',
                                dataType: 'json',
                                success: function (data) {
                                    var message = data.success ? data.message : 'Error updating category.';
                                    BootstrapDialog.alert({
                                        type: data.success ? BootstrapDialog.TYPE_SUCCESS : BootstrapDialog.TYPE_DANGER,
                                        message: message,
                                        callback: function () {
                                            if (data.success) location.reload();
                                        }
                                    });
                                },
                                error: function (jqXHR, textStatus) {
                                    BootstrapDialog.alert({
                                        type: BootstrapDialog.TYPE_DANGER,
                                        message: 'An error occurred: ' + textStatus
                                    });
                                }
                            });
                        }
                    }
                });
            }, 'json');
        }

        // Helper function to compare arrays
        function arraysEqual(arr1, arr2) {
            if (arr1.length !== arr2.length) return false;
            for (let i = 0; i < arr1.length; i++) {
                if (arr1[i] !== arr2[i]) return false;
            }
            return true;
        }
    });
</script>


</body>
</html>
