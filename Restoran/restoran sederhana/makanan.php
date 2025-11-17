<?php
session_start();
if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit();
}

$jenis = $_GET['jenis'];

// DATA MENU (bisa diganti sesuai kebutuhan)
$data = [
    'makanan' => [
        ['nama' => 'Nasi Goreng', 'harga' => 15000],
        ['nama' => 'Mie Ayam', 'harga' => 12000],
        ['nama' => 'Ayam Geprek', 'harga' => 14000],
    ],
    'minuman' => [
        ['nama' => 'Es Teh', 'harga' => 5000],
        ['nama' => 'Kopi Susu', 'harga' => 10000],
        ['nama' => 'Jus Jeruk', 'harga' => 8000],
    ],
    'snack' => [
        ['nama' => 'Kentang Goreng', 'harga' => 7000],
        ['nama' => 'Risoles', 'harga' => 5000],
        ['nama' => 'Tahu Crispy', 'harga' => 6000],
    ]
];

// Tambah ke keranjang
if (isset($_GET['add'])) {
    $id = $_GET['add'];
    $_SESSION['keranjang'][] = $data[$jenis][$id];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kategori <?php echo ucfirst($jenis); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2 class="title">Kategori: <?php echo ucfirst($jenis); ?></h2>

<a href="kategori.php" class="btn-back">Kembali</a>
<a href="keranjang.php" class="btn-keranjang">Keranjang</a>

<div class="menu-container">
<?php foreach ($data[$jenis] as $i => $item): ?>
    <div class="menu-card">
        <img src="https://via.placeholder.com/100" alt="">
        <h3><?php echo $item['nama']; ?></h3>
        <p>Rp <?php echo $item['harga']; ?></p>
        <a href="makanan.php?jenis=<?php echo $jenis; ?>&add=<?php echo $i; ?>" class="btn-add">+ Tambah</a>
    </div>
<?php endforeach; ?>
</div>

</body>
</html>