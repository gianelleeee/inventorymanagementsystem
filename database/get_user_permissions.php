<?php
// database/get_user_permissions.php
header('Content-Type: application/json'); // Set the content type to JSON

// Include your database connection file
include 'connection.php'; // Ensure the path is correct

// Assuming you're using a GET request to get user permissions
if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    try {
        // Prepare your SQL statement
        $stmt = $conn->prepare("SELECT permissions FROM users WHERE id = :user_id");
        
        // Bind the parameters
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // If permissions are stored as a comma-separated string
            $permissionsArray = explode(',', $result['permissions']);
            echo json_encode(['permissions' => $permissionsArray]);
        } else {
            echo json_encode(['permissions' => []]); // No permissions found
        }
    } catch (PDOException $e) {
        // Handle error
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'User ID not provided']);
}
?>
