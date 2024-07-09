<?php
$id = $_GET['id'];

include('connection.php');

$stmt = $conn->prepare("SELECT * FROM products WHERE id=$id");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);


echo json_encode($row);


?>