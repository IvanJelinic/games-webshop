<?php
$host = 'localhost:3307';
$username = 'root';
$password = '';
$database = 'webshop';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
