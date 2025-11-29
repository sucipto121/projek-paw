<?php 
session_start(); 
if (!isset($_SESSION['user'])) {
  header("Location: login.php?err=Silakan login terlebih dahulu");
  exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard - Restoran Laut Nusantara</title>
<link rel="stylesheet" href="css/admin.css">
<style>
  * { 
    box-sizing: border-box; 
    margin: 0; 
    padding: 0; 
  }
  
  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background: #f8f9fa;
  }
  
  .main {
    padding: 32px;
    max-width: 1400px;
  }
  
  .welcome {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 32px;
    box-shadow: 0 8px 24px rgba(255, 107, 53, 0.3);
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
    position: relative;
    overflow: hidden;
  }
  
  .welcome::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
  }
  
  .welcome h2 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 8px;
    position: relative;
    z-index: 1;
  }
  
  .welcome-subtitle {
    font-size: 16px;
    opacity: 0.9;
    position: relative;
    z-index: 1;
  }
  
  .welcome-actions {
    display: flex;
    gap: 12px;
    position: relative;
    z-index: 1;
  }
  
  .btn {
    padding: 12px 28px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .btn.primary {
    background: white;
    color: #ff6b35;
    box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
  }
  
  .btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 255, 255, 0.4);
  }
  
  .btn.ghost {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
  }
  
  .btn.ghost:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
  }
  
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
  }
  
  .stat-card {
    background: white;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 2px 8px rgba(255, 107, 53, 0.08);
    transition: all 0.3s ease;
    border: 2px solid transparent;
  }
  
  .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(255, 107, 53, 0.2);
    border-color: #ffe8e0;
  }
  
  .stat-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
  }
  
  .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
  }
  
  .stat-icon.primary {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
  }
  
  .stat-icon.success {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
  }
  
  .stat-icon.info {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  }
  
  .stat-icon.warning {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
  }
  
  .stat-title {
    font-size: 13px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
  }
  
  .stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #ff6b35;
    margin-bottom: 8px;
  }
  
  .stat-change {
    font-size: 13px;
    color: #666;
  }
  
  .stat-change.positive {
    color: #2e7d32;
  }
  
  .stat-change.negative {
    color: #c62828;
  }
  
  .cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
  }
  
  .card {
    background: white;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
  }
  
  .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(255, 107, 53, 0.15);
    border-left-color: #ff6b35;
  }
  
  .card h3 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 12px;
    color: #ff6b35;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .card p {
    color: #666;
    line-height: 1.6;
    font-size: 14px;
  }
  
  .card-action {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
  }
  
  .card-link {
    color: #ff6b35;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
  }
  
  .card-link:hover {
    gap: 10px;
    color: #f7931e;
  }
  
  .quick-actions {
    background: white;
    border-radius: 16px;
    padding: 28px;
    margin-top: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  }
  
  .quick-actions h3 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #ff6b35;
  }
  
  .action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
  }
  
  .action-btn {
    padding: 16px 20px;
    background: #fafafa;
    border: 2px solid #e8e8e8;
    border-radius: 12px;
    text-decoration: none;
    color: #ff6b35;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .action-btn:hover {
    background: #fff5f0;
    border-color: #ffd4c0;
    transform: translateX(4px);
  }
  
  .action-icon {
    font-size: 24px;
  }
  
  .brand-logo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
  }
  
  @media (max-width: 768px) {
    .main {
      padding: 20px;
    }
    
    .welcome {
      flex-direction: column;
      text-align: center;
      gap: 20px;
      padding: 32px 24px;
    }
    
    .welcome h2 {
      font-size: 24px;
    }
    
    .stats-grid {
      grid-template-columns: 1fr;
    }
    
    .cards {
      grid-template-columns: 1fr;
    }
    
    .action-buttons {
      grid-template-columns: 1fr;
    }
  }
</style>
</head>
<body>

