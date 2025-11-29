<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami — Rasa Laut Nusantara</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Layout-only changes. Colors are preserved from the original theme. */

        /* keep global fonts/defaults unchanged; only adjust spacing/layout */
        .about-section{
            max-width:900px;
            margin:60px auto;
            background:#fff; /* preserve original white card */
            padding:30px;
            border-radius:12px;
            box-shadow:0 8px 28px rgba(0,0,0,.08);
            border:1px solid rgba(0,0,0,0.03);
        }

        .about-section h2{
            text-align:center;
            margin-bottom:6px;
            font-size:28px;
            font-weight:700;
            color:inherit;
        }

        .about-section p{
            line-height:1.75;
            margin-bottom:14px;
            font-size:16px;
            color:inherit;
            text-align:center;
        }

        .team{margin-top:30px;}

        .team h3{font-size:20px;margin-bottom:14px;text-align:center}


        /* Keep original card layout: use flex so positions don't change */
        .team-members{
            display:flex;
            gap:20px;
            justify-content:center;
            flex-wrap:wrap;
        }

        .member{
            background:#fafafa; /* keep original card color */
            width:120px;
            text-align:center;
            padding:18px 10px;
            border-radius:10px;
            margin-bottom: 12px;
            transition: transform .25s ease, box-shadow .25s ease;
            box-shadow:0 3px 12px rgba(0,0,0,.06);
        }

        .member:hover{
            transform:translateY(-6px);
            box-shadow:0 14px 36px rgba(0,0,0,.08);
        }

        .member img{
            width:120px;
            height:120px;
            object-fit:cover;
            border-radius:50%;
            margin-bottom:12px;
            object-position:top;
            border:3px solid #0275d8; /* preserve original accent */
        }

        .member strong{
            display:block;
            font-size:17px;
            margin-bottom:6px;
            color:#222;
        }

        .member p{font-size:14px;color:#555;margin:0}

        .btn-back{
            display:block;
            margin:26px auto 0;
            background:#0275d8; /* original blue */
            color:#fff;
            padding:10px 22px;
            border-radius:8px;
            text-decoration:none;
            text-align:center;
            width:fit-content;
            transition:.25s;

        }
        .btn-back:hover{background:#025aa5}

        /* footer layout spacing only */
        .contact-footer{margin-top:34px;padding:24px 12px}

        .copy{margin-top:18px;color:#666;text-align:center;font-size:13px}
    </style>
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
      <div class="brand">
        <div class="logo">Rasa Laut Nusantara</div>
      </div>
      <nav class="main-nav">
        <a href="index.php">Beranda</a>
        <a href="#" id="contact">Contact</a>
      </nav>
    </div>
</header>

<div class="about-section" id="aboutSection">
    <h2 style="font-family: fairplay display;">Tentang Kami</h2>
    <p>
        Selamat datang di <strong>Rasa Laut Nusantara</strong>!  
        Kami adalah restoran dengan konsep hidangan laut modern yang menyajikan cita rasa asli Nusantara.  
        Bahan-bahan segar dari hasil laut Indonesia kami olah secara kreatif untuk memberikan pengalaman kuliner terbaik.
    </p>

    <p>
        Dengan tim koki berpengalaman, kami menghadirkan hidangan premium dengan harga yang tetap bersahabat.
        Kepuasan pelanggan adalah prioritas kami, baik untuk makan di tempat maupun melalui layanan pemesanan kami.
    </p>

    <p>
        Nikmati suasana nyaman, pelayanan ramah, dan rasa yang akan membuatmu ingin kembali lagi!
    </p>

    <div class="team">
        <h3 style="text-align:center; font-family:fairplay display">Tim Kami</h3>
        <div class="team-members">

            <div class="member">
                <img src="images/satya.jpg" alt="">
                <strong>Satya</strong>
                <p>Head Chef</p>
            
            </div>

            <div class="member">
                <img src="images/rozi.jpg" alt="">
                <strong>Rozikin</strong>
                <p>Manager Operasional</p>
              
            </div>

            <div class="member">
                <img src="images/budi.jpg" alt="">
                <strong>Budi</strong>
                <p>Barista & Mixologist</p>
            </div>

            <div class="member">
                <img src="images/aisyah.jpg" alt="">
                <strong>Aisyah</strong>
                <p>Customer Relations</p>
            </div>

            <div class="member">
                <img src="images/brian.jpg" alt="">
                <strong>Brian</strong>
                <p>Logistik & Pengadaan</p>
            </div>

        </div>
    </div>

    <a href="index.php" class="btn-back">Kembali ke Menu</a>
</div>

<footer class="contact-footer" id="contactFooter">
    <div class="container contact-grid">
      
      <div class="contact-item">
        <h3>Kontak Kami</h3>
        <p>Jl. Rasa Laut No. 27, Nusantara</p>
        <p><strong>Tel:</strong> +62 812-3456-7890</p>
        <p><strong>Email:</strong> support@rasalaut.com</p>
      </div>

      <div class="contact-item">
        <h3>Jam Operasional</h3>
        <p>Senin – Jumat: 10.00 – 22.00</p>
        <p>Sabtu – Minggu: 09.00 – 23.00</p>
      </div>

      <div class="contact-item">
        <h3>Sosial Media</h3>
        <p>Ikuti kami:</p>
        <div class="social">
          <a href="#">Facebook</a>
          <a href="#">Instagram</a>
          <a href="#">TikTok</a>
        </div>
      </div>

    </div>

    <div class="copy">
      &copy; <?=date('Y')?> Rasa Laut Nusantara — Semua Hak Dilindungi.
    </div>
</footer>

<script src="script.js"></script>

</body>
</html>
