<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami — Rasa Laut Nusantara</title>
    <style>
<<<<<<< HEAD
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
=======
        /* keep global fonts/defaults unchanged; only adjust spacing/layout */
        .about-section{
            max-width:900px;
            margin:60px auto;
            background:#fff; /* preserve original white card */
            padding:30px;
            border-radius:12px;
            box-shadow:0 8px 28px rgba(0,0,0,.08);
            border:1px solid rgba(0,0,0,0.03);
>>>>>>> bcb20222c5ecc8e3de2ed33f6763055bb4151145
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }

        /* Header */
        .site-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
            animation: slideDown 0.6s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand img {
            height: 50px;
            width: 50px;
            object-fit: contain;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .main-nav {
            display: flex;
            gap: 30px;
        }

        .main-nav a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            position: relative;
            transition: color 0.3s;
        }

        .main-nav a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            transition: width 0.3s;
        }

        .main-nav a:hover {
            color: #ff6b35;
        }

        .main-nav a:hover::after {
            width: 100%;
        }

        /* About Section */
        .about-section {
            max-width: 1000px;
            margin: 80px auto;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            padding: 60px 50px;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.8s ease-out;
            position: relative;
            overflow: hidden;
        }

        .about-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ff6b35 0%, #f7931e 100%);
        }

        @keyframes fadeInUp {
            from {
                transform: translateY(40px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .about-section h2 {
            font-family: 'Playfair Display', serif;
            text-align: center;
            margin-bottom: 30px;
            font-size: 42px;
            font-weight: 700;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .about-section p {
            line-height: 1.8;
            margin-bottom: 20px;
            font-size: 17px;
            color: #555;
            text-align: center;
            font-weight: 400;
        }

        .about-section p strong {
            color: #ff6b35;
            font-weight: 600;
        }

        /* Team Section */
        .team {
            margin-top: 60px;
        }

        .team h3 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            margin-bottom: 40px;
            text-align: center;
            color: #333;
        }

        .team-members {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 30px;
            justify-items: center;
        }

        .member {
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            width: 180px;
            text-align: center;
            padding: 25px 15px;
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out backwards;
        }

        .member:nth-child(1) { animation-delay: 0.1s; }
        .member:nth-child(2) { animation-delay: 0.2s; }
        .member:nth-child(3) { animation-delay: 0.3s; }
        .member:nth-child(4) { animation-delay: 0.4s; }
        .member:nth-child(5) { animation-delay: 0.5s; }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .member::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 107, 53, 0.1), transparent);
            transition: left 0.5s;
        }

        .member:hover::before {
            left: 100%;
        }

        .member:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 20px 50px rgba(255, 107, 53, 0.3);
        }

        .member img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
            object-position: top;
            border: 4px solid transparent;
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(135deg, #ff6b35, #f7931e) border-box;
            transition: transform 0.4s;
        }

        .member:hover img {
            transform: scale(1.1) rotate(5deg);
        }

        .member strong {
            display: block;
            font-size: 18px;
            margin-bottom: 8px;
            color: #222;
            font-weight: 600;
        }

        .member p {
            font-size: 14px;
            color: #ff6b35;
            margin: 0;
            font-weight: 500;
        }

        /* Button */
        .btn-back {
            display: inline-block;
            margin: 40px auto 0;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: #fff;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.4);
            position: relative;
            overflow: hidden;
            display: block;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-back::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-back:hover::before {
            left: 100%;
        }

        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 107, 53, 0.5);
        }

        .btn-back:active {
            transform: translateY(-1px);
        }

        /* Footer */
        .contact-footer {
            background: #1a1a1a;
            backdrop-filter: blur(10px);
            margin-top: 80px;
            padding: 50px 20px 30px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
        }

        .contact-item h3 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 15px;
            color: #fff;
            font-size: 20px;
        }

        .contact-item p {
            color: #bbb;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .contact-item p strong {
            color: #fff;
        }

        .social {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .social a {
            color: #ff6b35;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .social a:hover {
            color: #f7931e;
        }

        .copy {
            text-align: center;
            color: #888;
            font-size: 14px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .about-section {
                margin: 40px 20px;
                padding: 40px 30px;
            }

            .about-section h2 {
                font-size: 32px;
            }

            .team h3 {
                font-size: 26px;
            }

            .team-members {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 20px;
            }

            .member {
                width: 150px;
                padding: 20px 10px;
            }

            .main-nav {
                gap: 20px;
            }
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
      <div class="brand">
<<<<<<< HEAD
        <img src="images/logo.jpg" alt="Rasa Laut Nusantara Logo">
=======
        <img src="images/logo.png">
>>>>>>> bcb20222c5ecc8e3de2ed33f6763055bb4151145
        <div class="logo">Rasa Laut Nusantara</div>
      </div>
      <nav class="main-nav">
        <a href="index.php">Beranda</a>
        <a href="#contactFooter">Contact</a>
      </nav>
    </div>
</header>

<div class="about-section">
    <h2>Tentang Kami</h2>
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
        <h3>Tim Kami</h3>
        <div class="team-members">

            <div class="member">
                <img src="images/satya.jpg" alt="Satya">
                <strong>Satya</strong>
                <p>Head Chef</p>
            </div>

            <div class="member">
                <img src="images/rozi.jpg" alt="Rozikin">
                <strong>Rozikin</strong>
                <p>Manager Operasional</p>
            </div>

            <div class="member">
                <img src="images/budi.jpg" alt="Budi">
                <strong>Budi</strong>
                <p>Barista & Mixologist</p>
            </div>

            <div class="member">
                <img src="images/aisyah.jpg" alt="Aisyah">
                <strong>Aisyah</strong>
                <p>Customer Relations</p>
            </div>

            <div class="member">
                <img src="images/brian.jpg" alt="Brian">
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
        <p><strong>Telepon:</strong> +62 877-2402-5788</p>
        <p><strong>Email:</strong> private.media165@gmail.com</p>
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
      &copy; 2024 Rasa Laut Nusantara — Semua Hak Dilindungi.
    </div>
</footer>

</body>
</html>