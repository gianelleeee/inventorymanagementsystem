<?php
require('../vendor/fpdf/fpdf.php'); // Update this to the actual path of your FPDF library

$type = $_GET['report'];
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

$mapping_filenames = [
    'category' => 'Category Report',
    'product' => 'Product Report',
    'delivery' => 'Delivery Report',
    'purchase' => 'Purchase History Report',
    'sale' => 'Sales Report',
    'user' => 'Users Report'
];

$file_name = $mapping_filenames[$type] . '.pdf';

header("Content-Disposition: attachment; filename=\"$file_name\"");
header("Content-Type: application/pdf");

// Create a new instance of FPDF
$pdf = new FPDF('L', 'mm', 'A4'); // 'L' for landscape, 'mm' for millimeters, 'A4' for paper size
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 10);

// Pull data from the database
include('connection.php');

// Define headers for each report type
$headers = [
    'product' => ['No.', 'Product Name', 'Description', 'Stock', 'Stocks Used', 'Created At', 'Updated At', 'Created By'],
    'category' => ['No.', 'Category Name', 'Products', 'Created By', 'Created At', 'Updated At'],
    'delivery' => ['No.', 'Product Name', 'Category Name', 'Qty Received', 'Date Received', 'Date Updated'],
    'purchase' => ['Batch #', 'Product', 'Quantity Ordered', 'Quantity Received', 'Category', 'Status', 'Ordered By', 'Created Date'],
    'sale' => ['Date', 'Product', 'Sales', 'Available Stocks', 'Category', 'Added By', 'Created Date']
];

$header = $headers[$type];

// Initialize column widths array
$columnWidths = array_fill(0, count($header), 0);

// Set initial font for width calculation
$pdf->SetFont('Arial', '', 10);

// Function to calculate the maximum width needed for each column
function calculateColumnWidths($data, $header, $pdf) {
    $columnWidths = array_fill(0, count($header), 0);
    
    // Calculate header widths
    foreach ($header as $i => $col) {
        $columnWidths[$i] = $pdf->GetStringWidth($col) + 4; // Add padding
    }
    
    // Calculate data widths
    foreach ($data as $rowData) {
        foreach ($rowData as $i => $col) {
            $columnWidths[$i] = max($columnWidths[$i], $pdf->GetStringWidth($col) + 4); // Add padding
        }
    }
    
    return $columnWidths;
}

// Fetch and output the data
$pdf->SetFont('Arial', 'B', 10); // Bold header font

// Function to output data rows
function outputDataRows($data, $columnWidths, $pdf, $header) {
    // Add header row
    foreach ($header as $i => $col) {
        $pdf->Cell($columnWidths[$i], 10, $col, 1);
    }
    $pdf->Ln();
    
    // Add data rows
    $pdf->SetFont('Arial', '', 10); // Regular font for content
    foreach ($data as $rowData) {
        foreach ($rowData as $i => $col) {
            $pdf->Cell($columnWidths[$i], 10, $col, 1);
        }
        $pdf->Ln();
    }
}

$data = []; // Initialize data array

$whereClause = "";
if ($start_date && $end_date) {
    $whereClause = "WHERE DATE(created_at) BETWEEN :start_date AND :end_date";
}

