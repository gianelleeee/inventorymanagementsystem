<?php
// Assuming 'id' is passed via GET parameter
$id = $_GET['id'];

include('connection.php');


// Fetch category associated with the product
$stmt = $conn->prepare("SELECT category_name, category.id FROM category, productscategory WHERE productscategory.product=:id AND productscategory.category=category.id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$category = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Return JSON response
header('Content-Type: application/json');
echo json_encode($category);
?>
