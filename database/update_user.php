<?php
$data = $_POST;
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;  // Corrected
$first_name = isset($data['f_name']) ? $data['f_name'] : '';  // Corrected
$last_name = isset($data['l_name']) ? $data['l_name'] : '';  // Corrected
$email = isset($data['email']) ? $data['email'] : '';
$permissions = isset($data['permissions']) ? $data['permissions'] : [];  // Will now receive as an array

// Prepare the permissions string if provided
$permissionsString = !empty($permissions) && is_array($permissions) ? implode(',', $permissions) : null;

try {
    include('connection.php');

    // Start the transaction
    $conn->beginTransaction();

    // Update the user details
    $sql = "UPDATE users SET email=?, first_name=?, last_name=?, updated_at=NOW() WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email, $first_name, $last_name, $user_id]);

    // Log if the user details update was successful
    if ($stmt->rowCount() > 0) {
        error_log('User details updated successfully for ID: ' . $user_id);
    } else {
        error_log('No changes made to user details for ID: ' . $user_id);
    }

    // If permissions were provided, update them as well
    if ($permissionsString !== null) {
        $sqlPermissions = "UPDATE users SET permissions=? WHERE id=?";
        $stmtPermissions = $conn->prepare($sqlPermissions);
        $stmtPermissions->execute([$permissionsString, $user_id]);

        // Log the number of rows affected for permissions update
        if ($stmtPermissions->rowCount() > 0) {
            error_log('Permissions updated successfully for user ID: ' . $user_id);
        } else {
            error_log('No permissions updated for user ID: ' . $user_id);
        }
    } else {
        error_log('No permissions to update for user ID: ' . $user_id);
    }

    // Commit the transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => $first_name . ' ' . $last_name . ' updated successfully!'
    ]);

} catch (PDOException $e) {
    // Rollback the transaction in case of an error
    $conn->rollBack();
    error_log('Error processing request: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error processing your request: ' . $e->getMessage()
    ]);
}
?>
