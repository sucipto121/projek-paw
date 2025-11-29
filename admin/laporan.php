<?php
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php?err=' . urlencode('Silakan login terlebih dahulu'));
  exit;
}

require 'koneksi.php';
$user = $_SESSION['user'];

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
$totalOrders = 0;
$totalRevenue = 0.0;
$itemsCount = [];
$perDay = [];
foreach ($orders as $o) {
  if ($o['status_pesanan'] === 'batal') {
      continue;
  }

  $totalOrders++;
  $totalRevenue += (float)$o['total_harga'];
  $details = $orderDetails[$o['id_pesanan']] ?? [];
  foreach ($details as $it) {
    $title = $it['nama_menu'] ?: 'Unknown Item';
    if (!isset($itemsCount[$title])) $itemsCount[$title] = 0;
    $itemsCount[$title] += (int)$it['jumlah'];
  }

  $ts = strtotime($o['tanggal_pesanan']);
  if ($ts === false) continue;
  $day = date('Y-m-d', $ts);
  if (!isset($perDay[$day])) {
    $perDay[$day] = ['total_transaksi' => 0, 'pendapatan' => 0.0];
  }
  $perDay[$day]['total_transaksi'] += 1;
  $perDay[$day]['pendapatan'] += (float)$o['total_harga'];
}

// Sort items by count
arsort($itemsCount);

