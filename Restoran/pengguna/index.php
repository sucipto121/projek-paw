<?php
// index.php - load menu from DB (fallback to show all if status filter hides rows)
require_once __DIR__ . '/../admin/koneksi.php';

$products = [];
$categories = ["All"];
$debug_warning = '';

// primary query: only show items with status 'tersedia'
$sql = "SELECT m.id_menu, m.nama_menu, m.deskripsi, m.harga, m.foto, m.status, m.stok, k.nama_kategori FROM menu m LEFT JOIN kategori k ON m.id_kategori = k.id_kategori WHERE m.status = 'tersedia' ORDER BY m.nama_menu ASC";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $img = '';
    if (!empty($row['foto'])) {
      $img = (strpos($row['foto'], '/') !== false) ? $row['foto'] : ('images/' . $row['foto']);
    } else {
      $img = 'images/default-food.jpg';
    }
    $cat = $row['nama_kategori'] ?: 'Uncategorized';
    $products[] = [
      'id' => (int)$row['id_menu'],
      'title' => $row['nama_menu'],
      'price' => (float)$row['harga'],
      'img' => $img,
      'category' => $cat,
      'description' => $row['deskripsi'] ?? '',
      'stock' => isset($row['stok']) ? (int)$row['stok'] : null
    ];
    if (!in_array($cat, $categories)) $categories[] = $cat;
  }
} else {
  $debug_warning = 'Query failed: ' . $mysqli->error;
}

// fallback: if nothing found (often because status values differ), load all menu rows
if (empty($products)) {
  $sql2 = "SELECT m.id_menu, m.nama_menu, m.deskripsi, m.harga, m.foto, m.status, m.stok, k.nama_kategori FROM menu m LEFT JOIN kategori k ON m.id_kategori = k.id_kategori ORDER BY m.nama_menu ASC";
  $stmt2 = $mysqli->prepare($sql2);
  if ($stmt2) {
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($row = $res2->fetch_assoc()) {
      $img = '';
      if (!empty($row['foto'])) {
        $img = (strpos($row['foto'], '/') !== false) ? $row['foto'] : ('images/' . $row['foto']);
      } else {
        $img = 'images/default-food.jpg';
      }
      $cat = $row['nama_kategori'] ?: 'Uncategorized';
      $products[] = [
        'id' => (int)$row['id_menu'],
        'title' => $row['nama_menu'],
        'price' => (float)$row['harga'],
        'img' => $img,
        'category' => $cat,
        'description' => $row['deskripsi'] ?? '',
        'stock' => isset($row['stok']) ? (int)$row['stok'] : null
      ];
      if (!in_array($cat, $categories)) $categories[] = $cat;
    }
    $debug_warning = ($debug_warning === '') ? 'No items matched status=tersedia; showing all items.' : $debug_warning . ' — fallback loaded all items.';
  } else {
    if ($debug_warning === '') $debug_warning = 'Fallback query failed: ' . $mysqli->error;
  }
}

// normalize and sort categories (All first, then alphabetically)
$otherCats = array_values(array_unique(array_filter($categories, function($c){ return $c !== 'All'; })));
usort($otherCats, function($a,$b){ return strcasecmp($a,$b); });
$categories = array_merge(['All'], $otherCats);

// sort products by category then by title for consistent display
usort($products, function($a, $b){
  $c = strcasecmp($a['category'] ?? '', $b['category'] ?? '');
  if ($c === 0) return strcasecmp($a['title'] ?? '', $b['title'] ?? '');
  return $c;
});
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
          // load recent testimonials from database instead of JSON
          $testimonials = [];
          try {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
              $q = $mysqli->prepare('SELECT u.komentar AS review, u.rating, p.nama FROM ulasan u LEFT JOIN pembeli p ON u.id_pembeli = p.id_pembeli ORDER BY u.tanggal DESC LIMIT 6');
              if ($q) {
                $q->execute();
                $res_t = $q->get_result();
                while ($r = $res_t->fetch_assoc()) {
                  $testimonials[] = $r;
                }
                $q->close();
              }
            }
          } catch (Throwable $e) {
            // ignore and fall back to empty testimonials
          }

          if (!empty($testimonials)) {
            foreach ($testimonials as $t) {
              echo '<div class="single-testimoni">';
              echo '<strong>'.htmlspecialchars($t['nama'] ?? 'Anonim').'</strong>';
              if (isset($t['rating']) && $t['rating'] !== null) {
                echo ' <span class="rating">';
                for ($s = 0; $s < (int)$t['rating']; $s++) echo '★';
                echo '</span>';
              }
              echo '<p>'.htmlspecialchars($t['review'] ?? '').'</p>';
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

      <?php if (!empty($debug_warning)): ?>
        <div class="card" style="background:#fff3cd;border:1px solid #ffeeba;padding:10px;margin-bottom:12px;color:#856404">
          <strong>Info:</strong> <?= htmlspecialchars($debug_warning) ?> — Found <?= count($products) ?> item(s).
        </div>
      <?php endif; ?>

      <div class="controls">
        <input id="searchInput" type="search" placeholder="Cari menu...">
        <select id="sortSel">
          <option value="default">Sort: Default</option>
          <option value="price-asc">Price ↑</option>
          <option value="price-desc">Price ↓</option>
        </select>
      </div>

      <div id="grid" class="grid">
        <?php foreach($products as $p):
            $cat = isset($p['category']) ? $p['category'] : 'Uncategorized';
            $price = isset($p['price']) ? floatval($p['price']) : 0;
            $imgSrc = !empty($p['img']) ? $p['img'] : 'images/default-food.jpg';
            $title = !empty($p['title']) ? $p['title'] : 'Nama menu tidak tersedia';
        ?>
          <article class="card product" data-cat="<?=htmlspecialchars($cat)?>" data-price="<?= $price ?>" data-stock="<?= isset($p['stock']) ? (int)$p['stock'] : 0 ?>">
            <div class="img-wrap"><img src="<?=htmlspecialchars($imgSrc)?>" alt="<?=htmlspecialchars($title)?>"></div>
            <div class="card-body">
              <h4><?=htmlspecialchars($title)?></h4>
              <?php $stockDisplay = isset($p['stock']) ? (int)$p['stock'] : null; ?>
              <div class="stock"><?php if ($stockDisplay === null) { echo 'Stok: -'; } elseif ($stockDisplay <= 0) { echo 'Habis'; } else { echo 'Stok: ' . $stockDisplay . ' pcs'; } ?></div>
              <div class="meta">
                <span class="price">Rp <?=number_format($price, 0, ',', '.')?></span>
                <button class="btn small" <?php if ($stockDisplay !== null && $stockDisplay <= 0) echo 'disabled'; ?>>Tambah ke keranjang</button>
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
