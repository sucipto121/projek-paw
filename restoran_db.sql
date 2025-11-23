-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 23, 2025 at 08:56 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restoran_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nama`, `password`, `created_at`, `username`) VALUES
(6, 'Admin Utama', '$2y$10$6f/6MYN9DNhjEsrfomRbouAVINnEM.rckKSDyN90x9DdzkwvhKmVO', '2025-11-22 10:34:59', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int NOT NULL,
  `id_pesanan` int DEFAULT NULL,
  `id_menu` int DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `harga_satuan` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_menu`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(10, 26, 15, 4, '6000.00', '24000.00'),
(12, 26, 4, 2, '60000.00', '120000.00'),
(13, 26, 9, 3, '35000.00', '105000.00'),
(14, 26, 8, 3, '45000.00', '135000.00'),
(15, 27, 14, 8, '6000.00', '48000.00'),
(16, 27, 15, 4, '6000.00', '24000.00'),
(17, 27, 12, 5, '4000.00', '20000.00'),
(18, 27, 4, 2, '60000.00', '120000.00'),
(19, 27, 9, 3, '35000.00', '105000.00'),
(21, 28, 15, 8, '6000.00', '48000.00'),
(22, 28, 13, 9, '6000.00', '54000.00'),
(23, 28, 4, 1, '60000.00', '60000.00'),
(24, 28, 9, 1, '35000.00', '35000.00'),
(25, 28, 8, 1, '45000.00', '45000.00'),
(26, 29, 4, 1, '60000.00', '60000.00');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) DEFAULT NULL,
  `deskripsi` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Seafood', 'Makanan seafood adalah segala jenis hewan dan tumbuhan laut yang bisa dimakan, seperti ikan, udang, kepiting, cumi-cumi, kerang, dan tiram. Makanan ini merupakan sumber protein dan omega-3 yang baik untuk kesehatan, namun bisa menjadi pemicu alergi bagi sebagian orang.'),
(2, 'Minuman', 'Menu minuman ini menyajikan berbagai pilihan yang menggugah selera untuk menemani hidangan Anda. Segarkan diri dengan Es Kelapa Muda yang manis dan alami, atau pilih Es Jus Melon yang kaya vitamin dengan rasa manis buah melon segar. Untuk sensasi asam-manis yang menyegarkan, nikmati Es Lemon Tea yang dibuat dari perpaduan teh dan lemon pilihan. Bagi pecinta kopi, tersedia Kopi Hitam Dingin dengan cita rasa kuat dan aroma khas yang menyegarkan di lidah.');

-- --------------------------------------------------------

--
-- Table structure for table `laporan_harian`
--

