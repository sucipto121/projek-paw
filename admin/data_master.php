<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php?err=' . urlencode('Silakan login terlebih dahulu'));
    exit;
}
require 'koneksi.php';

$foto_column_ok = true;
$foto_column_note = '';
try {
  $q = $mysqli->query("SELECT DATA_TYPE, COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='menu' AND COLUMN_NAME='foto' LIMIT 1");
  if ($q) {
    $col = $q->fetch_assoc();
    if ($col) {
      $dt = strtolower($col['DATA_TYPE'] ?? '');
      if (!in_array($dt, ['blob','mediumblob','longblob'])) {
        $foto_column_ok = false;
        $foto_column_note = 'Kolom `menu.foto` bertipe ' . ($col['COLUMN_TYPE'] ?? $dt) . ", sebaiknya gunakan BLOB/MEDIUMBLOB/LONGBLOB agar gambar biner tersimpan dengan benar.";
      }
    } else {
      $foto_column_ok = false;
      $foto_column_note = 'Kolom `menu.foto` tidak ditemukan di database. Pastikan tabel `menu` memiliki kolom `foto` bertipe BLOB.';
    }
  }
} catch (Throwable $e) {
  $foto_column_ok = false;
  $foto_column_note = 'Tidak bisa memeriksa tipe kolom foto: ' . $e->getMessage();
}

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'add_kategori') {
    $nama = trim($_POST['nama_kategori'] ?? '');
    $des = trim($_POST['deskripsi_kategori'] ?? '');
    if ($nama !== '') {
      $stmt = $mysqli->prepare('INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)');
      if ($stmt) { $stmt->bind_param('ss', $nama, $des); $stmt->execute(); }
    }
    header('Location: data_master.php');
    exit;
  }
  if ($_POST['action'] === 'add_menu') {
    $id_kat = (int)($_POST['id_kategori'] ?? 0);
    $nama = trim($_POST['nama_menu'] ?? '');
    $des = trim($_POST['deskripsi_menu'] ?? '');
    $harga = floatval($_POST['harga'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);
    $errors = [];
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
      $f = $_FILES['foto'];
      if ($f['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Kesalahan saat mengunggah file (kode: ' . $f['error'] . ').';
      } else {
        $maxSize = 2 * 1024 * 1024;
        if ($f['size'] > $maxSize) $errors[] = 'Ukuran gambar terlalu besar. Maksimum 2 MB.';
        $imgInfo = @getimagesize($f['tmp_name']);
        if ($imgInfo === false) {
          $errors[] = 'File bukan gambar yang valid.';
        } else {
          $mime = $imgInfo['mime'] ?? '';
          $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
          if (!in_array($mime, $allowed)) $errors[] = 'Tipe gambar tidak didukung. Gunakan JPG/PNG/GIF/WEBP.';
          $maxW = 3000; $maxH = 3000;
          if (!empty($imgInfo[0]) && !empty($imgInfo[1]) && ($imgInfo[0] > $maxW || $imgInfo[1] > $maxH)) {
            $errors[] = 'Dimensi gambar terlalu besar (maks ' . $maxW . 'x' . $maxH . ').';
          }
          if (empty($errors)) {
            $foto = file_get_contents($f['tmp_name']);
          }
        }
      }
    }
    $status = in_array($_POST['status'] ?? 'tersedia', ['tersedia','habis']) ? $_POST['status'] : 'tersedia';
    if ($nama === '') $errors[] = 'Nama menu tidak boleh kosong.';
    if (!empty($errors)) {
      $_SESSION['flash_errors'] = $errors;
      header('Location: data_master.php');
      exit;
    }
    if ($nama !== '') {
      $stmt = $mysqli->prepare('INSERT INTO menu (id_kategori, nama_menu, deskripsi, harga, stok, foto, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
      if ($stmt) {
        $is_blob_storage = !empty($foto_column_ok);
        $foto_param = '';
        if ($foto !== null) {
          if ($is_blob_storage) {
            $foto_param = $foto;
          } else {
            $uploadDir = realpath(__DIR__ . '/../pengguna') . DIRECTORY_SEPARATOR . 'images';
            if ($uploadDir === false) $uploadDir = __DIR__ . '/../pengguna/images';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $origName = isset($f['name']) ? $f['name'] : ('img_' . time());
            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            $ext = $ext ? strtolower($ext) : 'jpg';
            try { $rand = bin2hex(random_bytes(6)); } catch (Throwable $e) { $rand = substr(md5(uniqid('',true)),0,12); }
            $filename = time() . '_' . $rand . '.' . $ext;
            $target = $uploadDir . DIRECTORY_SEPARATOR . $filename;
            if (isset($f['tmp_name']) && is_uploaded_file($f['tmp_name'])) {
              if (@move_uploaded_file($f['tmp_name'], $target)) {
                $foto_param = $filename;
              } else {
                if ($foto !== null) {
                  @file_put_contents($target, $foto);
                  if (file_exists($target)) $foto_param = $filename;
                }
              }
            }
          }
        }

        if ($is_blob_storage) {
          $types = 'issdibs'; 
        } else {
          $types = 'issdiss'; 
        }
        $stmt->bind_param($types, $id_kat, $nama, $des, $harga, $stok, $foto_param, $status);
        if ($is_blob_storage && $foto !== null) {
          $stmt->send_long_data(5, $foto);
        }
        $ok = $stmt->execute();
        if ($ok) {
          $_SESSION['flash_success'] = 'Menu berhasil ditambahkan.';
          header('Location: ../pengguna/index.php');
          exit;
        } else {
          $_SESSION['flash_errors'] = ['Gagal menyimpan data: ' . $stmt->error];
          header('Location: data_master.php');
          exit;
        }
      }
    }
  }
  if ($_POST['action'] === 'delete_kategori' && isset($_POST['id_kategori'])) {
    $id = (int)$_POST['id_kategori'];
    $mysqli->query("DELETE FROM menu WHERE id_kategori = $id");
    $mysqli->query("DELETE FROM kategori WHERE id_kategori = $id");
    header('Location: data_master.php');
    exit;
  }
  if ($_POST['action'] === 'delete_menu' && isset($_POST['id_menu'])) {
    $id = (int)$_POST['id_menu'];
    $mysqli->query("DELETE FROM menu WHERE id_menu = $id");
    header('Location: data_master.php');
    exit;
  }
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
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: #f8f9fa;
    }
    
    .main {
      padding: 24px;
      max-width: 1400px;
    }
    
    .card {
      background: white;
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
      margin-bottom: 24px;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
      box-shadow: 0 4px 12px rgba(255, 107, 53, 0.15);
    }
    
    .card h3 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 24px;
      color: #ff6b35;
      padding-bottom: 12px;
      border-bottom: 2px solid #ffe8e0;
    }
    
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #4a4a4a;
      font-size: 14px;
    }
    
    input[type="text"], 
    input[type="number"], 
    input[name="nama_kategori"],
    input[name="nama_menu"],
    input[name="harga"],
    input[name="stok"],
    select, 
    textarea {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e8e8e8;
      border-radius: 12px;
      font-size: 14px;
      transition: all 0.3s ease;
      background: #fafafa;
      font-family: inherit;
      margin-bottom: 16px;
    }
    
    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: #ff6b35;
      background: white;
      box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
      transform: translateY(-1px);
    }
    
    input[type="file"] {
      padding: 12px;
      border: 2px dashed #ffd4c0;
      border-radius: 12px;
      width: 100%;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s;
      background: #fafafa;
      margin-bottom: 16px;
    }
    
    input[type="file"]:hover {
      border-color: #ff6b35;
      background: #fff5f0;
    }
    
    textarea {
      min-height: 100px;
      resize: vertical;
    }
    
    .btn {
      padding: 12px 28px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-size: 15px;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-block;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .btn.primary {
      background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
      color: white;
      box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }
    
    .btn.primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
      background: linear-gradient(135deg, #f7931e 0%, #ff8c42 100%);
    }
    
    .btn.primary:active {
      transform: translateY(0);
    }
    
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      font-size: 14px;
      margin-top: 8px;
    }
    
    thead {
      background: linear-gradient(135deg, #fff5f0 0%, #ffe8e0 100%);
    }
    
    th {
      padding: 14px 18px;
      text-align: left;
      font-weight: 600;
      color: #ff6b35;
      border-bottom: 2px solid #ffd4c0;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }
    
    th:first-child {
      border-top-left-radius: 10px;
    }
    
    th:last-child {
      border-top-right-radius: 10px;
    }
    
    td {
      padding: 16px 18px;
      border-bottom: 1px solid #f5f5f5;
      color: #555;
    }
    
    tbody tr {
      transition: all 0.2s ease;
      background: white;
    }
    
    tbody tr:hover {
      background: #fff5f0;
      transform: scale(1.002);
      box-shadow: 0 2px 8px rgba(255, 107, 53, 0.08);
    }
    
    tbody tr:last-child td {
      border-bottom: none;
    }
    
    tbody tr:last-child td:first-child {
      border-bottom-left-radius: 10px;
    }
    
    tbody tr:last-child td:last-child {
      border-bottom-right-radius: 10px;
    }
    
    .status-badge {
      display: inline-block;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .status-tersedia {
      background: #e8f5e9;
      color: #2e7d32;
    }
    
    .status-habis {
      background: #ffebee;
      color: #c62828;
    }
    
    .table-wrapper {
      overflow-x: auto;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    
    .empty-state {
      text-align: center;
      padding: 48px 20px;
      color: #999;
      font-size: 15px;
    }
    
    .form-section {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 16px;
    }
    
    .form-section.full {
      grid-template-columns: 1fr;
    }
    
    .brand-logo {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
    }
    
    @media (max-width: 768px) {
      .form-section {
        grid-template-columns: 1fr;
      }
      
      .card {
        padding: 20px;
      }
      
      table {
        font-size: 12px;
      }
      
      th, td {
        padding: 10px 12px;
      }
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <img src="images/logo.jpg" alt="Logo Restoran Laut Nusantara" class="brand-logo">
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
    <aside class="sidebar">
      <div class="menu">
        <a href="index.php">Home</a>
        <a class="active" href="data_master.php">Data Master</a>
        <a href="transaksi.php">Transaksi</a>
        <a href="laporan.php">Laporan</a>
      </div>
    </aside>
    <main class="main">
      <?php if (!empty($_SESSION['flash_errors']) || !empty($_SESSION['flash_success'])): ?>
        <div style="margin-bottom:16px;">
          <?php if (!empty($_SESSION['flash_errors'])): ?>
            <div style="background:#ffe6e6;border:1px solid #ffb3b3;padding:12px;border-radius:8px;color:#8a1f1f;margin-bottom:8px;">
              <strong>Terjadi kesalahan:</strong>
              <ul style="margin-top:8px;padding-left:18px;">
                <?php foreach($_SESSION['flash_errors'] as $err): ?>
                  <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php unset($_SESSION['flash_errors']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['flash_success'])): ?>
            <div style="background:#e6ffea;border:1px solid #b7f0c9;padding:12px;border-radius:8px;color:#116622;">
              <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <div class="card">
        <h3>📦 Tambah Kategori</h3>
        <form method="POST">
          <input type="hidden" name="action" value="add_kategori">
          <label>Nama Kategori</label>
          <input name="nama_kategori" placeholder="Contoh: Makanan Laut" required>
          
          <label>Deskripsi</label>
          <textarea name="deskripsi_kategori" placeholder="Deskripsi kategori..."></textarea>
          
          <button class="btn primary" type="submit">Tambah Kategori</button>
        </form>
      </div>

      <div class="card">
        <h3>🍽️ Tambah Menu</h3>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="add_menu">
          
          <div class="form-section">
            <div>
              <label>Kategori</label>
              <select name="id_kategori" required>
                <option value="0">-- Pilih Kategori --</option>
                <?php foreach($kats as $c): ?>
                  <option value="<?= $c['id_kategori'] ?>"><?= e($c['nama_kategori']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label>Nama Menu</label>
              <input name="nama_menu" placeholder="Contoh: Udang Bakar" required>
            </div>
          </div>
          
          <div class="form-section full">
            <div>
              <label>Deskripsi</label>
              <textarea name="deskripsi_menu" placeholder="Deskripsi menu..."></textarea>
            </div>
          </div>
          
          <div class="form-section">
            <div>
              <label>Harga (Rp)</label>
              <input name="harga" type="number" step="0.01" value="0" min="0" placeholder="0">
            </div>
            <div>
              <label>Stok</label>
              <input name="stok" type="number" value="0" min="0" placeholder="0">
            </div>
          </div>
          
          <div class="form-section">
            <div>
              <label>Upload Foto</label>
              <input id="foto" type="file" name="foto" accept="image/*">
              <div style="margin-top:8px">
                <img id="foto_preview" alt="Preview gambar" style="max-width:220px; max-height:160px; border-radius:8px; display:none; object-fit:cover; box-shadow:0 2px 8px rgba(0,0,0,0.1)">
              </div>
            </div>
            <div>
              <label>Status</label>
              <select name="status">
                <option value="tersedia">Tersedia</option>
                <option value="habis">Habis</option>
              </select>
            </div>
          </div>
          
          <button class="btn primary" type="submit">Tambah Menu</button>
        </form>
      </div>

      <div class="card">
        <h3>📋 Daftar Kategori</h3>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nama Kategori</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($kats)): ?>
                <tr><td colspan="3" class="empty-state">Belum ada kategori</td></tr>
              <?php else: ?>
                <?php foreach($kats as $c): ?>
                  <tr>
                    <td><strong>#<?= $c['id_kategori'] ?></strong></td>
                    <td><strong><?= e($c['nama_kategori']) ?></strong></td>
                    <td><?= e($c['deskripsi']) ?: '-' ?></td>
                    <td>
                      <form method="POST" onsubmit="return confirm('Hapus kategori ini? Semua menu di kategori ini juga akan dihapus!');" style="display:inline">
                        <input type="hidden" name="action" value="delete_kategori">
                        <input type="hidden" name="id_kategori" value="<?= $c['id_kategori'] ?>">
                        <button type="submit" class="btn danger" style="padding:4px 10px;font-size:13px">Hapus</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <h3>🍴 Daftar Menu</h3>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nama Menu</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($menus)): ?>
                <tr><td colspan="6" class="empty-state">Belum ada menu</td></tr>
              <?php else: ?>
                <?php foreach($menus as $m): ?>
                  <tr>
                    <td><strong>#<?= $m['id_menu'] ?></strong></td>
                    <td><strong><?= e($m['nama_menu']) ?></strong></td>
                    <td><?= e($m['nama_kategori'] ?? '-') ?></td>
                    <td><strong>Rp <?= number_format($m['harga'], 0, ',', '.') ?></strong></td>
                    <td><?= (int)$m['stok'] ?></td>
                    <td>
                      <span class="status-badge status-<?= e($m['status']) ?>">
                        <?= ucfirst(e($m['status'])) ?>
                      </span>
                    </td>
                    <td>
                      <form method="POST" onsubmit="return confirm('Hapus menu ini?');" style="display:inline">
                        <input type="hidden" name="action" value="delete_menu">
                        <input type="hidden" name="id_menu" value="<?= $m['id_menu'] ?>">
                        <button type="submit" class="btn danger" style="padding:4px 10px;font-size:13px">Hapus</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</body>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const input = document.getElementById('foto');
  const img = document.getElementById('foto_preview');
  if (!input || !img) return;
  input.addEventListener('change', function(){
    const file = input.files && input.files[0];
    if (!file) {
      img.style.display = 'none';
      img.src = '';
      return;
    }
    if (!file.type.startsWith('image/')) {
      img.style.display = 'none';
      img.src = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = function(ev){
      img.src = ev.target.result;
      img.style.display = 'block';
    };
    reader.readAsDataURL(file);
  });
});
</script>
</html>