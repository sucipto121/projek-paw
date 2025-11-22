<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['nama'] = $_POST['nama'];
    $_SESSION['meja'] = $_POST['meja'];

    header("Location: index.php");
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
    <table class="login-table">
        <tr>
            <td><label>Nama Pemesan</label></td>
            <td><input type="text" name="nama" required></td>
        </tr>
        <tr>
            <td><label>Nomor Meja</label></td>
            <td><input type="text" name="meja" required></td>
        </tr>
        <tr>
            <td colspan="2" class="btn-center">
                <button type="submit" class="btn-login">Masuk</button>
            </td>
        </tr>
    </table>
</form>

    </div>

</body>
</html>