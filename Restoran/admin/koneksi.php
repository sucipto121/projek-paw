<?php
$host = $_ENV['APP_DB_HOST'] ?? "localhost";
$db   = $_ENV['APP_DB_NAME'] ?? "restoran_db"; 
$user = $_ENV['APP_DB_USER'] ?? "root";
$pass = $_ENV['APP_DB_PASSWORD'] ?? ""; 

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("Gagal koneksi MySQL: " . $mysqli->connect_error);
}
?>