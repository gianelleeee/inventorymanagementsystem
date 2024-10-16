<?php
// Start the session
session_start();

// Set the default timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Ensure email is set
if (!isset($_POST['email']) || empty($_POST['email'])) {
    $_SESSION['response'] = [
        'success' => false,
        'message' => 'Email is required.'
    ];
    header('Location: ../' . $_SESSION['redirect_to']);
    exit();
}

// Capture the user info from the session
$user = $_SESSION['user'];

// Capture email and permissions
$email = $_POST['email'];
$permissions = isset($_POST['permissions']) ? $_POST['permissions'] : '';

// Prepare the database insert array
$currentTimestamp = date('Y-m-d H:i:s'); // Current timestamp
$db_arr = [
    'email' => $email,
    'permissions' => $permissions, // Add permissions to the array
    'created_at' => $currentTimestamp, // Set created_at to current timestamp
    'updated_at' => $currentTimestamp, // Set updated_at to the same timestamp
    'created_by' => $user['id'] // Assuming you track who added the record
];

try {
    // Include the database connection file
    include('connection.php');

    // Check if the email already exists
    $checkSql = "SELECT COUNT(*) FROM users WHERE email = :email";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute(['email' => $email]);
    $emailExists = $checkStmt->fetchColumn();

    if ($emailExists > 0) {
        // If the email exists, return a response
        $_SESSION['response'] = [
            'success' => false,
            'message' => 'Email already exists in the system.'
        ];
    } else {
        // Create the insert query
        $sql = "INSERT INTO users (email, permissions, created_at, updated_at, created_by) 
                VALUES (:email, :permissions, :created_at, :updated_at, :created_by)";
        
        // Prepare and execute the insert query
        $stmt = $conn->prepare($sql);
        $stmt->execute($db_arr);

        // On success
        $_SESSION['response'] = [
            'success' => true,
            'message' => 'Successfully added the email and permissions to the system!'
        ];
    }
} catch (PDOException $e) {
    // On error
    $_SESSION['response'] = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ];
}

// Redirect to the original form page
header('Location: ../' . $_SESSION['redirect_to']);
exit();
?>
