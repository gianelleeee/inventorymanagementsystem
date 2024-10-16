<?php
// Start the session
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$user = $_SESSION['user'];

// Check for 'user_view' permission
$userPermissions = explode(',', $user['permissions']); // Assuming permissions are stored as a comma-separated string
$hasViewPermission = in_array('user_view', $userPermissions);

// Prepare to show the users
$show_table = 'users';
$users = include('database/show.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>IMS View Users</title>
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
                            <h1 class="section_header"> <i class="fa fa-list"></i> List of Users</h1>
                            
                            <?php if ($hasViewPermission): ?>
                                <div class="section_content">
                                    <div class="users">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Email</th>
                                                    <th>Created By</th>
                                                    <th>Created At</th>
                                                    <th>Updated At</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($users as $index => $userItem) { ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td class="firstName"><?= $userItem['first_name'] ?></td>
                                                        <td class="lastName"><?= $userItem['last_name'] ?></td>
                                                        <td class="email"><?= $userItem['email'] ?></td>
                                                        <td><?= $userItem['created_by_first_name'] . ' ' . $userItem['created_by_last_name'] ?></td> <!-- Display Created By -->
                                                        <td><?= date('M d, Y @ h:i:s A', strtotime($userItem['created_at'])) ?></td>
                                                        <td><?= date('M d, Y @ h:i:s A', strtotime($userItem['updated_at'])) ?></td>
                                                        <td>
                                                            <a href="" class="updateUser" data-userid="<?= $userItem['id'] ?>"><i class="fa fa-pencil"></i>Edit</a>
                                                            <a href="" class="deleteUser" data-userid="<?= $userItem['id'] ?>" data-fname="<?= $userItem['first_name'] ?>" data-lname="<?= $userItem['last_name'] ?>"><i class="fa fa-trash"></i>Delete</a>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                        <p class="user_count"><?= count($users) ?> Users</p>
                                    </div>
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

    <script>
    function script() {
        this.permissions = []; // Initialize permissions array

        this.initialize = function() {
            this.registerEvents();
        };

        this.registerEvents = function() {
            document.addEventListener('click', (e) => {
                var targetElement = e.target;
                var classList = targetElement.classList;

                // Handle delete user
                if (classList.contains('deleteUser')) {
                    e.preventDefault();
                    var userId = targetElement.dataset.userid;
                    var fname = targetElement.dataset.fname;
                    var lname = targetElement.dataset.lname;
                    var fullname = fname + ' ' + lname;

                    // Check if the user has permission to delete
                    if (!<?= json_encode(in_array('user_delete', explode(',', $user['permissions']))) ?>) {
                        BootstrapDialog.alert({
                            type: BootstrapDialog.TYPE_WARNING,
                            message: 'Access Denied: You do not have permission to delete users.'
                        });
                        return; // Exit the function if the user does not have permission
                    }

                    BootstrapDialog.confirm({
                        title: 'Delete User',
                        type: BootstrapDialog.TYPE_DANGER,
                        message: 'Are you sure to delete <strong>' + fullname + '</strong> ?',
                        callback: function(isDelete) {
                            if (isDelete) {
                                $.ajax({
                                    method: 'POST',
                                    data: {
                                        id: userId,
                                        table: 'users'
                                    },
                                    url: 'database/delete.php',
                                    dataType: 'json',
                                    success: function(data) {
                                        var message = data.success ? fullname + ' successfully deleted!' : 'Error Processing Your Request.';

                                        BootstrapDialog.alert({
                                            type: data.success ? BootstrapDialog.TYPE_SUCCESS : BootstrapDialog.TYPE_DANGER,
                                            message: message,
                                            callback: function() {
                                                if (data.success) location.reload();
                                            }
                                        });
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        BootstrapDialog.alert({
                                            type: BootstrapDialog.TYPE_DANGER,
                                            message: 'An error occurred: ' + textStatus
                                        });
                                    }
                                });
                            }
                        }
                    });
                }

                // Handle update user
                if (classList.contains('updateUser')) {
                    e.preventDefault();

                    var firstName = targetElement.closest('tr').querySelector('td.firstName').innerHTML;
                    var lastName = targetElement.closest('tr').querySelector('td.lastName').innerHTML;
                    var email = targetElement.closest('tr').querySelector('td.email').innerHTML;
                    var userId = targetElement.dataset.userid;

                    // Check if the user has permission to edit
                    if (!<?= json_encode(in_array('user_edit', explode(',', $user['permissions']))) ?>) {
                        BootstrapDialog.alert({
                            type: BootstrapDialog.TYPE_WARNING,
                            message: 'Access Denied: You do not have permission to edit users.'
                        });
                        return; // Exit the function if the user does not have permission
                    }

                    // Fetch the user permissions (replace with your actual method to get permissions)
                    $.ajax({
                        method: 'GET',
                        url: 'database/get_user_permissions.php', // Endpoint to get user permissions
                        data: { user_id: userId },
                        dataType: 'json',
                        success: function(userPermissionsData) {
                            var userPermissions = userPermissionsData.permissions; // Assume this returns an array of permissions

                            BootstrapDialog.confirm({
                                title: 'Update ' + firstName + ' ' + lastName,
                                message: '<form action="/action_page.php">\
                                    <div class="form-group">\
                                        <label for="firstName">First Name:</label>\
                                        <input type="text" class="form-control" id="firstName" value="'+ firstName +'">\
                                    </div>\
                                    <div class="form-group">\
                                        <label for="lastName">Last Name:</label>\
                                        <input type="text" class="form-control" id="lastName" value="'+ lastName +'">\
                                    </div>\
                                    <div class="form-group">\
                                        <label for="email">Email address:</label>\
                                        <input type="email" class="form-control" id="emailUpdate" value="'+ email +'">\
                                    </div>\
                                </form>\
                                <div id="permissions">\
                                    <h4>Permissions</h4>\
                                    <hr>\
                                    <div id="permissionsContainer">\
                                        <div class="permission">\
                                            <div class="row">\
                                                <div class="col-md-2">\
                                                    <p class="moduleName">Dashboard</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('dashboard_view') ? 'permissionActive' : '') +'" data-value="dashboard_view">View</p>\
                                                </div>\
                                            </div>\
                                        </div>\
                                        <div class="permission">\
                                            <div class="row">\
                                                <div class="col-md-2">\
                                                    <p class="moduleName">Reports</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('reports_view') ? 'permissionActive' : '') +'" data-value="reports_view">View</p>\
                                                </div>\
                                            </div>\
                                        </div>\
                                        <div class="permission">\
                                            <div class="row">\
                                                <div class="col-md-2">\
                                                    <p class="moduleName">Stock</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('stock_view') ? 'permissionActive' : '') +'" data-value="stock_view">View</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('stock_add') ? 'permissionActive' : '') +'" data-value="stock_add">Add</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('stock_delete') ? 'permissionActive' : '') +'" data-value="stock_delete">Delete</p>\
                                                </div>\
                                            </div>\
                                        </div>\
                                        <div class="permission">\
                                            <div class="row">\
                                                <div class="col-md-2">\
                                                    <p class="moduleName">Order</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('order_view') ? 'permissionActive' : '') +'" data-value="order_view">View</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('order_add') ? 'permissionActive' : '') +'" data-value="order_add">Add</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('order_update') ? 'permissionActive' : '') +'" data-value="order_update">Update</p>\
                                                </div>\
                                            </div>\
                                        </div>\
                                        <div class="permission">\
                                            <div class="row">\
                                                <div class="col-md-2">\
                                                    <p class="moduleName">Product</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('product_view') ? 'permissionActive' : '') +'" data-value="product_view">View</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('product_add') ? 'permissionActive' : '') +'" data-value="product_add">Add</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('product_edit') ? 'permissionActive' : '') +'" data-value="product_edit">Edit</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('product_delete') ? 'permissionActive' : '') +'" data-value="product_delete">Delete</p>\
                                                </div>\
                                            </div>\
                                        </div>\
                                        <div class="permission">\
                                            <div class="row">\
                                                <div class="col-md-2">\
                                                    <p class="moduleName">Category</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('category_view') ? 'permissionActive' : '') +'" data-value="category_view">View</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('category_add') ? 'permissionActive' : '') +'" data-value="category_add">Add</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('category_edit') ? 'permissionActive' : '') +'" data-value="category_edit">Edit</p>\
                                                </div>\
                                                <div class="col-md-2">\
                                                    <p class="moduleFunc '+ (userPermissions.includes('category_delete') ? 'permissionActive' : '') +'" data-value="category_delete">Delete</p>\
                                                </div>\
                                            </div>\
                                        </div>\
                                    </div>\
                                </div>',
                                callback: function(isConfirm) {
                                    if (isConfirm) {
                                        // Collect updated data
                                        var updatedFirstName = document.getElementById('firstName').value;
                                        var updatedLastName = document.getElementById('lastName').value;
                                        var updatedEmail = document.getElementById('emailUpdate').value;

                                        // Collect selected permissions
                                        var permissions = [];
                                        document.querySelectorAll('.permission .moduleFunc.permissionActive').forEach(function(permissionElement) {
                                            permissions.push(permissionElement.dataset.value);
                                        });

                                        // Update user info
                                        $.ajax({
                                            method: 'POST',
                                            url: 'database/update_user.php',
                                            data: {
                                                user_id: userId,  // Changed to 'user_id'
                                                f_name: updatedFirstName,  // Changed to 'f_name'
                                                l_name: updatedLastName,  // Changed to 'l_name'
                                                email: updatedEmail,
                                                permissions: permissions // Send as an array, not joined
                                            },
                                            dataType: 'json',
                                            success: function(response) {
                                                if (response.success) {
                                                    BootstrapDialog.alert({
                                                        type: BootstrapDialog.TYPE_SUCCESS,
                                                        message: 'User updated successfully!',
                                                        callback: function() {
                                                            location.reload();
                                                        }
                                                    });
                                                } else {
                                                    BootstrapDialog.alert({
                                                        type: BootstrapDialog.TYPE_DANGER,
                                                        message: 'Error updating user: ' + response.message // Include error message
                                                    });
                                                }
                                            },
                                            error: function(jqXHR, textStatus, errorThrown) {
                                                BootstrapDialog.alert({
                                                    type: BootstrapDialog.TYPE_DANGER,
                                                    message: 'An error occurred: ' + textStatus
                                                });
                                            }
                                        });

                                    }
                                }
                            });
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            BootstrapDialog.alert({
                                type: BootstrapDialog.TYPE_DANGER,
                                message: 'An error occurred while fetching permissions: ' + textStatus
                            });
                        }
                    });
                }

                // Handle permission toggling
                if (classList.contains('moduleFunc')) {
                    targetElement.classList.toggle('permissionActive');
                }
            });
        };
    }

    window.onload = function() {
        var app = new script();
        app.initialize();
    };
</script>


</body>
</html>
