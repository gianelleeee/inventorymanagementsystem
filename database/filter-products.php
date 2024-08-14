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
    if (!$product['category_names']) {
        $product['category_names'] = [];
    } else {
        $product['category_names'] = explode(', ', $product['category_names']);
    }

    $created_by_id = $product['created_by'];
    $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = :created_by_id");
    $stmt->bindParam(':created_by_id', $created_by_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $product['created_by_name'] = $user['first_name'] . ' ' . $user['last_name'];
}

echo json_encode(['products' => $products]);
