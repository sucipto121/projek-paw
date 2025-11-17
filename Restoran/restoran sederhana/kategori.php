<?php
session_start();
if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pilih Kategori</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2 class="title">Pilih Kategori</h2>

<div class="kategori-container">
    <a href="makanan.php?jenis=makanan" class="kategori-card">Makanan</a>
    <a href="makanan.php?jenis=minuman" class="kategori-card">Minuman</a>
    <a href="makanan.php?jenis=snack" class="kategori-card">Snack</a>
</div>

</body>
</html>