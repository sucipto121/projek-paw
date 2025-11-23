<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php?err=' . urlencode('Silakan login terlebih dahulu'));
    exit;
}

require 'koneksi.php'; // pastikan $mysqli (mysqli connection) tersedia dari sini

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/*
 * Update status handler
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id_pesanan = isset($_POST['id_pesanan']) ? (int)$_POST['id_pesanan'] : 0;
    $status = isset($_POST['status']) ? strtolower(trim($_POST['status'])) : '';
    $valid = ['pending','diproses','dimasak','selesai','batal'];

    if ($id_pesanan > 0 && in_array($status, $valid, true)) {
        $stmt = $mysqli->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
        if ($stmt) {
            $stmt->bind_param('si', $status, $id_pesanan);
            $stmt->execute();
        }
    }
    header('Location: transaksi.php');
    exit;
}

/*
 * Ambil semua pesanan dari database
 * Kita akan menampilkan: id_pesanan, kode_pesanan, nama pembeli, metode_order, status_pesanan, total_harga, bayar, kembalian, tanggal_pesanan
 */
$orders = [];
$q = "
    SELECT p.id_pesanan, p.kode_pesanan, p.id_pembeli, p.metode_order, p.status_pesanan,
           p.total_harga, p.bayar, p.kembalian, p.tanggal_pesanan,
           b.nama AS nama_pembeli
    FROM pesanan p
    LEFT JOIN pembeli b ON b.id_pembeli = p.id_pembeli
    ORDER BY p.tanggal_pesanan DESC, p.id_pesanan DESC
";
$res = $mysqli->query($q);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $orders[] = $r;
    }
}

/*
 * Ambil detail untuk semua pesanan yang ada di $orders
 * agar tidak melakukan query per baris, kita kumpulkan id_pesanan lalu fetch semua detail sekaligus
 */
$orderIds = array_map(function($o){ return (int)$o['id_pesanan']; }, $orders);
$detailsByOrder = [];

