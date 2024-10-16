<?php
    include('connection.php');

    // The table name you're working with (from the $show_table variable)
    $table_name = $show_table;

    // Assuming the 'created_by' column references the user who created the record
    // Fetching users with the creator's first and last name (assuming it references the 'users' table)
    $sql = "SELECT u.*, c.first_name AS created_by_first_name, c.last_name AS created_by_last_name 
            FROM $table_name u
            LEFT JOIN users c ON u.created_by = c.id 
            ORDER BY u.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    return $stmt->FetchAll();
?>