if ($type === 'product') {
    // Query for product report
    $stmt = $conn->prepare("
        SELECT p.id, p.product_name, p.description, p.stock, 
               (SELECT SUM(sp.sales) FROM sales_product sp WHERE sp.product = p.id) AS stocks_used, 
               CONCAT(u.first_name, ' ', u.last_name) AS user_name, p.created_at, p.updated_at
        FROM products p
        LEFT JOIN users u ON p.created_by = u.id
        $whereClause
        ORDER BY p.created_at DESC
    ");
    if ($whereClause) {
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
    }
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $products = $stmt->fetchAll();
    
    $count = 1; // Initialize the counter
    foreach ($products as $product) {
        $data[] = [
            $count++,
            $product['product_name'],
            $product['description'],
            $product['stock'],
            $product['stocks_used'] ?: 0, // Add 'stocks used'
            $product['created_at'],
            $product['updated_at'],
            $product['user_name']
        ];
    }
} elseif ($type === 'category') {
    // Query for category report
    $stmt = $conn->prepare("
        SELECT c.category_name AS category_name, GROUP_CONCAT(p.product_name SEPARATOR ', ') AS product_names, CONCAT(u.first_name, ' ', u.last_name) AS user_name, c.created_at, c.updated_at
        FROM productscategory pc
        LEFT JOIN category c ON pc.category = c.id
        LEFT JOIN users u ON c.created_by = u.id
        LEFT JOIN products p ON pc.product = p.id
        $whereClause
        GROUP BY c.category_name, u.first_name, u.last_name, c.created_at, c.updated_at
        ORDER BY c.created_at DESC
    ");
    if ($whereClause) {
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
    }
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $categories = $stmt->fetchAll();
    
    $count = 1; // Initialize the counter
    foreach ($categories as $category) {
        $data[] = [
            $count++,
            $category['category_name'],
            $category['product_names'],
            $category['user_name'],
            $category['created_at'],
            $category['updated_at']
        ];
    }
} elseif ($type === 'delivery') {
    // Query for delivery report
    $stmt = $conn->prepare("
        SELECT d.id, p.product_name, c.category_name, d.qty_received, d.date_received, d.date_updated
        FROM order_product_history d
        LEFT JOIN order_product op ON d.order_product_id = op.id
        LEFT JOIN productscategory pc ON op.product = pc.product
        LEFT JOIN products p ON pc.product = p.id
        LEFT JOIN category c ON pc.category = c.id
        $whereClause
        ORDER BY d.date_received DESC;
    ");
    if ($whereClause) {
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
    }
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $deliveries = $stmt->fetchAll();
    
    $count = 1; // Initialize the counter
    foreach ($deliveries as $delivery) {
        $data[] = [
            $count++,
            $delivery['product_name'],
            $delivery['category_name'],
            $delivery['qty_received'],
            $delivery['date_received'],
            $delivery['date_updated']
        ];
    }
} elseif ($type === 'purchase') {
    // Query for purchase report
    $stmt = $conn->prepare("
        SELECT op.batch, p.product_name, op.quantity_ordered, op.quantity_received,
               c.category_name, op.status, CONCAT(u.first_name, ' ', u.last_name) AS ordered_by, 
               op.created_at
        FROM order_product op
        LEFT JOIN productscategory pc ON op.product = pc.product
        LEFT JOIN products p ON pc.product = p.id
        LEFT JOIN category c ON pc.category = c.id
        LEFT JOIN users u ON op.created_by = u.id
        $whereClause
        GROUP BY op.batch, p.product_name, op.quantity_ordered, op.quantity_received, c.category_name, op.status, u.first_name, u.last_name, op.created_at
        ORDER BY op.created_at DESC;
    ");
    if ($whereClause) {
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
    }
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $purchases = $stmt->fetchAll();
    
    foreach ($purchases as $purchase) {
        $data[] = [
            $purchase['batch'],
            $purchase['product_name'],
            $purchase['quantity_ordered'],
            $purchase['quantity_received'],
            $purchase['category_name'],
            $purchase['status'],
            $purchase['ordered_by'],
            $purchase['created_at']
        ];
    }
} elseif ($type === 'sale') {
    // Query for sales report
    $stmt = $conn->prepare("
        SELECT s.date, p.product_name, s.sales, p.available_stock, c.category_name, 
               CONCAT(u.first_name, ' ', u.last_name) AS added_by, s.created_at
        FROM sales_product s
        LEFT JOIN products p ON s.product = p.id
        LEFT JOIN productscategory pc ON p.id = pc.product
        LEFT JOIN category c ON pc.category = c.id
        LEFT JOIN users u ON s.created_by = u.id
        $whereClause
        ORDER BY s.created_at DESC;
    ");
    if ($whereClause) {
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
    }
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $sales = $stmt->fetchAll();
    
    foreach ($sales as $sale) {
        $data[] = [
            $sale['date'],
            $sale['product_name'],
            $sale['sales'],
            $sale['available_stock'],
            $sale['category_name'],
            $sale['added_by'],
            $sale['created_at']
        ];
    }
}

// Calculate column widths
$columnWidths = calculateColumnWidths($data, $header, $pdf);

// Output the data rows
outputDataRows($data, $columnWidths, $pdf, $header);

$pdf->Output();
?>
