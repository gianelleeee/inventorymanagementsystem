<?php
// Assuming 'id' is passed via GET parameter
$id = $_GET['id'];

include('connection.php');

// Fetch category details
$stmt = $conn->prepare("SELECT * FROM category WHERE id=:id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    // Handle case where category with given ID does not exist
    http_response_code(404); // Set HTTP response code to indicate Not Found
    exit;
}

// Fetch products associated with the category
$stmt = $conn->prepare("SELECT product_name, products.id FROM products, productscategory WHERE productscategory.category=:id AND productscategory.product=products.id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extract category IDs into an array
$product_ids = array_column($products, 'id');

// Assign product IDs to the category details array
$category['products'] = $product_ids;

// Return JSON response
header('Content-Type: application/json');
echo json_encode($category);
?>
