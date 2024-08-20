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
                                        // Updated SQL query to get available_stock directly from sales_product
                                        $stmt = $conn->prepare("
                                            SELECT 
                                                sales_product.id, 
                                                sales_product.date, 
                                                sales_product.product, 
                                                products.product_name, 
                                                sales_product.available_stock, 
                                                sales_product.sales, 
                                                COALESCE(CONCAT(users.first_name, ' ', users.last_name), 'User Deleted') AS full_name, 
                                                category.category_name, 
                                                sales_product.created_at
                                            FROM 
                                                sales_product
                                            JOIN 
                                                products ON sales_product.product = products.id
                                            JOIN 
                                                category ON sales_product.category = category.id
                                            LEFT JOIN 
                                                users ON sales_product.created_by = users.id
                                            ORDER BY 
                                                sales_product.date DESC
                                        ");
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
                                                        <th>Available Stock</th> <!-- New Column -->
                                                        <th>Sales</th>
                                                        <th>Category</th>
                                                        <th>Added By</th>
                                                        <th>Created Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($sales as $index => $sale): ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td class="po_product"><?= $sale['product_name']?></td>
                                                        <td class="po_stock"><?= $sale['available_stock']?></td> <!-- New Column -->
                                                        <td class="po_qty_sales"><?= $sale['sales']?></td>
                                                        <td class="po_category"><?= $sale['category_name']?></td>
                                                        <td><?= $sale['full_name']?></td>
                                                        <td>
                                                            <?= $sale['created_at']?>
                                                            <input type="hidden" class="po_qty_row_id" value="<?= $sale['id']?>">
                                                            <input type="hidden" class="po_qty_productid" value="<?= $sale['product']?>">
                                                        </td>
                                                        <td>
                                                            <button class="appDeliveryHistoryBtn deleteSalesBtn" data-id="<?= $sale['id']?>">Delete</button>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <!-- <div class="soSalesUpdateBtnContainer alignRight"> -->
                                                <!-- Removed undefined variable batch_id
                                                <button class="orderBtn updateSoBtn" data-id="<?= str_replace(['-', ' '], ['_', '_'], $sale_date) ?>">Update</button>
                                            </div> -->
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
        function script(){
        var vm = this;

        this.registerEvents = function() {
            document.addEventListener('click', function(e){
                var targetElement = e.target;
                var classList = targetElement.classList;

                if(classList.contains('updateSoBtn')){
                    e.preventDefault();

                    var batchNumber = targetElement.dataset.id;
                    var batchNumberContainer = 'container-' + batchNumber;

                    // Get all sales record
                    var productList = document.querySelectorAll('#' + batchNumberContainer + ' .po_product');
                    var stockList = document.querySelectorAll('#' + batchNumberContainer + ' .po_stock'); // New List
                    var sales = document.querySelectorAll('#' + batchNumberContainer + ' .po_qty_sales');
                    var categoryList = document.querySelectorAll('#' + batchNumberContainer + ' .po_category');
                    var rowIds = document.querySelectorAll('#' + batchNumberContainer + ' .po_qty_row_id');
                    var pIds = document.querySelectorAll('#' + batchNumberContainer + ' .po_qty_productid');

                    var soListArr = [];

                    for(var i = 0; i < productList.length; i++){
                        soListArr.push({
                            name: productList[i].innerText,
                            stock: stockList[i].innerText, // New Data
                            sale: sales[i].innerText,
                            category: categoryList[i].innerText,
                            id: rowIds[i].value,
                            pid: pIds[i].value
                        });
                    }

                    // Store in HTML
                    var soListHtml = '\
                        <table id="formTable_'+ batchNumber +'">\
                            <thead>\
                                <tr>\
                                    <th>Product Name</th>\
                                    <th>Sales</th>\
                                    <th>Category</th>\
                                </tr>\
                            </thead>\
                        <tbody>';

                    soListArr.forEach(function(poList){
                        soListHtml += '\
                            <tr>\
                                <td class="po_product alignLeft">'+ poList.name +'</td>\
                                <td class="po_sales"><input type="number" value="'+ poList.sale + '"/></td>\
                                <td class="po_category alignLeft">'+ poList.category +'</td>\
                                <input type="hidden" class="po_qty_row_id" value="'+ poList.id +'">\
                                <input type="hidden" class="po_qty_pid" value="'+ poList.pid +'">\
                            </tr>';
                    });
                    soListHtml += '</tbody></table>'; 

                    BootstrapDialog.confirm({
                        type: BootstrapDialog.TYPE_PRIMARY,
                        title: 'Update Sales History: Date: <strong>'+ batchNumber +'</strong>',
                        message: soListHtml,
                        callback: function (toAdd) {
                            if(toAdd){
                                var formTableContainer = 'formTable_' + batchNumber;
                                var rowIds = document.querySelectorAll('#' + formTableContainer + ' .po_qty_row_id');
                                var sales = document.querySelectorAll('#' + formTableContainer + ' .po_sales');
                                var pids = document.querySelectorAll('#' + formTableContainer + ' .po_qty_pid');

                                var poListArrForm = [];

                                for(var i = 0; i < rowIds.length; i++){
                                    poListArrForm.push({
                                        sale: sales[i].querySelector('input').value,
                                        id: rowIds[i].value,
                                        pid: pids[i].value
                                    });
                                }

                                // Send request / update database
                                $.ajax({
                                method: 'POST',
                                data: JSON.stringify({ payload: poListArrForm }), // Convert the data to JSON string
                                url: 'database/update-stocks.php',
                                dataType: 'json',
                                contentType: 'application/json', // Set the content type to JSON
                                success: function (data) {
                                    var message = data.message;

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
                }

                if(classList.contains('deleteSalesBtn')){
                    e.preventDefault();

                    var salesId = targetElement.dataset.id;

                    BootstrapDialog.confirm({
                        type: BootstrapDialog.TYPE_DANGER,
                        title: 'Delete Sales Record',
                        message: 'Are you sure you want to delete this sales record?',
                        callback: function (result) {
                            if(result){
                                $.ajax({
                                    method: 'POST',
                                    url: 'database/delete-sales.php',
                                    data: JSON.stringify({ id: salesId }), // Pass the sales ID
                                    dataType: 'json',
                                    contentType: 'application/json',
                                    success: function (data) {
                                        var message = data.message;

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
                }
            });
        }

        this.registerEvents();
    }

    // Initialize the script
    new script();

    </script>

</body>
</html>
