<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php?err=' . urlencode('Silakan login terlebih dahulu'));
    exit;
}
require 'koneksi.php';

$res = $mysqli->query('SELECT id_menu, nama_menu, stok, harga, status FROM menu ORDER BY nama_menu');
$rows = [];
if ($res) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Stok Menu — Admin</title>
<link rel="stylesheet" href="css/admin.css">
<style>table{width:100%;border-collapse:collapse}th,td{padding:8px;border-bottom:1px solid #eee}</style>
</head>
<body>
<header class="topbar"><div class="brand"><div class="logo">R</div><h1>Stok Menu</h1></div></header>
<main style="padding:16px">
  <a class="btn" href="data_master.php">Kembali ke Data Master</a>
  <h2>Daftar Stok</h2>
  <table>
    <thead><tr><th>ID</th><th>Nama Menu</th><th>Harga</th><th>Stok</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['id_menu']) ?></td>
        <td><?= htmlspecialchars($r['nama_menu']) ?></td>
        <td>Rp <?= number_format($r['harga'],0,',','.') ?></td>
        <td><?= htmlspecialchars($r['stok']) ?></td>
        <td><?= htmlspecialchars($r['status']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</main>
</body>
</html>
