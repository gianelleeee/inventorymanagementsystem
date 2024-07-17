<?php
// Assuming 'id' is passed via GET parameter
$id = $_GET['id'];

include('connection.php');

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id=:id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    // Handle case where product with given ID does not exist
    http_response_code(404); // Set HTTP response code to indicate Not Found
    exit;
}

// Fetch categories associated with the product
$stmt = $conn->prepare("SELECT category_name, category.id FROM category, productscategory WHERE productscategory.product=:id AND productscategory.category=category.id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extract category IDs into an array
$category_ids = array_column($categories, 'id');

// Assign category IDs to the product details array
$product['category'] = $category_ids;

// Return JSON response
header('Content-Type: application/json');
echo json_encode($product);
?>
