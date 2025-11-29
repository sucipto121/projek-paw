<?php
// testimoni.php
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $review = trim($_POST['review']);
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
    $id_menu = (isset($_POST['id_menu']) && $_POST['id_menu'] !== '') ? (int)$_POST['id_menu'] : null;

    if ($name && $review) {
        $dbSaved = false;
        $dbError = '';
        try {
            require_once __DIR__ . '/../admin/koneksi.php';
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                // ensure pembeli exists (by name) or create
                $pstmt = $mysqli->prepare('SELECT id_pembeli FROM pembeli WHERE nama = ? LIMIT 1');
                if ($pstmt) {
                    $pstmt->bind_param('s', $name);
                    $pstmt->execute();
                    $pstmt->bind_result($id_pembeli);
                    if ($pstmt->fetch()) {
                        $pstmt->close();
                    } else {
                        $pstmt->close();
                        $ins = $mysqli->prepare('INSERT INTO pembeli (nama) VALUES (?)');
                        if ($ins) {
                            $ins->bind_param('s', $name);
                            $ins->execute();
                            $id_pembeli = $ins->insert_id;
                            $ins->close();
                        }
                    }

                    // insert ulasan
                    $insU = $mysqli->prepare('INSERT INTO ulasan (id_pembeli, id_menu, rating, komentar) VALUES (?, ?, ?, ?)');
                    if ($insU) {
                        $insU->bind_param('iiis', $id_pembeli, $id_menu, $rating, $review);
                        $dbSaved = $insU->execute();
                        if ($insU->error) $dbError = $insU->error;
                        $insU->close();
                    } else {
                        $dbError = $mysqli->error;
                    }
                } else {
                    $dbError = $mysqli->error;
                }
            } else {
                $dbError = 'Database connection not available';
            }
        } catch (Throwable $e) {
            $dbError = $e->getMessage();
        }

        if ($dbSaved) {
            $message = "Terima kasih! Testimoni Anda berhasil dikirim.";
        } else {
            $message = "Gagal menyimpan testimoni.";
            file_put_contents(__DIR__ . '/../admin/ulasan_errors.log',
                date('Y-m-d H:i:s') . " | name=$name | id_menu=$id_menu | rating=$rating | error=$dbError\n",
                FILE_APPEND
            );
        }
    } else {
        $message = "Nama dan testimoni harus diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kirim Testimoni — Rasa Laut Nusantara</title>

<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #f7f5f2; /* warna background website utama */
        padding: 40px;
        color: #1a1a1a;
    }

    .form-container {
        max-width: 550px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border-top: 5px solid #EF4F4F; /* warna merah coral utama tema */
    }

    h2 {
        text-align: center;
        font-weight: 700;
        color: #EF4F4F; /* warna heading tema */
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-top: 15px;
        font-weight: 600;
        color: #333;
    }

    input, textarea, select {
        width: 100%;
        padding: 12px;
        margin-top: 7px;
        border: 1px solid #dcdcdc;
        border-radius: 8px;
        font-size: 14px;
        background: #fafafa;
        transition: 0.2s;
    }

    input:focus, textarea:focus, select:focus {
        outline: none;
        border-color: #EF4F4F;
        background: white;
        box-shadow: 0 0 4px rgba(239,79,79,0.3);
    }

    button {
        width: 100%;
        margin-top: 20px;
        background: #EF4F4F;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: 0.2s;
    }

    button:hover {
        background: #D93F3F;
        transform: translateY(-1px);
    }

    .message {
        text-align: center;
        font-weight: 600;
        padding: 10px;
        border-radius: 8px;
        background: #ffe7e7;
        color: #D93F3F;
        margin-bottom: 10px;
        border: 1px solid #ffb3b3;
    }

    .back {
        display: block;
        text-align: center;
        margin-top: 20px;
        text-decoration: none;
        color: #EF4F4F;
        font-weight: 600;
    }

    .back:hover {
        text-decoration: underline;
    }

</style>

</head>
<body>

<div class="form-container">
    <h2>Kirim Testimoni</h2>

    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php
    // Load menu list
    $menuOptions = [];
    try {
        require_once __DIR__ . '/../admin/koneksi.php';
        if (isset($mysqli)) {
            $res = $mysqli->query("SELECT id_menu, nama_menu FROM menu ORDER BY nama_menu");
            while ($row = $res->fetch_assoc()) $menuOptions[] = $row;
        }
    } catch (Throwable $e) {}
    ?>

    <form action="" method="post">

        <label>Nama Anda:</label>
        <input type="text" name="name" placeholder="Masukkan nama Anda" required>

        <label>Pilih Menu (Opsional):</label>
        <select name="id_menu">
            <option value="">Tidak memilih menu</option>
            <?php foreach ($menuOptions as $m): ?>
                <option value="<?= $m['id_menu'] ?>"><?= htmlspecialchars($m['nama_menu']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Rating:</label>
        <select name="rating" required>
            <option value="">-- Pilih rating --</option>
            <?php for ($i=5;$i>=1;$i--): ?>
                <option value="<?=$i?>"><?=$i?> ★</option>
            <?php endfor; ?>
        </select>

        <label>Testimoni:</label>
        <textarea name="review" rows="5" placeholder="Tulis pengalaman Anda..." required></textarea>

        <button type="submit">Kirim Testimoni</button>
    </form>

    <a href="index.php" class="back">← Kembali ke Menu</a>
</div>

</body>
</html>
