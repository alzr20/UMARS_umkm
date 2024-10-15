-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 28 Agu 2024 pada 00.40
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `umkm`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`) VALUES
(42, 7, 11, 1),
(46, 6, 11, 5),
(47, 6, 13, 8),
(48, 6, 17, 9),
(49, 6, 16, 8),
(54, 6, 14, 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `order_date` datetime DEFAULT NULL,
  `shipping_date` datetime DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `proof_of_transfer` varchar(255) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `refund` varchar(255) DEFAULT 'none',
  `note` text DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `status`, `order_date`, `shipping_date`, `total`, `proof_of_transfer`, `shipping_address`, `refund`, `note`) VALUES
(1, 2, 'accepted', NULL, NULL, 340000.00, 'Mari Bermain.png', NULL, 'none', 'none'),
(2, 2, 'accepted', NULL, '2024-07-26 00:00:00', 160000.00, 'Screenshot (3).png', 'Matraman', 'none', 'none'),
(3, 2, 'rejected', NULL, '2024-07-28 00:00:00', 320000.00, 'Screenshot (6).png', 'disana saja', 'none', 'none'),
(4, 2, 'accepted', NULL, '2024-07-26 00:00:00', 425000.00, 'Screenshot (1).png', 'Coba COba', 'none', 'none'),
(5, 2, 'accepted', NULL, '2024-07-27 00:00:00', 570000.00, 'Screenshot (11).png', 'matraman', 'none', 'none'),
(6, 2, 'rejected', NULL, '2024-07-29 00:00:00', 80000.00, 'Screenshot (23).png', 'kokokokoko', 'none', 'none'),
(7, 2, 'pending', NULL, '2024-07-31 00:00:00', 80000.00, 'Screenshot (41).png', 'xing pin', 'none', 'none'),
(8, 5, 'rejected', NULL, '2024-08-02 00:00:00', 175000.00, 'Screenshot (10).png', 'Jalan Matraman', 'none', 'none'),
(9, 2, 'accepted', NULL, '2024-08-02 12:40:00', 1035000.00, 'Screenshot (22).png', 'lenteng agung', 'none', 'none'),
(10, 5, 'pending', NULL, '2024-08-03 11:45:00', 635000.00, 'Screenshot (41).png', 'ancol', 'none', 'none'),
(11, 6, 'rejected', NULL, '2024-08-03 11:00:00', 510000.00, 'Screenshot (27).png', 'Pulau Terapung', 'none', 'none'),
(12, 6, 'accepted', '2024-07-28 15:09:51', '2024-08-10 15:10:00', 400000.00, 'Screenshot (16).png', 'Oman', 'none', 'none'),
(13, 2, 'rejected', '2024-07-28 15:39:17', '2024-08-10 13:45:00', 80000.00, 'Screenshot (26).png', 'dadadadadad', 'none', 'none'),
(14, 6, 'rejected', '2024-07-29 01:07:56', '2024-08-09 12:08:00', 300000.00, 'Screenshot (27).png', 'padang', 'none', 'none'),
(15, 2, 'accepted', '2024-07-29 05:56:06', '2024-08-03 15:00:00', 42000.00, 'Screenshot (4).png', 'matraman', 'none', 'none'),
(16, 2, 'accepted', '2024-07-29 07:49:56', '2024-08-14 13:50:00', 715000.00, 'ERD.jpg', 'Kota Tua', 'none', 'none'),
(17, 2, 'accepted', '2024-08-01 08:33:20', '2024-08-10 15:30:00', 105000.00, 'MgR.jpg', 'JL . MATRAMAN JAYA NO . 14', 'none', 'none'),
(18, 2, 'accepted', '2024-08-10 01:50:10', '2024-08-24 10:50:00', 155000.00, 'UPI-YAI.jpg', 'Halimun', 'none', 'none'),
(19, 11, 'accepted', '2024-08-13 09:41:51', '2024-08-17 16:45:00', 100000.00, 'Mari Bermain.png', 'Tambak', 'bukti udh isi dari 22.35.png', NULL),
(20, 11, 'accepted', '2024-08-16 02:15:14', '2024-08-20 10:20:00', 560000.00, 'C.png', 'Jakarta Pusat - Jalan Matraman Jaya No D14', 'none', NULL),
(21, 11, 'pending', '2024-08-16 02:51:07', '2024-08-31 12:55:00', 600000.00, 'Screenshot (3).png', 'Jakarta Barat - taman kota', 'none', 'none');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(17, 14, 11, 50, 6000.00),
(18, 15, 11, 7, 6000.00),
(19, 16, 11, 15, 6000.00),
(20, 16, 13, 4, 85000.00),
(21, 16, 14, 3, 95000.00),
(22, 17, 11, 5, 6000.00),
(23, 17, 14, 15, 5000.00),
(24, 18, 18, 20, 4000.00),
(25, 18, 14, 15, 5000.00),
(26, 19, 17, 10, 6000.00),
(27, 19, 18, 10, 4000.00),
(28, 20, 18, 20, 4000.00),
(29, 20, 17, 30, 6000.00),
(30, 20, 11, 50, 6000.00),
(31, 21, 13, 80, 7500.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `image_url`) VALUES
(11, 'Roti Coklat', 'roti denga isi coklat yang premium dengan harga murah', 6000.00, 'Gambar WhatsApp 2024-07-28 pukul 15.59.08_99a77747.jpg'),
(13, 'Roti Keju', 'Roti dengan chedar keju yang melimpah', 7500.00, 'Gambar WhatsApp 2024-07-28 pukul 15.59.06_66cd03a1.jpg'),
(14, 'Risol Sayur', 'isian dengan wortel dan kentang', 5000.00, 'Gambar WhatsApp 2024-07-29 pukul 14.32.31_844ce2dd.jpg'),
(16, 'Pastel', 'suka dengan bihun dan sayur-sayuran ini dia pastel dari kami dengan isian yang banyak', 5500.00, 'Gambar WhatsApp 2024-07-29 pukul 14.32.31_bea06940.jpg'),
(17, 'Pie Buah', 'pie dengan toping buah yang masih segar', 6000.00, 'Gambar WhatsApp 2024-07-30 pukul 21.05.35_e3e53065.jpg'),
(18, 'Eclair', 'Toping coklat yang meleleh dan isi krim yang banyak dan manis', 4000.00, 'Gambar WhatsApp 2024-08-04 pukul 18.56.17_7ccab646.jpg'),
(19, 'Kue SUS', 'krim yang banyak dan juga yang halus', 4000.00, 'Gambar WhatsApp 2024-08-04 pukul 18.56.17_26d11b65.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `role` enum('admin','customer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `phone_number`, `role`) VALUES
(1, 'Admin123', '$2y$10$KtlhfGqCeBj166PtQv.Z8OdnnbQ1LJ5iG9F6i4mjBqidwSd.ndLOy', 'admin@exaple.com', NULL, 'admin'),
(2, 'Najib123', '$2y$10$dVkC52CQEYOBsoKSJH.1LeFohYl/QobOYlZWrS0XsThQ8dlrgXECC', 'najib@example.com', NULL, 'customer'),
(5, 'Alzr666', '$2y$10$F2Ol34A7QD8Vo0tc2eZAPOII169u.g6deJeNA8jsXnoaJcllmBmmu', 'alzr@gmail.com', NULL, 'customer'),
(6, '26Najib', '$2y$10$aiK3jCGQJjhAhC9fgOQ5HeQkIaqKKet99cr7R3ztInLKPxkNEEHWa', '26najib@gmail.com', '085780553251', 'customer'),
(7, 'Khoiri26', '$2y$10$.Ju.Qq7DtzpK/OeoMehYFO5sIPCd6tcgSO7G95/fgd6aoTEMxRsK2', 'khoiri@gmail.com', '021536987153', 'customer'),
(11, 'Bonang123', '$2y$10$o0VkDbgWphas8MYkjtffruvYcgNQV9M5gFbr3T7rBKjXV2tC5IiTe', 'bonang@gmail.com', '021555446399', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
