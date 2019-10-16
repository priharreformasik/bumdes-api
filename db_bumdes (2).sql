-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2019 at 02:26 AM
-- Server version: 10.3.16-MariaDB
-- PHP Version: 7.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_bumdes`
--

-- --------------------------------------------------------

--
-- Table structure for table `data_akun`
--

CREATE TABLE `data_akun` (
  `id` int(11) NOT NULL,
  `id_klasifikasi_akun` int(11) DEFAULT NULL,
  `nama` varchar(40) DEFAULT NULL,
  `posisi_normal` enum('Debit','Kredit','','') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `data_akun`
--

INSERT INTO `data_akun` (`id`, `id_klasifikasi_akun`, `nama`, `posisi_normal`) VALUES
(1110, 11, 'Kas', 'Debit'),
(1111, 11, 'Kas di Bank', 'Debit'),
(1120, 11, 'Piutang Dagang', 'Debit'),
(1130, 11, 'Sewa dibayar dimuka', 'Debit'),
(1210, 12, 'Tanah', 'Debit'),
(1220, 12, 'Gedung', 'Debit'),
(1230, 12, 'Kendaraan', 'Debit'),
(1240, 12, 'Peralatan Kantor', 'Debit'),
(2110, 21, 'Utang Dagang', 'Kredit'),
(2120, 21, 'Utang Gaji', 'Kredit'),
(2130, 21, 'Utang Bank', 'Kredit'),
(2210, 22, 'Obligasi', 'Kredit'),
(3100, 3, 'Modal Disetor', 'Kredit'),
(3110, 3, 'Saldo Laba Ditahan', 'Kredit'),
(3120, 3, 'Saldo Laba Tahun Berjalan', 'Kredit'),
(4110, 4, 'Pendapatan Wisata', 'Kredit'),
(4120, 4, 'Pendapatan Homestay', 'Kredit'),
(4130, 4, 'Pendapatan Resto', 'Kredit'),
(4140, 4, 'Pendapatan Event', 'Kredit'),
(5110, 5, 'Biaya Gaji', 'Debit'),
(5120, 5, 'Biaya Listrik, Air, dan Telepon', 'Debit'),
(5130, 5, 'Biaya Administasi dan Umum', 'Debit'),
(5140, 5, 'Biaya Pemasaran', 'Debit'),
(5150, 5, 'Biaya Perlengkapan Kantor', 'Debit'),
(5160, 5, 'Biaya Sewa', 'Debit'),
(5170, 5, 'Biaya Asuransi', 'Debit'),
(5180, 5, 'Biaya Penyusutan Gedung', 'Debit'),
(5190, 5, 'Biaya Penyusutan Kendaraan', 'Debit'),
(5200, 5, 'Biaya Penyusutan Peralatan Kantor', 'Debit'),
(6110, 6, 'Pendapatan Lain-lain', 'Kredit'),
(7110, 7, 'Biaya Lain-lain', 'Debit'),
(12201, 12, 'Akumulasi Penyusutan Gedung', 'Kredit'),
(12301, 12, 'Akumulasi Penyusutan Kendaraan', 'Kredit'),
(12401, 12, 'Akumulasi Penyusutan Peralatan Kantor', 'Kredit'),
(51192, 5, 'beban kas 2', 'Debit'),
(51193, 5, 'beban kas 3', 'Kredit');

-- --------------------------------------------------------

--
-- Table structure for table `jurnal`
--

CREATE TABLE `jurnal` (
  `id` int(11) NOT NULL,
  `id_kwitansi` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `id_data_akun` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `posisi_normal` enum('d','k','','') DEFAULT NULL,
  `saldo_akhir` int(11) DEFAULT NULL,
  `id_neraca_awal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jurnal`
--

INSERT INTO `jurnal` (`id`, `id_kwitansi`, `tanggal`, `id_data_akun`, `jumlah`, `posisi_normal`, `saldo_akhir`, `id_neraca_awal`) VALUES
(8, 4, '2019-09-02', 1111, 1000, 'd', NULL, NULL),
(9, 4, '2019-09-02', 1110, 10, 'k', NULL, NULL),
(10, 4, '2019-09-02', 1111, 50, 'k', NULL, NULL),
(18, 13, '2019-10-12', 1111, 60000, 'k', NULL, NULL),
(29, 24, '2019-10-12', 1111, 90000, 'k', NULL, NULL),
(31, 26, '2019-10-12', 1111, 70000, 'd', NULL, NULL),
(32, 27, '2019-10-12', 4110, 70000, 'd', NULL, NULL),
(33, 28, '2019-10-12', 4110, 70000, 'k', NULL, NULL),
(34, 29, '2019-10-12', 5110, 50000, 'k', NULL, NULL),
(35, 30, '2019-10-12', 5110, 50000, '', NULL, NULL),
(36, 30, '2019-10-12', 4110, 40000, 'd', NULL, NULL),
(37, 30, '2019-10-12', 3110, 30000, 'k', NULL, NULL),
(38, 30, '2019-10-12', 4110, 30000, 'd', NULL, NULL),
(39, 30, '2019-10-12', 5110, 30000, 'k', NULL, NULL),
(41, 31, '2019-10-13', 1120, 30000, 'd', NULL, NULL),
(42, 32, '2019-10-13', 4110, 30000, 'k', NULL, NULL),
(43, 33, '2019-10-13', 4120, 30000, 'd', NULL, NULL),
(44, 34, '2019-10-14', 5120, 30000, 'd', NULL, NULL),
(46, 35, '2019-10-14', 4140, 30000, 'k', NULL, NULL),
(48, 37, '2019-10-14', 4130, 30000, 'd', NULL, NULL),
(51, 37, '2019-10-14', 3100, 30000, 'd', NULL, NULL),
(52, 38, '2019-10-15', 1110, 30000, 'k', NULL, NULL),
(55, 39, '2019-10-15', 1110, 30000, 'd', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `klasifikasi_akun`
--

CREATE TABLE `klasifikasi_akun` (
  `id` int(11) NOT NULL,
  `nama` varchar(40) DEFAULT NULL,
  `id_parent_akun` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `klasifikasi_akun`
--

INSERT INTO `klasifikasi_akun` (`id`, `nama`, `id_parent_akun`) VALUES
(3, 'Ekuitas', 3),
(4, 'Pendapatan', 4),
(5, 'Beban', 5),
(6, 'Pendapatan Lainnya', 6),
(7, 'Biaya Lainnya', 7),
(9, 'beban hati', 5),
(11, 'Aset Lancar', 1),
(12, 'Aset Tetap', 1),
(21, 'Utang Lancar', 2),
(22, 'Utang Jangka Panjang', 2),
(597, 'beban hati', 5),
(598, 'beban hati', 5),
(900, 'beban hati', 5),
(901, 'beban hati', 5),
(902, 'beban hati', 5),
(906, 'beban hati', 5),
(907, 'beban hati', 5),
(909, 'beban hati', 5),
(5646, 'beban hati', 5),
(56467, 'beban hati', 5);

-- --------------------------------------------------------

--
-- Table structure for table `kwitansi`
--

CREATE TABLE `kwitansi` (
  `id` int(11) NOT NULL,
  `no_kwitansi` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `kwitansi`
--

INSERT INTO `kwitansi` (`id`, `no_kwitansi`, `keterangan`) VALUES
(4, 'kw-102', 'pembayaran gaji karyawan'),
(6, '109d', 'transaksi apaa ya'),
(12, '109d', 'transaksi apaa ya'),
(13, '109d', 'transaksi apaa ya'),
(14, '109d', 'transaksi apaa ya'),
(15, '109d', 'transaksi apaa ya'),
(16, '109d', 'transaksi apaa ya'),
(17, '109d', 'transaksi apaa ya'),
(18, '109d', 'transaksi apaa ya'),
(19, '109d', 'transaksi apaa ya'),
(20, '101d', 'transaksi apaa ya'),
(21, '101d', 'transaksi apaa ya'),
(22, '101d', 'transaksi apaa ya'),
(23, '101d', 'transaksi apaa ya'),
(24, '108d', 'transaksi apaa ya 2'),
(25, '108d', 'transaksi apaa ya 2'),
(26, '106d', 'transaksi apaa ya 3'),
(27, '416d', 'pendapatan'),
(28, '416d', 'pendapatan'),
(29, '516d', 'biaya'),
(30, '716d', 'biaya'),
(31, '111', 'kas kasan'),
(32, '111', 'pendapatan'),
(33, '111', 'pendapatan'),
(34, '5gjd', 'biaya'),
(35, '5gjd', 'biaya'),
(36, '5gjd', 'kas aja'),
(37, 'dghj', 'rugi'),
(38, 'kw15', 'kas kw15'),
(39, 'kw15', 'kas kw15');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2016_06_01_000001_create_oauth_auth_codes_table', 1),
(4, '2016_06_01_000002_create_oauth_access_tokens_table', 1),
(5, '2016_06_01_000003_create_oauth_refresh_tokens_table', 1),
(6, '2016_06_01_000004_create_oauth_clients_table', 1),
(7, '2016_06_01_000005_create_oauth_personal_access_clients_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `neraca_awal`
--

CREATE TABLE `neraca_awal` (
  `id` int(11) NOT NULL,
  `id_data_akun` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jumlah` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `neraca_awal`
