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

            // Check if update was successful
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
