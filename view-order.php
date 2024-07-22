<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

include('database/connection.php');
$user = $_SESSION['user'];



?>

<!DOCTYPE html>
<html>
<head>
    <title>IMS View Purchase History</title>
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
                        <h1 class="section_header"><i class="fa fa-list"></i> Purchase History</h1>
                            <div class="section_content">
                                <div class="poListContainers">
                                    <div class="poList">
                                        <?php
                                            $stmt = $conn->prepare("SELECT order_product.id, products.product_name, order_product.quantity_ordered, order_product.quantity_received, users.first_name, users.last_name, category.category_name, order_product.status, order_product.created_at, order_product.batch
                                                FROM order_product, category, products, users 
                                                WHERE 
                                                    order_product.category = category.id 
                                                AND 
                                                    order_product.product = products.id 
                                                AND 
                                                    order_product.created_by = users.id
                                                ORDER BY 
                                                    order_product.created_at DESC");
                                            $stmt->execute();
                                            $purchase_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            $data = [];
                                            foreach($purchase_orders as $purchase_order){
                                                $data[$purchase_order['batch']][] = $purchase_order;
                                            }
                                        ?>

                                        <?php
                                            foreach($data as $batch_id => $batch_pos){
                                        ?>
                                        <p>Batch #: <?= $batch_id ?></p>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Product</th>
                                                    <th>Quantity Ordered</th>
                                                    <th>Quantity Received</th>
                                                    <th>Category</th>
                                                    <th>Status</th>
                                                    <th>ordered by</th>
                                                    <th>Created Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    foreach($batch_pos as $index => $batch_po){
                                                ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= $batch_po['product_name']?></td>
                                                    <td><?= $batch_po['quantity_ordered']?></td>
                                                    <td><?= $batch_po['quantity_received']?></td>
                                                    <td><?= $batch_po['category_name']?></td>
                                                    <td><span class="po-badge po-badge-<?= $batch_po['status']?>"><?= $batch_po['status']?></span></td>
                                                    <td><?= $batch_po['first_name'] . ' ' . $batch_po['last_name']?></td>
                                                    <td><?= $batch_po['created_at']?></td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                        <div class="poOrderUpdateBtnContainer alignRight">
                                            <button class="orderBtn updatePoBtn" data-id="<?= $batch_id?>">Update</button>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('partials/scripts.php');?>



<script>

    function script() {
        var vm = this;

        this.registerEvents = function() {
        },

        this.saveUpdatedData = function() {
        },

        this.showEditDialog = function() {
        },

        this.initialize = function() {
            this.registerEvents();
        }
    }
    var script = new script;
    script.initialize();
</script>



</body>
</html>
