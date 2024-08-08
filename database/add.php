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

// Loop through the columns
$db_arr = [];
$user = $_SESSION['user'];
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

$table_properties = implode(", ", array_keys($db_arr));
$table_placeholders = ':' . implode(", :", array_keys($db_arr));

// Adding the record to the main table
try {
    $sql = "INSERT INTO 
                $table_name ($table_properties) 
            VALUES
                ($table_placeholders)";

    include('connection.php');

    $stmt = $conn->prepare($sql);
    $stmt->execute($db_arr);

    // Get saved id
    $product_id = $conn->lastInsertId();

    // Add category
    if ($table_name === 'products') {
        $category = isset($_POST['category']) ? $_POST['category'] : [];
        if ($category) {
            // Loop through category and add record
            foreach ($category as $cat) {
                $category_data = [
                    'category' => $cat,
                    'product' => $product_id,
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

    $response = [
        'success' => true,
        'message' => 'Successfully added to the system!'
    ];

} catch (PDOException $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

$_SESSION['response'] = $response;
header('Location: ../' . $_SESSION['redirect_to']);
exit();
?>