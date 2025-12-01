<?php
session_start();
$message = '';
$dbSaved = false;
$dbError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $id_menu = !empty($_POST['id_menu']) ? intval($_POST['id_menu']) : null;
    $rating = intval($_POST['rating'] ?? 0);
    $review = trim($_POST['review'] ?? '');

    if ($name && $review) {
        try {
            require_once __DIR__ . '/../admin/koneksi.php';
            
            if ($mysqli && $mysqli->ping()) {
                // Check or insert pembeli
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

                    // Insert ulasan
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
            file_put_contents(__DIR__ . '/../admin/ulasan_errors.log', date('Y-m-d H:i:s') . " | name=$name | id_menu=$id_menu | rating=$rating | error=$dbError\n", FILE_APPEND);
        }
    } else {
        $message = "Nama dan testimoni harus diisi!";
    }
}

// Fetch menu options
$menuOptions = [];
try {
    require_once __DIR__ . '/../admin/koneksi.php';
    if ($mysqli && $mysqli->ping()) {
        $res = $mysqli->query("SELECT id_menu, nama_menu FROM menu ORDER BY nama_menu");
        while ($row = $res->fetch_assoc()) $menuOptions[] = $row;
    }
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Testimoni</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ff9a56 0%, #ff6b35 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.95;
            font-weight: 300;
        }

        .content {
            padding: 40px 30px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #ff6b35;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .back-link:hover {
            color: #ff8c42;
            transform: translateX(-5px);
        }

        .back-link::before {
            content: "←";
            margin-right: 8px;
            font-size: 18px;
        }

        .message {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 500;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #ff6b35;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .rating-group {
            display: flex;
            flex-direction: column;
        }

        .stars {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 10px;
        }

        .stars input[type="radio"] {
            display: none;
        }

        .stars label {
            cursor: pointer;
            font-size: 32px;
            color: #ddd;
            transition: all 0.2s ease;
            margin: 0;
        }

        .stars label:hover,
        .stars label:hover ~ label,
        .stars input[type="radio"]:checked ~ label {
            color: #ff6b35;
            transform: scale(1.1);
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 640px) {
            .container {
                border-radius: 16px;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 30px 20px;
            }

            .stars label {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✨ Kirim Testimoni</h1>
            <p>Bagikan pengalaman Anda bersama kami</p>
        </div>

        <div class="content">
            <a href="index.php" class="back-link">Kembali ke Menu</a>

            <?php if ($message): ?>
                <div class="message <?= $dbSaved ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Nama Anda *</label>
                    <input type="text" id="name" name="name" required placeholder="Masukkan nama Anda">
                </div>

                <div class="form-group">
                    <label for="id_menu">Pilih Menu (Opsional)</label>
                    <select id="id_menu" name="id_menu">
                        <option value="">Tidak memilih menu</option>
                        <?php foreach ($menuOptions as $opt): ?>
                            <option value="<?= $opt['id_menu'] ?>">
                                <?= htmlspecialchars($opt['nama_menu']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group rating-group">
                    <label>Rating *</label>
                    <div class="stars">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
                            <label for="star<?= $i ?>">★</label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="review">Testimoni *</label>
                    <textarea id="review" name="review" required placeholder="Ceritakan pengalaman Anda..."></textarea>
                </div>

                <button type="submit" class="submit-btn">Kirim Testimoni</button>
            </form>
        </div>
    </div>
</body>
</html>