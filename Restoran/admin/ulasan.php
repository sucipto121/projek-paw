<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php?err=' . urlencode('Silakan login terlebih dahulu'));
    exit;
}
require 'koneksi.php';

// handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id_ulasan'])) {
    $id = (int)$_POST['id_ulasan'];
    $stmt = $mysqli->prepare('DELETE FROM ulasan WHERE id_ulasan = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    header('Location: ulasan.php');
    exit;
}

// export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $outName = 'ulasan_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $outName . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Pembeli','Menu','Rating','Komentar','Tanggal']);
    $q = $mysqli->prepare('SELECT u.id_ulasan, p.nama AS pembeli, m.nama_menu, u.rating, u.komentar, u.tanggal FROM ulasan u LEFT JOIN pembeli p ON u.id_pembeli = p.id_pembeli LEFT JOIN menu m ON u.id_menu = m.id_menu ORDER BY u.tanggal DESC');
    if ($q) {
        $q->execute();
        $r = $q->get_result();
        while ($row = $r->fetch_assoc()) {
            fputcsv($out, [$row['id_ulasan'], $row['pembeli'], $row['nama_menu'], $row['rating'], $row['komentar'], $row['tanggal']]);
        }
    }
    fclose($out);
    exit;
}

// fetch reviews
$reviews = [];
$q = $mysqli->prepare('SELECT u.id_ulasan, p.nama AS pembeli, COALESCE(m.nama_menu, "-") AS menu, u.rating, u.komentar, u.tanggal FROM ulasan u LEFT JOIN pembeli p ON u.id_pembeli = p.id_pembeli LEFT JOIN menu m ON u.id_menu = m.id_menu ORDER BY u.tanggal DESC');
if ($q) {
    $q->execute();
    $res = $q->get_result();
    while ($row = $res->fetch_assoc()) $reviews[] = $row;
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Ulasan — Admin</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>table{width:100%;border-collapse:collapse}th,td{padding:8px;border-bottom:1px solid #eee;text-align:left} .small{font-size:13px;color:#666}</style>
</head>
<body>
<header class="topbar"><div class="brand"><div class="logo">R</div><h1>Ulasan Pelanggan</h1></div></header>
<div class="layout" style="padding:18px">
  <div style="margin-bottom:12px"><a class="btn" href="data_master.php">Kembali</a> <a class="btn primary" href="ulasan.php?export=csv">Export CSV</a></div>
  <div class="card">
    <h3>Daftar Ulasan (<?php echo count($reviews) ?>)</h3>
    <table>
      <thead>
        <tr><th>ID</th><th>Pembeli</th><th>Menu</th><th>Rating</th><th>Komentar</th><th>Tanggal</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php foreach($reviews as $r): ?>
        <tr>
          <td><?php echo e($r['id_ulasan']) ?></td>
          <td><?php echo e($r['pembeli'] ?? '-') ?></td>
          <td><?php echo e($r['menu'] ?? '-') ?></td>
          <td><?php echo e($r['rating']) ?></td>
          <td><div class="small"><?php echo e($r['komentar']) ?></div></td>
          <td><?php echo e($r['tanggal']) ?></td>
          <td>
            <form method="post" onsubmit="return confirm('Hapus ulasan ini?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id_ulasan" value="<?php echo e($r['id_ulasan']) ?>">
              <button class="btn ghost" type="submit">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
