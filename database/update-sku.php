<?php
$category_name = isset($_POST['category_name']) ? $_POST['category_name'] : '';
$products = isset($_POST['products']) ? $_POST['products'] : [];
$category_id = isset($_POST['cid']) ? $_POST['cid'] : '';

if ($category_name && $category_id) {
    try {
        include('connection.php');

        // Update the category record
        $sql = "UPDATE category SET category_name=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$category_name, $category_id]);

        // Delete the old values
        $sql = "DELETE FROM productscategory WHERE category=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$category_id]);

        // Insert new records
        $sql = "INSERT INTO productscategory (category, product, updated_at, created_at) VALUES (:category_id, :product_id, :updated_at, :created_at)";
        $stmt = $conn->prepare($sql);

        $current_time = date('Y-m-d H:i:s');
        foreach ($products as $product) {
            $stmt->execute([
                'category_id' => $category_id,
                'product_id' => $product,
                'updated_at' => $current_time,
                'created_at' => $current_time
            ]);
        }

        // Check if update was successful
        if ($stmt->rowCount() > 0) {
            $response = [
                'success' => true,
                'message' => "<strong>$category_name</strong> successfully updated!"
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No rows affected. Update failed.'
            ];
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Missing required fields.'
    ];
}

echo json_encode($response);
?>