if (!empty($orderIds)) {
    // buat placeholders untuk IN clause
    $in = implode(',', array_fill(0, count($orderIds), '?'));
    // query mengambil id_pesanan, id_menu, jumlah, harga_satuan, subtotal, dan nama menu
    $sql = "
        SELECT d.id_pesanan, d.id_menu, d.jumlah, d.harga_satuan, d.subtotal, m.nama_menu
        FROM detail_pesanan d
        LEFT JOIN menu m ON m.id_menu = d.id_menu
        WHERE d.id_pesanan IN ($in)
        ORDER BY d.id_pesanan
    ";

    // karena kita tidak tahu berapa banyak parameter, gunakan prepared statement dinamis
    $types = str_repeat('i', count($orderIds)); // semua integer
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        // bind dynamically
        $stmt_params = array_merge([$types], $orderIds);
        // PHP 5.6+ call_user_func_array requires references
        $tmp = [];
        foreach ($stmt_params as $key => $val) $tmp[$key] = &$stmt_params[$key];
        call_user_func_array([$stmt, 'bind_param'], $tmp);
        $stmt->execute();
        $r = $stmt->get_result();
        while ($row = $r->fetch_assoc()) {
            $id = $row['id_pesanan'];
            if (!isset($detailsByOrder[$id])) $detailsByOrder[$id] = [];
            $detailsByOrder[$id][] = $row;
        }
        $stmt->close();
    } else {
        // fallback: query tanpa prepared (untuk environment sederhana) - hanya jika prepare gagal
        $idList = implode(',', $orderIds);
        $r2 = $mysqli->query("
            SELECT d.id_pesanan, d.id_menu, d.jumlah, d.harga_satuan, d.subtotal, m.nama_menu
            FROM detail_pesanan d
            LEFT JOIN menu m ON m.id_menu = d.id_menu
            WHERE d.id_pesanan IN ($idList)
            ORDER BY d.id_pesanan
        ");
        if ($r2) {
            while ($row = $r2->fetch_assoc()) {
                $id = $row['id_pesanan'];
                if (!isset($detailsByOrder[$id])) $detailsByOrder[$id] = [];
                $detailsByOrder[$id][] = $row;
            }
            $r2->close();
        }
    }
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Transaksi — Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        table { width:100%; border-collapse:collapse }
        td,th { padding:8px; border-bottom:1px solid #eee; vertical-align: top; }
        .status-form { display:flex; gap:6px; align-items:center }
        .items-list { font-size: 13px; color: #333; }
        .items-list div { margin-bottom: 4px; }
        .small-muted { font-size:12px; color:#666; }
        .no-orders { padding:16px; text-align:center; color:#666; }
        .col-id { width:50px; text-align:center; }
        .col-center { text-align:center; }
        .col-right { text-align:right; }
    </style>
</head>
<body>
<header class="topbar">
    <div class="brand">
        <div class="logo">R</div>
        <h1>Transaksi</h1>
    </div>

    <div class="user">
        <div class="avatar"><?= e(strtoupper(substr($_SESSION['user']['nama'],0,1))) ?></div>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <div class="menu">
            <a href="index.php">Home</a>
            <a href="transaksi.php" class="active">Transaksi</a>
            <a href="laporan.php">Laporan</a>
        </div>
    </aside>

    <main class="main">
        <div class="card">
            <h3>Daftar Pesanan</h3>

            <?php if (empty($orders)): ?>
                <div class="no-orders">Belum ada pesanan.</div>
            <?php else: ?>

            <table>
                <thead>
                <tr>
                    <th class="col-id">No</th>
                    <th class="col-id">ID</th>
                    <th>Pelanggan</th>
                    <th>Items</th>
                    <th class="col-center">Status</th>
                    <th class="col-center">Waktu</th>
                    <th class="col-center">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $i => $o): 
                    $id = (int)$o['id_pesanan'];
                    $items = $detailsByOrder[$id] ?? [];
                ?>
                    <tr>
                        <td class="col-id"><?= $i+1 ?></td>
                        <td class="col-id"><?= e($o['id_pesanan']) ?></td>
                        <td>
                            <div><strong><?= e($o['nama_pembeli'] ?: '—') ?></strong></div>
                        </td>
                        <td class="items-list">
                            <?php if (empty($items)): ?>
                                <div class="small-muted">Tidak ada item </div>
                            <?php else: ?>
                                <?php foreach ($items as $it): ?>
                                    <div>
                                        <?= e($it['nama_menu'] ?: ('menu#'.$it['id_menu'])) ?>
                                        &nbsp;×&nbsp;<?= (int)$it['jumlah'] ?>
                                        <span class="small-muted"> — <?= number_format((float)$it['harga_satuan'],0,',','.') ?> (subtotal <?= number_format((float)$it['subtotal'],0,',','.') ?>)</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td class="col-center">
                            <div style="margin-bottom:6px;"><strong style="text-transform:capitalize"><?= e($o['status_pesanan']) ?></strong></div>
                            <div class="small-muted">Total: Rp <?= number_format((float)$o['total_harga'],0,',','.') ?></div>
                        </td>
                        <td class="col-center">
                            <?= e($o['tanggal_pesanan']) ?><br>
                            <div class="small-muted">Bayar <?= number_format((float)$o['bayar'],0,',','.') ?> — Kembali <?= number_format((float)$o['kembalian'],0,',','.') ?></div>
                        </td>
                        <td class="col-center">
                            <form class="status-form" method="POST" style="margin:0;justify-content:center">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id_pesanan" value="<?= $id ?>">

                                <select name="status" aria-label="status">
                                    <?php
                                    $options = ['diproses'=>'Diproses','dimasak'=>'Dimasak','selesai'=>'Selesai','batal'=>'Batal'];
                                    foreach ($options as $val => $label):
                                        $sel = ($val === $o['status_pesanan']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= e($val) ?>" <?= $sel ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <button class="btn primary" type="submit">Update</button>
                            </form>
                            <div style="margin-top:8px">
                                <a class="btn ghost" href="detail_pesanan.php?id=<?= $id ?>">Detail</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php endif; ?>

        </div>
    </main>
</div>

</body>
</html>
