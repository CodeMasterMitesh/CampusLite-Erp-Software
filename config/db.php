<?php
// Database connection using mysqli (procedural)
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'tuition360';

$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
// Set charset
mysqli_set_charset($conn, 'utf8mb4');
?>
