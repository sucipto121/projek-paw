<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php?err=' . urlencode('Silakan login terlebih dahulu'));
    exit;
}
require 'koneksi.php';

// Pastikan variabel $user tersedia untuk header dan tampilan
$user = $_SESSION['user'];

// Handle create kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_kategori') {
        $nama = trim($_POST['nama_kategori'] ?? '');
        $des = trim($_POST['deskripsi_kategori'] ?? '');
        if ($nama !== '') {
            $stmt = $mysqli->prepare('INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)');
            if ($stmt) { $stmt->bind_param('ss', $nama, $des); $stmt->execute(); }
        }
    }
    if ($_POST['action'] === 'add_menu') {
        $id_kat = (int)($_POST['id_kategori'] ?? 0);
        $nama = trim($_POST['nama_menu'] ?? '');
        $des = trim($_POST['deskripsi_menu'] ?? '');
        $harga = floatval($_POST['harga'] ?? 0);
        $stok = (int)($_POST['stok'] ?? 0);
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto = file_get_contents($_FILES['foto']['tmp_name']);
        }
        $status = in_array($_POST['status'] ?? 'tersedia', ['tersedia','habis']) ? $_POST['status'] : 'tersedia';
        if ($nama !== '') {
            $stmt = $mysqli->prepare('INSERT INTO menu (id_kategori, nama_menu, deskripsi, harga, stok, foto, status) VALUES (?, ?, ?, ?, ?, ?)');
            if ($stmt) { $stmt->bind_param('issdis', $id_kat, $nama, $des, $harga, $stok, $status, $foto); $stmt->execute(); }
        }
    }
    header('Location: data_master.php');
    exit;
}

$kats = [];
$menus = [];
$res = $mysqli->query('SELECT * FROM kategori ORDER BY id_kategori DESC');
if ($res) { while ($r = $res->fetch_assoc()) $kats[] = $r; }
$res = $mysqli->query('SELECT m.*, k.nama_kategori FROM menu m LEFT JOIN kategori k ON m.id_kategori=k.id_kategori ORDER BY m.id_menu DESC');
if ($res) { while ($r = $res->fetch_assoc()) $menus[] = $r; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Data Master</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>label{display:block;margin-bottom:6px} table{width:100%;border-collapse:collapse} td,th{padding:8px;border-bottom:1px solid #eee}</style>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <div class="logo">R</div>
      <h1>Restoran Laut Nusantara</h1>
    </div>
    <nav>
      <div class="top-menu">
        <a href="index.php">Dashboard</a>
        <a href="data_master.php">Data Master</a>
        <a href="transaksi.php">Transaksi</a>
        <a href="laporan.php">Laporan</a>
      </div>
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
    <aside class="sidebar"><div class="menu"><a href="index.php">Home</a><a class="active" href="data_master.php">Data Master</a><a href="transaksi.php">Transaksi</a><a href="laporan.php">Laporan</a></div></aside>
    <main class="main">
      <div class="card">
        <h3>Tambah Kategori</h3>
        <form method="POST">
          <input type="hidden" name="action" value="add_kategori">
          <label>Nama Kategori <input name="nama_kategori" required></label>
          <label>Deskripsi <textarea name="deskripsi_kategori"></textarea></label>
          <button class="btn primary" type="submit">Tambah</button>
        </form>
      </div>

      <div class="card" style="margin-top:12px">
        <h3>Tambah Menu</h3>
        <form method="POST">
          <input type="hidden" name="action" value="add_menu">
          <label>Kategori <select name="id_kategori">
            <option value="0">-- Pilih --</option>
            <?php foreach($kats as $c): ?>
              <option value="<?= $c['id_kategori'] ?>"><?= e($c['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select></label>
          <label>Nama Menu <input name="nama_menu" required></label>
          <label>Deskripsi <textarea name="deskripsi_menu"></textarea></label>
          <label>Harga <input name="harga" type="number" step="0.01" value="0"></label>
          <label>Stok <input name="stok" type="number" value="0"></label>
          <label>Upload Foto<input type="file" name="foto" accept="image/*"></label>
          <label>Status <select name="status"><option value="tersedia">tersedia</option><option value="habis">habis</option></select></label>
          <button class="btn primary" type="submit">Tambah Menu</button>
        </form>
      </div>

      <div class="card" style="margin-top:12px">
        <h3>Daftar Kategori</h3>
        <table>
          <thead><tr><th>ID</th><th>Nama</th><th>Deskripsi</th></tr></thead>
          <tbody>
            <?php foreach($kats as $c): ?>
              <tr><td><?= $c['id_kategori'] ?></td><td><?= e($c['nama_kategori']) ?></td><td><?= e($c['deskripsi']) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="card" style="margin-top:12px">
        <h3>Daftar Menu</h3>
        <table>
          <thead><tr><th>ID</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach($menus as $m): ?>
              <tr>
                <td><?= $m['id_menu'] ?></td>
                <td><?= e($m['nama_menu']) ?></td>
                <td><?= e($m['nama_kategori'] ?? '-') ?></td>
                <td><?= number_format($m['harga'],2,',','.') ?></td>
                <td><?= (int)$m['stok'] ?></td>
                <td><?= e($m['status']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
