<?php
session_start();

$post_data = $_POST;
$products = $post_data['products'];
$qty = array_values($post_data['quantity']);
$date = $post_data['date'];

$post_data_arr = [];

// Build an array of products and their quantities
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
    // Begin a transaction
    $conn->beginTransaction();

    // Insert sales data
    foreach($post_data_arr as $pid => $category_qty) {
        foreach($category_qty as $cid => $qty) {
            // Calculate available stock
            $stock_query = "SELECT stock FROM products WHERE id = :pid";
            $stock_stmt = $conn->prepare($stock_query);
            $stock_stmt->execute(['pid' => $pid]);
            $current_stock = $stock_stmt->fetchColumn();
            $available_stock = $current_stock - $qty;

            // Insert into sales_product table
            $values = [
                'category' => $cid,
                'product' => $pid,
                'sales' => $qty,
                'available_stock' => $available_stock,
                'created_by' => $_SESSION['user']['id'],
                'updated_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'date' => $date
            ];
            $sql = "INSERT INTO sales_product
                        (category, product, sales, available_stock, created_by, updated_at, created_at, date) 
                    VALUES
                        (:category, :product, :sales, :available_stock, :created_by, :updated_at, :created_at, :date)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($values);

            // Update stock quantity in products table
            $update_values = [
                'pid' => $pid,
                'qty' => $qty
            ];
            $update_sql = "UPDATE products
                           SET stock = stock - :qty
                           WHERE id = :pid";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute($update_values);
        }
    }

    // Commit the transaction
    $conn->commit();
    
    $success = true;
    $message = 'Sales Successfully Added and Stocks Updated!';

} catch(\Exception $e) {
    // Rollback the transaction if something went wrong
    $conn->rollBack();
    $message = $e->getMessage();
}

$_SESSION['response'] = [
    'message' => $message,
    'success' => $success
];

header('Location: ../product-sales.php');
exit;
?>
