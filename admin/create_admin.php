<?php
// One-time admin creation script. Remove after use.
require 'koneksi.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($nama === '' || $password === '') {
        $err = 'Isi nama dan password.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare('INSERT INTO admin (nama,password) VALUES (?, ?)');
        if (!$stmt) {
            $err = 'Gagal membuat admin: ' . $mysqli->error;
        } else {
            $stmt->bind_param('ss', $nama, $hash);
            if ($stmt->execute()) {
                $ok = true;
            } else {
                $err = 'Gagal mengeksekusi query: ' . $stmt->error;
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Buat Admin</title>
  <link rel="stylesheet" href="css/login.css">
  <style>.box{max-width:420px;margin:60px auto;padding:20px;background:#fff;border-radius:8px}</style>
<head>
<body>
  <div class="box">
    <h2>Buat Akun Admin (Sekali Pakai)</h2>
    <?php if (!empty($err)): ?><div class="alert"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if (!empty($ok)): ?>
      <div class="alert" style="background:#e6ffed;border-left:4px solid #16a34a">Admin berhasil dibuat. <a href="login.php">Masuk</a></div>
    <?php else: ?>
    <form method="POST">
      <label>Nama admin<br><input type="text" name="nama" required></label><br><br>
      <label>Password<br><input type="password" name="password" required></label><br><br>
      <button class="btn primary" type="submit">Buat Admin</button>
    </form>
    <?php endif; ?>
    <p style="font-size:12px;color:#666;margin-top:10px">Hapus file ini setelah admin dibuat.</p>
  </div>
</body>
</html>
