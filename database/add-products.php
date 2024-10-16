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
        // Retrieve the product name from the form
        $product_name = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';

        // Check if the product name already exists
        $check_sql = "SELECT * FROM products WHERE product_name = :product_name";
        $stmt = $conn->prepare($check_sql);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->execute();

        // If the product already exists, set an error message and redirect
        if ($stmt->rowCount() > 0) {
            $_SESSION['response'] = [
                'success' => false,
                'message' => 'Product name already exists! Please choose a different name.'
            ];
            header('Location: ../products-add.php');
            exit();
        }

        // Prepare the data for the products table
        $product_data = [
            'product_name' => $product_name,
            'description' => isset($_POST['description']) ? $_POST['description'] : '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['user']['id'] // Assume user session contains 'id'
        ];

        // Prepare the SQL query for inserting into products table
        $product_sql = "INSERT INTO products 
                        (product_name, description, created_at, updated_at, created_by) 
                        VALUES 
                        (:product_name, :description, :created_at, :updated_at, :created_by)";
        
        $stmt = $conn->prepare($product_sql);
        $stmt->execute($product_data);

        // Get the last inserted product ID
        $product_id = $conn->lastInsertId();

        // Add category data for the product in productscategory table
        if (isset($_POST['category'])) {
            foreach ($_POST['category'] as $category_id) {
                $category_data = [
                    'category' => $category_id,
                    'product' => $product_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $category_sql = "INSERT INTO productscategory
                                (category, product, created_at, updated_at)
                                VALUES (:category, :product, :created_at, :updated_at)";
                
                $stmt = $conn->prepare($category_sql);
                $stmt->execute($category_data);
            }
        }

        // Set the success message
        $_SESSION['response'] = [
            'success' => true,
            'message' => 'Product successfully added to the system!'
        ];

    } catch (PDOException $e) {
        // Set the error message
        $_SESSION['response'] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Redirect back to the product add page
header('Location: ../products-add.php');
exit();
?>
