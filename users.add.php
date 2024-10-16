<?php
// Start the session
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$user = $_SESSION['user'];

// Check for 'user_add' permission
$userPermissions = explode(',', $user['permissions']); // Assuming permissions are stored as a comma-separated string
$hasAddPermission = in_array('user_add', $userPermissions);

// Prepare to show the users
$show_table = 'users';
$_SESSION['redirect_to'] = 'users.add.php';
$users = include('database/show.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>IMS Add Users</title>
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
                            <h1 class="section_header"> <i class="fa fa-plus"></i> Create User</h1>
                            
                            <?php if ($hasAddPermission): ?>
                                <div id="userAddFormContainer">
                                    <form action="database/add-user.php" method="POST" class="appForm" id="userAddForm">
                                        <div class="appFormInputContainer">
                                            <label for="email">Email</label>
                                            <input type="email" class="appFormInput" name="email" id="email" required>
                                        </div>
                                        <input type="hidden" id="permission_el" name="permissions">
                                        <?php include('partials/permissions.php'); ?>
                                        <input type="hidden" name="table" value="users">
                                        <button type="submit" class="appBtn"><i class="fa fa-add"></i> Add User</button>
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

    <script>
function loadScript() {
    this.permissions = [];

    this.initialize = function() {
        this.registerEvent();
        this.addFormValidation(); // Call the validation function
    };

    this.registerEvent = function() {
        // Click event
        document.addEventListener('click', (e) => {
            let target = e.target;

            // Check if class name = moduleFunc - is clicked
            if (target.classList.contains('moduleFunc')) {
                // Get value
                let permissionName = target.dataset.value;

                // Toggle active class
                if (target.classList.contains('permissionActive')) {
                    target.classList.remove('permissionActive');

                    // Remove from array
                    script.permissions = script.permissions.filter((name) => {
                        return name !== permissionName;
                    });
                } else {
                    target.classList.add('permissionActive');
                    script.permissions.push(permissionName);
                }

                // Update the hidden element
                document.getElementById('permission_el').value = script.permissions.join(',');
            }
        });
    };

    this.addFormValidation = function() {
        // Form submission validation
        document.getElementById('userAddForm').addEventListener('submit', function(e) {
            if (script.permissions.length === 0) {
                e.preventDefault(); // Prevent form submission
                alert('Please select at least one permission.');
            }
        });
    };
}

var script = new loadScript(); // Create an instance
script.initialize(); // Initialize the script
</script>

</body>
</html>
