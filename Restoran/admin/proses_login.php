<?php
session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Basic CSRF check
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: login.php?err=' . urlencode('Token keamanan tidak valid'));
    exit;
}

// optional token lifetime (15 minutes)
if (!empty($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 900) {
    unset($_SESSION['csrf_token']);
    unset($_SESSION['csrf_token_time']);
    header('Location: login.php?err=' . urlencode('Token kedaluwarsa, muat ulang halaman'));
    exit;
}

$input = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($input === '' || $password === '') {
    header('Location: login.php?err=' . urlencode('Lengkapi username dan password'));
    exit;
}

// Try to authenticate by `username` or `nama` if `username` exists; otherwise fallback to `nama` only
$twoParamSql = "SELECT id_admin AS id, nama, username, password FROM admin WHERE nama = ? OR username = ? LIMIT 1";
$oneParamSql = "SELECT id_admin AS id, nama, password FROM admin WHERE nama = ? LIMIT 1";

$stmt = $mysqli->prepare($twoParamSql);
$usedTwoParams = true;
if (!$stmt) {
    // fallback if `username` column doesn't exist
    $stmt = $mysqli->prepare($oneParamSql);
    $usedTwoParams = false;
}
if (!$stmt) {
    header('Location: login.php?err=' . urlencode('Kesalahan server: ' . $mysqli->error));
    exit;
}

if ($usedTwoParams) {
    $stmt->bind_param('ss', $input, $input);
} else {
    $stmt->bind_param('s', $input);
}

$stmt->execute();
$res = $stmt->get_result();

// Logging helper (no passwords)
$logFile = __DIR__ . '/login_attempts.log';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$time = date('c');

if ($res->num_rows !== 1) {
    // admin not found
    @file_put_contents($logFile, "[$time] LOGIN_FAIL: admin not found ('$input') from $ip\n", FILE_APPEND);
    header('Location: login.php?err=' . urlencode('Nama admin tidak ditemukan'));
    exit;
}

$row = $res->fetch_assoc();
$stored = $row['password'];

$ok = false;
if (password_verify($password, $stored)) {
    $ok = true;
} else {
    // Backward compatibility: MD5
    if (strlen($stored) === 32 && md5($password) === $stored) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $mysqli->prepare("UPDATE admin SET password = ? WHERE id_admin = ?");
        if ($upd) {
            $upd->bind_param('si', $newHash, $row['id']);
            $upd->execute();
        }
        $ok = true;
    }
}

if ($ok) {
    session_regenerate_id(true);
    $sessionUsername = isset($row['username']) && $row['username'] !== null && $row['username'] !== '' ? $row['username'] : $row['nama'];
    $_SESSION['user'] = [
        'id' => $row['id'],
        'username' => $sessionUsername,
        'nama' => $row['nama'],
        'level' => 1
    ];
    unset($_SESSION['csrf_token']);
    unset($_SESSION['csrf_token_time']);
    @file_put_contents($logFile, "[$time] LOGIN_OK: admin '{$row['nama']}' (id: {$row['id']}) from $ip\n", FILE_APPEND);
    header('Location: index.php');
    exit;
} else {
    @file_put_contents($logFile, "[$time] LOGIN_FAIL: wrong password for '{$row['nama']}' from $ip\n", FILE_APPEND);
    header('Location: login.php?err=' . urlencode('Password salah'));
    exit;
}
