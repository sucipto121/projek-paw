<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php?err=' . urlencode('Silakan login terlebih dahulu'));
    exit;
}

require 'koneksi.php';
$user = $_SESSION['user'];

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
 * Ambil detail untuk semua pesanan
 */
$orderIds = array_map(function($o){ return (int)$o['id_pesanan']; }, $orders);
$detailsByOrder = [];

if (!empty($orderIds)) {
    $in = implode(',', array_fill(0, count($orderIds), '?'));
    $sql = "
        SELECT d.id_pesanan, d.id_menu, d.jumlah, d.harga_satuan, d.subtotal, m.nama_menu
        FROM detail_pesanan d
        LEFT JOIN menu m ON m.id_menu = d.id_menu
        WHERE d.id_pesanan IN ($in)
        ORDER BY d.id_pesanan
    ";

    $types = str_repeat('i', count($orderIds));
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt_params = array_merge([$types], $orderIds);
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
    <title>Transaksi - Restoran Laut Nusantara</title>
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
        
        .card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        
        .card h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #ff6b35;
            display: flex;
            align-items: center;
            gap: 8px;
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
        
        tbody tr:last-child td {
            border-bottom: none;
        }
        
        .col-id {
            width: 60px;
            text-align: center;
            font-weight: 600;
            color: #666;
        }
        
        .col-center {
            text-align: center;
        }
        
        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .customer-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }
        
        .customer-name {
            font-weight: 600;
            color: #ff6b35;
        }
        
        .items-list {
            font-size: 13px;
            color: #333;
        }
        
        .items-list .item {
            margin-bottom: 8px;
            padding: 8px 12px;
            background: #fafafa;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .item-name {
            font-weight: 500;
            color: #ff6b35;
        }
        
        .item-qty {
            display: inline-block;
            padding: 2px 8px;
            background: #ffe8e0;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #ff6b35;
            margin: 0 8px;
        }
        
        .item-price {
            font-size: 12px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .status-pending {
            background: #fff3e0;
            color: #e65100;
        }
        
        .status-diproses {
            background: #e3f2fd;
            color: #1565c0;
        }
        
        .status-dimasak {
            background: #f3e5f5;
            color: #6a1b9a;
        }
        
        .status-selesai {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-batal {
            background: #ffebee;
            color: #c62828;
        }
        
        .total-info {
            font-size: 16px;
            font-weight: 700;
            color: #ff6b35;
            margin-top: 4px;
        }
        
        .payment-info {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        
        .time-info {
            font-size: 13px;
            color: #666;
        }
        
        .date-info {
            font-weight: 600;
            color: #ff6b35;
            margin-bottom: 4px;
        }
        
        .status-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: center;
        }
        
        select {
            padding: 8px 12px;
            border: 2px solid #ffe8e0;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            width: 100%;
        }
        
        select:focus {
            outline: none;
            border-color: #ff6b35;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }
        
        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            width: 100%;
        }
        
        .btn.primary {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(255, 107, 53, 0.3);
        }
        
        .btn.primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(255, 107, 53, 0.4);
        }
        
        .btn.ghost {
            background: #fafafa;
            color: #ff6b35;
            border: 2px solid #ffe8e0;
        }
        
        .btn.ghost:hover {
            background: #fff5f0;
            border-color: #ffd4c0;
        }
        
        .no-orders {
            padding: 60px 20px;
            text-align: center;
            color: #999;
            font-size: 16px;
        }
        
        .no-orders-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
        
        .action-column {
            min-width: 140px;
        }
        
        .brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
        }
        
        @media (max-width: 1200px) {
            .items-list .item {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
        }
        
        @media (max-width: 768px) {
            .main {
                padding: 16px;
            }
            
            .card {
                padding: 20px;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 10px 8px;
            }
            
            .customer-info {
                flex-direction: column;
                gap: 8px;
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
            <a href="data_master.php">Data Master</a>
            <a href="transaksi.php" class="active">Transaksi</a>
            <a href="laporan.php">Laporan</a>
        </div>
    </aside>

    <main class="main">
        <div class="card">
            <h3>🛒 Daftar Pesanan</h3>

            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <div class="no-orders-icon">📦</div>
                    <div>Belum ada pesanan</div>
                </div>
            <?php else: ?>

            <div class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th class="col-id">No</th>
                        <th class="col-id">ID</th>
                        <th>Pelanggan</th>
                        <th>Items</th>
                        <th class="col-center">Status & Total</th>
                        <th class="col-center">Waktu</th>
                        <th class="col-center action-column">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $i => $o): 
                        $id = (int)$o['id_pesanan'];
                        $items = $detailsByOrder[$id] ?? [];
                        $statusClass = 'status-' . strtolower($o['status_pesanan']);
                    ?>
                        <tr>
                            <td class="col-id"><?= $i+1 ?></td>
                            <td class="col-id"><strong>#<?= e($o['id_pesanan']) ?></strong></td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-avatar">
                                        <?= strtoupper(substr($o['nama_pembeli'] ?: 'G', 0, 1)) ?>
                                    </div>
                                    <div class="customer-name"><?= e($o['nama_pembeli'] ?: 'Guest') ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="items-list">
                                    <?php if (empty($items)): ?>
                                        <div style="color:#999;font-style:italic">Tidak ada item</div>
                                    <?php else: ?>
                                        <?php foreach ($items as $it): ?>
                                            <div class="item">
                                                <div>
                                                    <span class="item-name"><?= e($it['nama_menu'] ?: ('Menu #'.$it['id_menu'])) ?></span>
                                                    <span class="item-qty">×<?= (int)$it['jumlah'] ?></span>
                                                </div>
                                                <div class="item-price">
                                                    Rp <?= number_format((float)$it['subtotal'],0,',','.') ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="col-center">
                                <div class="status-badge <?= $statusClass ?>">
                                    <?= ucfirst(e($o['status_pesanan'])) ?>
                                </div>
                                <div class="total-info">Rp <?= number_format((float)$o['total_harga'],0,',','.') ?></div>
                                <div class="payment-info">
                                    Bayar: Rp <?= number_format((float)$o['bayar'],0,',','.') ?>
                                    <br>Kembalian: Rp <?= number_format((float)$o['kembalian'],0,',','.') ?>
                                </div>
                            </td>
                            <td class="col-center">
                                <div class="date-info"><?= date('d M Y', strtotime($o['tanggal_pesanan'])) ?></div>
                                <div class="time-info"><?= date('H:i', strtotime($o['tanggal_pesanan'])) ?> WIB</div>
                            </td>
                            <td class="col-center">
                                <form class="status-form" method="POST">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id_pesanan" value="<?= $id ?>">

                                    <select name="status" aria-label="status">
                                        <?php
                                        $options = [
                                            'pending'=>'Pending',
                                            'diproses'=>'Diproses',
                                            'dimasak'=>'Dimasak',
                                            'selesai'=>'Selesai',
                                            'batal'=>'Batal'
                                        ];
                                        foreach ($options as $val => $label):
                                            $sel = ($val === $o['status_pesanan']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= e($val) ?>" <?= $sel ?>><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button class="btn primary" type="submit">Update</button>
                                    <a class="btn ghost" href="detail_pesanan.php?id=<?= $id ?>">Detail</a>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php endif; ?>

        </div>
    </main>
</div>

</body>
</html>