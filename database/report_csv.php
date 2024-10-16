<?php
$type = $_GET['report'];
$file_name = '.xls';

// Get the date range from the URL parameters
$startDate = $_GET['start_date'] ?? null; // Optional start date
$endDate = $_GET['end_date'] ?? null;     // Optional end date

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
    // Query for product report with stocks used, filtered by date range if provided
    $sql = "
        SELECT p.id, p.product_name, p.description, p.stock,
               COALESCE(SUM(sp.sales), 0) AS stocks_used,
               CONCAT(u.first_name, ' ', u.last_name) AS user_name, p.created_at, p.updated_at
        FROM products p
        LEFT JOIN users u ON p.created_by = u.id
        LEFT JOIN sales_product sp ON p.id = sp.product";

    // Add date filtering if dates are provided
    if ($startDate && $endDate) {
        $sql .= " WHERE p.created_at BETWEEN :startDate AND :endDate";
    }

    $sql .= " GROUP BY p.id, p.product_name, p.description, p.stock, u.first_name, u.last_name, p.created_at, p.updated_at
              ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);
    
    // Bind the date parameters if they exist
    if ($startDate && $endDate) {
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
    }

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
} elseif ($type === 'category') {
    // Query for category report with date filtering if provided
    $sql = "
        SELECT c.id, c.category_name, COUNT(p.id) AS product_count, 
               CONCAT(u.first_name, ' ', u.last_name) AS created_by, c.created_at
        FROM category c
        LEFT JOIN productscategory pc ON c.id = pc.category
        LEFT JOIN products p ON pc.product = p.id
        LEFT JOIN users u ON c.created_by = u.id";

    // Add date filtering if dates are provided
    if ($startDate && $endDate) {
        $sql .= " WHERE c.created_at BETWEEN :startDate AND :endDate";
    }

    $sql .= " GROUP BY c.id, c.category_name, u.first_name, u.last_name, c.created_at
              ORDER BY c.created_at DESC;";

    $stmt = $conn->prepare($sql);
    
    // Bind the date parameters if they exist
    if ($startDate && $endDate) {
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
    }

    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $categories = $stmt->fetchAll();

    // Define the formal header row for category report
    $header = ['number' => 'No.', 'category_name' => 'Category Name', 'product_count' => 'Number of Products', 'created_by' => 'Created By', 'created_at' => 'Created At'];

    // Output the header row
    echo implode("\t", $header) . "\n";

    $count = 1; // Initialize the counter
    foreach ($categories as $category) {
        // Map the data to the formal header names
        $row = [
            'number' => $count++,
            'category_name' => $category['category_name'],
            'product_count' => $category['product_count'],
            'created_by' => $category['created_by'],
            'created_at' => $category['created_at']
        ];

        // Detect double-quotes and escape any value that contains them
        array_walk($row, function (&$str) {
            $str = preg_replace("/\t/", "\\t", $str);
            $str = preg_replace("/\r?\n/", "\\n", $str);
            if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        });

        echo implode("\t", $row) . "\n";
    }
}elseif ($type === 'delivery') {
    // Query for delivery report, with date filtering if provided
    $sql = "
        SELECT d.id, d.order_product_id, d.qty_received, d.date_received, d.date_updated,
               p.product_name, c.category_name
        FROM order_product_history d
        LEFT JOIN order_product op ON d.order_product_id = op.id
        LEFT JOIN productscategory pc ON op.product = pc.product
        LEFT JOIN products p ON pc.product = p.id
        LEFT JOIN category c ON pc.category = c.id";

    // Add date filtering if dates are provided
    if ($startDate && $endDate) {
        $sql .= " WHERE d.date_received BETWEEN :startDate AND :endDate";
    }

    $sql .= " ORDER BY d.date_received DESC;";

    $stmt = $conn->prepare($sql);
    
    // Bind the date parameters if they exist
    if ($startDate && $endDate) {
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
    }

    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $deliveries = $stmt->fetchAll();

    // Define the formal header row for delivery report
    $header = [
        'number' => 'No.', 
        'orderproductid' => 'Order Product ID', 
        'product_name' => 'Product Name', 
        'category_name' => 'Category Name', 
        'qty_received' => 'Quantity Received', 
        'date_received' => 'Date Received', 
        'date_updated' => 'Date Updated'
    ];

    // Output the header row
    echo implode("\t", $header) . "\n";

    $count = 1; // Initialize the counter
    foreach ($deliveries as $delivery) {
        // Map the data to the formal header names
        $row = [
            'number' => $count++,
            'orderproductid' => $delivery['order_product_id'],
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
    // Query for purchase history report, with date filtering if provided
    $sql = "
        SELECT op.id, op.product, op.quantity_ordered, op.created_at, 
               CONCAT(u.first_name, ' ', u.last_name) AS user_name, p.product_name
        FROM order_product op
        LEFT JOIN products p ON op.product = p.id
        LEFT JOIN users u ON op.created_by = u.id";

    // Add date filtering if dates are provided
    if ($startDate && $endDate) {
        $sql .= " WHERE op.created_at BETWEEN :startDate AND :endDate";
    }

    $sql .= " ORDER BY op.created_at DESC;";

    $stmt = $conn->prepare($sql);
    
    // Bind the date parameters if they exist
    if ($startDate && $endDate) {
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
    }

    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $purchases = $stmt->fetchAll();

    // Define the formal header row for purchase report
    $header = ['number' => 'No.', 'product_name' => 'Product Name', 'qty_ordered' => 'Quantity Ordered', 'date_ordered' => 'Date Ordered', 'created_by' => 'Created By'];

    // Output the header row
    echo implode("\t", $header) . "\n";

    $count = 1; // Initialize the counter
    foreach ($purchases as $purchase) {
        // Map the data to the formal header names
        $row = [
            'number' => $count++,
            'product_name' => $purchase['product_name'],
            'qty_ordered' => $purchase['quantity_ordered'],
            'date_ordered' => $purchase['created_at'],
            'created_by' => $purchase['user_name']
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
    // Query for sales report, with date filtering if provided
    $sql = "
        SELECT sp.id, sp.product, sp.sales AS qty_sold, sp.date AS date_sold, 
               CONCAT(u.first_name, ' ', u.last_name) AS user_name, p.product_name
        FROM sales_product sp
        LEFT JOIN products p ON sp.product = p.id
        LEFT JOIN users u ON sp.created_by = u.id";

    // Add date filtering if dates are provided
    if ($startDate && $endDate) {
        $sql .= " WHERE sp.date BETWEEN :startDate AND :endDate";
    }

    $sql .= " ORDER BY sp.date DESC;";

    $stmt = $conn->prepare($sql);
    
    // Bind the date parameters if they exist
    if ($startDate && $endDate) {
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
    }

    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $sales = $stmt->fetchAll();

    // Define the formal header row for sales report
    $header = ['number' => 'No.', 'product_name' => 'Product Name', 'qty_sold' => 'Quantity Sold', 'date_sold' => 'Date Sold', 'created_by' => 'Created By'];

    // Output the header row
    echo implode("\t", $header) . "\n";

    $count = 1; // Initialize the counter
    foreach ($sales as $sale) {
        // Map the data to the formal header names
        $row = [
            'number' => $count++,
            'product_name' => $sale['product_name'],
            'qty_sold' => $sale['qty_sold'],
            'date_sold' => $sale['date_sold'],
            'created_by' => $sale['user_name']
        ];

        // Detect double-quotes and escape any value that contains them
        array_walk($row, function (&$str) {
            $str = preg_replace("/\t/", "\\t", $str);
            $str = preg_replace("/\r?\n/", "\\n", $str);
            if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        });

        echo implode("\t", $row) . "\n";
    }
} elseif ($type === 'user') {
    // Query for user report, with date filtering if provided
    $sql = "
        SELECT id, first_name, last_name, email, created_at
        FROM users";

    // Add date filtering if dates are provided
    if ($startDate && $endDate) {
        $sql .= " WHERE created_at BETWEEN :startDate AND :endDate";
    }

    $sql .= " ORDER BY created_at DESC;";

    $stmt = $conn->prepare($sql);
    
    // Bind the date parameters if they exist
    if ($startDate && $endDate) {
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
    }

    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $users = $stmt->fetchAll();

    // Define the formal header row for user report
    $header = ['number' => 'No.', 'first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email', 'created_at' => 'Created At'];

    // Output the header row
    echo implode("\t", $header) . "\n";

    $count = 1; // Initialize the counter
    foreach ($users as $user) {
        // Map the data to the formal header names
        $row = [
            'number' => $count++,
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'created_at' => $user['created_at']
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

$conn = null; // Close the database connection
?>
