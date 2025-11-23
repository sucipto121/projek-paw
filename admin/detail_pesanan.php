<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php?err=' . urlencode('Silakan login terlebih dahulu'));
    exit;
}

require 'koneksi.php';

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pesanan <= 0) {
    die("ID Pesanan tidak valid.");
}

// Ambil data pesanan
$q = "
    SELECT p.*, b.nama AS nama_pembeli
    FROM pesanan p
    LEFT JOIN pembeli b ON b.id_pembeli = p.id_pembeli
    WHERE p.id_pesanan = ?
    LIMIT 1
";
$stmt = $mysqli->prepare($q);
if (!$stmt) die("Prepare failed: " . $mysqli->error);
$stmt->bind_param('i', $id_pesanan);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

// Ambil detail pesanan
$q_detail = "
    SELECT d.*, m.nama_menu, m.foto
    FROM detail_pesanan d
    LEFT JOIN menu m ON m.id_menu = d.id_menu
    WHERE d.id_pesanan = ?
    ORDER BY d.id_menu ASC
";
$stmt_d = $mysqli->prepare($q_detail);
if (!$stmt_d) die("Prepare detail failed: " . $mysqli->error);
$stmt_d->bind_param('i', $id_pesanan);
$stmt_d->execute();
$res_d = $stmt_d->get_result();
$details = [];
while ($row = $res_d->fetch_assoc()) {
    $details[] = $row;
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Detail Pesanan #<?= e($order['kode_pesanan']) ?> — Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .detail-card { background:#fff; padding:24px; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.1); max-width:800px; margin:0 auto; }
        .header-info { display:flex; justify-content:space-between; margin-bottom:24px; border-bottom:1px solid #eee; padding-bottom:16px; }
        .info-group h4 { margin:0 0 4px; color:#666; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; }
        .info-group div { font-size:16px; font-weight:600; color:#333; }
        table.items { width:100%; border-collapse:collapse; margin-bottom:24px; }
        table.items th { text-align:left; border-bottom:2px solid #eee; padding:12px 8px; color:#666; font-size:13px; }
        table.items td { border-bottom:1px solid #eee; padding:12px 8px; vertical-align:middle; }
        .total-row td { border-top:2px solid #eee; font-weight:bold; font-size:16px; }
        .btn-back { display:inline-block; margin-bottom:16px; text-decoration:none; color:#666; font-size:14px; }
        .btn-back:hover { color:#333; }
        .status-badge { display:inline-block; padding:4px 12px; border-radius:16px; font-size:12px; font-weight:bold; text-transform:uppercase; color:#fff; background:#999; }
        .status-pending { background:#f39c12; }
        .status-diproses { background:#3498db; }
        .status-dimasak { background:#9b59b6; }
        .status-selesai { background:#2ecc71; }
        .status-batal { background:#e74c3c; }
        
        .payment-section { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-top: 24px; border: 1px solid #eee; }
        .payment-form { display: flex; gap: 10px; align-items: flex-end; }
        .form-group { display: flex; flex-direction: column; gap: 4px; flex: 1; }
        .form-group label { font-size: 12px; font-weight: bold; color: #555; }
        .form-group input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .alert { padding: 10px; background: #fee; color: #c00; border-radius: 4px; margin-bottom: 16px; }
        .success-msg { padding: 10px; background: #eef; color: #009; border-radius: 4px; margin-bottom: 16px; }
        .paid-stamp { color: #2ecc71; font-weight: bold; font-size: 18px; border: 2px solid #2ecc71; padding: 8px 16px; border-radius: 4px; display: inline-block; transform: rotate(-5deg); }
    </style>
</head>
<body>
<header class="topbar">
    <div class="brand">
        <div class="logo">R</div>
        <h1>Detail Pesanan</h1>
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
        <a href="transaksi.php" class="btn-back">← Kembali ke Daftar Transaksi</a>

        <?php if (isset($error)): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'pembayaran_sukses'): ?>
            <div class="success-msg">Pembayaran berhasil disimpan!</div>
        <?php endif; ?>

        <div class="detail-card">
            <div class="header-info">
                <div class="info-group">
                    <h4>Kode Pesanan</h4>
                    <div><?= e($order['kode_pesanan']) ?></div>
                </div>
                <div class="info-group">
                    <h4>Pelanggan</h4>
                    <div><?= e($order['nama_pembeli'] ?: 'Umum') ?></div>
                </div>
                <div class="info-group">
                    <h4>Tanggal</h4>
                    <div><?= date('d M Y H:i', strtotime($order['tanggal_pesanan'])) ?></div>
                </div>
                <div class="info-group">
                    <h4>Status</h4>
                    <div>
                        <span class="status-badge status-<?= e($order['status_pesanan']) ?>">
                            <?= e($order['status_pesanan']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <table class="items">
                <thead>
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Menu</th>
                        <th style="text-align:center;width:80px">Qty</th>
                        <th style="text-align:right;width:120px">Harga Satuan</th>
                        <th style="text-align:right;width:120px">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($details)): ?>
                        <tr><td colspan="5" style="text-align:center;padding:24px;color:#999">Tidak ada item detail.</td></tr>
                    <?php else: ?>
                        <?php foreach ($details as $i => $d): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td>
                                <strong><?= e($d['nama_menu'] ?: 'Item #'.$d['id_menu']) ?></strong>
                            </td>
                            <td style="text-align:center"><?= (int)$d['jumlah'] ?></td>
                            <td style="text-align:right">Rp <?= number_format($d['harga_satuan'], 0, ',', '.') ?></td>
                            <td style="text-align:right">Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4" style="text-align:right">Total Tagihan</td>
                        <td style="text-align:right">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:right;border:none;padding-top:4px;color:#666">Bayar</td>
                        <td style="text-align:right;border:none;padding-top:4px;color:#666">Rp <?= number_format($order['bayar'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:right;border:none;padding-top:4px;color:#666">Kembalian</td>
                        <td style="text-align:right;border:none;padding-top:4px;color:#666">Rp <?= number_format($order['kembalian'], 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Bagian Pembayaran (Kasir) -->
            <div class="payment-section">
                <?php if ($order['status_pesanan'] === 'selesai' || $order['bayar'] >= $order['total_harga']): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div>
                            <h3 style="margin:0 0 8px">Pembayaran Selesai</h3>
                            <p style="margin:0;color:#666">
                                Pesanan ini sudah lunas.<br>
                                <small>Dibayar: Rp <?= number_format($order['bayar'], 0, ',', '.') ?> — Kembali: Rp <?= number_format($order['kembalian'], 0, ',', '.') ?></small>
                            </p>
                        </div>
                        <div class="paid-stamp">LUNAS</div>
                    </div>
                <?php else: ?>
                    <div style="text-align:center; padding: 20px;">
                        <h3 style="margin-top:0">Menunggu Pembayaran</h3>
                        <p>Pesanan ini belum dibayar. Silakan lakukan pembayaran di kasir.</p>
                        <a href="pembayaran.php?id=<?= $id_pesanan ?>" class="btn primary" style="font-size:16px; padding:12px 24px;">
                            Bayar Sekarang (Rp <?= number_format($order['total_harga'], 0, ',', '.') ?>)
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div style="text-align:right; margin-top:24px;">
                <button onclick="window.print()" class="btn ghost">Cetak Nota / Detail</button>
            </div>
        </div>
    </main>
</div>

</body>
</html>
