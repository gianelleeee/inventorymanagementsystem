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
                                                        <td class="po_product"><?= $batch_po['product_name']?></td>
                                                        <td class="po_qty_ordered"><?= $batch_po['quantity_ordered']?></td>
                                                        <td class="po_qty_received"><?= $batch_po['quantity_received']?></td>
                                                        <td class="po_category"><?= $batch_po['category_name']?></td>
                                                        <td class="po_status"><span class="po-badge po-badge-<?= $batch_po['status']?>"><?= $batch_po['status']?></span></td>
                                                        <td><?= $batch_po['first_name'] . ' ' . $batch_po['last_name']?></td>
                                                        <td>
                                                            <?= $batch_po['created_at']?>
                                                            <input type="hidden" class="po_qty_row_id" value="<?= $batch_po['id']?>">
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
                targetElement = e.target;
                classList = targetElement.classList;

                if(classList.contains('updatePoBtn')){
                    e.preventDefault();

                    batchNumber = targetElement.dataset.id;
                    batchNumberContainer = 'container-' + batchNumber;


                    //get all purchase order product records
                    productList = document.querySelectorAll('#' + batchNumberContainer + ' .po_product');
                    qtyOrderedList = document.querySelectorAll('#' + batchNumberContainer + ' .po_qty_ordered');
                    qtyReceivedList = document.querySelectorAll('#' + batchNumberContainer + ' .po_qty_received');
                    categoryList = document.querySelectorAll('#' + batchNumberContainer + ' .po_category');
                    statusList = document.querySelectorAll('#' + batchNumberContainer + ' .po_status');
                    rowIds = document.querySelectorAll('#' + batchNumberContainer + ' .po_qty_row_id');

                    poListArr =[];

                    for(i=0;i<productList.length;i++){
                        poListArr.push({
                            name: productList[i].innerText,
                            qtyOrdered: qtyOrderedList[i].innerText,
                            qtyReceived: qtyReceivedList[i].innerText,
                            category: categoryList[i].innerText,
                            status: statusList[i].innerText,
                            id: rowIds[i].value
                        });
                    }

                    //store in html
                    var poListHtml = '\
                        <table id="formTable_'+ batchNumber +'">\
                            <thead>\
                                <tr>\
                                    <th>Product Name</th>\
                                    <th>Quantity Ordered</th>\
                                    <th>Quantity Received</th>\
                                    <th>Category</th>\
                                    <th>Status</th>\
                                </tr>\
                            </thead>\
                            <tbody>';

                            
                            poListArr.forEach((poList) => {
                                poListHtml +='\
                                    <tr>\
                                        <td class="po_product alignLeft">'+ poList.name +'</td>\
                                        <td class="po_qty_ordered">'+ poList.qtyOrdered +'</td>\
                                        <td class="po_qty_received"><input type="number" value="'+ poList.qtyReceived +'"/></td>\
                                        <td class="po_category alignLeft">'+ poList.category +'</td>\
                                        <td>\
                                            <select class="po_status">\
                                                <option value="pending" '+ (poList.status == 'pending' ? 'selected' : '') +'>Pending</option>\
                                                <option value="incomplete" '+ (poList.status == 'incomplete' ? 'selected' : '') +'>Incomplete</option>\
                                                <option value="complete" '+ (poList.status == 'complete' ? 'selected' : '') +'>Complete</option>\
                                            </select>\
                                            <input type="hidden" class="po_qty_row_id" value="'+ poList.id +'">\
                                        </td>\
                                    </tr>\
                                ';
                            });
                            poListHtml += '</tbody></table>';

                            console.log(poListHtml);

                            BootstrapDialog.confirm({
                            type: BootstrapDialog.TYPE_PRIMARY,
                            title: 'Update Purchase Order: Batch #: <strong>'+ batchNumber +'</strong>',
                            message: poListHtml,
                            callback: function (toAdd) {
                                //if we add
                                if(toAdd){
                                    formTableContainer = 'formTable_' + batchNumber;
                                    //get all purchase order product records
                                    qtyReceivedList = document.querySelectorAll('#' + formTableContainer + ' .po_qty_received input');
                                    statusList = document.querySelectorAll('#' + formTableContainer + ' .po_status');
                                    qtyReceivedList = document.querySelectorAll('#' + formTableContainer + ' .po_qty_received input');
                                    qtyOrdered = document.querySelectorAll('#' + formTableContainer + ' .po_qty_ordered');

                                    poListArrForm =[];

                                    for(i=0;i<qtyReceivedList.length;i++){
                                        poListArrForm.push({
                                            qtyReceived: qtyReceivedList[i].value,
                                            status: statusList[i].value,
                                            id: rowIds[i].value,
                                            qtyOrdered: qtyOrdered[i].innerText
                                        });
                                    }

                                    //send request / update database
                                    $.ajax({
                                        method: 'POST',
                                        data: {
                                            payload: poListArrForm
                                        },
                                        url: 'database/update-order.php',
                                        dataType: 'json',
                                        success: function (data) {
                                            message = data.message;
            
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
            })
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
