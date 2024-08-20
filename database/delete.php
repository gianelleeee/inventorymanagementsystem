<?php
$data = $_POST;
$id = (int) $data['id'];
$table = $data['table'];

// Whitelist of allowed tables
$allowed_tables = ['category', 'products', 'users']; // Added 'users' to the list

// Validate the table name
if (!in_array($table, $allowed_tables)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid table name',
    ]);
    exit;
}

try {
    include('connection.php');

    // Start transaction
    $conn->beginTransaction();

    if ($table === 'category') {
        $category_id = $id;

        // Delete related rows
        $command = $conn->prepare("DELETE FROM order_product WHERE category = :id");
        $command->execute([':id' => $category_id]);

        $command = $conn->prepare("DELETE FROM sales_product WHERE category = :id");
        $command->execute([':id' => $category_id]);

        // Delete the category itself
        $command = $conn->prepare("DELETE FROM productscategory WHERE category = :id");
        $command->execute([':id' => $category_id]);
    }

    if ($table === 'products') {
        $product_id = $id;

        // Delete related rows
        $command = $conn->prepare("DELETE FROM sales_product WHERE product = :id");
        $command->execute([':id' => $product_id]);

        $command = $conn->prepare("DELETE FROM order_product_history WHERE order_product_id IN (SELECT id FROM order_product WHERE product = :id)");
        $command->execute([':id' => $product_id]);

        $command = $conn->prepare("DELETE FROM order_product WHERE product = :id");
        $command->execute([':id' => $product_id]);

        $command = $conn->prepare("DELETE FROM productscategory WHERE product = :id");
        $command->execute([':id' => $product_id]);

        // Finally, delete the product itself
        $command = $conn->prepare("DELETE FROM products WHERE id = :id");
        $command->execute([':id' => $product_id]);
    }

    if ($table === 'users') {
        $user_id = $id;

        // Update related rows to set created_by to NULL instead of deleting
        $command = $conn->prepare("UPDATE sales_product SET created_by = NULL WHERE created_by = :id");
        $command->execute([':id' => $user_id]);

        $command = $conn->prepare("UPDATE order_product SET created_by = NULL WHERE created_by = :id");
        $command->execute([':id' => $user_id]);

        $command = $conn->prepare("UPDATE products SET created_by = NULL WHERE created_by = :id");
        $command->execute([':id' => $user_id]);

        $command = $conn->prepare("UPDATE category SET created_by = NULL WHERE created_by = :id");
        $command->execute([':id' => $user_id]);

        // Finally, delete the user itself
        $command = $conn->prepare("DELETE FROM users WHERE id = :id");
        $command->execute([':id' => $user_id]);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
    ]);

} catch (PDOException $e) {
    // Rollback transaction in case of error
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
?>
