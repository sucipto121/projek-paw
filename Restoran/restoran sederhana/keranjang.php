<?php
session_start();
if (!isset($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];

$total = 0;
foreach ($_SESSION['keranjang'] as $k) {
    $total += $k['harga'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Keranjang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2 class="title">Keranjang Belanja</h2>

<a href="kategori.php" class="btn-back">Kembali</a>

<div class="cart-box">
<?php foreach ($_SESSION['keranjang'] as $item): ?>
    <p><?php echo $item['nama']; ?> - Rp <?php echo $item['harga']; ?></p>
<?php endforeach; ?>

<h3>Total: Rp <?php echo $total; ?></h3>

<a href="bayar.php" class="btn-pay">Bayar</a>
</div>

</body>
</html>