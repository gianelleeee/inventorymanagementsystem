<?php
$type = $_GET['report'];
$file_name = '.xls';

$mapping_filenames = [
    'category' => 'Category Report',
    'product' => 'Product Report',
    'delivery' => 'Delivery Report',
    'purchase' => 'Purchase History Report',
    'sale' => 'Sales Report',
    'user' => 'Users Report'
];

$file_name = $mapping_filenames[$type] . '.xls';

header("Content-Disposition: attachment; filename=\"$file_name\"");
header("Content-Type: application/vnd.ms-excel");

// Pull data from the database
include('connection.php');

if ($type === 'product') {
    // Query for product report with stocks used
    $stmt = $conn->prepare("
        SELECT p.id, p.product_name, p.description, p.stock,
               COALESCE(SUM(sp.sales), 0) AS stocks_used,
               CONCAT(u.first_name, ' ', u.last_name) AS user_name, p.created_at, p.updated_at
        FROM products p
        LEFT JOIN users u ON p.created_by = u.id
        LEFT JOIN sales_product sp ON p.id = sp.product
        GROUP BY p.id, p.product_name, p.description, p.stock, u.first_name, u.last_name, p.created_at, p.updated_at
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $products = $stmt->fetchAll();

    // Define the formal header row for product report
    $header = ['number' => 'No.', 'product_name' => 'Product Name', 'description' => 'Description', 'stock' => 'Stock', 'stocks_used' => 'Stocks Used', 'created_at' => 'Created At', 'updated_at' => 'Updated At', 'user_name' => 'Created By'];

    // Output the header row
    echo implode("\t", $header) . "\n";

    $count = 1; // Initialize the counter
    foreach ($products as $product) {
        // Map the data to the formal header names, replacing id with sequential number
        $row = [
            'number' => $count++,
            'product_name' => $product['product_name'],
            'description' => $product['description'],
            'stock' => $product['stock'],
            'stocks_used' => $product['stocks_used'],
            'created_at' => $product['created_at'],
            'updated_at' => $product['updated_at'],
            'user_name' => $product['user_name']
        ];

        // Detect double-quotes and escape any value that contains them
        array_walk($row, function (&$str) {
            $str = preg_replace("/\t/", "\\t", $str);
            $str = preg_replace("/\r?\n/", "\\n", $str);
            if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        });

        echo implode("\t", $row) . "\n";
    }
}
 elseif ($type === 'delivery') {
    // Query for delivery report
    $stmt = $conn->prepare("
        SELECT d.id, d.order_product_id, d.qty_received, d.date_received, d.date_updated,
               p.product_name, c.category_name
        FROM order_product_history d
        LEFT JOIN order_product op ON d.order_product_id = op.id
        LEFT JOIN productscategory pc ON op.product = pc.product
        LEFT JOIN products p ON pc.product = p.id
        LEFT JOIN category c ON pc.category = c.id
        ORDER BY d.date_received DESC;
    ");
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $deliveries = $stmt->fetchAll();

    // Define the formal header row for delivery report
    $header = ['number' => 'No.', 'product_name' => 'Product Name', 'category_name' => 'Category Name', 'qty_received' => 'Qty Received', 'date_received' => 'Date Received', 'date_updated' => 'Date Updated'];

    // Output the header row
    echo implode("\t", $header) . "\n";

    $count = 1; // Initialize the counter
    foreach ($deliveries as $delivery) {
        // Map the data to the formal header names
        $row = [
            'number' => $count++,
            'product_name' => $delivery['product_name'],
            'category_name' => $delivery['category_name'],
            'qty_received' => $delivery['qty_received'],
            'date_received' => $delivery['date_received'],
            'date_updated' => $delivery['date_updated']
        ];

        // Detect double-quotes and escape any value that contains them
        array_walk($row, function (&$str) {
            $str = preg_replace("/\t/", "\\t", $str);
            $str = preg_replace("/\r?\n/", "\\n", $str);
            if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        });

        echo implode("\t", $row) . "\n";
    }
} elseif ($type === 'purchase') {
    // Query for purchase report
    $stmt = $conn->prepare("
        SELECT op.batch, op.id, p.product_name, op.quantity_ordered, op.quantity_received,
               c.category_name, op.status, CONCAT(u.first_name, ' ', u.last_name) AS ordered_by, 
               op.created_at,
               GROUP_CONCAT(CONCAT('Qty: ', oph.qty_received, ' on ', oph.date_received) ORDER BY oph.date_received DESC SEPARATOR ', ') AS delivery_history
        FROM order_product op
        LEFT JOIN productscategory pc ON op.product = pc.product
        LEFT JOIN products p ON pc.product = p.id
        LEFT JOIN category c ON pc.category = c.id
        LEFT JOIN users u ON op.created_by = u.id
        LEFT JOIN order_product_history oph ON op.id = oph.order_product_id
        GROUP BY op.batch, op.id, p.product_name, op.quantity_ordered, op.quantity_received, c.category_name, op.status, u.first_name, u.last_name, op.created_at
        ORDER BY op.created_at DESC;
    ");
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $purchases = $stmt->fetchAll();

    // Define the formal header row for purchase report
    $header = ['batch' => 'BATCH #', 'number' => '#', 'product_name' => 'PRODUCT', 'quantity_ordered' => 'QUANTITY ORDERED', 'quantity_received' => 'QUANTITY RECEIVED', 'category_name' => 'CATEGORY', 'status' => 'STATUS', 'ordered_by' => 'ORDERED BY', 'created_at' => 'CREATED DATE', 'delivery_history' => 'DELIVERY HISTORY'];

    // Output the header row
    echo implode("\t", $header) . "\n";

    $count = 1; // Initialize the counter
    foreach ($purchases as $purchase) {
        // Map the data to the formal header names
        $row = [
            'batch' => $purchase['batch'],
            'number' => $count++,
            'product_name' => $purchase['product_name'],
            'quantity_ordered' => $purchase['quantity_ordered'],
            'quantity_received' => $purchase['quantity_received'],
            'category_name' => $purchase['category_name'],
            'status' => $purchase['status'],
            'ordered_by' => $purchase['ordered_by'],
            'created_at' => $purchase['created_at'],
            'delivery_history' => $purchase['delivery_history']
        ];

        // Detect double-quotes and escape any value that contains them
        array_walk($row, function (&$str) {
            $str = preg_replace("/\t/", "\\t", $str);
            $str = preg_replace("/\r?\n/", "\\n", $str);
            if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        });

        echo implode("\t", $row) . "\n";
    }
} elseif ($type === 'sale') {
    // Query for sales report with available stocks
    $stmt = $conn->prepare("
        SELECT sp.date, p.product_name, sp.sales, c.category_name, sp.available_stock,
               CONCAT(u.first_name, ' ', u.last_name) AS created_by, sp.created_at
        FROM sales_product sp
        LEFT JOIN productscategory pc ON sp.product = pc.product
        LEFT JOIN products p ON pc.product = p.id
        LEFT JOIN category c ON pc.category = c.id
        LEFT JOIN users u ON sp.created_by = u.id
        ORDER BY sp.date DESC
    ");
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $sales = $stmt->fetchAll();

    // Define the header row, including the available stocks column
    $header = ['date' => 'Date', 'product_name' => 'Product', 'sales' => 'Sales', 'available_stock' => 'Available Stock', 'category_name' => 'Category', 'created_by' => 'Added By', 'created_at' => 'Created Date'];

    // Output the header row
    echo implode("\t", $header) . "\n";

    foreach ($sales as $sale) {
        // Map the data to the formal header names
        $row = [
            'date' => $sale['date'],
            'product_name' => $sale['product_name'],
            'sales' => $sale['sales'],
            'available_stock' => $sale['available_stock'],
            'category_name' => $sale['category_name'],
            'created_by' => $sale['created_by'],
            'created_at' => $sale['created_at']
        ];

        // Detect double-quotes and escape any value that contains them
        array_walk($row, function (&$str) {
            $str = preg_replace("/\t/", "\\t", $str);
            $str = preg_replace("/\r?\n/", "\\n", $str);
            if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        });

        // Output the data row
        echo implode("\t", $row) . "\n";
    }
}
?>