--

INSERT INTO `neraca_awal` (`id`, `id_data_akun`, `tanggal`, `jumlah`, `created_at`, `updated_at`) VALUES
(2, 1111, '2019-09-23', '10000', NULL, NULL),
(4, 1110, '2019-09-19', '50000', NULL, NULL),
(10, 1120, '2019-10-13', '70000', NULL, NULL),
(11, 1210, '2019-10-13', '30000', NULL, NULL),
(13, 1220, '2019-10-13', '30000', NULL, NULL),
(15, 1230, '2019-10-13', '30000', NULL, NULL),
(16, 4110, '2019-10-13', '30000', NULL, NULL),
(17, 4140, '2019-10-13', '50000', NULL, NULL),
(18, 3100, '2019-10-14', '93000', NULL, NULL),
(19, 4130, '2019-10-14', '43000', NULL, NULL),
(21, 3120, '2019-10-15', '40000', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_access_tokens`
--

INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('0e081a339ee2f7dd03afb2cf0fa9a65ca0b47ce1c22b51292b69459be1fc7bcd2a4db3081211e193', 5, 1, 'MyApp', '[]', 0, '2019-09-25 13:11:54', '2019-09-25 13:11:54', '2020-09-25 20:11:54'),
('1060485d2f2cca779217002a2b19b426961a6b3da60d8059d1ef7e3cadbe455a79a82b2730320b0f', 1, 1, 'MyApp', '[]', 0, '2019-09-19 00:15:46', '2019-09-19 00:15:46', '2020-09-19 07:15:46'),
('19d35a8436ea89bc9f9725ea4796c2b478b37a03fdef9d58878f5a96e63d50ff4bf8c29850f72dda', 5, 1, 'MyApp', '[]', 0, '2019-10-02 00:20:22', '2019-10-02 00:20:22', '2020-10-02 07:20:22'),
('2ba8290657a62a7be2f35d757b7ccdbbd45549a3ec3dadd8fd5f9d806818ed5274a181e2747088c4', 5, 1, 'MyApp', '[]', 0, '2019-10-02 01:14:06', '2019-10-02 01:14:06', '2020-10-02 08:14:06'),
('401dbc81d7cc82bb6bfd1b8d96b6c4171ea5186a61ca39443745a04492039e6feefcf69e64e572d5', 2, 1, 'MyApp', '[]', 0, '2019-09-25 10:58:13', '2019-09-25 10:58:13', '2020-09-25 17:58:13'),
('434c878f8bc3554abffc8f66087081779e56f125d6e1b7e606761d06a105fd8044433fedf7fd7773', 1, 1, 'MyApp', '[]', 0, '2019-10-14 07:52:46', '2019-10-14 07:52:46', '2020-10-14 14:52:46'),
('4bd76f686ab57ff85d81a8f1199090a039d7262674bd3efe9444f004337344695fff4ce3c381b74b', 5, 1, 'MyApp', '[]', 0, '2019-10-02 00:20:44', '2019-10-02 00:20:44', '2020-10-02 07:20:44'),
('58ef21e468e8a697105e6296021e385d9f09f7b3e3a9a7111beab4e8ea4d4bf4ff235f3754851232', 5, 1, 'MyApp', '[]', 0, '2019-09-25 18:44:54', '2019-09-25 18:44:54', '2020-09-26 01:44:54'),
('5a1793ff98f23b1d56ff593e15144db6fc64630bfc7b4072e7b84a6dd3fdcc9fd3f5bd874d45eca8', 1, 1, 'MyApp', '[]', 0, '2019-10-14 20:52:41', '2019-10-14 20:52:41', '2020-10-15 03:52:41'),
('64a5b16b6c60c7e3da48e55df1f8ad5afc276985019338ffefa30d58c777ac8d08a366bd7bbe797c', 1, 1, 'MyApp', '[]', 0, '2019-09-19 00:08:03', '2019-09-19 00:08:03', '2020-09-19 07:08:03'),
('6e59745d3e569d663288fa71e976705b66515c2906a2670c4c69b952f510261053fe6d238f49f8c7', 1, 1, 'MyApp', '[]', 0, '2019-09-19 00:07:48', '2019-09-19 00:07:48', '2020-09-19 07:07:48'),
('7a1dfa8abece88b577d4fb27d3285b912f18d9cbd75b284118d2c207a91b314a4d3772b4c78bedca', 1, 1, 'MyApp', '[]', 0, '2019-10-14 07:50:42', '2019-10-14 07:50:42', '2020-10-14 14:50:42'),
('84f6fc622dfd9ba99766434df8d1ff0a371995ea438b6a23ef1a0121b018f23a664464a440c6b0c8', 1, 1, 'MyApp', '[]', 1, '2019-10-14 07:29:07', '2019-10-14 07:29:07', '2020-10-14 14:29:07'),
('880f3d43339f1cc6463868dc116877c10bbf8e5276847a64b4df5b2ebb2b1b2c12f524e358bb156e', 1, 1, 'MyApp', '[]', 1, '2019-10-14 07:53:56', '2019-10-14 07:53:56', '2020-10-14 14:53:56'),
('9247c45820b431411e73668c2b1bb1382f2a90219a633fe7e6e4422aef3598020dc496dcc4dfd951', 5, 1, 'MyApp', '[]', 0, '2019-10-02 01:15:36', '2019-10-02 01:15:36', '2020-10-02 08:15:36'),
('b171fc56da81525ba68b6fd1649c9c10898025f86902812a0aabee0033fc66450ed45bc0c3af17f6', 5, 1, 'MyApp', '[]', 0, '2019-10-02 00:06:19', '2019-10-02 00:06:19', '2020-10-02 07:06:19'),
('b4e8f4d42297c7d8744c51d44db653229e494fad7167aa327d2e17b00ce307aafae985d0ec0e7e4f', 1, 1, 'MyApp', '[]', 0, '2019-10-06 21:05:16', '2019-10-06 21:05:16', '2020-10-07 04:05:16'),
('c174c0613036d502e0337b7abbf59024af0a208b351d697b87bb621ec6d2623887f58864530d9718', 1, 1, 'MyApp', '[]', 0, '2019-10-06 21:05:48', '2019-10-06 21:05:48', '2020-10-07 04:05:48'),
('c44619a7d2148676e4d2a7bb1eb5fc0c8c5caacaaf711e10697d805392093d091b17ecba588b4ea0', 4, 1, 'MyApp', '[]', 0, '2019-09-25 11:09:32', '2019-09-25 11:09:32', '2020-09-25 18:09:32'),
('c7f7bca63298305714031a79d77207855ea4578f913574c3571b03b2f3aded58dc6054bed329eeb1', 5, 1, 'MyApp', '[]', 0, '2019-10-02 00:07:08', '2019-10-02 00:07:08', '2020-10-02 07:07:08'),
('cca9aa338202679d4e1f1c2a306ad58880460e4885630ffa22d5d8385f71a9ed42cac989813bcd84', 1, 1, 'MyApp', '[]', 0, '2019-09-19 02:06:55', '2019-09-19 02:06:55', '2020-09-19 09:06:55'),
('e1814f13fd540a1890ad959d29f8a7ab50da0114a26c4acf2e83ffce0b5b2d67302fdbdcab8eff59', 1, 1, 'MyApp', '[]', 0, '2019-10-14 07:54:23', '2019-10-14 07:54:23', '2020-10-14 14:54:23'),
('ebc1cc54d9d7cb062ebeb3adf0cca5d735b8831ee4c3154ac11cba518d170616ff6d2e69f163e6ad', 3, 1, 'MyApp', '[]', 0, '2019-09-25 10:59:45', '2019-09-25 10:59:45', '2020-09-25 17:59:45'),
('eccd558c5251f5d9770925500eb2dc6012bc3f1cadc4f47da9d5eb5a9cacc6ec73a29359a3c832b3', 5, 1, 'MyApp', '[]', 0, '2019-10-06 21:00:00', '2019-10-06 21:00:00', '2020-10-07 04:00:00'),
('ef6999110cbc1da43637a57e10281aa33c6f7666da34c97857817e5506a6ebf63ff8ca10380c6565', 1, 1, 'MyApp', '[]', 0, '2019-10-06 21:06:37', '2019-10-06 21:06:37', '2020-10-07 04:06:37'),
('fec37a5595f7d594615e22355c50a25853bf85dbc1f56befb9a7ccfab1e80533e616c04ace83a5a9', 1, 1, 'MyApp', '[]', 0, '2019-09-19 23:05:00', '2019-09-19 23:05:00', '2020-09-20 06:05:00');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Laravel Personal Access Client', 'GyM0NIeiaCJa5o1yypvE71BFG6JPpkUWvqQLhSvK', 'http://localhost', 1, 0, 0, '2019-09-18 00:33:48', '2019-09-18 00:33:48'),
(2, NULL, 'Laravel Password Grant Client', 'No6BjsEL1w1VLdE2BL3vGkDj6BRHF6kZdDo34fuC', 'http://localhost', 0, 1, 0, '2019-09-18 00:33:48', '2019-09-18 00:33:48');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2019-09-18 00:33:48', '2019-09-18 00:33:48');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parent_akun`
--

CREATE TABLE `parent_akun` (
  `id` int(11) NOT NULL,
  `nama` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `parent_akun`
--

INSERT INTO `parent_akun` (`id`, `nama`) VALUES
(1, 'Aset'),
(2, 'Liabilitas/Utang'),
(3, 'Ekuitas'),
(4, 'Pendapatan'),
(5, 'Beban'),
(6, 'Pendapatan Lainnya'),
(7, 'dunno 1'),
(10, 'Biaya Lainlain'),
(11, 'Biaya Lainlain');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `email_verified_at`, `password`, `remember_token`, `api_token`, `alamat`, `no_telepon`, `created_at`, `updated_at`) VALUES
(1, 'bumdes', 'ningrum@gmail.com', NULL, '$2y$10$DHHky0.7uxUHqOaeUMtAmeOh0YFO0n4oL0.lzhwuDeEuJc4992pIm', NULL, NULL, 'Yogyakarta', '0868789819', '2019-09-19 00:07:47', '2019-10-14 07:55:09'),
(2, NULL, 'bumdes12@gmail.com', NULL, '$2y$10$uvKuQlQlSq/thYQ2Gm5TJ.Y4nLLHpNGE/iajNAkYmLbEDYRAc2Clq', NULL, NULL, NULL, NULL, '2019-09-25 10:58:12', '2019-09-25 10:58:12'),
(3, NULL, 'bumdes123@gmail.com', NULL, '$2y$10$iTp451QqgPMNoRK.CAOaf.snWZGdHEP7BRakqQfZMF0rD72ILz9Ja', NULL, NULL, NULL, NULL, '2019-09-25 10:59:45', '2019-09-25 10:59:45'),
(4, NULL, 'bumdes1234@gmail.com', NULL, '$2y$10$XBR5/auTN7vcSc0uF9pm.ujfgxKxRv4d/Zi6P79hjIIb0LEkJ7zdu', NULL, NULL, NULL, NULL, '2019-09-25 11:06:21', '2019-09-25 11:06:21'),
(5, 'bumdes 12345', 'bumdes12345@gmail.com', NULL, '$2y$10$F8Td8E0kuEoJC9zmpJP2ZuxUONAGG7.hy8XXQtCKuMIq4ogzshesK', NULL, NULL, 'Yogyakarta', '08687878798', '2019-09-25 13:11:40', '2019-09-25 13:11:40'),
(6, 'bumdes s', 'bumdess@gmail.com', NULL, '$2y$10$R.xa.GQ4rYnBPp4wm2TvmuMAtyLRMcx1BXpJkCcrIt5rqOZpQpByy', NULL, NULL, 'Yogyakarta', '0868787879823', '2019-09-30 22:00:34', '2019-09-30 22:00:34'),
(7, 'bumdessss', 'bumdesss@gmail.com', NULL, '$2y$10$3M4DqXf6RAWNigV1XUKQ/eiAddl7RtJSTV.fwnRqAX1VVCt21nW8C', NULL, NULL, 'Yogyakarta a', '086878980', '2019-10-02 00:22:20', '2019-10-02 00:22:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data_akun`
--
ALTER TABLE `data_akun`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_klasifikasi_akun` (`id_klasifikasi_akun`);

--
-- Indexes for table `jurnal`
--
ALTER TABLE `jurnal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kwitansi` (`id_kwitansi`),
  ADD KEY `id_data_akun` (`id_data_akun`),
  ADD KEY `id_neraca_awal` (`id_neraca_awal`);

--
-- Indexes for table `klasifikasi_akun`
--
ALTER TABLE `klasifikasi_akun`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_parent_akun` (`id_parent_akun`);

--
-- Indexes for table `kwitansi`
--
ALTER TABLE `kwitansi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `neraca_awal`
--
ALTER TABLE `neraca_awal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_data_akun` (`id_data_akun`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_personal_access_clients_client_id_index` (`client_id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `parent_akun`
--
ALTER TABLE `parent_akun`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jurnal`
--
ALTER TABLE `jurnal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `kwitansi`
--
ALTER TABLE `kwitansi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `neraca_awal`
--
ALTER TABLE `neraca_awal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `data_akun`
--
ALTER TABLE `data_akun`
  ADD CONSTRAINT `data_akun_ibfk_1` FOREIGN KEY (`id_klasifikasi_akun`) REFERENCES `klasifikasi_akun` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jurnal`
--
ALTER TABLE `jurnal`
  ADD CONSTRAINT `jurnal_ibfk_2` FOREIGN KEY (`id_kwitansi`) REFERENCES `kwitansi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jurnal_ibfk_3` FOREIGN KEY (`id_data_akun`) REFERENCES `data_akun` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jurnal_ibfk_4` FOREIGN KEY (`id_neraca_awal`) REFERENCES `neraca_awal` (`id`);

--
-- Constraints for table `klasifikasi_akun`
--
ALTER TABLE `klasifikasi_akun`
  ADD CONSTRAINT `klasifikasi_akun_ibfk_1` FOREIGN KEY (`id_parent_akun`) REFERENCES `parent_akun` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `neraca_awal`
--
ALTER TABLE `neraca_awal`
  ADD CONSTRAINT `neraca_awal_ibfk_1` FOREIGN KEY (`id_data_akun`) REFERENCES `data_akun` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
