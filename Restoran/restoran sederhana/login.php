<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['nama'] = $_POST['nama'];
    $_SESSION['meja'] = $_POST['meja'];

    header("Location: kategori.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Halaman Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

    <div class="login-box">
        <h2>Login Pemesanan</h2>

        <form method="POST">
            <label>Nama Pemesan</label>
            <input type="text" name="nama" required>

            <label>Nomor Meja</label>
            <input type="text" name="meja" required>

            <button type="submit">Masuk</button>
        </form>
    </div>

</body>
</html>