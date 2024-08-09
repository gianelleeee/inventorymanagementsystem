<?php
// Set the timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Check if form data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure all required fields are present
    if (isset($_POST['product_name'], $_POST['description'], $_POST['pid'])) {
        $product_name = htmlspecialchars(strip_tags($_POST['product_name']));
        $description = htmlspecialchars(strip_tags($_POST['description']));
        $pid = (int)$_POST['pid'];

        // Update product record
        try {
            // Include your database connection
            include('connection.php');

            // Begin transaction
            $conn->beginTransaction();

            // Set the current timestamp for the updated_at field
            $updated_at = date('Y-m-d H:i:s');

            // Prepare and execute the SQL update statement for the product
            $sql = "UPDATE products SET product_name=?, description=?, updated_at=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$product_name, $description, $updated_at, $pid]);

            // Check if the update affected any rows
            if ($stmt->rowCount() > 0) {
                // Delete old categories associated with the product
                $sql = "DELETE FROM productscategory WHERE product=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$pid]);

                // Re-insert updated categories
                $category = isset($_POST['category']) ? $_POST['category'] : [];
                $created_at = date('Y-m-d H:i:s');

                foreach ($category as $cat) {
                    $sql = "INSERT INTO productscategory
                                (category, product, updated_at, created_at) 
                            VALUES
                                (:category_id, :product_id, :updated_at, :created_at)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':category_id' => $cat,
                        ':product_id' => $pid,
                        ':updated_at' => $updated_at,  // Reuse the same updated_at timestamp
                        ':created_at' => $created_at
                    ]);
                }

                // Commit transaction
                $conn->commit();

                $response = [
                    'success' => true,
                    'message' => "<strong>$product_name</strong> successfully updated!"
                ];
            } else {
                // Rollback transaction if nothing was updated
                $conn->rollBack();
                $response = [
                    'success' => false,
                    'message' => 'No changes were made.'
                ];
            }
        } catch (PDOException $e) {
            // Rollback transaction if something goes wrong
            $conn->rollBack();

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
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid request method.'
    ];
}

// Send JSON response back to the client-side JavaScript
header('Content-Type: application/json');
echo json_encode($response);
?>