CREATE TABLE `laporan_harian` (
  `id_laporan` int NOT NULL,
  `tanggal` date DEFAULT NULL,
  `total_transaksi` int DEFAULT NULL,
  `pendapatan` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `laporan_harian`
--

INSERT INTO `laporan_harian` (`id_laporan`, `tanggal`, `total_transaksi`, `pendapatan`) VALUES
(1, '2025-11-23', 3, '522000.00'),
(2, '2025-11-22', 1, '452000.00');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id_menu` int NOT NULL,
  `id_kategori` int DEFAULT NULL,
  `nama_menu` varchar(150) DEFAULT NULL,
  `deskripsi` text,
  `harga` decimal(10,2) DEFAULT NULL,
  `stok` int DEFAULT '0',
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('tersedia','habis') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id_menu`, `id_kategori`, `nama_menu`, `deskripsi`, `harga`, `stok`, `foto`, `status`) VALUES
(4, 1, 'Cumi Goreng Tepung', 'Enak pedas', '60000.00', 5, 'cumi.jpg', 'tersedia'),
(8, 1, 'Kepiting Asam Manis', 'Enak Asam Manis', '45000.00', 16, 'kepiting.jpg', 'tersedia'),
(9, 1, 'Udang Saus Mentega', 'Mantab dan pedas', '35000.00', 10, 'udang.jpg', 'tersedia'),
(11, 1, 'Kakap Merah Bakar', 'Mantab dan pedas', '70000.00', 25, 'kakap.jpg', 'tersedia'),
(12, 2, 'Lemon Tea', 'ES SEGER', '4000.00', 45, 'lemon.jpg', 'tersedia'),
(13, 2, 'Jus Melon', 'ES SEGER', '6000.00', 30, 'melon.jpg', 'tersedia'),
(14, 2, 'Kelapa Muda', 'ES SEGER', '6000.00', 31, 'kelapa.jpg', 'tersedia'),
(15, 2, 'Cappuccino', 'kopi mantab', '6000.00', 28, 'cappucinno.jpg', 'tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_bayar` int NOT NULL,
  `id_pesanan` int DEFAULT NULL,
  `metode_bayar` enum('tunai','qris','transfer') DEFAULT NULL,
  `total_bayar` decimal(10,2) DEFAULT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `status_bayar` enum('menunggu','berhasil','gagal') DEFAULT 'menunggu',
  `tanggal_bayar` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_bayar`, `id_pesanan`, `metode_bayar`, `total_bayar`, `bukti_bayar`, `status_bayar`, `tanggal_bayar`) VALUES
(1, 30, 'tunai', '10000.00', NULL, 'berhasil', '2025-11-23 20:41:18'),
(2, 29, 'tunai', '70000.00', NULL, 'berhasil', '2025-11-23 20:41:49');

-- --------------------------------------------------------

--
-- Table structure for table `pembeli`
--

CREATE TABLE `pembeli` (
  `id_pembeli` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pembeli`
--

INSERT INTO `pembeli` (`id_pembeli`, `nama`, `created_at`) VALUES
(7, 'aku', '2025-11-22 15:39:41'),
(8, 'buddddddd', '2025-11-22 18:48:31'),
(9, 'Meja 1', '2025-11-22 19:21:15'),
(10, 'budi', '2025-11-22 19:21:15'),
(11, 'rudy', '2025-11-22 19:21:15'),
(12, 'haya', '2025-11-22 19:21:15'),
(13, 'samuel', '2025-11-22 19:21:15'),
(14, 'assa', '2025-11-22 19:21:15'),
(15, 'riislo', '2025-11-22 20:47:59'),
(16, 'marno', '2025-11-23 03:52:54'),
(17, 'skkk', '2025-11-23 03:56:33'),
(18, 'yoi bto', '2025-11-23 04:47:02');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int NOT NULL,
  `kode_pesanan` varchar(50) DEFAULT NULL,
  `id_pembeli` int DEFAULT NULL,
  `metode_order` enum('dine_in','takeaway','delivery') DEFAULT NULL,
  `status_pesanan` enum('pending','diproses','dimasak','selesai','batal') DEFAULT 'pending',
  `total_harga` decimal(10,2) DEFAULT NULL,
  `bayar` decimal(10,2) DEFAULT NULL,
  `kembalian` decimal(10,2) DEFAULT NULL,
  `tanggal_pesanan` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `kode_pesanan`, `id_pembeli`, `metode_order`, `status_pesanan`, `total_harga`, `bayar`, `kembalian`, `tanggal_pesanan`) VALUES
(26, 'ORD46F3EA', 10, 'dine_in', 'selesai', '452000.00', '0.00', '0.00', '2025-11-22 19:44:52'),
(27, 'cb39ae338a43e8ea26de75f089d6e874', 10, 'dine_in', 'selesai', '452000.00', '0.00', '0.00', '2025-11-22 12:44:52'),
(28, 'ORDF0288E', 15, 'dine_in', 'batal', '242000.00', '0.00', '0.00', '2025-11-22 20:47:59'),
(29, 'ORD1EB4FC', 17, 'dine_in', 'selesai', '60000.00', '70000.00', '10000.00', '2025-11-23 03:56:33'),
(30, 'ORD69D1C4', 18, 'dine_in', 'selesai', '10000.00', '10000.00', '0.00', '2025-11-23 04:47:02');

-- --------------------------------------------------------

--
-- Table structure for table `ulasan`
--

CREATE TABLE `ulasan` (
  `id_ulasan` int NOT NULL,
  `id_pembeli` int DEFAULT NULL,
  `id_menu` int DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `komentar` text,
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `ulasan`
--

INSERT INTO `ulasan` (`id_ulasan`, `id_pembeli`, `id_menu`, `rating`, `komentar`, `tanggal`) VALUES
(1, 7, 13, 4, 'cukup puas', '2025-11-22 15:39:41'),
(2, 16, 9, 5, 'enak', '2025-11-23 03:52:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `laporan_harian`
--
ALTER TABLE `laporan_harian`
  ADD PRIMARY KEY (`id_laporan`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_bayar`);

--
-- Indexes for table `pembeli`
--
ALTER TABLE `pembeli`
  ADD PRIMARY KEY (`id_pembeli`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_pembeli` (`id_pembeli`);

--
-- Indexes for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD PRIMARY KEY (`id_ulasan`),
  ADD KEY `id_pembeli` (`id_pembeli`),
  ADD KEY `id_menu` (`id_menu`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `laporan_harian`
--
ALTER TABLE `laporan_harian`
  MODIFY `id_laporan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_bayar` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pembeli`
--
ALTER TABLE `pembeli`
  MODIFY `id_pembeli` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id_ulasan` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`),
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`);

--
-- Constraints for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD CONSTRAINT `ulasan_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`),
  ADD CONSTRAINT `ulasan_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
