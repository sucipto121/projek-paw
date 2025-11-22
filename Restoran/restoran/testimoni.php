<?php
// testimoni.php
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $review = trim($_POST['review']);

    if($name && $review){
        $file = 'testimoni.json';
        $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

        $data[] = [
            'name' => $name,
            'review' => $review,
            'time' => date('Y-m-d H:i:s')
        ];

        if(file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT))){
            $message = "Terima kasih, testimoni berhasil dikirim!";
        } else {
            $message = "Gagal menyimpan testimoni.";
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
    <form action="" method="post">
        <label>Nama:</label>
        <input type="text" name="name" placeholder="Nama Anda" required>
        <label>Testimoni:</label>
        <textarea name="review" rows="5" placeholder="Tulis testimoni Anda..." required></textarea>
        <button type="submit">Kirim Testimoni</button>
    </form>
    <a href="index.php" class="back">Kembali ke Menu</a>
</div>

</body>
</html>
