CREATE DATABASE IF NOT EXISTS restoran_db;
USE restoran_db;

CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100),
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pembeli (
    id_pembeli INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100),
    deskripsi TEXT
);

CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT,
    nama_menu VARCHAR(150),
    deskripsi TEXT,
    harga DECIMAL(10,2),
    stok INT DEFAULT 0,
    foto VARCHAR(255),
    status ENUM('tersedia','habis') DEFAULT 'tersedia',
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
);

CREATE TABLE keranjang (
    id_keranjang INT AUTO_INCREMENT PRIMARY KEY,
    id_pembeli INT,
    id_menu INT,
    jumlah INT,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pembeli) REFERENCES pembeli(id_pembeli),
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
);

CREATE TABLE pesanan (
    id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(50),
    id_pembeli INT,
    metode_order ENUM('dine_in','takeaway','delivery'),
    status_pesanan ENUM('pending','diproses','dimasak','selesai','batal') DEFAULT 'pending',
    total_harga DECIMAL(10,2),
    bayar DECIMAL(10,2),
    kembalian DECIMAL(10,2),
    tanggal_pesanan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pembeli) REFERENCES pembeli(id_pembeli)
);

CREATE TABLE detail_pesanan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan INT,
    id_menu INT,
    jumlah INT,
    harga_satuan DECIMAL(10,2),
    subtotal DECIMAL(10,2),
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan),
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
);

CREATE TABLE pembayaran (
    id_bayar INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan INT,
    metode_bayar ENUM('tunai','qris','transfer'),
    total_bayar DECIMAL(10,2),
    bukti_bayar VARCHAR(255),
    status_bayar ENUM('menunggu','berhasil','gagal') DEFAULT 'menunggu',
    tanggal_bayar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan)
);

CREATE TABLE ulasan (
    id_ulasan INT AUTO_INCREMENT PRIMARY KEY,
    id_pembeli INT,
    id_menu INT,
    rating INT CHECK(rating BETWEEN 1 AND 5),
    komentar TEXT,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pembeli) REFERENCES pembeli(id_pembeli),
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
);

CREATE TABLE laporan_harian (
    id_laporan INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE,
    total_transaksi INT,
    pendapatan DECIMAL(10,2)
);
