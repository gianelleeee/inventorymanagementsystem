<?php
    //start the session
    session_start();
    
    $error_message = '';
    if($_POST){
        include('database/connection.php');

        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $query = 'SELECT * FROM users WHERE users.email= "'. $email .'" AND users.password="'. $password .'" LIMIT 1';
        $stmt = $conn->prepare($query);
        $stmt->execute();

        

        if($stmt->rowCount() > 0){
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $user = $stmt->fetchAll()[0];
            $_SESSION['user'] = $user;
            
            header('Location: dashboard.php');

        } else $error_message = 'Please make sure that username and password are correct';
        


    }



?>




<!DOCTYPE html>
<html lang="en">
<head>
    <title>IMS Login</title>

    <link rel="stylesheet" type="text/css" href="css/stylesheet.css">
</head>
<body id="loginBody">
    <?php
        if(!empty($error_message)){ ?>
    
        <div style="background: #fff; text-align: center; color: red; font-size: 20px; padding: 11px;">
            <strong>Error:</strong><p><?= $error_message ?></p>
        </div>
    <?php } ?>
    <div class="container">
        <div class="loginHeader">
            <h1>IMS</h1>
            <p>INVENTORY MANAGEMENT SYSTEM</p>
        </div>
        <div class="loginBody">
            <form action="index.php" method="POST">
                <div class="loginInputsContainer">
                    <label for="">Email</label>
                    <input placeholder="email"  name="email" type="text" />
                </div>
                <div class="loginInputsContainer">
                    <label for="">Password</label>
                    <input placeholder="password"  name="password" type="password" />
                </div>
                <div class="loginButtonContainer">
                    <button>Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>