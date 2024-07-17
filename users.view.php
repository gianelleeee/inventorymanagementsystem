<?php
// Start the session
    session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit();
    }

    $show_table = 'users';
    $user = $_SESSION['user'];
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
                            <h1 class="section_header"> <i class="fa fa-list"></i> List of User</h1>
                            <div class="section_content">
                                <div class="users">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Email</th>
                                                <th>Created At</th>
                                                <th>Updated At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($users as $index => $user){ ?>
                                                <tr>
                                                    <td><?= $index +1 ?></td>
                                                    <td class="firstName"><?= $user['first_name']?></td>
                                                    <td class="lastName"><?= $user['last_name']?></td>
                                                    <td class="email"><?= $user['email']?></td>
                                                    <td><?= date('M d, Y @ h:i:s: A', strtotime($user['created_at'])) ?></td>
                                                    <td><?= date('M d, Y @ h:i:s: A', strtotime($user['updated_at'])) ?></td>
                                                    <td>
                                                        <a href="" class="updateUser" data-userid="<?= $user['id']?>"><i class="fa fa-pencil"></i>Edit</a>
                                                        <a href="" class="deleteUser" data-userid="<?= $user['id']?>" data-fname="<?= $user['first_name']?>" data-lname="<?= $user['last_name']?>"><i class="fa fa-trash"></i>Delete</a>
                                                    </td>
                                                </tr>
                                            <?php }?>
                                            
                                        </tbody>
                                    </table>
                                    <p class="user_count"><?= count($users) ?> Users</p>
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
    function script(){

        this.initialize = function(){
            this.registerEvents();
        };

        this.registerEvents = function(){
            document.addEventListener('click', function(e){
                var targetElement = e.target;
                var classList = targetElement.classList;

                if(classList.contains('deleteUser')){
                    e.preventDefault();
                    var userId = targetElement.dataset.userid;
                    var fname = targetElement.dataset.fname;
                    var lname = targetElement.dataset.lname;
                    var fullname = fname + ' ' + lname;

                    BootstrapDialog.confirm({
                        title: 'Delete User',
                        type: BootstrapDialog.TYPE_DANGER,
                        message: 'Are you sure to delete <strong>' + fullname + '</strong> ?',
                        callback: function(isDelete){
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

                if(classList.contains('updateUser')){
                    e.preventDefault();

                    var firstName = targetElement.closest('tr').querySelector('td.firstName').innerHTML;
                    var lastName = targetElement.closest('tr').querySelector('td.lastName').innerHTML;
                    var email = targetElement.closest('tr').querySelector('td.email').innerHTML;
                    var userId = targetElement.dataset.userid;

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
                        </form>',
                        callback: function(isUpdate){
                            if(isUpdate){
                                $.ajax({
                                    method: 'POST',
                                    data: {
                                        user_id: userId,
                                        f_name: document.getElementById('firstName').value,
                                        l_name: document.getElementById('lastName').value,
                                        email: document.getElementById('emailUpdate').value
                                    },
                                    url: 'database/update_user.php',
                                    dataType: 'json',
                                    success: function(data){
                                        if(data.success){
                                            BootstrapDialog.alert({
                                                type: BootstrapDialog.TYPE_SUCCESS,
                                                message: data.message,
                                                callback: function(){
                                                    location.reload();
                                                }
                                            });
                                        }else{
                                            BootstrapDialog.alert({
                                                type: BootstrapDialog.TYPE_DANGER,
                                                message: data.message
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    });
                }
            });
        };

    }

    var myScript = new script();
    myScript.initialize();
</script>

</body>
</html>
