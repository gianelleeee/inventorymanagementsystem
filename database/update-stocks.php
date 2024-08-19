<?php
// database/update-sales.php

// Include your database connection file
include('connection.php');

// Function to send JSON response
function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate the payload
    if ($data === null || !isset($data['payload'])) {
        sendResponse(false, 'Invalid data format.');
    }

    // Prepare SQL statements
    $getOldSalesStmt = $conn->prepare('SELECT sales FROM sales_product WHERE id = :id');
    $updateSalesStmt = $conn->prepare('UPDATE sales_product SET sales = :sales, available_stock = :available_stock WHERE id = :id');
    $updateStockStmt = $conn->prepare('UPDATE products SET stock = stock + :difference WHERE id = :product_id');

    try {
        // Begin a transaction
        $conn->beginTransaction();

        // Loop through each item in the payload
        foreach ($data['payload'] as $item) {
            // Validate item data
            if (!isset($item['id']) || !isset($item['sale']) || !isset($item['pid'])) {
                sendResponse(false, 'Missing data fields in payload.');
            }

            // Get the current sales value
            $getOldSalesStmt->bindParam(':id', $item['id'], PDO::PARAM_INT);
            $getOldSalesStmt->execute();
            $oldSales = $getOldSalesStmt->fetchColumn();

            if ($oldSales === false) {
                sendResponse(false, 'Sales record not found for ID: ' . $item['id']);
            }

            // Calculate the difference between the new and old sales values
            $salesDifference = $item['sale'] - $oldSales;

            // Update the sales record and available stock
            // Calculate available stock
            $availableStock = $item['available_stock'] - $salesDifference;
            
            $updateSalesStmt->bindParam(':sales', $item['sale'], PDO::PARAM_INT);
            $updateSalesStmt->bindParam(':available_stock', $availableStock, PDO::PARAM_INT);
            $updateSalesStmt->bindParam(':id', $item['id'], PDO::PARAM_INT);
            $updateSalesStmt->execute();

            // Adjust the stock based on the sales difference
            $stockDifference = -$salesDifference; // This will correctly adjust stock: negative difference means we add to stock, positive difference means we subtract from stock
            
            $updateStockStmt->bindParam(':difference', $stockDifference, PDO::PARAM_INT);
            $updateStockStmt->bindParam(':product_id', $item['pid'], PDO::PARAM_INT);
            $updateStockStmt->execute();
        }

        // Commit the transaction
        $conn->commit();
        sendResponse(true, 'Sales records and stock quantities updated successfully.');
    } catch (Exception $e) {
        // Rollback the transaction if an error occurs
        $conn->rollBack();
        sendResponse(false, 'An error occurred: ' . $e->getMessage());
    }
} else {
    // Not a POST request
    sendResponse(false, 'Invalid request method.');
}
?>
