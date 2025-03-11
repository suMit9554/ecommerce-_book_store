
<?php

$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "sumit_php"; 
$port = 3307;

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$smtp_username = "sumitshukl2024@gmail.com"; 
$smtp_password = "byeo huiu urmp oeya"; 
$smtp_host = "smtp.gmail.com"; 
$smtp_port = 587; 

?>
