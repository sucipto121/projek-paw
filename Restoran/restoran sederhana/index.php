<?php
session_start();
if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

/* --- Ambil Kategori --- */
$categories = [];
$qCat = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
while ($c = mysqli_fetch_assoc($qCat)) {
    $categories[] = [
        "id" => $c["id_kategori"],
        "name" => $c["nama_kategori"]
    ];
}

/* --- Ambil Produk --- */
$products = [];
$q = mysqli_query($conn, "
    SELECT m.*, k.nama_kategori 
    FROM menu m
    JOIN kategori k ON k.id_kategori = m.id_kategori
");

while ($row = mysqli_fetch_assoc($q)) {
    $products[] = [
        "id" => $row["id_menu"],
        "title" => $row["nama_menu"],
        "price" => $row["harga"],
        "img" => $row["foto"],
        "category_id" => $row["id_kategori"],
        "category_name" => $row["nama_kategori"]
    ];
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Restoran — Menu</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <div class="brand">
      <div class="logo">Rasa Laut Nusantara</div>
      <nav class="main-nav">
        <a href="#" id="home">Beranda</a>
        <a href="#" id="ourMenu">Menu Kami</a>
        <a href="about.php">Tentang Kami</a>
        <a href="#" id="contact">Contact</a>
      </nav>
    </div>
    <div class="actions">
      <button class="btn ghost" id="searchBtn">Cari</button>
      <button class="btn primary" id="orderNowBtn">Pesan Sekarang</button>
    </div>
  </div>
</header>

<!-- Hero -->
<div class="hero container" id="heroContainer">
  <div class="hero-left">
    <p class="kicker">MAKAN DI TEMPAT</p>
    <h1>Rasa yang berbicara lebih keras daripada kata-kata!</h1>
    <p class="lead">Cara kreatif untuk menyampaikan bahwa rasa lebih kuat dari deskripsi.</p>
    <div class="hero-cta"><button class="btn ghost">Proses Pesanan</button></div>

    <ul class="stats">
      <li><strong>06</strong><span>Remake</span></li>
      <li><strong>10</strong><span>Penghargaan</span></li>
      <li><strong>20</strong><span>Cabang</span></li>
    </ul>
  </div>

  <div class="hero-right">
    <div class="pizza-wrap">
      <img src="images/restoran.jpg" alt="Hero food">
    </div>
  </div>
</div>

<main class="container main">

  <aside class="sidebar">
    <!-- Kategori -->
    <div class="card">
      <h3>Kategori</h3>
      <div class="categories" id="categories">
        <?php foreach($categories as $c): ?>
          <button class="chip" data-cat="<?=htmlspecialchars($c['id'])?>">
            <?=htmlspecialchars($c['name'])?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Highlights -->
    <div class="card">
      <h3>Highlights</h3>
      <p>Kami membuat makanan lezat dan beraroma menggunakan bahan organik.</p>
    </div>

    <!-- Testimoni -->
    <div class="card">
      <h3>Testimoni Pelanggan</h3>
      <div class="testi-slider" id="testiSlider">
        <?php
        $file = 'testimoni.json';
        if(file_exists($file)){
            $testi_data = json_decode(file_get_contents($file), true);
            foreach($testi_data as $t){
                echo '<div class="single-testimoni">';
                echo '<strong>'.htmlspecialchars($t['name']).'</strong>';
                echo '<p>'.htmlspecialchars($t['review']).'</p>';
                echo '</div>';
            }
        } else {
            echo "<div class='single-testimoni'><p>Belum ada testimoni.</p></div>";
        }
        ?>
      </div>
      <div class="slider-controls">
        <button id="prevTesti">&lt;</button>
        <button id="nextTesti">&gt;</button>
      </div>
      <a href="testimoni.php" class="btn small" style="display:block;margin-top:10px;">Kirim Testimoni</a>
    </div>
  </aside>

  <section class="content" id="menuSection">
    <div class="menu-intro">
      <h2>Menu Kami</h2>
      <p>Cita rasa hidangannya kaya, bersemangat, dan berkesan.</p>
    </div>

    <div class="controls">
      <input id="searchInput" type="search" placeholder="Cari menu...">
      <select id="sortSel">
        <option value="default">Sort: Default</option>
        <option value="price-asc">Price ↑</option>
        <option value="price-desc">Price ↓</option>
      </select>
    </div>

    <div id="grid" class="grid">
      <?php foreach($products as $p): ?>
        <article class="card product"
          data-cat="<?=htmlspecialchars($p['category_id'])?>"
          data-price="<?=floatval($p['price'])?>">

          <div class="img-wrap">
            <img src="<?=htmlspecialchars($p['img'])?>" alt="">
          </div>

          <div class="card-body">
            <h4><?=htmlspecialchars($p['title'])?></h4>

            <div class="meta">
              <span class="price">Rp <?=number_format($p['price'], 0, ',', '.')?></span>
              <button class="btn small">Tambah ke keranjang</button>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

</main>

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

  <div class="copy">&copy; <?=date('Y')?> Rasa Laut Nusantara — Semua Hak Dilindungi.</div>
</footer>

<script>
  const PRODUCTS = <?php echo json_encode($products); ?>;
</script>

<!-- Panel Keranjang -->
<div id="cartPanel" class="cart-panel">
  <div class="cart-header">
    <h3>Pesanan Anda</h3>
    <button id="closeCart" class="btn small">X</button>
  </div>

  <div id="cartItems"></div>

  <div class="cart-footer">
    <p><strong>Total:</strong> Rp <span id="cartTotal">0</span></p>

    <label class="input-label">Nama Pemesan:</label>
    <input type="text" id="customerName" class="input-box" placeholder="Masukkan nama">

    <label class="input-label">Nomor Meja:</label>
    <input type="number" id="tableNumber" class="input-box" placeholder="Contoh: 12" min="1">

    <button id="checkoutBtn" class="btn primary" style="width:100%;margin-top:14px;">
      Checkout Pesanan
    </button>
  </div>
</div>

<button id="openCart" class="cart-float-btn">🛒 Keranjang</button>

<script src="script.js"></script>

</body>
</html>
