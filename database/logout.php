<?php
    session_start();

    //remove all session variables
    session_unset();

    //destroy
    session_destroy();

    header('Location: ../index.php');
    exit();

?>