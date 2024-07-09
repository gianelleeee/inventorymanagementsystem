<?php
// Start the session
    session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit();
    }

    $_SESSION['table'] = 'products';
    $user = $_SESSION['user'];
    $products = include('database/show.php');
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
                                                <th>Description</th>
                                                <th>Created By</th>
                                                <th>Created At</th>
                                                <th>Updated At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($products as $index => $product){ ?>
                                                <tr>
                                                    <td><?= $index +1 ?></td>
                                                    <td class="firstName"><?= $product['product_name']?></td>
                                                    <td class="lastName"><?= $product['description']?></td>
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
                                                    <td><?= date('M d, Y @ h:i:s: A', strtotime($product['created_at'])) ?></td>
                                                    <td><?= date('M d, Y @ h:i:s: A', strtotime($product['updated_at'])) ?></td>
                                                    <td>
                                                        <a href="" class="updateProduct" data-pid="<?= $product['id']?>"><i class="fa fa-pencil"></i>Edit</a>
                                                        <a href="" class="deleteProduct" data-name="<?= $product['product_name'] ?>" data-pid="<?= $product['id']?>" ><i class="fa fa-trash"></i>Delete</a>
                                                    </td>
                                                </tr>
                                            <?php }?>
                                            
                                        </tbody>
                                    </table>
                                    <p class="user_count"><?= count($products) ?> Products</p>
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
    function Script() {
        this.registerEvents = function() {
            document.addEventListener('click', function(e) {
                var targetElement = e.target;
                var classList = targetElement.classList;

                if (classList.contains('deleteProduct')) {
                    e.preventDefault(); // Prevents the default mechanism

                    var pId = targetElement.dataset.pid;
                    var pName = targetElement.dataset.name;

                    BootstrapDialog.confirm({
                        type: BootstrapDialog.TYPE_DANGER,
                        title: 'Delete Product',
                        message: 'Are you sure to delete <strong>' + pName + '</strong>?',
                        callback: function(isDelete) {
                            if (isDelete) {
                                $.ajax({
                                    method: 'POST',
                                    data: {
                                        id: pId,
                                        table: 'products'
                                    },
                                    url: 'database/delete.php',
                                    dataType: 'json',
                                    success: function(data) {
                                        var message = data.success ? pName + ' successfully deleted!' : 'Error Processing Your Request.';

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
            });
        }
    }

    // Initialize the script and register events
    var myScript = new Script();
    myScript.registerEvents();
</script>




</body>
</html>
