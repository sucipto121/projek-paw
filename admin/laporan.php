<?php
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php?err=' . urlencode('Silakan login terlebih dahulu'));
  exit;
}

require 'koneksi.php';

// parse date filter
$start = isset($_GET['start']) ? trim($_GET['start']) : '';
$end = isset($_GET['end']) ? trim($_GET['end']) : '';

function parseDate($s) {
  $t = strtotime($s);
  return $t === false ? null : $t;
}

$sTs = parseDate($start);
$eTs = parseDate($end);
if ($sTs && !$eTs) { $eTs = time(); }

// build SQL date filters
$where = [];
$params = [];
$types = '';
if ($start !== '') {
  $where[] = "p.tanggal_pesanan >= ?";
  $params[] = date('Y-m-d 00:00:00', strtotime($start));
  $types .= 's';
}
if ($end !== '') {
  $where[] = "p.tanggal_pesanan <= ?";
  $params[] = date('Y-m-d 23:59:59', strtotime($end));
  $types .= 's';
}

$orders = [];
$sql = "SELECT p.*, pb.nama AS pembeli_nama FROM pesanan p LEFT JOIN pembeli pb ON p.id_pembeli = pb.id_pembeli";
if (!empty($where)) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY p.tanggal_pesanan DESC';

$stmt = $mysqli->prepare($sql);
if ($stmt) {
  if (!empty($params)) { $stmt->bind_param($types, ...$params); }
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $orders[] = $row;
  }
  $stmt->close();
}

// fetch detail items for listed orders
$orderDetails = [];
if (!empty($orders)) {
  $ids = array_map(function($r){ return (int)$r['id_pesanan']; }, $orders);
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $types2 = str_repeat('i', count($ids));
  $sql2 = "SELECT dp.id_pesanan, dp.jumlah, dp.harga_satuan, dp.subtotal, m.nama_menu FROM detail_pesanan dp LEFT JOIN menu m ON dp.id_menu = m.id_menu WHERE dp.id_pesanan IN ($placeholders)";
  $stmt2 = $mysqli->prepare($sql2);
  if ($stmt2) {
    // bind dynamic ints
    $bind_names = [];
    $bind_names[] = $types2;
    foreach ($ids as $k => $id) $bind_names[] = &$ids[$k];
    call_user_func_array([$stmt2, 'bind_param'], $bind_names);
    $stmt2->execute();
    $r2 = $stmt2->get_result();
    while ($d = $r2->fetch_assoc()) {
      $orderDetails[$d['id_pesanan']][] = $d;
    }
    $stmt2->close();
  } else {
    // fallback if prepare fails
    $idList = implode(',', $ids);
    $r2 = $mysqli->query("SELECT dp.id_pesanan, dp.jumlah, dp.harga_satuan, dp.subtotal, m.nama_menu FROM detail_pesanan dp LEFT JOIN menu m ON dp.id_menu = m.id_menu WHERE dp.id_pesanan IN ($idList)");
    if ($r2) {
      while ($d = $r2->fetch_assoc()) {
        $orderDetails[$d['id_pesanan']][] = $d;
      }
      $r2->close();
    }
  }
}

// compute summary from DB data
$totalOrders = count($orders);
$totalRevenue = 0.0;
$itemsCount = [];
$perDay = []; // aggregate by date (Y-m-d)
foreach ($orders as $o) {
  $totalRevenue += (float)$o['total_harga'];
  $details = $orderDetails[$o['id_pesanan']] ?? [];
  foreach ($details as $it) {
    $title = $it['nama_menu'] ?: 'Unknown Item';
    if (!isset($itemsCount[$title])) $itemsCount[$title] = 0;
    $itemsCount[$title] += (int)$it['jumlah'];
  }

  // aggregate per-day using date portion of tanggal_pesanan
  $ts = strtotime($o['tanggal_pesanan']);
  if ($ts === false) continue;
  $day = date('Y-m-d', $ts);
  if (!isset($perDay[$day])) {
    $perDay[$day] = ['total_transaksi' => 0, 'pendapatan' => 0.0];
  }
  $perDay[$day]['total_transaksi'] += 1;
  $perDay[$day]['pendapatan'] += (float)$o['total_harga'];
}

// === UPSERT per-hari ke tabel laporan_harian ===
// Approach: untuk setiap tanggal yang ada di $perDay
//  - jika ada baris WHERE DATE(tanggal)=? -> UPDATE
//  - jika tidak -> INSERT
foreach ($perDay as $day => $agg) {
  // Try to update first using prepared statement on tanggal column
  // We'll assume laporan_harian.tanggal stores DATE or DATETIME; compare by DATE(tanggal)=?
  // First check if exists
  $check = $mysqli->prepare("SELECT id_laporan FROM laporan_harian WHERE DATE(tanggal) = ?");
  if ($check) {
    $check->bind_param('s', $day);
    $check->execute();
    $res = $check->get_result();
    if ($res && $res->num_rows > 0) {
      // update
      $row = $res->fetch_assoc();
      $idlap = $row['id_laporan'];
      $upd = $mysqli->prepare("UPDATE laporan_harian SET total_transaksi = ?, pendapatan = ? WHERE id_laporan = ?");
      if ($upd) {
        $tt = (int)$agg['total_transaksi'];
        $pd = (float)$agg['pendapatan'];
        $upd->bind_param('idi', $tt, $pd, $idlap);
        $upd->execute();
        $upd->close();
      }
    } else {
      // insert
      $ins = $mysqli->prepare("INSERT INTO laporan_harian (tanggal, total_transaksi, pendapatan) VALUES (?, ?, ?)");
      if ($ins) {
        // use date as 'Y-m-d' (00:00:00) to store
        $dateValue = $day . ' 00:00:00';
        $tt = (int)$agg['total_transaksi'];
        $pd = (float)$agg['pendapatan'];
        $ins->bind_param('sid', $dateValue, $tt, $pd);
        $ins->execute();
        $ins->close();
      }
    }
    $check->close();
  } else {
    // fallback: naive upsert using SELECT then INSERT/UPDATE without prepare (worst case)
    $daySql = $mysqli->real_escape_string($day);
    $q = "SELECT id_laporan FROM laporan_harian WHERE DATE(tanggal) = '$daySql' LIMIT 1";
    $r = $mysqli->query($q);
    if ($r && $r->num_rows > 0) {
      $row = $r->fetch_assoc();
      $idlap = $row['id_laporan'];
      $tt = (int)$agg['total_transaksi'];
      $pd = (float)$agg['pendapatan'];
      $mysqli->query("UPDATE laporan_harian SET total_transaksi = $tt, pendapatan = $pd WHERE id_laporan = $idlap");
    } else {
      $dateValue = $mysqli->real_escape_string($day . ' 00:00:00');
      $tt = (int)$agg['total_transaksi'];
      $pd = (float)$agg['pendapatan'];
      $mysqli->query("INSERT INTO laporan_harian (tanggal, total_transaksi, pendapatan) VALUES ('$dateValue',$tt,$pd)");
    }
  }
}

