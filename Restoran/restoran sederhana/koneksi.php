<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "restoran_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("KONEKSI GAGAL: " . mysqli_connect_error());
}
?>
