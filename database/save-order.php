<?php
session_start();

$post_data = $_POST;
$products = $post_data['products'];
$qty = array_values($post_data['quantity']);

$post_data_arr = [];

foreach($products as $key => $pid) {
    if(isset($qty[$key])) {
        $post_data_arr[$pid] = $qty[$key];
    }
}

// Include connection
include('connection.php');

// Store data
$batch = time();

$success = false;
try {
    foreach($post_data_arr as $pid => $category_qty) {
        foreach($category_qty as $cid => $qty) {
            // Insert to database
            $values = [
                'category' => $cid,
                'product' => $pid,
                'quantity_ordered' => $qty,
                'status' => 'ORDERED',
                'batch' => $batch,
                'created_by' => $_SESSION['user']['id'],
                'updated_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $sql = "INSERT INTO order_product
                        (category, product, quantity_ordered, status, batch, created_by, updated_at, created_at) 
                    VALUES
                        (:category, :product, :quantity_ordered, :status, :batch, :created_by, :updated_at, :created_at)";

            $stmt = $conn->prepare($sql);
            $stmt->execute($values);
        }
    }
    
    $success = true;
    $message = 'Order Successfully Created!';

} catch(\Exception $e) {
    $message = $e->getMessage();
}

$_SESSION['response'] = [
    'message' => $message,
    'success' => $success
];

header('Location: ../products-order.php');
exit;

?>
