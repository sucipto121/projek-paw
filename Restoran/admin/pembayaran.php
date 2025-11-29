<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require 'koneksi.php';

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if ($id_pesanan <= 0) {
    die("ID Pesanan tidak valid.");
}

// Ambil data pesanan
$stmt = $mysqli->prepare("SELECT * FROM pesanan WHERE id_pesanan = ?");
$stmt->bind_param('i', $id_pesanan);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

if ($order['status_pesanan'] === 'selesai') {
    // Jika sudah selesai, redirect ke detail
    header("Location: detail_pesanan.php?id=$id_pesanan");
    exit;
}

// Handle Post Pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bayar = isset($_POST['bayar']) ? (float)$_POST['bayar'] : 0;
    $metode = isset($_POST['metode']) ? $_POST['metode'] : 'tunai';
    $total = (float)$order['total_harga'];

    if ($bayar < $total) {
        $error = "Uang pembayaran kurang! Total tagihan: Rp " . number_format($total, 0, ',', '.');
    } else {
        $kembalian = $bayar - $total;
        
        // 1. Simpan ke tabel pembayaran
        // Cek apakah tabel pembayaran ada (berdasarkan request user baru dibuat)
        // Kita asumsikan tabel 'pembayaran' sudah ada sesuai request user.
        
        $mysqli->begin_transaction();
        
        try {
            $stmt_pay = $mysqli->prepare("INSERT INTO pembayaran (id_pesanan, metode_bayar, total_bayar, status_bayar) VALUES (?, ?, ?, 'berhasil')");
            if (!$stmt_pay) throw new Exception("Gagal prepare pembayaran: " . $mysqli->error);
            
            $stmt_pay->bind_param('isd', $id_pesanan, $metode, $bayar);
            if (!$stmt_pay->execute()) throw new Exception("Gagal insert pembayaran: " . $stmt_pay->error);
            $stmt_pay->close();

            // 2. Update status pesanan di tabel pesanan
            $status_order = 'selesai';
            $stmt_order = $mysqli->prepare("UPDATE pesanan SET status_pesanan = ?, bayar = ?, kembalian = ? WHERE id_pesanan = ?");
            if (!$stmt_order) throw new Exception("Gagal prepare update pesanan: " . $mysqli->error);
            
            $stmt_order->bind_param('sddi', $status_order, $bayar, $kembalian, $id_pesanan);
            if (!$stmt_order->execute()) throw new Exception("Gagal update pesanan: " . $stmt_order->error);
            $stmt_order->close();

            $mysqli->commit();
            
            // Redirect ke detail pesanan dengan pesan sukses
            header("Location: detail_pesanan.php?id=$id_pesanan&msg=paid");
            exit;

        } catch (Exception $ex) {
            $mysqli->rollback();
            $error = "Terjadi kesalahan: " . $ex->getMessage();
        }
    }
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pembayaran Pesanan #<?= e($order['kode_pesanan']) ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .pay-card { max-width: 500px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .total-display { font-size: 32px; font-weight: bold; color: #2c3e50; text-align: center; margin: 20px 0; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; box-sizing: border-box; }
        .btn-block { width: 100%; display: block; padding: 14px; font-size: 18px; }
        .kembalian-display { text-align: center; font-size: 18px; color: #27ae60; margin-top: 10px; font-weight: bold; display: none; }
        .alert-error { background: #fee; color: #c00; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="pay-card">
    <h2 style="text-align:center;margin-top:0">Pembayaran Kasir</h2>
    <p style="text-align:center;color:#666">Kode: <?= e($order['kode_pesanan']) ?></p>
    
    <div class="total-display">
        Rp <?= number_format($order['total_harga'], 0, ',', '.') ?>
    </div>

    <?php if ($error): ?>
        <div class="alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="payForm">
        <div class="form-group">
            <label>Metode Pembayaran</label>
            <input type="text" value="Tunai" readonly style="background:#eee; color:#555">
            <input type="hidden" name="metode" value="tunai">
        </div>

        <div class="form-group">
            <label>Jumlah Uang Diterima (Rp)</label>
            <input type="number" name="bayar" id="bayarInput" min="<?= $order['total_harga'] ?>" required placeholder="0">
        </div>

        <div id="kembalianInfo" class="kembalian-display">
            Kembalian: Rp <span id="kembalianVal">0</span>
        </div>

        <div style="display:flex; gap:10px; margin-top:30px;">
            <a href="detail_pesanan.php?id=<?= $id_pesanan ?>" class="btn ghost" style="flex:1;text-align:center">Batal</a>
            <button type="submit" class="btn primary btn-block" style="flex:2">Proses Bayar</button>
        </div>
    </form>
</div>

<script>
    const total = <?= (float)$order['total_harga'] ?>;
    const input = document.getElementById('bayarInput');
    const kembalianInfo = document.getElementById('kembalianInfo');
    const kembalianVal = document.getElementById('kembalianVal');

    input.addEventListener('input', function() {
        const val = parseFloat(this.value) || 0;
        if (val >= total) {
            const diff = val - total;
            kembalianVal.innerText = diff.toLocaleString('id-ID');
            kembalianInfo.style.display = 'block';
        } else {
            kembalianInfo.style.display = 'none';
        }
    });
</script>

</body>
</html>