// export CSV if requested
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  $fname = 'laporan_' . date('Ymd_His') . '.csv';
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="' . $fname . '"');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['No','Kode','Pelanggan','Items','Status','Waktu','Total']);
  $i = 1;
  foreach ($orders as $o) {
    $items = [];
    $sum = (float)$o['total_harga'];
    $details = $orderDetails[$o['id_pesanan']] ?? [];
    foreach ($details as $it) {
      $items[] = ($it['nama_menu'] ?: 'Unknown') . ' x' . (int)$it['jumlah'];
    }
    $time = $o['tanggal_pesanan'];
    $pembeli = $o['pembeli_nama'] ?? '';
    fputcsv($out, [$i++, $o['kode_pesanan'] ?? '', $pembeli, implode('; ', $items), $o['status_pesanan'] ?? '', $time, $sum]);
  }
  fclose($out);
  exit;
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Laporan — Admin</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>table{width:100%;border-collapse:collapse}th,td{padding:8px;border-bottom:1px solid #eee;text-align:left}tfoot td{font-weight:700}</style>
  </head>
<body>
  <?php /* layout header included inline in this file; no external include needed */ ?>
  <header class="topbar">
    <div class="brand"><div class="logo">R</div><h1>Restoran Admin</h1></div>
    <nav></nav>
    <div class="user"><div class="avatar"><?= strtoupper(substr($_SESSION['user']['nama'],0,1)) ?></div></div>
  </header>

  <div class="layout">
    <aside class="sidebar">
      <div class="menu">
        <a href="index.php">Home</a>
        <a href="data_master.php">Data Master</a>
        <a href="transaksi.php">Transaksi</a>
        <a class="active" href="laporan.php">Laporan</a>
      </div>
    </aside>

    <main class="main">
      <div class="welcome">
        <h2>Laporan Pesanan</h2>
        <div>
          <a class="btn ghost" href="laporan.php">Refresh</a>
          <a class="btn primary" href="laporan.php?export=csv&start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>">Export CSV</a>
          <button class="btn ghost" onclick="window.print();">Print</button>
        </div>
      </div>

      <div class="card">
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <label>Mulai: <input type="date" name="start" value="<?= e($start) ?>"></label>
          <label>Sampai: <input type="date" name="end" value="<?= e($end) ?>"></label>
          <button class="btn primary" type="submit">Filter</button>
        </form>
      </div>

      <div class="cards">
        <div class="card">
          <h3>Total Pesanan</h3>
          <p><?= $totalOrders ?> pesanan</p>
        </div>
        <div class="card">
          <h3>Total Pendapatan</h3>
          <p>Rp <?= number_format($totalRevenue,0,',','.') ?></p>
        </div>
        <div class="card">
          <h3>Item Terlaris</h3>
          <p>
          <?php if (empty($itemsCount)): ?>Tidak ada pesanan<?php else: ?>
            <ul>
            <?php foreach ($itemsCount as $it => $q): ?>
              <li><?= e($it) ?> — <?= $q ?> pcs</li>
            <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          </p>
        </div>
      </div>

      <div class="card" style="margin-top:16px">
        <h3>Daftar Pesanan</h3>
        <table>
          <thead><tr><th>No</th><th>Pelanggan</th><th>Items</th><th>Status</th><th>Waktu</th><th>Total</th></tr></thead>
          <tbody>
          <?php $i=1; foreach($orders as $o):
              $sum = (float)($o['total_harga'] ?? 0);
              $details = $orderDetails[$o['id_pesanan']] ?? [];
              $items = [];
              foreach ($details as $it) {
                  $items[] = e($it['nama_menu'] ?: 'Unknown') . ' x' . (int)$it['jumlah'];
              }

              $meja = '';
              $pname = $o['pembeli_nama'] ?? '';
              if (stripos($pname, 'Meja ') === 0) {
                  $meja = trim(substr($pname, 5));
              }
          ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= e($pname ?: '-') ?></td>
              <td><?= implode('<br>', $items) ?></td>
              <td><?= e($o['status_pesanan'] ?? '') ?></td>
              <td><?= e($o['tanggal_pesanan'] ?? '') ?></td>
              <td>Rp <?= number_format($sum,0,',','.') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot><tr><td colspan="5">Total</td><td>Rp <?= number_format($totalRevenue,0,',','.') ?></td></tr></tfoot>
        </table>
      </div>

    </main>
  </div>

</body>
</html>