<header class="topbar">
  <div class="brand">
    <img src="images/logo.jpg" alt="Logo Restoran Laut Nusantara" class="brand-logo">
    <h1>Restoran Laut Nusantara</h1>       
  </div>
  <nav>
    <div class="top-menu">
      <a href="index.php">Dashboard</a>
      <a href="data_master.php">Data Master</a>
      <a href="transaksi.php">Transaksi</a>
      <a href="laporan.php">Laporan</a>
    </div>
  </nav>
  <div class="user">
    <div class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div>
    <div>
      <div class="name"><?= htmlspecialchars($user['nama']) ?></div>
      <a href="logout.php">Logout</a>
    </div>
  </div>
</header>

<div class="layout">
  <aside class="sidebar">
    <div class="menu">
      <a class="active" href="index.php">Home</a>
      <?php if(!empty($user['level']) && $user['level'] == 1): ?>
        <a href="data_master.php">Data Master</a>
      <?php endif; ?>
      <a href="transaksi.php">Transaksi</a>
      <a href="laporan.php">Laporan</a>
    </div>
  </aside>
  
  <main class="main">
    <div class="welcome">
      <div>
        <h2>Selamat datang, <?= htmlspecialchars($user['nama']) ?>! 👋</h2>
        <p class="welcome-subtitle">Kelola restoran Anda dengan mudah dan efisien</p>
      </div>
      <div class="welcome-actions">
        <a class="btn primary" href="logout.php">Logout</a>
      </div>
    </div>
    
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-icon primary">📊</div>
          <div class="stat-title">Total Pesanan</div>
        </div>
        <div class="stat-value">247</div>
        <div class="stat-change positive">↑ 12% dari kemarin</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-icon success">💰</div>
          <div class="stat-title">Pendapatan</div>
        </div>
        <div class="stat-value">Rp 8,5jt</div>
        <div class="stat-change positive">↑ 8% dari kemarin</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-icon info">🍽️</div>
          <div class="stat-title">Menu Aktif</div>
        </div>
        <div class="stat-value">42</div>
        <div class="stat-change">6 menu baru</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-icon warning">⏰</div>
          <div class="stat-title">Pesanan Aktif</div>
        </div>
        <div class="stat-value">8</div>
        <div class="stat-change">Sedang diproses</div>
      </div>
    </div>
    
    <div class="cards">
      <div class="card">
        <h3>📦 Ringkasan Pesanan</h3>
        <p>Menampilkan pesanan terbaru, status, dan total hari ini. Pantau semua transaksi dengan mudah.</p>
        <div class="card-action">
          <a href="transaksi.php" class="card-link">
            Lihat Semua →
          </a>
        </div>
      </div>
      
      <div class="card">
        <h3>🍴 Menu</h3>
        <p>Kelola daftar menu, harga, dan ketersediaan. Tambah, edit, atau hapus menu dengan cepat.</p>
        <div class="card-action">
          <a href="data_master.php" class="card-link">
            Kelola Menu →
          </a>
        </div>
      </div>
      
      <div class="card">
        <h3>📈 Laporan</h3>
        <p>Ekspor laporan penjualan dan analitik. Lihat performa bisnis Anda dalam satu tempat.</p>
        <div class="card-action">
          <a href="laporan.php" class="card-link">
            Lihat Laporan →
          </a>
        </div>
      </div>
    </div>
    
    <div class="quick-actions">
      <h3>⚡ Aksi Cepat</h3>
      <div class="action-buttons">
        <a href="transaksi.php" class="action-btn">
          <span class="action-icon">➕</span>
          <span>Pesanan Baru</span>
        </a>
        <a href="data_master.php" class="action-btn">
          <span class="action-icon">🍽️</span>
          <span>Tambah Menu</span>
        </a>
        <a href="laporan.php" class="action-btn">
          <span class="action-icon">📊</span>
          <span>Lihat Statistik</span>
        </a>
        <a href="#" class="action-btn">
          <span class="action-icon">⚙️</span>
          <span>Pengaturan</span>
        </a>
      </div>
    </div>
  </main>
</div>

</body>
</html>