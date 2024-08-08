<?php
    //start the session
    session_start();
    if(isset($_SESSION['user'])) header('location: dashboard.php');
    
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

            //captures data of currently login user
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
    <style>
        .link a {
            color: white;
            margin-top: 10px;
            font-size: 20px;
        }
        .text-center{
            text-align: center;
        }

        h2, p{
            color: white;
        }

        .link a:hover{
            color: #649037;
        }
    </style>
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
                <h2 class="text-center">Login Form</h2>
                <p class="text-center">Login with your email and password.</p>
                <div class="loginInputsContainer">
                    <label for="">Email</label>
                    <input placeholder="email"  name="email" type="text" />
                </div>
                <div class="loginInputsContainer">
                    <label for="">Password</label>
                    <input placeholder="password"  name="password" type="password" />
                </div>
                <div class="link forget-pass text-left" style="padding-top: 6px;"><a href="forgot-password.php">Forgot password?</a></div>
                <div class="loginButtonContainer">
                    <button>Login</button>
                </div>
                <div class="link login-link text-center" style="padding-top: 10px;"><a href="register.php">Register here</a></div>
            </form>
            <div>

            </div>
        </div>
    </div>
</body>
</html>