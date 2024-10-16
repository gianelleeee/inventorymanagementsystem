<?php
// Start the session
session_start();

// Capture the table mappings
include('table_columns.php');

// Set the default timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Capture the table name
$table_name = $_SESSION['table'];
$columns = $table_columns_mapping[$table_name];

// Initialize array for storing data
$db_arr = [];
$user = $_SESSION['user'];

// Loop through the columns and prepare data
foreach ($columns as $column) {
    if (in_array($column, ['created_at', 'updated_at'])) {
        $value = date('Y-m-d H:i:s');
    } elseif ($column == 'created_by') {
        $value = $user['id'];
    } elseif ($column == 'password') {
        $value = password_hash($_POST[$column], PASSWORD_DEFAULT);
    } else {
        $value = isset($_POST[$column]) ? $_POST[$column] : '';
    }

    $db_arr[$column] = $value;
}

// Check if product_name already exists for the products table
if ($table_name === 'products') {
    $product_name = $db_arr['product_name']; // Adjust the column name as necessary
    $checkProductQuery = "SELECT COUNT(*) FROM products WHERE product_name = :product_name";
    include('connection.php');
    $checkProductStmt = $conn->prepare($checkProductQuery);
    $checkProductStmt->execute(['product_name' => $product_name]);
    $productExists = $checkProductStmt->fetchColumn();

    if ($productExists) {
        $_SESSION['response'] = [
            'success' => false,
            'message' => 'Product name already exists!'
        ];
        header('Location: ../' . $_SESSION['redirect_to']);
        exit();
    }
}

// Check if category_name already exists for the category table
if ($table_name === 'category') {
    $category_name = $db_arr['category_name']; // Adjust the column name as necessary
    $checkCategoryQuery = "SELECT COUNT(*) FROM category WHERE category_name = :category_name";
    include('connection.php');
    $checkCategoryStmt = $conn->prepare($checkCategoryQuery);
    $checkCategoryStmt->execute(['category_name' => $category_name]);
    $categoryExists = $checkCategoryStmt->fetchColumn();

    if ($categoryExists) {
        $_SESSION['response'] = [
            'success' => false,
            'message' => 'Category name already exists!'
        ];
        header('Location: ../' . $_SESSION['redirect_to']);
        exit();
    }
}

// Insert record into the main table
$table_properties = implode(", ", array_keys($db_arr));
$table_placeholders = ':' . implode(", :", array_keys($db_arr));

try {
    $sql = "INSERT INTO 
                $table_name ($table_properties) 
            VALUES 
                ($table_placeholders)";

    include('connection.php');

    $stmt = $conn->prepare($sql);
    $stmt->execute($db_arr);

    // Get the last inserted product ID
    $product_id = $conn->lastInsertId();

    // Add categories to productscategory table if applicable
    if ($table_name === 'products') {
        $category = isset($_POST['category']) ? $_POST['category'] : []; // Ensure category array is captured from form
        if (!empty($category)) {
            foreach ($category as $cat) {
                $category_data = [
                    'category' => $cat, // This should be the category ID
                    'product' => $product_id, // The new product ID
                    'updated_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $sql = "INSERT INTO productscategory
                            (category, product, updated_at, created_at)
                        VALUES 
                            (:category, :product, :updated_at, :created_at)";

                $stmt = $conn->prepare($sql);
                $stmt->execute($category_data);
            }
        }
    }

    // Response for successful insertion
    $response = [
        'success' => true,
        'message' => 'Successfully added to the system!'
    ];

} catch (PDOException $e) {
    // Error handling
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Store the response and redirect
$_SESSION['response'] = $response;
header('Location: ../' . $_SESSION['redirect_to']);
exit();
?>
