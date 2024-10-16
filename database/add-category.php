<?php
// Start the session
session_start();

// Set the default timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('connection.php'); // Database connection file

    try {
        // Retrieve the category name from the form
        $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';

        // Check if the category name already exists
        $check_sql = "SELECT * FROM category WHERE category_name = :category_name";
        $stmt = $conn->prepare($check_sql);
        $stmt->bindParam(':category_name', $category_name);
        $stmt->execute();

        // If the category already exists, set an error message and redirect
        if ($stmt->rowCount() > 0) {
            $_SESSION['response'] = [
                'success' => false,
                'message' => 'Category name already exists! Please choose a different name.'
            ];
            header('Location: ../sku-add.php');
            exit();
        }

        // Prepare the data for the category table
        $category_data = [
            'category_name' => $category_name,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['user']['id'] // Assume user session contains 'id'
        ];

        // Prepare the SQL query for inserting into category table
        $category_sql = "INSERT INTO category 
                         (category_name, created_at, updated_at, created_by) 
                         VALUES 
                         (:category_name, :created_at, :updated_at, :created_by)";
        
        $stmt = $conn->prepare($category_sql);
        $stmt->execute($category_data);

        // Set the success message
        $_SESSION['response'] = [
            'success' => true,
            'message' => 'Category successfully added to the system!'
        ];

    } catch (PDOException $e) {
        // Set the error message
        $_SESSION['response'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Redirect back to the category add page
header('Location: ../sku-add.php');
exit();
?>
