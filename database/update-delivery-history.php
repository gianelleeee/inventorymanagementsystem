<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updates = json_decode($_POST['updates'], true);

    $success = true;

    foreach ($updates as $update) {
        $id = $update['id'];
        $qty_received = $update['qty_received'];

        // Update the database
        $stmt = $conn->prepare("UPDATE order_product_history SET qty_received = :qty_received WHERE id = :id");
        $stmt->bindParam(':qty_received', $qty_received);
        $stmt->bindParam(':id', $id);

        if (!$stmt->execute()) {
            $success = false;
            break;
        }
    }

    echo json_encode(['success' => $success]);
}
?>
