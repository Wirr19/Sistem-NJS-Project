-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Sep 2026 pada 06.42
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_peramalan_penjualan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `id_penjualan` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `nama_buket` varchar(100) NOT NULL,
  `jenis_buket` varchar(50) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `harga` decimal(12,2) DEFAULT NULL,
  `total_harga` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `id_pengguna`, `tanggal`, `nama_buket`, `jenis_buket`, `jumlah`, `harga`, `total_harga`) VALUES
(2, 2, '2026-06-23', 'Tumbelina', NULL, 20, NULL, NULL),
(4, 2, '2026-07-24', 'Tumbelina', NULL, 32, NULL, NULL),
(5, 2, '2025-08-15', 'Tumbelina', 'Fresh Flower', 18, 150000.00, 2700000.00),
(6, 2, '2025-09-15', 'Tumbelina', 'Fresh Flower', 22, 150000.00, 3300000.00),
(7, 2, '2025-10-15', 'Tumbelina', 'Fresh Flower', 25, 150000.00, 3750000.00),
(8, 2, '2025-11-15', 'Tumbelina', 'Fresh Flower', 29, 150000.00, 4350000.00),
(9, 2, '2025-12-15', 'Tumbelina', 'Fresh Flower', 35, 150000.00, 5250000.00),
(10, 2, '2026-01-15', 'Tumbelina', 'Fresh Flower', 38, 150000.00, 5700000.00),
(11, 2, '2026-02-15', 'Tumbelina', 'Fresh Flower', 42, 150000.00, 6300000.00),
(12, 2, '2026-03-15', 'Tumbelina', 'Fresh Flower', 46, 150000.00, 6900000.00),
(13, 2, '2026-04-15', 'Tumbelina', 'Fresh Flower', 50, 150000.00, 7500000.00),
(14, 2, '2026-05-15', 'Tumbelina', 'Fresh Flower', 55, 150000.00, 8250000.00),
(15, 2, '2026-06-15', 'Tumbelina', 'Fresh Flower', 60, 150000.00, 9000000.00),
(16, 2, '2026-07-15', 'Tumbelina', 'Fresh Flower', 65, 150000.00, 9750000.00),
(17, 2, '2026-09-03', 'Mawar', NULL, 12, NULL, NULL),
(19, 2, '2026-09-14', 'Tumbelina', NULL, 20, NULL, NULL),
(20, 2, '2026-08-14', 'Scarlet Veil Bouqet', NULL, 55, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `peramalan`
--

CREATE TABLE `peramalan` (
  `id_peramalan` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `periode` varchar(20) NOT NULL,
  `nilai_a` float NOT NULL,
  `nilai_b` float NOT NULL,
  `hasil_prediksi` float NOT NULL,
  `nilai_mape` float DEFAULT NULL,
  `mad` double DEFAULT NULL,
  `mse` double DEFAULT NULL,
  `tanggal_proses` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peramalan`
--

INSERT INTO `peramalan` (`id_peramalan`, `id_pengguna`, `periode`, `nilai_a`, `nilai_b`, `hasil_prediksi`, `nilai_mape`, `mad`, `mse`, `tanggal_proses`) VALUES
(1, 2, '2026-07', 8, 12, 44, 54.64, 53, 2809, '2026-07-24 15:32:20'),
(2, 2, '2026-07', 8, 12, 44, 54.64, 53, 2809, '2026-07-24 19:34:04'),
(3, 2, '2026-08', 5.13636, 6.09441, 84.36, 14.88, 6.6, 67.91, '2026-08-07 15:00:18'),
(4, 2, '2026-10', 16.7143, 3.71429, 72.4286, NULL, NULL, NULL, '2026-09-14 18:09:25'),
(5, 2, '2026-09', 9.65385, 5.12637, 81.4231, 154.447, 49.423076923077, 2442.6405325444, '2026-09-14 18:09:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok`
--

CREATE TABLE `stok` (
  `id_stok` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `nama_bahan` varchar(100) NOT NULL,
  `jenis` varchar(50) NOT NULL,
  `jumlah_stok` int(11) NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `stok_minimum` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stok`
--

INSERT INTO `stok` (`id_stok`, `id_pengguna`, `nama_bahan`, `jenis`, `jumlah_stok`, `satuan`, `stok_minimum`) VALUES
(2, 2, 'Matahari', 'Bunga Segar', 150, 'Tangkai', 50),
(3, 2, 'lily', 'Bunga Segar', 30, 'Tangkai', 20);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id_pengguna` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('owner','admin') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id_pengguna`, `username`, `password`, `nama`, `role`) VALUES
(2, 'owner', '$2y$10$f/GcxSSPHey1.SovG4irpubpZG1QdJopvmKlcy.NrvQGCryZCYBDy', 'Cintia', 'owner');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD KEY `fk_penjualan_user` (`id_pengguna`);

--
-- Indeks untuk tabel `peramalan`
--
ALTER TABLE `peramalan`
  ADD PRIMARY KEY (`id_peramalan`),
  ADD KEY `fk_peramalan_user` (`id_pengguna`);

--
-- Indeks untuk tabel `stok`
--
ALTER TABLE `stok`
  ADD PRIMARY KEY (`id_stok`),
  ADD KEY `fk_stok_user` (`id_pengguna`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `peramalan`
--
ALTER TABLE `peramalan`
  MODIFY `id_peramalan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `stok`
--
ALTER TABLE `stok`
  MODIFY `id_stok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `fk_penjualan_user` FOREIGN KEY (`id_pengguna`) REFERENCES `user` (`id_pengguna`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peramalan`
--
ALTER TABLE `peramalan`
  ADD CONSTRAINT `fk_peramalan_user` FOREIGN KEY (`id_pengguna`) REFERENCES `user` (`id_pengguna`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `stok`
--
ALTER TABLE `stok`
  ADD CONSTRAINT `fk_stok_user` FOREIGN KEY (`id_pengguna`) REFERENCES `user` (`id_pengguna`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
