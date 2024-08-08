<?php

// Set the default timezone to Philippines
date_default_timezone_set('Asia/Manila');


    session_start();

    $table_name = $_SESSION['table'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $encrypted = password_hash($password, PASSWORD_DEFAULT);

    //Adding the record
    try{
        $command = "INSERT INTO 
                        $table_name(first_name, last_name, email, password, created_at, updated_at) 
                    VALUES 
                        ('".$first_name."', '".$last_name."', '".$email."', '".$encrypted."', NOW(), NOW())";

        include('connection.php');

        $response = [
            'success' => true,
            'message' => $first_name . ' ' . $last_name . ' Successfully Registered!'
        ];

        $conn->exec($command);
    } 
    catch(PDOException $e){
        $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    }
   
    $_SESSION['response'] = $response;
    header('Location: ../register.php');
?>