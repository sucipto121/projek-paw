<?php
// save_order.php
header("Content-Type: application/json; charset=utf-8");

// baca body
$raw = file_get_contents("php://input");
if (!$raw) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "No input received"]);
    exit;
}

$order = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid JSON: " . json_last_error_msg()]);
    exit;
}

// Validasi sederhana
if (empty($order['customer']) || empty($order['table']) || empty($order['items']) || !is_array($order['items'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing required order fields"]);
    exit;
}

// Generate kode unik untuk order (yang akan dipakai nota)
$order_code = "ORD" . strtoupper(substr(uniqid(), -6));
$order['order_code'] = $order_code;
// juga simpan id unik (untuk kebutuhan internal)
$order['order_id'] = uniqid();

// try to also persist into database and decrement stock
require_once __DIR__ . '/../admin/koneksi.php';

// begin transaction
$mysqli->begin_transaction();
$db_errors = [];

// ensure pembeli exists
$customer = trim($order['customer']);
$pembeli_id = null;
if ($customer !== '') {
    $pq = $mysqli->prepare('SELECT id_pembeli FROM pembeli WHERE LOWER(nama) = LOWER(?) LIMIT 1');
    if ($pq) {
        $pq->bind_param('s', $customer);
        $pq->execute();
        $pres = $pq->get_result();
        if ($pres && $pres->num_rows > 0) {
            $pembeli_id = $pres->fetch_assoc()['id_pembeli'];
        } else {
            $insp = $mysqli->prepare('INSERT INTO pembeli (nama) VALUES (?)');
            if ($insp) { $insp->bind_param('s', $customer); $insp->execute(); $pembeli_id = $mysqli->insert_id; }
            else { $db_errors[] = 'Failed to prepare pembeli insert: ' . $mysqli->error; }
        }
    } else { $db_errors[] = 'Failed to prepare pembeli select: ' . $mysqli->error; }
}

// prepare to check stock for each item
$items = $order['items'];
$ins_pesanan = null;
$pesanan_id = null;
$total_harga = 0.0;

// first, resolve menu ids and check stock
$resolved = [];
foreach ($items as $it) {
    $title = trim($it['title'] ?? '');
    $qty = (int)($it['qty'] ?? 0);
    $price = (float)($it['price'] ?? 0);
    if ($qty <= 0) continue;
    $mq = $mysqli->prepare('SELECT id_menu, harga, stok FROM menu WHERE LOWER(nama_menu) = LOWER(?) LIMIT 1');
    if (!$mq) { $db_errors[] = 'Prepare menu select failed: ' . $mysqli->error; break; }
    $mq->bind_param('s', $title);
    $mq->execute();
    $mres = $mq->get_result();
    if ($mres && $mres->num_rows > 0) {
        $m = $mres->fetch_assoc();
        $id_menu = $m['id_menu'];
        $harga_unit = (float)$m['harga'];
        $stok_now = (int)$m['stok'];
        if ($stok_now < $qty) {
            $db_errors[] = "Stok tidak cukup untuk item '{$title}' (tersedia: {$stok_now}, diminta: {$qty})";
        }
        $resolved[] = ['id_menu'=>$id_menu, 'qty'=>$qty, 'harga'=>$harga_unit];
        $total_harga += $harga_unit * $qty;
    } else {
        // menu not found — treat as external item, allow but no stock update
        $resolved[] = ['id_menu'=>null, 'qty'=>$qty, 'harga'=>$price, 'title'=>$title];
        $total_harga += $price * $qty;
    }
}

if (!empty($db_errors)) {
    $mysqli->rollback();
    http_response_code(400);
    echo json_encode(["success"=>false, "error"=>implode('; ', $db_errors)]);
    exit;
}

// insert pesanan
$ins = $mysqli->prepare('INSERT INTO pesanan (kode_pesanan, id_pembeli, metode_order, status_pesanan, total_harga, bayar, kembalian, tanggal_pesanan) VALUES (?, ?, ?, ?, ?, 0, 0, NOW())');
if ($ins) {
    $metode = 'dine_in';
    $status = 'pending';
    $ins->bind_param('sissd', $order_code, $pembeli_id, $metode, $status, $total_harga);
    if ($ins->execute()) {
        $pesanan_id = $mysqli->insert_id;
    } else {
        $db_errors[] = 'Insert pesanan failed: ' . $ins->error;
    }
} else { $db_errors[] = 'Prepare insert pesanan failed: ' . $mysqli->error; }

// insert detail and decrement stock
if (empty($db_errors) && $pesanan_id) {
    foreach ($resolved as $r) {
        $idm = $r['id_menu'];
        $qty = $r['qty'];
        $harga_unit = $r['harga'];
        $subtotal = $harga_unit * $qty;
        $dins = $mysqli->prepare('INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)');
        if ($dins) {
            if ($idm === null) {
                // insert with NULL id_menu
                $dins->bind_param('iiidd', $pesanan_id, $idm, $qty, $harga_unit, $subtotal);
            } else {
                $dins->bind_param('iiidd', $pesanan_id, $idm, $qty, $harga_unit, $subtotal);
            }
            if (!$dins->execute()) { $db_errors[] = 'Insert detail failed: ' . $dins->error; break; }
        } else { $db_errors[] = 'Prepare insert detail failed: ' . $mysqli->error; break; }

        // decrement stock when id_menu available
        if ($idm !== null) {
            $up = $mysqli->prepare('UPDATE menu SET stok = stok - ? WHERE id_menu = ? AND stok >= ?');
            if ($up) {
                $up->bind_param('iii', $qty, $idm, $qty);
                $up->execute();
                if ($up->affected_rows === 0) {
                    $db_errors[] = "Gagal mengurangi stok untuk menu id {$idm}. Mungkin stok tidak cukup.";
                    break;
                }
            } else { $db_errors[] = 'Prepare stok update failed: ' . $mysqli->error; break; }
        }
    }
}

if (!empty($db_errors)) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(["success"=>false, "error"=>implode('; ', $db_errors)]);
    exit;
}

$mysqli->commit();

// file path
$file = __DIR__ . "/orders.json";

// pastikan file ada dan minimal konten array
if (!file_exists($file)) {
    // coba buat file dengan array kosong
    if (false === @file_put_contents($file, json_encode([], JSON_PRETTY_PRINT))) {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Failed to create orders file. Check folder permissions."]);
        exit;
    }
}

// sekarang baca dan tulis dengan flock untuk menghindari race condition
$fp = fopen($file, "c+");
if (!$fp) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Unable to open orders file for reading/writing"]);
    exit;
}

if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Unable to lock orders file"]);
    exit;
}

// pastikan pointer di awal dan ambil isi
rewind($fp);
$contents = stream_get_contents($fp);
$existing = [];
if ($contents !== false && strlen(trim($contents)) > 0) {
    $existing = json_decode($contents, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // jika file korup, kita backup dan reset array
        $bak = $file . ".bak." . time();
        file_put_contents($bak, $contents);
        $existing = [];
    }
}
if (!is_array($existing)) $existing = [];

// tambahkan order baru
$existing[] = $order;

// tulis ulang file (truncate dulu)
rewind($fp);
ftruncate($fp, 0);
if (false === fwrite($fp, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    flock($fp, LOCK_UN);
    fclose($fp);
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Failed to write orders file"]);
    exit;
}

fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

// sukses — kembalikan order_code (nota akan menggunakan kode ini)
echo json_encode([
    "success" => true,
    "order_code" => $order_code,
    "order_id" => $order['order_id']
]);
