-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Jul 2025 pada 05.35
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `red bear`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','published','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `user_id`, `title`, `content`, `image_path`, `status`, `created_at`, `updated_at`) VALUES
(2, 2, 'asdas', 'dasdwasd', NULL, 'pending', '2025-07-10 03:08:08', '2025-07-10 03:08:08'),
(3, 2, 'fdfdf', 'afafafaf', 'img/blog/686f2fa031939_ig 03.png', 'pending', '2025-07-10 03:12:32', '2025-07-10 03:12:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `public_id` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `tersedia` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `jenis` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `offline_table_sessions`
--

CREATE TABLE `offline_table_sessions` (
  `id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `guest_count` int(11) NOT NULL DEFAULT 1,
  `session_code` varchar(255) NOT NULL,
  `status` enum('occupied','vacant') DEFAULT 'occupied',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `offline_table_sessions`
--

INSERT INTO `offline_table_sessions` (`id`, `table_id`, `guest_count`, `session_code`, `status`, `created_at`) VALUES
(89, 5, 1, 'f55qcelk57op148qkrd83q3o34_5', 'vacant', '2025-06-29 12:58:51'),
(90, 7, 1, '8fp8jg4eg7gldvljdcvovtmni6_7', 'vacant', '2025-06-29 12:59:06'),
(91, 2, 1, '71gnlfuqmm84hll7dju92ef72c_2', 'vacant', '2025-06-29 12:59:18'),
(92, 5, 8, 't3mno47m3fhle0rpku8cggh56e_5', 'vacant', '2025-06-29 13:10:33'),
(93, 5, 1, 'ocvhmrfp3fe6pkaias9bu25aae_5', 'vacant', '2025-06-29 13:13:00'),
(94, 2, 1, 'qm60m1mvijgjfjd9aoa6tn18j2_2', 'vacant', '2025-06-30 10:34:26'),
(95, 1, 1, '9akq9m913v4dv5q6q60l4l887e_1', 'vacant', '2025-07-01 00:36:00'),
(96, 6, 1, '8c7noe6ng78001mudqr1fl5sj5_6', 'vacant', '2025-07-01 01:43:28'),
(98, 5, 1, '8c7noe6ng78001mudqr1fl5sj5_5', 'vacant', '2025-07-01 02:03:56'),
(100, 3, 1, '1dnq5m0luv9jggm3ao61dq67q8_3', 'vacant', '2025-07-01 02:08:21'),
(101, 2, 1, '744b2gn4mscjt60v3v0nhau9g5_2', 'vacant', '2025-07-01 09:36:16'),
(102, 2, 1, 'qpo24t29d6ea5j3a7djl7tg524_2', 'vacant', '2025-07-01 13:03:42'),
(103, 1, 2, 'ha83hni47g5gcha9d13m8scvdf_1', 'vacant', '2025-07-01 13:47:18'),
(104, 2, 1, 'bntgssk3sinpe1vfr1mpr72rj0_2', 'vacant', '2025-07-02 06:15:37'),
(105, 4, 2, 'vosr51f0mih02uacq1igjt890p_4', 'vacant', '2025-07-04 09:51:51'),
(106, 2, 1, 'av4vn6fk627htl2d8o0b1k4les_2', 'vacant', '2025-07-04 09:53:38'),
(107, 4, 1, 'av4vn6fk627htl2d8o0b1k4les_4', 'vacant', '2025-07-04 09:54:28'),
(109, 4, 1, 'hvtki33g997laefmhbk0tf5eas_4', 'vacant', '2025-07-04 09:54:59'),
(110, 7, 1, 'c5uo8a5v8e7dnu5ta18gehtlu8_7', 'vacant', '2025-07-04 12:50:39'),
(111, 5, 1, '4u3np9crfu5gp40uhticpnivgg_5', 'vacant', '2025-07-09 01:37:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `offline_table_session_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `menu_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `catatan` text DEFAULT NULL,
  `status` enum('menunggu','memasak','selesai','ditolak') NOT NULL DEFAULT 'menunggu',
  `order_type` enum('offline','booking') NOT NULL DEFAULT 'offline',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `offline_table_session_id`, `booking_id`, `username`, `menu_id`, `menu_name`, `quantity`, `catatan`, `status`, `order_type`, `created_at`) VALUES
(139, 0, 53, 'ino123', 12, 'Amer', 4, '', 'menunggu', 'booking', '2025-06-29 12:34:08'),
(140, 0, 53, 'ino123', 10, 'Nasi Goreng', 1, '', 'selesai', 'booking', '2025-06-29 12:34:16'),
(141, 0, 53, 'ino123', 11, 'BBQ', 1, '', 'menunggu', 'booking', '2025-06-29 12:34:16'),
(142, 0, 54, 'wyndra', 12, 'Amer', 7, '', 'selesai', 'booking', '2025-06-29 12:34:35'),
(143, 0, 54, 'wyndra', 11, 'BBQ', 1, '', 'menunggu', 'booking', '2025-06-29 12:34:35'),
(144, 0, 52, 'Mighdad Abdul Fattah Jaba', 10, 'Nasi Goreng', 4, '', 'selesai', 'booking', '2025-06-29 12:35:50'),
(145, 89, NULL, 'Mighdad', 12, 'Amer', 4, '', 'selesai', 'offline', '2025-06-29 12:58:51'),
(146, 89, NULL, 'Mighdad', 11, 'BBQ', 1, '', 'selesai', 'offline', '2025-06-29 12:58:51'),
(147, 90, NULL, 'ino', 12, 'Amer', 2, '', 'selesai', 'offline', '2025-06-29 12:59:06'),
(148, 90, NULL, 'ino', 10, 'Nasi Goreng', 1, '', 'selesai', 'offline', '2025-06-29 12:59:06'),
(149, 91, NULL, 'wyn', 10, 'Nasi Goreng', 3, '', 'selesai', 'offline', '2025-06-29 12:59:18'),
(150, 91, NULL, 'wyn', 10, 'Nasi Goreng', 4, '', 'selesai', 'offline', '2025-06-29 13:05:05'),
(151, 92, NULL, 'loli', 10, 'Nasi Goreng', 1, '', 'menunggu', 'offline', '2025-06-29 13:10:33'),
(152, 93, NULL, 'noni', 12, 'Amer', 4, '', 'selesai', 'offline', '2025-06-29 13:13:00'),
(153, 94, NULL, 'asd', 20, 'sda', 1, '', 'menunggu', 'offline', '2025-06-30 10:34:26'),
(154, 95, NULL, 'asdw', 29, 'nasgord', 2, '', 'menunggu', 'offline', '2025-07-01 00:36:00'),
(155, 0, 59, 'ino123', 29, 'nasgord', 1, '', 'ditolak', 'booking', '2025-07-01 01:41:47'),
(156, 0, 59, 'ino123', 30, 'ikan lele bakar', 3, '', 'selesai', 'booking', '2025-07-01 01:41:56'),
(157, 96, NULL, 'mng', 29, 'nasgord', 1, '', 'selesai', 'offline', '2025-07-01 01:43:28'),
(158, 96, NULL, 'mng', 30, 'ikan lele bakar', 3, '', 'selesai', 'offline', '2025-07-01 01:43:48'),
(159, 98, NULL, 'asd', 29, 'nasgord', 2, '', 'memasak', 'offline', '2025-07-01 02:03:56'),
(160, 0, 60, 'Mighdad Abdul Fattah Jaba', 30, 'ikan lele bakar', 1, '', 'memasak', 'booking', '2025-07-01 02:07:21'),
(161, 0, 60, 'Mighdad Abdul Fattah Jaba', 29, 'nasgord', 1, '', 'menunggu', 'booking', '2025-07-01 02:07:25'),
(162, 100, NULL, 'fsa', 29, 'nasgord', 3, '', 'menunggu', 'offline', '2025-07-01 02:08:21'),
(163, 100, NULL, 'fsa', 30, 'ikan lele bakar', 3, '', 'memasak', 'offline', '2025-07-01 02:08:21'),
(164, 0, 61, 'ino123', 30, 'ikan lele bakar', 1, '', 'memasak', 'booking', '2025-07-01 02:09:55'),
(165, 101, NULL, 'asf', 31, 'BBQ', 1, '', 'menunggu', 'offline', '2025-07-01 09:36:16'),
(166, 0, 64, 'ino123', 31, 'BBQ', 4, '', 'menunggu', 'booking', '2025-07-01 13:01:14'),
(167, 102, NULL, 'mighdad', 31, 'BBQ', 4, '', 'menunggu', 'offline', '2025-07-01 13:03:42'),
(168, 103, NULL, 'mighdad', 31, 'BBQ', 1, '', 'memasak', 'offline', '2025-07-01 13:47:18'),
(169, 103, NULL, 'mighdad', 32, 'Wagyu A5', 1, 'banyakin!', 'menunggu', 'offline', '2025-07-01 13:47:18'),
(170, 103, NULL, 'mighdad', 33, 'Blue Hawaii', 1, '', 'menunggu', 'offline', '2025-07-01 13:47:18'),
(171, 0, 66, 'ino123', 33, 'Blue Hawaii', 1, '', 'selesai', 'booking', '2025-07-02 06:12:48'),
(172, 0, 66, 'ino123', 31, 'BBQ', 1, '', 'selesai', 'booking', '2025-07-02 06:12:48'),
(173, 0, 66, 'ino123', 33, 'Blue Hawaii', 1, '', 'selesai', 'booking', '2025-07-02 06:15:03'),
(174, 104, NULL, 'mighdad', 33, 'Blue Hawaii', 4, '', 'memasak', 'offline', '2025-07-02 06:15:37'),
(175, 104, NULL, 'mighdad', 32, 'Wagyu A5', 1, '', 'menunggu', 'offline', '2025-07-02 06:15:37'),
(176, 105, NULL, 'mighdad', 31, 'BBQ', 1, '', 'selesai', 'offline', '2025-07-04 09:51:51'),
(177, 106, NULL, 'jnj', 31, 'BBQ', 1, '', 'menunggu', 'offline', '2025-07-04 09:53:38'),
(178, 107, NULL, 'asd', 31, 'BBQ', 1, '', 'menunggu', 'offline', '2025-07-04 09:54:28'),
(179, 109, NULL, 'asdsa', 31, 'BBQ', 1, '', 'selesai', 'offline', '2025-07-04 09:54:59'),
(180, 0, 67, 'ino123', 31, 'BBQ', 1, '', 'menunggu', 'booking', '2025-07-04 12:42:06'),
(181, 110, NULL, 'mighdad', 31, 'BBQ', 1, '', 'memasak', 'offline', '2025-07-04 12:50:39'),
(182, 111, NULL, 'Ino', 33, 'Blue Hawaii', 1, '', 'menunggu', 'offline', '2025-07-09 01:37:36'),
(183, 111, NULL, 'Ino', 32, 'Wagyu A5', 7, '', 'menunggu', 'offline', '2025-07-09 01:37:36'),
(184, 0, 70, 'ino123', 33, 'Blue Hawaii', 1, '', 'memasak', 'booking', '2025-07-10 00:00:41'),
(185, 0, 70, 'ino123', 32, 'Wagyu A5', 1, '', 'menunggu', 'booking', '2025-07-10 00:01:41'),
(186, 0, 71, 'ino123', 32, 'Wagyu A5', 1, '', 'menunggu', 'booking', '2025-07-10 00:06:04'),
(187, 0, 71, 'ino123', 33, 'Blue Hawaii', 1, '', 'menunggu', 'booking', '2025-07-10 00:06:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tables`
--

CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `table_number` int(11) NOT NULL,
  `capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tables`
--

INSERT INTO `tables` (`id`, `table_number`, `capacity`) VALUES
(1, 1, 8),
(2, 2, 8),
(3, 3, 8),
(4, 4, 8),
(5, 5, 8),
(6, 6, 8),
(7, 7, 8),
(8, 8, 8);

-- --------------------------------------------------------

--
-- Struktur dari tabel `table_bookings`
--

CREATE TABLE `table_bookings` (
  `id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `guest_count` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `table_code` varchar(12) NOT NULL,
  `status` enum('booked','cancelled') DEFAULT 'booked',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `table_bookings`
--

INSERT INTO `table_bookings` (`id`, `table_id`, `user_id`, `guest_count`, `booking_date`, `booking_time`, `table_code`, `status`, `created_at`) VALUES
(59, 2, 2, 2, '2025-07-01', '10:00:00', '3998GUOA1528', 'cancelled', '2025-07-01 01:40:44'),
(60, 2, 4, 2, '2025-07-01', '10:30:00', '1826RQYV7045', 'cancelled', '2025-07-01 02:07:11'),
(61, 7, 2, 2, '2025-07-01', '10:30:00', '7509LNRU3016', 'cancelled', '2025-07-01 02:09:19'),
(62, 2, 4, 2, '2025-07-01', '11:30:00', '5045RPZJ7437', 'cancelled', '2025-07-01 02:15:34'),
(63, 1, 2, 2, '2025-07-01', '18:00:00', '0123HXAZ8043', 'cancelled', '2025-07-01 09:40:23'),
(64, 5, 2, 2, '2025-07-01', '20:30:00', '9023NSAZ7878', 'booked', '2025-07-01 12:27:34'),
(65, 2, 2, 2, '2025-07-02', '10:30:00', '2199TRLN6351', 'cancelled', '2025-07-02 02:07:21'),
(66, 3, 2, 2, '2025-07-02', '16:00:00', '9168HNLB7201', 'cancelled', '2025-07-02 06:12:28'),
(67, 1, 2, 2, '2025-07-04', '20:30:00', '6326QDIA9389', 'booked', '2025-07-04 11:15:38'),
(68, 7, 2, 4, '2025-07-09', '10:00:00', '7359ZRTS6324', 'booked', '2025-07-09 01:42:13'),
(69, 2, 2, 2, '2025-07-09', '21:00:00', '8752WAQB5730', 'booked', '2025-07-09 12:25:28'),
(70, 6, 2, 2, '2025-07-10', '08:30:00', '1709NAJY9653', 'cancelled', '2025-07-09 22:52:19'),
(71, 7, 2, 2, '2025-07-10', '08:30:00', '1009XOUQ9127', 'booked', '2025-07-10 00:05:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `saldo` decimal(12,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `saldo`, `created_at`, `updated_at`) VALUES
(1, 'ino', 'ino@gmail.com', '$2y$10$3PYCxq73tgyRmtiuzsp32..X4iFyp1mufdZdT.yixAPQZo.mCJw6e', 'admin', 9997999999.99, '2025-06-16 11:38:59', '2025-06-26 21:29:14'),
(2, 'ino123', 'ino123@gmail.com', '$2y$10$bWXQQIMct1ta2UgCnoewfOcFdstlGuvIOJxQlaQ4i4PHUCFhTqtvC', 'user', 9983599999.99, '2025-06-16 11:42:17', '2025-07-10 08:05:42'),
(3, 'asdas', 'dasdas@gmail.com', '$2y$10$2DWTAZh5vpCcRIYqrPwbj.2N0bOz/XjZdm20NAeB7YRsN9boi1BX6', 'user', 40000.00, '2025-06-21 14:01:52', '2025-06-29 21:20:34'),
(4, 'Mighdad Abdul Fattah Jaba', 'mighdad@gmail.com', '$2y$10$61XwY8A/1Rq7ihxDqJ7Cc..NMGYXZ.J/.DfDiZGaVlZFYFtOJPebO', 'user', 9990799999.99, '2025-06-29 13:21:04', '2025-07-01 10:15:34'),
(5, 'wyndra', 'wyn@gmail.com', '$2y$10$YK4dPr/leazwKNmX/ctF9OhKFzg7FDps5t9ZtmeHQHwHoM1rDimZW', 'user', 9998799999.99, '2025-06-29 20:14:34', '2025-06-29 20:32:44');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `offline_table_sessions`
--
ALTER TABLE `offline_table_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_code` (`session_code`),
  ADD KEY `idx_sessions_table_status` (`table_id`,`status`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `offline_table_session_id` (`offline_table_session_id`),
  ADD KEY `menu_id` (`menu_id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_orders_session_status` (`offline_table_session_id`,`status`),
  ADD KEY `idx_orders_created_status` (`created_at`,`status`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `order_type` (`order_type`),
  ADD KEY `idx_orders_booking_status` (`booking_id`,`status`);

--
-- Indeks untuk tabel `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_number` (`table_number`);

--
-- Indeks untuk tabel `table_bookings`
--
ALTER TABLE `table_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_code` (`table_code`),
  ADD KEY `table_id` (`table_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `offline_table_sessions`
--
ALTER TABLE `offline_table_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT untuk tabel `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `table_bookings`
--
ALTER TABLE `table_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `offline_table_sessions`
--
ALTER TABLE `offline_table_sessions`
  ADD CONSTRAINT `offline_table_sessions_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`);

--
-- Ketidakleluasaan untuk tabel `table_bookings`
--
ALTER TABLE `table_bookings`
  ADD CONSTRAINT `table_bookings_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
