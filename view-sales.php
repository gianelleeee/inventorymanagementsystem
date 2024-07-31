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
    <title>IMS View Sales History</title>
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
                        <h1 class="section_header"><i class="fa fa-list"></i> Sales History</h1>
                            <div class="section_content">
                                <div class="soListContainers">
                                    <div class="soList">
                                        <?php
                                            $stmt = $conn->prepare("SELECT sales_product.id, sales_product.date, sales_product.product, products.product_name, sales_product.sales, users.first_name, users.last_name, category.category_name, sales_product.created_at
                                                FROM sales_product
                                                JOIN category ON sales_product.category = category.id
                                                JOIN products ON sales_product.product = products.id
                                                JOIN users ON sales_product.created_by = users.id
                                                ORDER BY sales_product.date DESC");
                                            $stmt->execute();
                                            $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            // Organize data by date
                                            $data = [];
                                            foreach($sales_data as $sale) {
                                                $date = $sale['date'];
                                                if (!isset($data[$date])) {
                                                    $data[$date] = [];
                                                }
                                                $data[$date][] = $sale;
                                            }
                                        ?>

                                        <?php
                                            foreach($data as $sale_date => $sales){
                                        ?>
                                        <div class="poList" id="container-<?= str_replace(['-', ' '], ['_', '_'], $sale_date) ?>">
                                            <p>Date: <?= $sale_date ?></p>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Product</th>
                                                        <th>Sales</th>
                                                        <th>Category</th>
                                                        <th>Added By</th>
                                                        <th>Created Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        foreach($sales as $index => $sale){
                                                    ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td class="po_product"><?= $sale['product_name']?></td>
                                                        <td class="po_qty_salesed"><?= $sale['sales']?></td>
                                                        <td class="po_category"><?= $sale['category_name']?></td>
                                                        <td><?= $sale['first_name'] . ' ' . $sale['last_name']?></td>
                                                        <td>
                                                            <?= $sale['created_at']?>
                                                            <input type="hidden" class="po_qty_row_id" value="<?= $sale['id']?>">
                                                            <input type="hidden" class="po_qty_productid" value="<?= $sale['product']?>">
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
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

</body>
</html>
