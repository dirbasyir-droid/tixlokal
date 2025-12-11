<?php
session_start();
// Database Credentials
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db = 'concert_db';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Ensure the uploads directory exists for receipts and concert images
if (!is_dir('uploads')) {
    mkdir('uploads');
}

// Simple redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}
?>