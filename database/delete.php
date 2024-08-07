<?php
    $data = $_POST;
    $id = (int) $data['id'];
    $table = $data['table'];

    // Whitelist of allowed tables
    $allowed_tables = ['category', 'products'];

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
