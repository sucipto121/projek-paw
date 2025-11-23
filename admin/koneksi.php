<?php
$host = "localhost";
$db   = "restoran_db"; 
$user = "root";
$pass = ""; 

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("Gagal koneksi MySQL: " . $mysqli->connect_error);
}
?>
