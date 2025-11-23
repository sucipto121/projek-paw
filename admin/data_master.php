<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php?err=' . urlencode('Silakan login terlebih dahulu'));
    exit;
}
require 'koneksi.php';

// Handle create kategori & menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    /* ----------------------- TAMBAH KATEGORI ----------------------- */
    if ($_POST['action'] === 'add_kategori') {
        $nama = trim($_POST['nama_kategori'] ?? '');
        $des = trim($_POST['deskripsi_kategori'] ?? '');

        if ($nama !== '') {
            $stmt = $mysqli->prepare('INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)');
            if ($stmt) {
                $stmt->bind_param('ss', $nama, $des);
                $stmt->execute();
            }
        }
    }

    /* ----------------------- TAMBAH MENU --------------------------- */
    if ($_POST['action'] === 'add_menu') {
        $id_kat = (int)($_POST['id_kategori'] ?? 0);
        $nama = trim($_POST['nama_menu'] ?? '');
        $des = trim($_POST['deskripsi_menu'] ?? '');
        $harga = floatval($_POST['harga'] ?? 0);
        $stok = (int)($_POST['stok'] ?? 0);
        $status = in_array($_POST['status'] ?? 'tersedia', ['tersedia','habis']) ? $_POST['status'] : 'tersedia';

        /* --- VALIDASI & SIMPAN FOTO --- */
        $img = null;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg','jpeg','png','gif'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                // nama file unik
                $filename = time() . "_" . rand(1000, 9999) . "." . $ext;
                $dest = "uploads/" . $filename;

                // Pindahkan file
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
                    $img = $filename; // SIMPAN NAMA FILE SAJA KE DATABASE
                }
            }
        }

        if ($nama !== '') {
            $stmt = $mysqli->prepare('INSERT INTO menu (id_kategori, nama_menu, deskripsi, harga, stok, foto, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('issdiss', $id_kat, $nama, $des, $harga, $stok, $img, $status);
                $stmt->execute();
            }
        }
    }

    header('Location: data_master.php');
    exit;
}


// Load kategori
$kats = [];
$res = $mysqli->query('SELECT * FROM kategori ORDER BY id_kategori DESC');
if ($res) {
    while ($r = $res->fetch_assoc()) $kats[] = $r;
}

// Load menu
$menus = [];
$res = $mysqli->query('SELECT m.*, k.nama_kategori FROM menu m LEFT JOIN kategori k ON m.id_kategori=k.id_kategori ORDER BY m.id_menu DESC');
if ($res) {
    while ($r = $res->fetch_assoc()) $menus[] = $r;
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Data Master — Admin</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>
    label{display:block;margin-bottom:6px}
    table{width:100%;border-collapse:collapse}
    td,th{padding:8px;border-bottom:1px solid #eee;text-align:left}
    th{background-color:#f8f9fa}
    .col-id{width:60px;text-align:center}
    .col-center{text-align:center}
    .col-right{text-align:right}
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand"><div class="logo">R</div><h1>Data Master</h1></div>
    <div class="user"><div class="avatar"><?= strtoupper(substr($_SESSION['user']['nama'],0,1)) ?></div></div>
  </header>

  <div class="layout">
    <aside class="sidebar">
      <div class="menu">
        <a href="index.php">Home</a>
        <a class="active" href="data_master.php">Data Master</a>
      </div>
    </aside>

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
        <form method="POST" enctype="multipart/form-data">
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
          <label>Upload Foto <input type="file" name="foto" accept="image/*"></label>
          <label>Status <select name="status">
            <option value="tersedia">tersedia</option>
            <option value="habis">habis</option>
          </select></label>

          <button class="btn primary" type="submit">Tambah Menu</button>
        </form>
      </div>

      <div class="card" style="margin-top:12px">
        <h3>Daftar Kategori</h3>
        <table>
          <thead><tr><th class="col-id">ID</th><th>Nama</th><th>Deskripsi</th></tr></thead>
          <tbody>
            <?php foreach($kats as $c): ?>
              <tr><td class="col-id"><?= $c['id_kategori'] ?></td><td><?= e($c['nama_kategori']) ?></td><td><?= e($c['deskripsi']) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="card" style="margin-top:12px">
        <h3>Daftar Menu</h3>
        <table>
          <thead><tr><th class="col-id">ID</th><th>Nama</th><th>Kategori</th><th class="col-right">Harga</th><th class="col-center">Stok</th><th class="col-center">Status</th></tr></thead>
          <tbody>
            <?php foreach($menus as $m): ?>
              <tr>
                <td class="col-id"><?= $m['id_menu'] ?></td>
                <td><?= e($m['nama_menu']) ?></td>
                <td><?= e($m['nama_kategori'] ?? '-') ?></td>
                <td class="col-right"><?= number_format($m['harga'],2,',','.') ?></td>
                <td class="col-center"><?= (int)$m['stok'] ?></td>
                <td class="col-center"><?= e($m['status']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </main>
  </div>
</body>
</html>