// UPSERT per-hari ke tabel laporan_harian
foreach ($perDay as $day => $agg) {
  $check = $mysqli->prepare("SELECT id_laporan FROM laporan_harian WHERE DATE(tanggal) = ?");
  if ($check) {
    $check->bind_param('s', $day);
    $check->execute();
    $res = $check->get_result();
    if ($res && $res->num_rows > 0) {
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
      $ins = $mysqli->prepare("INSERT INTO laporan_harian (tanggal, total_transaksi, pendapatan) VALUES (?, ?, ?)");
      if ($ins) {
        $dateValue = $day . ' 00:00:00';
        $tt = (int)$agg['total_transaksi'];
        $pd = (float)$agg['pendapatan'];
        $ins->bind_param('sid', $dateValue, $tt, $pd);
        $ins->execute();
        $ins->close();
      }
    }
    $check->close();
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
  <title>Laporan - Restoran Laut Nusantara</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: #f8f9fa;
    }
    
    .main {
      padding: 24px;
      max-width: 1600px;
    }
    
    .page-header {
      background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
      border-radius: 20px;
      padding: 32px 40px;
      margin-bottom: 24px;
      box-shadow: 0 8px 24px rgba(255, 107, 53, 0.3);
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: white;
      position: relative;
      overflow: hidden;
    }
    
    .page-header::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 300px;
      height: 300px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }
    
    .page-header h2 {
      font-size: 28px;
      font-weight: 700;
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .header-actions {
      display: flex;
      gap: 12px;
      position: relative;
      z-index: 1;
    }
    
    .btn {
      padding: 10px 24px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .btn.primary {
      background: white;
      color: #ff6b35;
      box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
    }
    
    .btn.primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 255, 255, 0.4);
    }
    
    .btn.ghost {
      background: rgba(255, 255, 255, 0.1);
      color: white;
      border: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    .btn.ghost:hover {
      background: rgba(255, 255, 255, 0.2);
      border-color: rgba(255, 255, 255, 0.5);
    }
    
    .card {
      background: white;
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      margin-bottom: 24px;
    }
    
    .card h3 {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 16px;
      color: #ff6b35;
    }
    
    .filter-form {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      align-items: flex-end;
    }
    
    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    
    .form-group label {
      font-size: 13px;
      font-weight: 600;
      color: #666;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .form-group input[type="date"] {
      padding: 10px 16px;
      border: 2px solid #e8e8e8;
      border-radius: 10px;
      font-size: 14px;
      transition: all 0.2s;
      background: #fafafa;
      min-width: 180px;
    }
    
    .form-group input[type="date"]:focus {
      outline: none;
      border-color: #ff6b35;
      background: white;
      box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 24px;
      margin-bottom: 24px;
    }
    
    .stat-card {
      background: white;
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      transition: all 0.3s ease;
      border-left: 4px solid transparent;
    }
    
    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(255, 107, 53, 0.15);
    }
    
    .stat-card:nth-child(1) { border-left-color: #ff6b35; }
    .stat-card:nth-child(2) { border-left-color: #2e7d32; }
    .stat-card:nth-child(3) { border-left-color: #1565c0; }
    
    .stat-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
    }
    
    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }
    
    .stat-card:nth-child(1) .stat-icon {
      background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    }
    
    .stat-card:nth-child(2) .stat-icon {
      background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    }
    
    .stat-card:nth-child(3) .stat-icon {
      background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    }
    
    .stat-title {
      font-size: 13px;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
    }
    
    .stat-value {
      font-size: 36px;
      font-weight: 700;
      color: #ff6b35;
      margin-bottom: 8px;
    }
    
    .stat-description {
      font-size: 14px;
      color: #666;
    }
    
    .top-items {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .top-items li {
      padding: 12px 16px;
      background: #fafafa;
      border-radius: 10px;
      margin-bottom: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: all 0.2s;
    }
    
    .top-items li:hover {
      background: #fff5f0;
      transform: translateX(4px);
    }
    
    .item-name {
      font-weight: 600;
      color: #ff6b35;
    }
    
    .item-count {
      background: #ff6b35;
      color: white;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 13px;
      font-weight: 600;
    }
    
    .table-wrapper {
      overflow-x: auto;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      font-size: 14px;
    }
    
    thead {
      background: linear-gradient(135deg, #fff5f0 0%, #ffe8e0 100%);
    }
    
    th {
      padding: 14px 16px;
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
      padding: 16px;
      border-bottom: 1px solid #f5f5f5;
      color: #555;
      vertical-align: top;
    }
    
    tbody tr {
      transition: all 0.2s ease;
      background: white;
    }
    
    tbody tr:hover {
      background: #fff5f0;
      box-shadow: 0 2px 8px rgba(255, 107, 53, 0.08);
    }
    
    tfoot {
      background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
      color: white;
      font-weight: 700;
    }
    
    tfoot td {
      padding: 16px;
      border: none;
      font-size: 16px;
    }
    
    tfoot td:first-child {
      border-bottom-left-radius: 10px;
    }
    
    tfoot td:last-child {
      border-bottom-right-radius: 10px;
    }
    
    .status-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }
    
    .status-pending { background: #fff3e0; color: #e65100; }
    .status-diproses { background: #e3f2fd; color: #1565c0; }
    .status-dimasak { background: #f3e5f5; color: #6a1b9a; }
    .status-selesai { background: #e8f5e9; color: #2e7d32; }
    .status-batal { background: #ffebee; color: #c62828; }
    
    .items-cell {
      font-size: 13px;
      line-height: 1.6;
    }
    
    .no-data {
      text-align: center;
      padding: 60px 20px;
      color: #999;
      font-size: 16px;
    }
    
    .no-data-icon {
      font-size: 64px;
      margin-bottom: 16px;
    }
    
    .brand-logo {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
    }
    
    @media print {
      .header-actions, .filter-form, .btn { display: none; }
      .page-header { background: #ff6b35; }
      body { background: white; }
    }
    
    @media (max-width: 768px) {
      .main { padding: 16px; }
      .page-header {
        flex-direction: column;
        gap: 20px;
        padding: 24px;
      }
      .stats-grid { grid-template-columns: 1fr; }
      .filter-form { flex-direction: column; align-items: stretch; }
      .form-group input[type="date"] { min-width: auto; }
      table { font-size: 12px; }
      th, td { padding: 10px 8px; }
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
        <a href="data_master.php">Data Master</a>
        <a href="transaksi.php">Transaksi</a>
        <a class="active" href="laporan.php">Laporan</a>
      </div>
    </aside>

    <main class="main">
      <div class="page-header">
        <h2>📊 Laporan Pesanan</h2>
        <div class="header-actions">
          <a class="btn ghost" href="laporan.php">Refresh</a>
          <a class="btn primary" href="laporan.php?export=csv&start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>">Export CSV</a>
          <button class="btn ghost" onclick="window.print();">Print</button>
        </div>
      </div>

      <div class="card">
        <h3>🔍 Filter Periode</h3>
        <form method="GET" class="filter-form">
          <div class="form-group">
            <label>Tanggal Mulai</label>
            <input type="date" name="start" value="<?= e($start) ?>">
          </div>
          <div class="form-group">
            <label>Tanggal Akhir</label>
            <input type="date" name="end" value="<?= e($end) ?>">
          </div>
          <button class="btn primary" type="submit">Terapkan Filter</button>
        </form>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon">📦</div>
            <div class="stat-title">Total Pesanan</div>
          </div>
          <div class="stat-value"><?= number_format($totalOrders) ?></div>
          <div class="stat-description">Pesanan berhasil</div>
        </div>
        
        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon">💰</div>
            <div class="stat-title">Total Pendapatan</div>
          </div>
          <div class="stat-value">Rp <?= number_format($totalRevenue,0,',','.') ?></div>
          <div class="stat-description">Revenue dari penjualan</div>
        </div>
        
        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon">🏆</div>
            <div class="stat-title">Item Terlaris</div>
          </div>
          <?php if (empty($itemsCount)): ?>
            <div class="no-data" style="padding:20px 0">Belum ada data</div>
          <?php else: ?>
            <ul class="top-items">
            <?php 
            $topItems = array_slice($itemsCount, 0, 5, true);
            foreach ($topItems as $it => $q): 
            ?>
              <li>
                <span class="item-name"><?= e($it) ?></span>
                <span class="item-count"><?= $q ?> pcs</span>
              </li>
            <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <h3>📋 Detail Pesanan</h3>
        <?php if (empty($orders)): ?>
          <div class="no-data">
            <div class="no-data-icon">📭</div>
            <div>Belum ada pesanan untuk periode ini</div>
          </div>
        <?php else: ?>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th style="width:50px">No</th>
                <th>Pelanggan</th>
                <th>Items</th>
                <th style="text-align:center">Status</th>
                <th style="text-align:center">Waktu</th>
                <th style="text-align:right">Total</th>
              </tr>
            </thead>
            <tbody>
            <?php $i=1; foreach($orders as $o):
                $sum = (float)($o['total_harga'] ?? 0);
                $details = $orderDetails[$o['id_pesanan']] ?? [];
                $items = [];
                foreach ($details as $it) {
                    $items[] = e($it['nama_menu'] ?: 'Unknown') . ' ×' . (int)$it['jumlah'];
                }
                $pname = $o['pembeli_nama'] ?? '-';
                $statusClass = 'status-' . strtolower($o['status_pesanan'] ?? 'pending');
            ?>
              <tr>
                <td style="text-align:center;font-weight:600"><?= $i++ ?></td>
                <td><strong><?= e($pname) ?></strong></td>
                <td class="items-cell"><?= implode('<br>', $items) ?></td>
                <td style="text-align:center">
                  <span class="status-badge <?= $statusClass ?>">
                    <?= ucfirst(e($o['status_pesanan'] ?? '')) ?>
                  </span>
                </td>
                <td style="text-align:center">
                  <div style="font-weight:600"><?= date('d M Y', strtotime($o['tanggal_pesanan'])) ?></div>
                  <div style="font-size:12px;color:#666"><?= date('H:i', strtotime($o['tanggal_pesanan'])) ?> WIB</div>
                </td>
                <td style="text-align:right"><strong>Rp <?= number_format($sum,0,',','.') ?></strong></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="5" style="text-align:right">TOTAL PENDAPATAN</td>
                <td style="text-align:right">Rp <?= number_format($totalRevenue,0,',','.') ?></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <?php endif; ?>
      </div>

    </main>
  </div>

</body>
</html>