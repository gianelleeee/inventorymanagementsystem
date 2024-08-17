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
                                        $stmt = $conn->prepare("SELECT order_product.id, order_product.product, products.product_name, order_product.quantity_ordered, order_product.quantity_received, users.first_name, users.last_name, category.category_name, order_product.status, order_product.created_at, order_product.batch
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
                                        <div class="poList" id="container-<?= $batch_id ?>">
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
                                                        <th>Ordered By</th>
                                                        <th>Created Date</th>
                                                        <th>Delivery History</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        foreach($batch_pos as $index => $batch_po){
                                                    ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td class="po_product"><?= $batch_po['product_name']?></td>
                                                        <td class="po_qty_ordered"><?= $batch_po['quantity_ordered']?></td>
                                                        <td class="po_qty_received"><?= $batch_po['quantity_received']?></td>
                                                        <td class="po_category"><?= $batch_po['category_name']?></td>
                                                        <td class="po_status"><span class="po-badge po-badge-<?= $batch_po['status']?>"><?= $batch_po['status']?></span></td>
                                                        <td><?= $batch_po['first_name'] . ' ' . $batch_po['last_name']?></td>
                                                        <td>
                                                            <?= $batch_po['created_at']?>
                                                            <input type="hidden" class="po_qty_row_id" value="<?= $batch_po['id']?>">
                                                            <input type="hidden" class="po_qty_productid" value="<?= $batch_po['product']?>">
                                                        </td>
                                                        <td>
                                                            <button class="appDeliveryHistoryBtn" data-id="<?= $batch_po['id']?>">Delivery History</button>
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                            <div class="poOrderUpdateBtnContainer alignRight">
                                                <button class="orderBtn updatePoBtn" data-id="<?= $batch_id?>">Update</button>
                                            </div>
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
            document.addEventListener('click', function(e){
                var targetElement = e.target;
                var classList = targetElement.classList;

                if(classList.contains('updatePoBtn')){
                    e.preventDefault();

                    var batchNumber = targetElement.dataset.id;
                    var batchNumberContainer = 'container-' + batchNumber;

                    // Get all purchase order product records
                    var productList = document.querySelectorAll('#' + batchNumberContainer + ' .po_product');
                    var qtyOrderedList = document.querySelectorAll('#' + batchNumberContainer + ' .po_qty_ordered');
                    var qtyReceivedList = document.querySelectorAll('#' + batchNumberContainer + ' .po_qty_received');
                    var categoryList = document.querySelectorAll('#' + batchNumberContainer + ' .po_category');
                    var statusList = document.querySelectorAll('#' + batchNumberContainer + ' .po_status');
                    var rowIds = document.querySelectorAll('#' + batchNumberContainer + ' .po_qty_row_id');
                    var pIds = document.querySelectorAll('#' + batchNumberContainer + ' .po_qty_productid');

                    var poListArr = [];

                    for(var i = 0; i < productList.length; i++){
                        poListArr.push({
                            name: productList[i].innerText,
                            qtyOrdered: qtyOrderedList[i].innerText,
                            qtyReceived: qtyReceivedList[i].innerText,
                            category: categoryList[i].innerText,
                            status: statusList[i].innerText,
                            id: rowIds[i].value,
                            pid: pIds[i].value,
                            qtyDelivered: 0 // Initialize qtyDelivered to 0
                        });
                    }

                    // Store in HTML
                    var poListHtml = '\
                        <table id="formTable_'+ batchNumber +'">\
                            <thead>\
                                <tr>\
                                    <th>Product Name</th>\
                                    <th>Quantity Ordered</th>\
                                    <th>Quantity Received</th>\
                                    <th>Quantity Delivered</th>\
                                    <th>Category</th>\
                                    <th>Status</th>\
                                </tr>\
                            </thead>\
                            <tbody>';

                    poListArr.forEach(function(poList) {
                        var disabledInput = poList.status === 'Complete' ? 'disabled' : '';
                        poListHtml += '\
                            <tr>\
                                <td class="po_product alignLeft">'+ poList.name +'</td>\
                                <td class="po_qty_ordered">'+ poList.qtyOrdered +'</td>\
                                <td class="po_qty_received">'+ poList.qtyReceived +'</td>\
                                <td class="po_qty_delivered"><input type="number" value="0" min="0" '+ disabledInput +'/></td>\
                                <td class="po_category alignLeft">'+ poList.category +'</td>\
                                <td class="po_status">'+ poList.status +'</td>\
                                <input type="hidden" class="po_qty_row_id" value="'+ poList.id +'">\
                                <input type="hidden" class="po_qty_pid" value="'+ poList.pid +'">\
                                <input type="hidden" class="po_qty_ordered_hidden" value="'+ poList.qtyOrdered +'">\
                                <input type="hidden" class="po_qty_received_hidden" value="'+ poList.qtyReceived +'">\
                            </tr>\
                        ';
                    });

                    poListHtml += '</tbody></table>';

                    BootstrapDialog.confirm({
                        type: BootstrapDialog.TYPE_PRIMARY,
                        title: 'Update Purchase Order: Batch #: <strong>'+ batchNumber +'</strong>',
                        message: poListHtml,
                        callback: function (toAdd) {
                            if(toAdd){
                                var formTableContainer = 'formTable_' + batchNumber;
                                var qtyReceivedList = document.querySelectorAll('#' + formTableContainer + ' .po_qty_received');
                                var qtyDeliveredList = document.querySelectorAll('#' + formTableContainer + ' .po_qty_delivered input');
                                var statusList = document.querySelectorAll('#' + formTableContainer + ' .po_status');
                                var rowIds = document.querySelectorAll('#' + formTableContainer + ' .po_qty_row_id');
                                var qtyOrdered = document.querySelectorAll('#' + formTableContainer + ' .po_qty_ordered');
                                var pids = document.querySelectorAll('#' + formTableContainer + ' .po_qty_pid');
                                var qtyOrderedHidden = document.querySelectorAll('#' + formTableContainer + ' .po_qty_ordered_hidden');
                                var qtyReceivedHidden = document.querySelectorAll('#' + formTableContainer + ' .po_qty_received_hidden');

                                var poListArrForm = [];
                                var changesMade = false;
                                var valid = true;

                                for(var i = 0; i < qtyDeliveredList.length; i++){
                                    var deliveredQty = qtyDeliveredList[i].value;
                                    var orderedQty = qtyOrdered[i].innerText;
                                    var receivedQty = qtyReceivedList[i].innerText;
                                    var status = statusList[i].innerText;

                                    // Check if the delivered quantity is negative
                                    if(parseInt(deliveredQty) < 0) {
                                        valid = false;
                                        BootstrapDialog.alert({
                                            type: BootstrapDialog.TYPE_WARNING,
                                            message: 'Quantity Delivered cannot be negative for Product: ' + qtyOrdered[i].innerText
                                        });
                                        break;
                                    }

                                    // Check if the quantity received exceeds the quantity ordered
                                    if(parseInt(receivedQty) > parseInt(orderedQty)) {
                                        valid = false;
                                        BootstrapDialog.alert({
                                            type: BootstrapDialog.TYPE_WARNING,
                                            message: 'Quantity Received cannot exceed Quantity Ordered for Product: ' + qtyOrdered[i].innerText
                                        });
                                        break;
                                    }

                                    // Check if there's any change
                                    if(deliveredQty != 0 || orderedQty != qtyOrderedHidden[i].value || receivedQty != qtyReceivedHidden[i].value) {
                                        changesMade = true;
                                    }

                                    poListArrForm.push({
                                        qtyReceived: receivedQty,
                                        qtyDelivered: deliveredQty,
                                        status: status,
                                        id: rowIds[i].value,
                                        qtyOrdered: orderedQty,
                                        pid: pids[i].value
                                    });
                                }

                                if(valid){
                                    if(changesMade){
                                        $.ajax({
                                            type: 'POST',
                                            url: 'database/update-order.php',
                                            data: {
                                                payload: poListArrForm
                                            },
                                            success: function(response) {
                                                var result = JSON.parse(response);
                                                BootstrapDialog.alert({
                                                    type: result.success ? BootstrapDialog.TYPE_SUCCESS : BootstrapDialog.TYPE_DANGER,
                                                    message: result.message
                                                });
                                                if(result.success) {
                                                    setTimeout(function() {
                                                        window.location.reload();
                                                    }, 2000);
                                                }
                                            },
                                            error: function() {
                                                BootstrapDialog.alert({
                                                    type: BootstrapDialog.TYPE_DANGER,
                                                    message: 'An error occurred while processing your request.'
                                                });
                                            }
                                        });
                                    } else {
                                        BootstrapDialog.alert({
                                            type: BootstrapDialog.TYPE_INFO,
                                            message: 'No changes were made.'
                                        });
                                    }
                                }
                            }
                        }
                    });
                }
            });
        };

        this.initialize = function() {
            this.registerEvents();
        };
    }

    $(document).ready(function() {
        var app = new script();
        app.initialize();
    });
    </script>
</body>
</html>
