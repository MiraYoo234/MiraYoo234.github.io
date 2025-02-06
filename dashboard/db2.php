<?php
// Database connection using PDO
$host = "192.168.100.218"; // Replace with your Linux server's IP
$username = "nyan";
$password = "nyan";
$database = "my_blog_database";

$conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
