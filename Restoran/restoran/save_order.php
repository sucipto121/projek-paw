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
