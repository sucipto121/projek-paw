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

                    // insert into ulasan with provided id_menu and rating (can be NULL)
                    $insU = $mysqli->prepare('INSERT INTO ulasan (id_pembeli, id_menu, rating, komentar) VALUES (?, ?, ?, ?)');
                    if ($insU) {
                        // bind parameters: i (id_pembeli), i (id_menu), i (rating), s (komentar)
                        $bindIdMenu = $id_menu;
                        $bindRating = $rating;
                        $insU->bind_param('iiis', $id_pembeli, $bindIdMenu, $bindRating, $review);
                        $dbSaved = $insU->execute();
                        if ($insU->error) {
                            $dbError = $insU->error;
                        }
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
            $message = "Terima kasih, testimoni berhasil dikirim!";
        } else {
            $message = "Gagal menyimpan testimoni ke database.";
            // log DB errors for debugging
            $logFile = __DIR__ . '/../admin/ulasan_errors.log';
            $logEntry = date('Y-m-d H:i:s') . " | name=" . $name . " | id_menu=" . ($id_menu ?? 'NULL') . " | rating=" . ($rating ?? 'NULL') . " | error=" . $dbError . PHP_EOL;
            file_put_contents($logFile, $logEntry, FILE_APPEND);
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
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container{
            max-width:500px;
            margin:50px auto;
            background:#fff;
            padding:25px;
            border-radius:10px;
            box-shadow:0 5px 15px rgba(0,0,0,.1);
        }
        input, textarea{
            width:100%;
            padding:10px;
            margin:8px 0;
            border:1px solid #ccc;
            border-radius:5px;
            font-size:14px;
        }
        button{
            background:#0275d8;
            color:#fff;
            border:none;
            padding:10px 20px;
            border-radius:5px;
            cursor:pointer;
            font-size:15px;
        }
        button:hover{
            background:#025aa5;
        }
        .message{
            margin:10px 0;
            color:green;
        }
        a.back{
            display:inline-block;
            margin-top:10px;
            text-decoration:none;
            color:#0275d8;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Kirim Testimoni</h2>
    <?php if($message): ?>
        <p class="message"><?=htmlspecialchars($message)?></p>
    <?php endif; ?>
    <?php
    // load menu list for optional association
    $menuOptions = [];
    try {
        require_once __DIR__ . '/../admin/koneksi.php';
        if (isset($mysqli) && $mysqli instanceof mysqli) {
            $res = $mysqli->query('SELECT id_menu, nama_menu FROM menu ORDER BY nama_menu');
            if ($res) {
                while ($r = $res->fetch_assoc()) {
                    $menuOptions[] = $r;
                }
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
    ?>

    <form action="" method="post">
        <label>Nama:</label>
        <input type="text" name="name" placeholder="Nama Anda" required>

        <label>Menu (opsional):</label>
        <select name="id_menu">
            <option value="">-- Pilih menu (opsional) --</option>
            <?php foreach ($menuOptions as $m): ?>
                <option value="<?=htmlspecialchars($m['id_menu'])?>"><?=htmlspecialchars($m['nama_menu'])?></option>
            <?php endforeach; ?>
        </select>

        <label>Rating:</label>
        <select name="rating" required>
            <option value="">-- Pilih rating --</option>
            <?php for ($i=5;$i>=1;$i--): ?>
                <option value="<?=$i?>"><?=$i?> &#9733;</option>
            <?php endfor; ?>
        </select>

        <label>Testimoni:</label>
        <textarea name="review" rows="5" placeholder="Tulis testimoni Anda..." required></textarea>
        <button type="submit">Kirim Testimoni</button>
    </form>
    <a href="index.php" class="back">Kembali ke Menu</a>
</div>

</body>
</html>
