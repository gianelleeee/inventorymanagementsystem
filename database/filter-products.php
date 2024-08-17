<?php
include('connection.php');

$category_id = $_GET['category_id'];

if ($category_id === 'all') {
    $stmt = $conn->prepare("SELECT p.*, GROUP_CONCAT(c.category_name SEPARATOR ', ') as category_names 
                            FROM products p 
                            LEFT JOIN productscategory pc ON p.id = pc.product 
                            LEFT JOIN category c ON c.id = pc.category 
                            GROUP BY p.id");
} else {
    $stmt = $conn->prepare("SELECT p.*, GROUP_CONCAT(c.category_name SEPARATOR ', ') as category_names 
                            FROM products p 
                            JOIN productscategory pc ON p.id = pc.product 
                            JOIN category c ON c.id = pc.category 
                            WHERE pc.category = :category_id 
                            GROUP BY p.id");
    $stmt->bindParam(':category_id', $category_id);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as &$product) {
    // Handle category names
    if (!$product['category_names']) {
        $product['category_names'] = [];
    } else {
        $product['category_names'] = explode(', ', $product['category_names']);
    }

    // Fetch created_by_name
    $created_by_id = $product['created_by'];
    $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = :created_by_id");
    $stmt->bindParam(':created_by_id', $created_by_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $product['created_by_name'] = $user['first_name'] . ' ' . $user['last_name'];

    // Calculate stocks used
    $product_id = $product['id'];
    $stmt = $conn->prepare("SELECT SUM(sales_product.sales) as total_sales 
                            FROM sales_product 
                            INNER JOIN productscategory pc ON sales_product.product = pc.product 
                            WHERE pc.product = :product_id");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $product['stocks_used'] = $result['total_sales'] ? $result['total_sales'] : 0;
}

echo json_encode(['products' => $products]);
