<?php
session_start();

$total = 0;
foreach ($_SESSION['keranjang'] as $k) {
    $total += $k['harga'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2 class="title">Detail Pembayaran</h2>

<div class="bayar-box">
    <p>Nama Pemesan: <b><?php echo $_SESSION['nama']; ?></b></p>
    <p>Nomor Meja: <b><?php echo $_SESSION['meja']; ?></b></p>

    <hr>

    <?php foreach ($_SESSION['keranjang'] as $item): ?>
        <p><?php echo $item['nama']; ?> - Rp <?php echo $item['harga']; ?></p>
    <?php endforeach; ?>

    <h3>Total Pembayaran: Rp <?php echo $total; ?></h3>
</div>

</body>
</html>