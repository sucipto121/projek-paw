<?php
// index.php
// Data produk sederhana
$products = [
    ["id"=>1,"title"=>"Cumi Goreng Tepung","price"=>60000,"img"=>"images/cumi.jpg","category"=>"Seafood"],
    ["id"=>2,"title"=>"Kepiting Asam Manis","price"=>45000,"img"=>"images/kepiting.jpg","category"=>"Seafood"],
    ["id"=>3,"title"=>"Udang Saus Mentega","price"=>35000,"img"=>"images/udang.jpg","category"=>"Seafood"],
    ["id"=>4,"title"=>"Kakap Merah Bakar","price"=>70000,"img"=>"images/kakap.jpg","category"=>"Seafood"],
    ["id"=>5,"title"=>"Lemon Tea","price"=>4000,"img"=>"images/lemon.jpg","category"=>"Minuman"],
    ["id"=>6,"title"=>"Jus Melon","price"=>6000,"img"=>"images/melon.jpg","category"=>"Minuman"],
    ["id"=>7,"title"=>"Kelapa Muda","price"=>8000,"img"=>"images/kelapa.jpg","category"=>"Minuman"],
    ["id"=>8,"title"=>"Cappuccino","price"=>10000,"img"=>"images/cappucinno.jpg","category"=>"Minuman"],
];

$categories = ["All","Seafood","Minuman"];
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
      <p class="lead">Cara yang kreatif dan mengesankan untuk menyampaikan gagasan bahwa rasa dan pengalaman makanan atau produk melampaui deskripsi belaka.</p>
      <div class="hero-cta">
        <button class="btn ghost">Proses Pesanan</button>
      </div>
      <ul class="stats">
        <li><strong>06</strong><span>Remake yang Tercapai</span></li>
        <li><strong>10</strong><span>Penghargaan</span></li>
        <li><strong>20</strong><span>Cabang di Seluruh Indonesia</span></li>
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
      <div class="card">
        <h3>Kategori</h3>
        <div class="categories" id="categories">
          <?php foreach($categories as $c): ?>
            <button class="chip" data-cat="<?=htmlspecialchars($c)?>"><?=htmlspecialchars($c)?></button>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="card">
        <h3>Highlights</h3>
        <p>Kami membuat makanan lezat dan beraroma menggunakan bahan-bahan organik.</p>
      </div>

      <div class="card">
        <h3>Testimoni Pelanggan</h3>
        <div class="testi-slider" id="testiSlider">
            <?php
            $file = 'testimoni.json';
            if(file_exists($file)){
                $testi_data = json_decode(file_get_contents($file), true);
                // tampilkan semua testimoni
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
          <article class="card product" data-cat="<?=htmlspecialchars($p['category'])?>" data-price="<?=floatval($p['price'])?>">
            <div class="img-wrap"><img src="<?=htmlspecialchars($p['img'])?>" alt=""></div>
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

    <div class="copy">
      &copy; <?=date('Y')?> Rasa Laut Nusantara — Semua Hak Dilindungi.
    </div>
  </footer>


  <script>
    const PRODUCTS = <?php echo json_encode($products); ?>;
  </script>

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
