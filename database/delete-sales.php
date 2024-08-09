<?php
header('Content-Type: application/json');
include('connection.php');

$data = json_decode(file_get_contents('php://input'), true);
$sales_id = $data['id'];

try {
    // Start transaction
    $conn->beginTransaction();

    // Get the product and sales amount to adjust stock
    $stmt = $conn->prepare("SELECT product, sales FROM sales_product WHERE id = :id");
    $stmt->execute(['id' => $sales_id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sale) {
        $product_id = $sale['product'];
        $sales_amount = $sale['sales'];

        // Update stock based on sales
        $stmt = $conn->prepare("UPDATE products SET stock = stock + :sales WHERE id = :product_id");
        $stmt->execute(['sales' => $sales_amount, 'product_id' => $product_id]);

        // Delete the sales record
        $stmt = $conn->prepare("DELETE FROM sales_product WHERE id = :id");
        $stmt->execute(['id' => $sales_id]);

        // Commit transaction
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Sales record deleted and stock updated.']);
    } else {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Sales record not found.']);
    }
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
