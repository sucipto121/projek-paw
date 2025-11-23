<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php?err=Silakan login terlebih dahulu");
  exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Home</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>

<header class="topbar">
  <div class="brand">
    <div class="logo">R</div>
    <h1>Rasa Laut Nusantara</h1>
    
  </div>
  <nav>
    <?php if(!empty($user['level']) && $user['level'] == 1): ?>
      <div class="top-menu">
        <a href="index.php">Dashboard</a>
        <a href="data_master.php">Data Master</a>
        <a href="transaksi.php">Transaksi</a>
        <a href="laporan.php">Laporan</a></div>
    <?php else: ?>
      <a href="index.php">Dashboard</a>
      <a href="transaksi.php">Transaksi</a>
      <a href="laporan.php">Laporan</a>
    <?php endif; ?>
  </nav>
  <div class="user">
    <div class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div>
    <div>
      <div class="name"><?= htmlspecialchars($user['nama']) ?></div>
      <a href="logout.php">Logout</a>
    </div>
  </div>
</header>

<div class="layout">
  <aside class="sidebar">
    <div class="menu">
      <a class="active" href="index.php">Home</a>
      <?php if(!empty($user['level']) && $user['level'] == 1): ?>
        <a href="data_master.php">Data Master</a>
      <?php endif; ?>
      <a href="transaksi.php">Transaksi</a>
      <a href="laporan.php">Laporan</a>
    </div>
  </aside>

  <main class="main">
    <div class="welcome">
      <h2>Selamat datang, <?= htmlspecialchars($user['nama']) ?></h2>
      <div>
        <a class="btn ghost" href="#">Profil</a>
        <a class="btn primary" href="logout.php">Logout</a>
      </div>
    </div>

    <div class="cards">
      <div class="card">
        <h3>Ringkasan Pesanan</h3>
        <p>Menampilkan pesanan terbaru, status, dan total hari ini.</p>
      </div>
      <div class="card">
        <h3>Menu</h3>
        <p>Kelola daftar menu, harga, dan ketersediaan.</p>
      </div>
      <div class="card">
        <h3>Laporan</h3>
        <p>Ekspor laporan penjualan dan analitik.</p>
      </div>
    </div>
  </main>
</div>

</body>
</html>
