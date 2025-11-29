<?php
require 'koneksi.php';

$ordersFile = __DIR__ . '/../pengguna/orders.json';
$logFile = __DIR__ . '/sync.log';
$orders = [];
if (file_exists($ordersFile)) {
    $raw = file_get_contents($ordersFile);
    $orders = json_decode($raw, true) ?: [];
}

function compute_order_code($o) {
    return md5((string)($o['time'] ?? '') . '|' . (string)($o['table'] ?? '') . '|' . (string)($o['customer'] ?? ''));
}

$log = [];
$insertedOrders = 0;
$insertedDetails = 0;
$skipped = 0;
$errors = [];

foreach ($orders as $idx => $o) {
    $code = compute_order_code($o);
    // check existing
    $q = $mysqli->prepare('SELECT id_pesanan FROM pesanan WHERE kode_pesanan = ? LIMIT 1');
    if (!$q) { $errors[] = "Prepare failed for select pesanan: " . $mysqli->error; continue; }
    $q->bind_param('s', $code);
    $q->execute();
    $res = $q->get_result();
    if ($res && $res->num_rows > 0) { $log[] = "Order #$idx already exists (code=$code)"; continue; }

    // pembeli
    $customer = trim($o['customer'] ?? '');
    if ($customer === '') $customer = 'Meja ' . ($o['table'] ?? '');
    $pid = null;
    $pstmt = $mysqli->prepare('SELECT id_pembeli FROM pembeli WHERE LOWER(nama) = LOWER(?) LIMIT 1');
    if (!$pstmt) { $errors[] = "Prepare failed for select pembeli: " . $mysqli->error; continue; }
    $pstmt->bind_param('s', $customer);
    $pstmt->execute();
    $pres = $pstmt->get_result();
    if ($pres && $pres->num_rows > 0) {
        $pid = $pres->fetch_assoc()['id_pembeli'];
    } else {
        $ins = $mysqli->prepare('INSERT INTO pembeli (nama) VALUES (?)');
        if (!$ins) { $errors[] = "Prepare failed for insert pembeli: " . $mysqli->error; continue; }
        $ins->bind_param('s', $customer);
        if (!$ins->execute()) { $errors[] = "Insert pembeli failed: " . $ins->error; continue; }
        $pid = $mysqli->insert_id;
    }

    if (!$pid) { $errors[] = "No pembeli id for order $idx"; continue; }

    $total = 0;
    foreach ($o['items'] as $it) { $total += (float)$it['price'] * (int)$it['qty']; }
    $date = date('Y-m-d H:i:s', strtotime($o['time'] ?? 'now'));
    $status = strtolower($o['status'] ?? 'pending');

    $insp = $mysqli->prepare('INSERT INTO pesanan (kode_pesanan, id_pembeli, metode_order, status_pesanan, total_harga, bayar, kembalian, tanggal_pesanan) VALUES (?, ?, ?, ?, ?, 0, 0, ?)');
    if (!$insp) { $errors[] = "Prepare failed insert pesanan: " . $mysqli->error; continue; }
    $metode = 'dine_in';
    $insp->bind_param('sissds', $code, $pid, $metode, $status, $total, $date);
    if (!$insp->execute()) { $errors[] = "Insert pesanan failed: " . $insp->error; continue; }
    $id_pesanan = $mysqli->insert_id;
    $insertedOrders++;

    foreach ($o['items'] as $it) {
        $title = $it['title'];
        $qmenu = $mysqli->prepare('SELECT id_menu, harga FROM menu WHERE LOWER(nama_menu) = LOWER(?) LIMIT 1');
        if (!$qmenu) { $errors[] = "Prepare failed select menu: " . $mysqli->error; break; }
        $qmenu->bind_param('s', $title);
        $qmenu->execute();
        $mres = $qmenu->get_result();
        $id_menu = null;
        $harga_unit = (float)$it['price'];
        if ($mres && $mres->num_rows > 0) {
            $mr = $mres->fetch_assoc();
            $id_menu = $mr['id_menu'];
            $harga_unit = $mr['harga'] ?: $harga_unit;
        }
        if ($id_menu) {
            $sub = $harga_unit * (int)$it['qty'];
            $dd = $mysqli->prepare('INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)');
            if (!$dd) { $errors[] = "Prepare failed insert detail: " . $mysqli->error; break; }
            $dd->bind_param('iiidd', $id_pesanan, $id_menu, $it['qty'], $harga_unit, $sub);
            if ($dd->execute()) { $insertedDetails++; } else { $errors[] = "Insert detail failed: " . $dd->error; }
        } else {
            $log[] = "Item '{$title}' not found in 'menu' table — detail skipped for order code $code";
            $skipped++;
        }
    }
}

// write log
$summary = [];
$summary[] = "Inserted orders: $insertedOrders";
$summary[] = "Inserted details: $insertedDetails";
$summary[] = "Skipped items (no menu match): $skipped";
if (!empty($errors)) {
    $summary[] = "Errors: ";
    foreach ($errors as $er) $summary[] = $er;
}
$summary = array_merge($summary, $log);
file_put_contents($logFile, implode("\n", $summary) . "\n", FILE_APPEND);

// print simple response for browser
header('Content-Type: text/plain; charset=utf-8');
echo "Import finished\n";
echo implode("\n", $summary);

echo "\n\nSync log: $logFile\n";

?>