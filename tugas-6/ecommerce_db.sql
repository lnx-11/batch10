-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2026
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Hapus database lama jika sudah ada agar tidak error "already exists"
--
DROP DATABASE IF EXISTS `ecommerce_db`;
CREATE DATABASE `ecommerce_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ecommerce_db`;

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_produk` varchar(255) NOT NULL,
  `kategori` varchar(100) DEFAULT 'Umum',
  `harga` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `stok` int(11) DEFAULT 10,
  `image` varchar(255) DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products` (Data dari Tugas 5)
--

INSERT INTO `products` (`id`, `nama_produk`, `kategori`, `harga`, `deskripsi`, `stok`, `image`, `label`, `color`) VALUES
(1, 'Headphone Studio One', 'Teknologi', 649000, 'Suara detail, nyaman dipakai seharian.', 15, 'assets/images/headphone-studio.jpg', 'Favorit', '#e7e5df'),
(2, 'Lampu Meja Arc', 'Rumah', 429000, 'Cahaya hangat untuk sudut yang tenang.', 20, 'assets/images/lampu-meja.jpg', 'Pilihan editor', '#e7e7df'),
(3, 'Jam Tangan Field', 'Gaya hidup', 789000, 'Desain sederhana, siap ikut ke mana saja.', 10, 'assets/images/jam-tangan.jpg', 'Baru', '#e5e8e3'),
(4, 'Sneaker Everyday', 'Gaya hidup', 579000, 'Ringan untuk langkah panjang dan santai.', 25, 'assets/images/sneaker.jpg', 'Terlaris', '#eee4dc'),
(5, 'Tumbler Transit', 'Gaya hidup', 189000, 'Menjaga minuman tetap pas sepanjang hari.', 50, 'assets/images/tumbler.jpg', '', '#e2e8e8'),
(6, 'Kamera Pocket 35', 'Teknologi', 1199000, 'Momen sehari-hari, tersimpan lebih berkesan.', 8, 'assets/images/kamera-pocket.jpg', 'Edisi pilihan', '#e7e4dc'),
(7, 'Vas Bentuk Sora', 'Rumah', 249000, 'Siluet organik untuk meja dan rak favorit.', 12, 'assets/images/vas-sora.jpg', '', '#e8e3df'),
(8, 'Tas Harian Transit', 'Aksesori', 359000, 'Ruang cukup untuk semua yang penting.', 30, 'assets/images/tas-transit.jpg', '', '#e6e7df');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`) VALUES
(1, 'Budi Santoso', 'budi@example.com', 'password123'),
(2, 'Siti Aminah', 'siti@example.com', 'rahasia456');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `product_id`, `quantity`, `total`) VALUES
(1, 1, 1, 1, 649000),
(2, 2, 5, 2, 378000);

--
-- Constraints untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- ============================================================
-- QUERY CRUD (Create, Read, Update, Delete) DATA PRODUK
-- ============================================================

-- A. CREATE (Menambahkan produk baru ke katalog)
INSERT INTO `products` (`nama_produk`, `kategori`, `harga`, `deskripsi`, `stok`, `image`, `label`, `color`)
VALUES ('Keyboard Mechanical Mini', 'Teknologi', 850000, 'Ketik lebih nyaman dengan layout ringkas.', 15, 'assets/images/keyboard.jpg', 'Baru', '#e7e5df');

-- B. READ (Membaca / Menampilkan data produk)
-- Membaca seluruh data produk
SELECT * FROM `products`;

-- Membaca produk berdasarkan kategori tertentu
SELECT * FROM `products` WHERE `kategori` = 'Teknologi';

-- C. UPDATE (Mengubah data produk berdasarkan ID)
UPDATE `products` 
SET `harga` = 599000, `stok` = 18 
WHERE `id` = 1;

-- D. DELETE (Menghapus data produk berdasarkan ID)
DELETE FROM `products` WHERE `id` = 9;