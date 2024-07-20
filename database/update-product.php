<?php
// Check if form data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure all required fields are present
    if (isset($_POST['product_name'], $_POST['description'], $_POST['pid'])) {
        $product_name = $_POST['product_name'];
        $description = $_POST['description'];
        $pid = $_POST['pid'];

        // Update product record
        try {
            // Include your database connection
            include('connection.php');

            // Prepare and execute the SQL update statement
            $sql = "UPDATE products SET product_name=?, description=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$product_name, $description, $pid]);

            // Delete the old values
            $sql = "DELETE FROM productscategory WHERE product=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$pid]);

            // Loop through category and add record
            $category = isset($_POST['category']) ? $_POST['category'] : [];
            $updated_at = date('Y-m-d H:i:s');
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
                    ':updated_at' => $updated_at,
                    ':created_at' => $created_at
                ]);
            }

            // Check if any rows were affected by the update
            if ($stmt->rowCount() > 0) {
                $response = [
                    'success' => true,
                    'message' => "<strong>$product_name</strong> successfully updated!"
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
