SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quotation_system`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `pic_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`id`, `company_name`, `pic_name`, `email`, `phone`) VALUES
(1, 'PT ASTRA JUOKU INDONESIA', 'Jamaludin', 'jamal123@outlook.com', '123456789000');

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `part_number` varchar(100) DEFAULT NULL,
  `product_name` varchar(150) DEFAULT NULL,
  `base_price` decimal(15,2) DEFAULT NULL,
  `sell_price` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `part_number`, `product_name`, `base_price`, `sell_price`) VALUES
(2, '11057-W001E', 'VR7', 20000.00, 30000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) NOT NULL,
  `quotation_number` varchar(50) DEFAULT NULL,
  `part_number` varchar(100) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `dpp` decimal(15,2) DEFAULT NULL,
  `tax` decimal(15,2) DEFAULT NULL,
  `grand_total` decimal(15,2) DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected') DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `quotations`
--

INSERT INTO `quotations` (`id`, `quotation_number`, `part_number`, `customer_id`, `subtotal`, `discount`, `dpp`, `tax`, `grand_total`, `status`, `created_at`) VALUES
(2, 'QT-20260124-085853', '11057-W001E', 1, 1500000.00, NULL, NULL, 165000.00, 1665000.00, 'draft', '2026-01-24 14:58:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `part_number` varchar(100) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `quotation_items`
--

INSERT INTO `quotation_items` (`id`, `quotation_id`, `part_number`, `product_id`, `qty`, `price`, `total`) VALUES
(2, 2, '11057-W001E', 2, 50, 30000.00, 1500000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_activity_log`
--

CREATE TABLE `tbl_activity_log` (
  `id` int(11) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `row_id` int(11) NOT NULL,
  `action` enum('insert','update','delete') NOT NULL,
  `column_name` varchar(50) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `description` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_activity_log`
--

INSERT INTO `tbl_activity_log` (`id`, `table_name`, `row_id`, `action`, `column_name`, `old_value`, `description`, `user_id`, `created_at`) VALUES
(135, 'tbl_users', 1, 'insert', 'login', NULL, 'Login berhasil user: Ahmad Rizqi Gustiansyah (admin)', 1, '2026-09-07 01:39:36'),
(136, 'tbl_users', 1, 'insert', 'login', NULL, 'Login berhasil user: Ahmad Rizqi Gustiansyah (admin)', 1, '2026-09-07 01:52:38'),
(137, 'tbl_users', 7, 'insert', 'login', NULL, 'Login berhasil user: Sandi Suwardi (sandi)', 7, '2026-09-07 02:05:21'),
(138, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'Trimitra Citrahasta / TCH\' (CDD / Delta Silicon) Rp 1.663.413', 7, '2026-09-07 02:07:21'),
(139, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Hongfa Electronic Blok.C\' (Fuso / BSD - Tanggerang Selatan) Rp 4.320.844', 7, '2026-09-07 02:19:31'),
(140, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Panasonic Gobel Life Solutions Manufacturing Indonesia\' (Fuso / Cileungsi - Kab. Bogor) Rp 3.187.023', 7, '2026-09-07 02:22:00'),
(141, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Bondor Indonesia Plant 1\' (Fuso / l. Olympic Raya, Kav A2-A, Sentul) Rp 3.113.573', 7, '2026-09-07 02:28:56'),
(142, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Bondor Indonesia Plant 1\' (Fuso / Jl. Olympic Raya, Kav A2-A, Sentul) Rp 3.113.573', 7, '2026-09-07 02:47:09'),
(143, 'tbl_transport_cost', 0, 'delete', 'id', 'Customer: PT. Bondor Indonesia Plant 1 | Ket: Fuso / l. Olympic Raya, Kav A2-A, Sentul | Biaya: Rp 3.113.573', 'Menghapus master transport cost (ID: 22)', 7, '2026-09-07 02:47:16'),
(144, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Ichikoh Indonesia\' (Fuso / MM2100) Rp 3.073.894', 7, '2026-09-07 02:50:19'),
(145, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. WILLFAR INFORMATION TECHNOLOGY\' (Fuso / Delta Silicon) Rp 2.201.340', 7, '2026-09-07 02:58:06'),
(146, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Willfar Information Technology\' (Fuso / Delta Silicon) Rp 2.201.340', 7, '2026-09-07 02:59:06'),
(147, 'tbl_transport_cost', 0, 'delete', 'id', 'Customer: PT. WILLFAR INFORMATION TECHNOLOGY | Ket: Fuso / Delta Silicon | Biaya: Rp 2.201.340', 'Menghapus master transport cost (ID: 25)', 7, '2026-09-07 02:59:13'),
(148, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Dharma Precision Parts\' (APV Box / Jababeka) Rp 422.700', 7, '2026-09-07 03:24:17'),
(149, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Dharma Precision Parts\' (APV Box / Jababeka) Rp 422.700', 7, '2026-09-07 03:24:48'),
(150, 'tbl_transport_cost', 0, 'delete', 'id', 'Customer: PT. Dharma Precision Parts | Ket: APV Box / Jababeka | Biaya: Rp 422.700', 'Menghapus master transport cost (ID: 27)', 7, '2026-09-07 03:30:07'),
(151, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Dharma Electrindo Manufacturing\' (APV Box / Jababeka) Rp 404.756', 7, '2026-09-07 03:31:25'),
(152, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. BS Indonesia\' (APV Box / Jababeka) Rp 408.744', 7, '2026-09-07 04:38:38'),
(153, 'tbl_mmp_head', 11, 'delete', 'keterangan_umum', 'Desember 2027', 'Menghapus dokumen Monitoring Material Price (MMP) ID #11: Desember 2027', 1, '2026-09-07 05:14:52'),
(154, 'tbl_users', 1, 'insert', 'login', NULL, 'Login berhasil user: Ahmad Rizqi Gustiansyah (admin)', 1, '2026-09-07 06:04:39'),
(155, 'tbl_users', 1, 'insert', 'login', NULL, 'Login berhasil user: Ahmad Rizqi Gustiansyah (admin)', 1, '2026-09-07 08:03:49'),
(156, 'tbl_users', 8, 'insert', 'login', NULL, 'Login berhasil user: Norma Putri Kusuma Dewi (norma)', 8, '2026-09-07 08:08:56'),
(157, 'tbl_rate_drafts', 15, 'delete', 'draft_title', NULL, 'Menghapus Master Rate: 15', 1, '2026-09-07 08:11:11'),
(158, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT Indonesia Thai Summit Plastech\' (APV Box / GIIC) Rp 478.525', 7, '2026-09-07 08:14:13'),
(159, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT Indonesia Thai Summit Plastech\' (APV Box / KIIC - Karawang Barat) Rp 577.769', 7, '2026-09-07 08:15:31'),
(160, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Hongfa Electronic Blok.C\' (APV Box / BSD - Tanggerang Selatan) Rp 1.201.102', 7, '2026-09-07 08:18:56'),
(161, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Trimitra Citrahasta\' (CDD / Delta Silicon) Rp 1.663.413', 7, '2026-09-07 08:23:27'),
(162, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Chao Long Motor Parts Indonesia\' (CDD / Delta Silicon) Rp 1.641.868', 7, '2026-09-07 08:24:01'),
(163, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT Toyo Denso Indonesia\' (CDD / MM2100) Rp 1.663.413', 7, '2026-09-07 08:26:03'),
(164, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Minda Asean Automotiv\' (CDD / KIIC - Karawang Barat) Rp 1.856.386', 7, '2026-09-07 08:49:20'),
(165, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Indonesia Koito\' (CDD / Kalihurip - Cikampek) Rp 2.070.349', 7, '2026-09-07 08:51:11'),
(166, 'tbl_mmp_head', 12, 'insert', 'view_edit_form', NULL, 'Membuka halaman edit MMP ID: 12 (SEPTEMBER 2026)', 1, '2026-09-07 08:53:48'),
(167, 'tbl_mmp_head', 12, 'insert', 'view_edit_form', NULL, 'Membuka halaman edit MMP ID: 12 (SEPTEMBER 2026)', 1, '2026-09-07 08:56:19'),
(168, 'tbl_mmp_head', 12, 'insert', 'view_edit_form', NULL, 'Membuka halaman edit MMP ID: 12 (SEPTEMBER 2026)', 1, '2026-09-07 08:57:57'),
(169, 'tbl_mmp_head', 12, 'insert', 'view_edit_form', NULL, 'Membuka halaman edit MMP ID: 12 (SEPTEMBER 2026)', 1, '2026-09-07 08:58:34'),
(170, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'Suzuki Indomobil Motor\' (CDD / Tambun) Rp 1.734.358', 7, '2026-09-07 09:01:39'),
(171, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Suzuki Indomobil Motor\' (CDD / Tambun) Rp 1.734.358', 7, '2026-09-07 09:03:04'),
(172, 'tbl_transport_cost', 0, 'delete', 'id', 'Customer: Suzuki Indomobil Motor | Ket: CDD / Tambun | Biaya: Rp 1.734.358', 'Menghapus master transport cost (ID: 39)', 7, '2026-09-07 09:03:12'),
(173, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Suzuki Indomobil Motor- GIIC\' (CDD / GIIC - Deltamas) Rp 1.765.968', 7, '2026-09-07 09:04:18'),
(174, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Cannet Elektrik Indonesia\' (CDD / Cikupa - Tanggerang) Rp 2.627.923', 7, '2026-09-07 09:05:08'),
(175, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Cipta Mandiri Wirasakti\' (CDD / Cileungsi) Rp 1.967.295', 7, '2026-09-07 09:06:21'),
(176, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Cipta Mandiri Wirasakti\' (CDD / Plumbon - Cirebon) Rp 4.279.826', 7, '2026-09-07 09:07:05'),
(177, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Hanamaster\' (CDD / Bandung) Rp 3.029.924', 7, '2026-09-07 09:07:45'),
(178, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Indonesia Thai Summit Plastech\' (CDD / KIIC - Karawang Barat) Rp 1.884.431', 7, '2026-09-07 09:08:46'),
(179, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'1	PT. Indonesia Thai Summit Plastech\' (CDD / GIIC - Deltamas) Rp 1.700.004', 7, '2026-09-07 09:09:32'),
(180, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Astra Jouku Indonesia\' (CDD / KIM - Karawang Timur) Rp 1.934.022', 7, '2026-09-07 09:14:01'),
(181, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Hyundai Motor Manufacturing Indonesia\' (CDD / GIIC - Deltamas) Rp 1.785.249', 7, '2026-09-07 09:14:46'),
(182, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Dae In Tech Indonesia\' (CDD / MM2100) Rp 1.665.340', 7, '2026-09-07 09:15:16'),
(183, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Hongfa Electronic Blok.C\' (CDD / BSD - Tanggerang Selatan) Rp 2.990.117', 7, '2026-09-07 09:16:50'),
(184, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Willfar Information Technology\' (CDD / Delta Silicon) Rp 1.685.031', 7, '2026-09-07 09:17:45'),
(185, 'tbl_transport_cost', 0, 'insert', 'customer_name', NULL, 'Menambahkan master transport cost customer \'PT. Astra Daihatsu Motor\' (CDD / Sunter - Jakarta Utara) Rp 2.325.340', 7, '2026-09-07 09:20:34'),
(186, 'tbl_transport_cost', 0, 'delete', 'id', 'Customer: Trimitra Citrahasta / TCH | Ket: CDD / Delta Silicon | Biaya: Rp 1.663.413', 'Menghapus master transport cost (ID: 19)', 7, '2026-09-07 09:26:41'),
(190, 'tbl_users', 1, 'insert', 'login', NULL, 'Login berhasil user: Ahmad Rizqi Gustiansyah (admin)', 1, '2026-09-08 01:33:39'),
(191, 'tbl_users', 4, 'insert', 'login', NULL, 'Login berhasil user: Angela Natalia Nurdin (natalia)', 4, '2026-09-08 01:46:24'),
(192, 'tbl_users', 1, 'insert', 'login', NULL, 'Login berhasil user: Ahmad Rizqi Gustiansyah (admin)', 1, '2026-09-08 01:47:08'),
(193, 'tbl_users', 4, 'insert', 'login', NULL, 'Login berhasil user: Angela Natalia Nurdin (natalia)', 4, '2026-09-08 01:59:51'),
(194, 'tbl_rate_drafts', 16, 'insert', 'draft_title', NULL, 'Membuat Master Rate Baru: 2026 TERMURAH (Ref: Bunga 5%, Listrik Up 5%, UMR 80%)', 4, '2026-09-08 02:14:30'),
(195, 'tbl_users', 4, 'update', 'session', NULL, 'Logout user ID: 4', 4, '2026-09-08 02:23:56'),
(196, 'tbl_users', 4, 'insert', 'login', NULL, 'Login berhasil user: Angela Natalia Nurdin (natalia)', 4, '2026-09-08 02:24:17'),
(197, 'tbl_users', 4, 'update', 'fullname', 'Angela Natalia Nurdin', 'Memperbarui data profil/password akun', 4, '2026-09-08 02:25:26'),
(198, 'tbl_users', 4, 'update', 'session', NULL, 'Logout user ID: 4', 4, '2026-09-08 02:25:31'),
(199, 'tbl_users', 4, 'insert', 'login', NULL, 'Login berhasil user: Angela Natalia Nurdin (natalia)', 4, '2026-09-08 02:25:42'),
(200, 'tbl_users', 4, 'insert', 'login', NULL, 'Login berhasil user: Angela Natalia Nurdin (natalia)', 4, '2026-09-08 02:25:44'),
(201, 'tbl_users', 4, 'insert', 'login', NULL, 'Login berhasil user: Angela Natalia Nurdin (natalia)', 4, '2026-09-08 02:25:44'),
(202, 'tbl_users', 4, 'update', 'session', NULL, 'Logout user ID: 4', 4, '2026-09-08 02:25:48'),
(203, 'tbl_users', 1, 'insert', 'login', NULL, 'Login berhasil user: Ahmad Rizqi Gustiansyah (admin)', 1, '2026-09-08 02:52:40'),
(204, 'tbl_rate_drafts', 16, 'update', 'draft_title', NULL, 'Memperbarui Master Rate: 2026 TERMURAH (Ref: Bunga 5%, Listrik Up 5%, UMR 80%)', 1, '2026-09-08 03:11:33'),
(205, 'tbl_rate_drafts', 16, 'update', 'draft_title', NULL, 'Memperbarui Master Rate: 2026 TERMURAH (Ref: Bunga 5%, Listrik Up 5%, UMR 80%)', 1, '2026-09-08 03:22:46'),
(206, 'tbl_users', 1, 'update', 'session', NULL, 'Logout user ID: 1', 1, '2026-09-08 06:08:53'),
(207, 'tbl_users', 1, 'insert', 'login', NULL, 'Login berhasil user: Ahmad Rizqi Gustiansyah (admin)', 1, '2026-09-08 07:33:26'),
(208, 'tbl_users', 1, 'update', 'session', NULL, 'Logout user ID: 1', 1, '2026-09-08 07:34:41'),
(209, 'tbl_users', 1, 'insert', 'login', NULL, 'Login berhasil user: Ahmad Rizqi Gustiansyah (admin)', 1, '2026-09-08 07:35:05'),
(210, 'tbl_users', 1, 'update', 'fullname', 'Ahmad Rizqi Gustiansyah', 'Memperbarui data profil/password akun', 1, '2026-09-08 07:36:00'),
(211, 'tbl_users', 1, 'insert', 'login', NULL, 'Login berhasil user: Ahmad Rizqi Gustiansyah (admin)', 1, '2026-09-08 07:36:28'),
(212, 'tbl_users', 1, 'update', 'session', NULL, 'Logout user ID: 1', 1, '2026-09-08 09:07:51'),
(227, 'tbl_users', 1, 'insert', 'login', NULL, 'Login berhasil user: Ahmad Rizqi Gustiansyah (admin)', 1, '2026-09-14 01:29:26'),
(228, 'tbl_packing_cost', 0, 'insert', 'excel_csv_import', NULL, 'Berhasil mengunduh/import massal 173 item data packing cost melalui file.', 1, '2026-09-14 03:53:41'),
(229, 'tbl_packing_cost', 175, 'update', 'part_name', 'Carton Box 166N Lens-Export', 'Memperbarui harga/detail packing: Carton Box 166N Lens-Export', 1, '2026-09-14 04:01:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_audit_log`
--

CREATE TABLE `tbl_audit_log` (
  `id` int(11) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `action_type` enum('insert','update','delete') DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_audit_log`
--

INSERT INTO `tbl_audit_log` (`id`, `table_name`, `record_id`, `action_type`, `field_name`, `old_value`, `new_value`, `changed_by`, `changed_at`) VALUES
(1, 'tbl_users', 1, 'update', 'fullname', 'Ahmad Rizqi Gustiansyah', 'Memperbarui data profil/password akun', 1, '2026-06-19 02:00:33'),
(2, 'tbl_users', 1, 'update', 'fullname', 'Ahmad Rizqi Gustiansyah', 'Memperbarui data profil/password akun', 1, '2026-06-19 02:00:50'),
(3, 'tbl_users', 1, 'update', 'fullname', 'Ahmad Rizqi Gustiansyah', 'Memperbarui data profil/password akun', 1, '2026-06-19 02:27:51'),
(4, 'tbl_packing_cost', 1, 'insert', 'part_name', 'Plastik 8x10', 'Menambahkan harga/detail packing: Plastik 8x10', 1, '2026-06-19 08:46:55'),
(5, 'tbl_packing_cost', 1, 'delete', 'part_name', 'Plastik 8x10', 'Menghapus harga/detail packing: Plastik 8x10', 1, '2026-06-19 09:00:54'),
(6, 'tbl_packing_cost', 1, 'delete', 'part_name', 'ID: 1', 'Menghapus harga/detail packing: ID: 1', 1, '2026-06-19 09:08:16'),
(7, 'tbl_packing_cost', 1, 'delete', 'part_name', 'ID: 1', 'Menghapus harga/detail packing: ID: 1', 1, '2026-06-19 09:08:21'),
(8, 'tbl_packing_cost', 0, 'insert', 'excel_csv_import', NULL, 'Berhasil mengunduh/import massal 16 item data packing cost melalui file.', 1, '2026-06-19 09:18:58'),
(9, 'tbl_rate_drafts', 1, 'insert', 'draft_title', NULL, 'Membuat Master Rate Baru: PERHITUNGAN TAHUN 2027 (Ref: BASED ON AKTUAL 2023)', 1, '2026-06-23 01:36:02'),
(10, 'tbl_rate_drafts', 2, 'insert', 'draft_title', NULL, 'Membuat Master Rate Baru: PERHITUNGAN TAHUN 2027 (Ref: BASED ON AKTUAL 2023)', 1, '2026-06-23 01:36:41'),
(11, 'tbl_rate_drafts', 1, 'delete', 'draft_title', NULL, 'Menghapus Master Rate: 1', 1, '2026-06-23 01:37:02'),
(12, 'tbl_rate_drafts', 1, 'delete', 'draft_title', NULL, 'Menghapus Master Rate: 1', 1, '2026-06-23 01:44:23'),
(13, 'tbl_rate_drafts', 1, 'delete', 'draft_title', NULL, 'Menghapus Master Rate: 1', 1, '2026-06-23 01:44:58'),
(14, 'tbl_quotation', 15, 'insert', 'quotation_no', NULL, 'Membuat Quotation Baru No: QT-2026-0001 | Customer: PT Chao Long Motor Parts Indonesia', 1, '2026-06-23 02:29:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_customer`
--

CREATE TABLE `tbl_customer` (
  `id` int(11) NOT NULL,
  `customer_code` varchar(50) DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `pic` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `payment_term` varchar(100) DEFAULT NULL,
  `currency` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_customer`
--

INSERT INTO `tbl_customer` (`id`, `customer_code`, `customer_name`, `address`, `pic`, `phone`, `email`, `payment_term`, `currency`, `created_at`, `updated_at`) VALUES
(62, NULL, 'PT Trimitra CitraHasta', '', '', '', '', '', '$', '2026-09-03 09:39:14', NULL),
(91, NULL, 'PT Astra Juoku Indonesia', '', '', '', '', '', 'USD', '2026-09-04 03:32:46', NULL),
(92, NULL, 'PT Ichikoh Indonesia', '', '', '', '', '', '$', '2026-09-04 06:11:21', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_dandori_master`
--

CREATE TABLE `tbl_dandori_master` (
  `id` int(11) NOT NULL,
  `mc_ton_min` int(11) NOT NULL,
  `mc_ton_max` int(11) NOT NULL,
  `machine_type` enum('horizontal','vertical') NOT NULL DEFAULT 'horizontal',
  `dandori_minutes` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_dandori_master`
--

INSERT INTO `tbl_dandori_master` (`id`, `mc_ton_min`, `mc_ton_max`, `machine_type`, `dandori_minutes`, `status`) VALUES
(1, 30, 40, 'horizontal', 135, 'inactive'),
(2, 30, 40, 'vertical', 135, 'active'),
(3, 40, 100, 'horizontal', 125, 'active'),
(4, 100, 180, 'horizontal', 140, 'active'),
(5, 210, 280, 'horizontal', 145, 'active'),
(6, 360, 850, 'horizontal', 170, 'active');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_exchange_rate`
--

CREATE TABLE `tbl_exchange_rate` (
  `id` int(11) NOT NULL,
  `currency_from` varchar(10) DEFAULT NULL,
  `currency_to` varchar(10) DEFAULT NULL,
  `rate` decimal(18,2) DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `revision_no` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_login_log`
--

CREATE TABLE `tbl_login_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `logout_time` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_login_log`
--

INSERT INTO `tbl_login_log` (`id`, `user_id`, `login_time`, `logout_time`, `ip_address`) VALUES
(212, 5, '2026-09-03 04:59:13', NULL, '::1'),
(213, 1, '2026-09-03 06:19:11', NULL, '::1'),
(214, 1, '2026-09-03 08:44:18', NULL, '::1'),
(215, 1, '2026-09-04 01:20:10', NULL, '::1'),
(216, 5, '2026-09-04 02:20:58', NULL, '::1'),
(217, 1, '2026-09-04 05:55:53', NULL, '::1'),
(218, 1, '2026-09-04 06:21:07', NULL, '::1'),
(219, 8, '2026-09-04 06:24:09', NULL, '::1'),
(220, 1, '2026-09-04 09:07:34', NULL, '::1'),
(221, 1, '2026-09-07 01:39:36', NULL, '::1'),
(222, 1, '2026-09-07 01:52:38', NULL, '::1'),
(223, 7, '2026-09-07 02:05:21', NULL, '::1'),
(224, 1, '2026-09-07 06:04:39', NULL, '::1'),
(225, 1, '2026-09-07 08:03:49', NULL, '::1'),
(226, 8, '2026-09-07 08:08:56', NULL, '::1'),
(227, 1, '2026-09-08 01:33:39', NULL, '::1'),
(228, 4, '2026-09-08 01:46:24', NULL, '::1'),
(229, 1, '2026-09-08 01:47:08', NULL, '::1'),
(230, 4, '2026-09-08 01:59:51', NULL, '::1'),
(231, 4, '2026-09-08 02:24:17', NULL, '::1'),
(232, 4, '2026-09-08 02:25:43', NULL, '::1'),
(233, 4, '2026-09-08 02:25:44', NULL, '::1'),
(234, 4, '2026-09-08 02:25:44', NULL, '::1'),
(235, 1, '2026-09-08 02:52:40', NULL, '::1'),
(236, 1, '2026-09-08 07:33:26', NULL, '::1'),
(237, 1, '2026-09-08 07:35:05', NULL, '::1'),
(238, 1, '2026-09-08 07:36:28', NULL, '::1'),
(239, 1, '2026-09-08 09:15:18', NULL, '::1'),
(240, 1, '2026-09-08 09:15:59', NULL, '::1'),
(241, 1, '2026-09-08 09:21:31', NULL, '::1'),
(242, 1, '2026-09-09 01:30:35', NULL, '::1'),
(243, 1, '2026-09-09 07:41:25', NULL, '::1'),
(244, 1, '2026-09-09 08:05:10', NULL, '::1'),
(245, 8, '2026-09-09 08:46:33', NULL, '::1'),
(246, 1, '2026-09-10 09:21:37', NULL, '::1'),
(247, 1, '2026-09-14 01:29:28', NULL, '::1');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_master_tonnage`
--

CREATE TABLE `tbl_master_tonnage` (
  `id` int(11) NOT NULL,
  `tonnage_key` varchar(20) NOT NULL,
  `tonnage_label` varchar(50) NOT NULL,
  `tonnage_value` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_master_tonnage`
--

INSERT INTO `tbl_master_tonnage` (`id`, `tonnage_key`, `tonnage_label`, `tonnage_value`, `sort_order`, `is_active`) VALUES
(1, '30_40', '30 - 40 TON', 35, 1, 1),
(2, '30_v', '30 TON VERTIKAL', 31, 2, 1),
(3, '40_v', '40 TON VERTIKAL', 41, 3, 1),
(4, '60', '60 TON', 60, 4, 1),
(5, '80', '80 TON', 80, 5, 1),
(6, '100', '100 TON', 100, 6, 1),
(7, '130', '130 TON', 130, 7, 1),
(8, '170', '170 TON', 170, 8, 1),
(9, '200', '200 TON', 200, 9, 1),
(10, '220', '220 TON', 220, 10, 1),
(11, '260', '260 TON', 260, 11, 1),
(12, '280', '280 TON', 280, 12, 1),
(13, '360', '360 TON', 360, 13, 1),
(14, '450', '450 TON', 450, 14, 1),
(15, '650', '650 TON', 650, 15, 1),
(16, '850', '850 TON', 850, 16, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_material_master`
--

CREATE TABLE `tbl_material_master` (
  `id` int(11) NOT NULL,
  `material_name` varchar(150) DEFAULT NULL,
  `material_type` varchar(100) DEFAULT NULL,
  `basic_price` decimal(18,2) DEFAULT NULL,
  `currency` varchar(20) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `revision_no` int(11) DEFAULT 0,
  `effective_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_mmp_det`
--

CREATE TABLE `tbl_mmp_det` (
  `detail_id` int(11) NOT NULL,
  `mmp_id` int(11) NOT NULL,
  `customer` varchar(150) NOT NULL,
  `part_number` varchar(100) NOT NULL,
  `part_name` varchar(200) NOT NULL,
  `mat_quotation` varchar(100) DEFAULT NULL,
  `mat_aktual` varchar(100) DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `harga_mkr` decimal(12,2) DEFAULT 0.00,
  `harga_pch` decimal(12,2) DEFAULT 0.00,
  `diff_kg` decimal(12,2) DEFAULT 0.00,
  `berat_part` decimal(10,3) DEFAULT 0.000,
  `qty_do` int(11) DEFAULT 0,
  `amt_mkr` decimal(15,2) DEFAULT 0.00,
  `amt_pch` decimal(15,2) DEFAULT 0.00,
  `diff_nominal` decimal(15,2) DEFAULT 0.00,
  `diff_persen` varchar(10) DEFAULT '0%',
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_mmp_det`
--

INSERT INTO `tbl_mmp_det` (`detail_id`, `mmp_id`, `customer`, `part_number`, `part_name`, `mat_quotation`, `mat_aktual`, `supplier`, `item_code`, `harga_mkr`, `harga_pch`, `diff_kg`, `berat_part`, `qty_do`, `amt_mkr`, `amt_pch`, `diff_nominal`, `diff_persen`, `remark`) VALUES
(17, 12, ' Astra Juoku Indonesia ', ' 25-AT07B ', ' HOUSING DRL, RH (D55L) ', ' PC MAKROLON 2405 901510 (BLACK) ', ' PC MAKROLON 2405 901510 MAS048 UWP BLACK ', ' ADYABINA ', '', 0.00, 57596.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(18, 12, ' Astra Juoku Indonesia ', ' 25-AT08B ', ' HOUSING DRL, LH (D55L) ', ' PC MAKROLON 2405 901510 (BLACK) ', ' PC MAKROLON 2405 901510 MAS048 UWP BLACK ', ' ADYABINA ', '', 0.00, 57596.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(19, 12, ' Astra Juoku Indonesia ', ' 45-AT07B1 ', ' HOUSING RR GCC, RH (D55L) ', ' PC MAKROLON 2405 702395 (GREY) ', ' PC MAKROLON 2405 702395 MAS048 GREY ', ' ADYABINA ', '', 0.00, 57596.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(20, 12, ' Astra Juoku Indonesia ', ' 45-AT08B1 ', ' HOUSING RR GCC, LH (D55L) ', ' PC MAKROLON 2405 702395 (GREY) ', ' PC MAKROLON 2405 702395 MAS048 GREY ', ' ADYABINA ', '', 0.00, 57596.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(21, 12, ' Astra Juoku Indonesia ', ' 15-0G43BV11 ', ' HOUSING BDG, RH ', ' ASA LI935 94592 ', ' ASA LI935 94592 BLACK ', ' ADYABINA ', '', 0.00, 53361.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(22, 12, ' Astra Juoku Indonesia ', ' 15-0G44BV11 ', ' HOUSING BDG, LH ', ' ASA LI935 94592 ', ' ASA LI935 94592 BLACK ', ' ADYABINA ', '', 0.00, 53361.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(23, 12, ' Astra Juoku Indonesia ', ' 45-0G51B ', ' HOUSING, RH ', ' ASA LI935 94592 ', ' ASA LI935 94592 BLACK ', ' ADYABINA ', '', 0.00, 53361.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(24, 12, ' Astra Juoku Indonesia ', ' 45-0G52B ', ' HOUSING, LH ', ' ASA LI935 94592 ', ' ASA LI935 94592 BLACK ', ' ADYABINA ', '', 0.00, 53361.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(25, 12, ' Astra Juoku Indonesia ', ' 16-0L87M ', ' INNER LENS ', ' PC SABIC RESIN 1003R ', ' PC (LEXAN AMBER 1601.87M) KUNING TRANSP ', ' Astra Juoku Indonesia ', '', 0.00, 75128.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(26, 12, ' Astra Juoku Indonesia ', ' 45-AT07L1I ', ' LENS RR GCC, RH (D55L) ', ' PC-GE92403R ', ' PC-GE92403R RED       ', ' Astra Juoku Indonesia ', '', 0.00, 62000.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(27, 12, ' Astra Juoku Indonesia ', ' 45-AT08L1I ', ' LENS RR GCC, LH (D55L) ', ' PC-GE92403R ', ' PC-GE92403R RED       ', ' Astra Juoku Indonesia ', '', 0.00, 62000.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(28, 12, ' Astra Juoku Indonesia ', ' 16-0L87L ', ' LENS ', ' PMMA CM-205 CLEAR ', ' PMMA CM-205 CLEAR ', ' Astra Juoku Indonesia ', '', 0.00, 37445.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(29, 12, ' Astra Juoku Indonesia ', ' 11-0G43L1 ', ' LENS CLEAR, RH (D21N) ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(30, 12, ' Astra Juoku Indonesia ', ' 11-0G44L1 ', ' LENS CLEAR, LH (D21N) ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(31, 12, ' Astra Juoku Indonesia ', ' 11-AT11M ', ' LIGHT GUIDE, RH (D26A) ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(32, 12, ' Astra Juoku Indonesia ', ' 11-AT12M ', ' LIGHT GUIDE, LH (D26A) ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(33, 12, ' Astra Juoku Indonesia ', ' 16-0G51L ', ' LENS, RH ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(34, 12, ' Astra Juoku Indonesia ', ' 16-0G51M ', ' INNER LENS, RH ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(35, 12, ' Astra Juoku Indonesia ', ' 16-0G52L ', ' LENS, LH ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(36, 12, ' Astra Juoku Indonesia ', ' 16-0G52M ', ' INNER LENS, LH ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(37, 12, ' Astra Juoku Indonesia ', ' 90-AN01L ', ' LENS (KUBOTA) ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(38, 12, ' Astra Juoku Indonesia ', ' 13-0M43L ', ' LENS STOP CENTER (D01N) ', ' PMMA SUMIPEX 4334 RED ', ' PMMA SUMIPEX 4334 RED ', ' INABATA ', '', 0.00, 68945.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(39, 12, ' Astra Juoku Indonesia ', ' 15-AT01L ', ' LENS BDG, RH ', ' PMMA SUMIPEX 4334 RED ', ' PMMA SUMIPEX 4334 RED ', ' INABATA ', '', 0.00, 68945.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(40, 12, ' Astra Juoku Indonesia ', ' 15-AT02L ', ' LENS BDG, LH ', ' PMMA SUMIPEX 4334 RED ', ' PMMA SUMIPEX 4334 RED ', ' INABATA ', '', 0.00, 68945.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(41, 12, ' Astra Juoku Indonesia ', ' 45-AT07L  ', ' LENS RR, RH (D55L) ', ' PMMA SUMIPEX 4334 RED ', ' PMMA SUMIPEX 4334 RED ', ' INABATA ', '', 0.00, 68945.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(42, 12, ' Astra Juoku Indonesia ', ' 45-AT08L  ', ' LENS RR, LH (D55L) ', ' PMMA SUMIPEX 4334 RED ', ' PMMA SUMIPEX 4334 RED ', ' INABATA ', '', 0.00, 68945.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(43, 12, ' Astra Juoku Indonesia ', ' 45-AT11L ', ' LENS, RH (D26A) ', ' PMMA SUMIPEX 4334 RED ', ' PMMA SUMIPEX 4334 RED ', ' INABATA ', '', 0.00, 68945.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(44, 12, ' Astra Juoku Indonesia ', ' 45-AT12L ', ' LENS, LH (D26A) ', ' PMMA SUMIPEX 4334 RED ', ' PMMA SUMIPEX 4334 RED ', ' INABATA ', '', 0.00, 68945.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(45, 12, ' Astra Juoku Indonesia ', ' 11-0L61L1 ', ' TAIL STOP LENS, RH (D40D) ', ' PMMA SUMIPEX 4312 RED  ', ' PMMA SUMIPEX 4312 RED ', ' INABATA ', '', 0.00, 67607.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(46, 12, ' Astra Juoku Indonesia ', ' 11-0L62L1 ', ' TAIL STOP LENS, LH (D40D) ', ' PMMA SUMIPEX 4312 RED  ', ' PMMA SUMIPEX 4312 RED  ', ' INABATA ', '', 0.00, 67607.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(47, 12, ' Astra Juoku Indonesia ', ' 45-0G05L ', ' LENS REFF ASSY (D91L) ', ' PMMA SUMIPEX 4312 RED  ', ' PMMA SUMIPEX 4312 RED  ', ' INABATA ', '', 0.00, 67607.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(48, 12, ' Astra Juoku Indonesia ', ' 14-0L87L ', ' LENS ', ' PC SABIC RESIN 0703R ', ' PC PANLITE L1225Z-100 ', ' WWRC ', '', 0.00, 45738.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(49, 12, ' Astra Juoku Indonesia ', ' 25-AT07LI ', ' LENS DRL, RH (D55L) ', ' PC L1225Z-100 ', ' PC PANLITE L1225Z-100 ', ' WWRC ', '', 0.00, 45738.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(50, 12, ' Astra Juoku Indonesia ', ' 25-AT08LI ', ' LENS DRL, LH (D55L) ', ' PC L1225Z-100 ', ' PC PANLITE L1225Z-100 ', ' WWRC ', '', 0.00, 45738.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(51, 12, ' Astra Juoku Indonesia ', ' 15-AT13H ', ' HOLDER, RH (D26A) ', ' PP AM3304 WH2579 WWC-70 ', ' PP AM3304 WH2579 WWC-70 ', ' WWRC ', '', 0.00, 35625.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(52, 12, ' Astra Juoku Indonesia ', ' 15-AT14H ', ' HOLDER, LH (D26A) ', ' PP AM3304 WH2579 WWC-70 ', ' PP AM3304 WH2579 WWC-70 ', ' WWRC ', '', 0.00, 35625.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(53, 12, ' Astra Juoku Indonesia ', ' 16-A701L ', ' LENS 5H45 STSL RH ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(54, 12, ' Astra Juoku Indonesia ', ' 16-A702L ', ' LENS 5H45 STSL LH ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(55, 12, ' Astra Juoku Indonesia ', ' 25-AT21L ', ' D02A LENS ILLUMINATION ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(56, 12, ' Astra Juoku Indonesia ', ' 25-AT22L ', ' D02A LENS ILLUMINATION ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(57, 12, ' Astra Juoku Indonesia ', ' 93-AD11L ', ' LENS K2VM WINKER REAR ', ' PMMA SUMIPEX MH NAT ', ' PMMA SUMIPEX MH NATURAL AJI ', ' INABATA ', '', 0.00, 60560.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(58, 12, ' Astra Juoku Indonesia ', ' 15-0G41L ', ' LENS BDG RH ', ' PMMA CM96108R (PMMA 4334 RED) ', ' PMMA SUMIPEX 4334 RED ', ' INABATA ', '', 0.00, 68945.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(59, 12, ' Astra Juoku Indonesia ', ' 15-0G42L ', ' LEND BDG LH ', ' PMMA CM96108R (PMMA 4334 RED) ', ' PMMA SUMIPEX 4334 RED ', ' INABATA ', '', 0.00, 68945.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(60, 12, ' Astra Juoku Indonesia ', ' 16-AR01M ', ' LIGHT GUIDE  INJECTION  RH DM022 SRL ', ' PC MAKROLON  LED2245 CLEAR ', ' PC MAKROLON  LED2245 CLEAR ', ' ADYABINA ', '', 0.00, 82159.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', ''),
(61, 12, ' Astra Juoku Indonesia ', ' 16-AR02M ', ' LIGHT GUIDE INJECTION LH DM022 SRL ', ' PC MAKROLON LED2245-CLEAR ', ' PC MAKROLON  LED2245 CLEAR ', ' ADYABINA ', '', 0.00, 82159.00, 0.00, 0.000, 0, 0.00, 0.00, 0.00, '0%', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_mmp_head`
--

CREATE TABLE `tbl_mmp_head` (
  `mmp_id` int(11) NOT NULL,
  `tanggal_input` datetime DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `keterangan_umum` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_mmp_head`
--

INSERT INTO `tbl_mmp_head` (`mmp_id`, `tanggal_input`, `user_id`, `keterangan_umum`) VALUES
(12, '2026-09-03 13:16:05', 5, 'SEPTEMBER 2026');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_mold_maintenance_master`
--

CREATE TABLE `tbl_mold_maintenance_master` (
  `id` int(11) NOT NULL,
  `category_type` enum('material','runner') NOT NULL,
  `key_name` varchar(50) NOT NULL,
  `cost_per_month` decimal(12,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `image_label` varchar(255) DEFAULT NULL,
  `flexible_fields` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_mold_maintenance_master`
--

INSERT INTO `tbl_mold_maintenance_master` (`id`, `category_type`, `key_name`, `cost_per_month`, `image`, `status`, `image_label`, `flexible_fields`) VALUES
(1, 'material', 'SPECIAL_MAT', 1000000.00, '', 'active', '', '[]'),
(2, 'material', 'OTHER_MAT', 500000.00, NULL, 'active', NULL, NULL),
(3, 'runner', 'COLD_RUNNER', 500000.00, NULL, 'active', NULL, NULL),
(4, 'runner', 'HOT_RUNNER', 1000000.00, NULL, 'active', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_packing_cost`
--

CREATE TABLE `tbl_packing_cost` (
  `id` int(11) NOT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `part_name` varchar(150) DEFAULT NULL,
  `item_category` varchar(100) DEFAULT NULL,
  `p` decimal(18,2) DEFAULT NULL,
  `l` decimal(18,2) DEFAULT NULL,
  `t` decimal(18,2) DEFAULT NULL,
  `volume_cm3` decimal(18,4) DEFAULT 0.0000,
  `max_capacity_gram` int(11) DEFAULT 0,
  `weight_gram` decimal(18,2) DEFAULT 0.00,
  `supplier_id` int(11) DEFAULT NULL,
  `profit_percent` decimal(18,2) DEFAULT 0.00,
  `selling_price` decimal(18,2) DEFAULT 0.00,
  `unit` varchar(50) DEFAULT NULL,
  `purchase_price` decimal(18,2) DEFAULT 0.00,
  `item_price` decimal(18,2) DEFAULT NULL,
  `size_detail` varchar(255) DEFAULT NULL,
  `returnable` enum('yes','no') DEFAULT 'no',
  `lifetime_month` int(11) DEFAULT 0,
  `depreciation_type` enum('monthly','yearly') DEFAULT 'monthly',
  `revision_no` int(11) DEFAULT 0,
  `revision_remark` text DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active_for_packing_standard` enum('yes','no') DEFAULT 'yes',
  `qty_per_kg` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_packing_cost`
--

INSERT INTO `tbl_packing_cost` (`id`, `item_code`, `part_name`, `item_category`, `p`, `l`, `t`, `volume_cm3`, `max_capacity_gram`, `weight_gram`, `supplier_id`, `profit_percent`, `selling_price`, `unit`, `purchase_price`, `item_price`, `size_detail`, `returnable`, `lifetime_month`, `depreciation_type`, `revision_no`, `revision_remark`, `effective_date`, `status`, `created_by`, `created_at`, `updated_at`, `active_for_packing_standard`, `qty_per_kg`) VALUES
(19, NULL, 'PLASTIK 8 x 10', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 52.47, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:09', '2026-09-14 03:29:09', 'yes', 667.00),
(20, NULL, 'PLASTIK LDPE 10 x 20 TEBAL 0.05', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 73.53, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:10', '2026-09-14 03:29:10', 'yes', 476.00),
(21, NULL, 'PLASTIK 12 x 20', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 87.50, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:10', '2026-09-14 03:29:10', 'yes', 400.00),
(22, NULL, 'PLASTIK LDPE 15 X 20 TEBAL 0.05', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 87.50, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:10', '2026-09-14 03:29:10', 'yes', 400.00),
(23, NULL, 'PLASTIK LDPE 15 X 20 TEBAL 0.08', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 100.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:10', '2026-09-14 03:29:10', 'yes', 350.00),
(24, NULL, 'PLASTIK LDPE 18 x 25 TEBAL 0.05', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 111.82, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:10', '2026-09-14 03:29:10', 'yes', 313.00),
(25, NULL, 'PLASTIK LDPE 18 x 25 TEBAL 0,08', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.01, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 157.66, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:10', '2026-09-14 03:29:10', 'yes', 222.00),
(26, NULL, 'PLASTIK LDPE 20 x 30 TEBAL 0.05', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.01, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 175.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:10', '2026-09-14 03:29:10', 'yes', 200.00),
(27, NULL, 'PLASTIK LDPE 20 x 30 TEBAL 0,08', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.01, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:10', '2026-09-14 03:29:10', 'yes', 200.00),
(28, NULL, 'PLASTIK LDPE 20 x 35 TEBAL 0,05', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.01, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 175.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:10', '2026-09-14 03:29:10', 'yes', 111.00),
(29, NULL, 'PLASTIK LDPE 25 x 45 TEBAL 0.05', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.02, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 315.32, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:10', '2026-09-14 03:29:10', 'yes', 67.00),
(30, NULL, 'PLASTIK LDPE 25 x 45 TEBAL 0.08', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.02, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 522.39, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:11', '2026-09-14 03:29:11', 'yes', 61.00),
(31, NULL, 'PLASTIK LDPE 35 x 50 TEBAL 0.05', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.02, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 573.77, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:11', '2026-09-14 03:29:11', 'yes', 56.00),
(32, NULL, 'PLASTIK LDPE 50 x 70 TEBAL 0.05', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.06, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 625.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:11', '2026-09-14 03:29:11', 'yes', 17.00),
(33, NULL, 'PLASTIK LDPE 60 x 100 TEBAL 0.05', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 2058.82, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:11', '2026-09-14 03:29:11', 'yes', 26.00),
(34, NULL, 'PLASTIK HDPE 116/62 x 55 x 0.3', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 35000.00, 1346.15, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:11', '2026-09-14 03:29:11', 'yes', 27.00),
(35, NULL, 'PLASTIK HDPE 116/62 x 85 x 0,3', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 35000.00, 1296.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:11', '2026-09-14 03:29:11', 'yes', 0.00),
(36, NULL, 'PLASTIK HDPE uk 60 x 100 0,5', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 35000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:11', '2026-09-14 03:29:11', 'yes', 0.00),
(37, NULL, 'PLASTIK LDPE 25 x 40 TEBAL 0,05', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 35000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:11', '2026-09-14 03:29:11', 'yes', 0.00),
(38, NULL, 'PLASTIK LDPE 50 x 70 TEBAL 0,03', 'PLASTIK', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 35000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:29:11', '2026-09-14 03:29:11', 'yes', 0.00),
(41, NULL, 'TRAY 16-AR01M LH (PS BLACK 0.9MM)', 'TRAY PANASONIC', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 10900.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:25', '2026-09-14 03:53:25', 'yes', 0.00),
(42, NULL, 'TRAY 16-AR01M RH (PS BLACK 0.9MM)', 'TRAY PANASONIC', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 10900.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:25', '2026-09-14 03:53:25', 'yes', 0.00),
(43, NULL, 'TRAY K2VM BACK LAMP HIPS BLACK t2.6 x 400 x 350 x 40', 'TRAY PANASONIC', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 93500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:25', '2026-09-14 03:53:25', 'yes', 0.00),
(44, NULL, 'TRAY K2VM BACK LAMP HIPS WHITE t2.6 x 400 x 350 x 40', 'TRAY PANASONIC', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 93500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:26', '2026-09-14 03:53:26', 'yes', 0.00),
(45, NULL, 'BLISTER TRAY P638 X T12 X L243 TEBAL 0.3 MM', 'TRAY PANASONIC', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 4000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:26', '2026-09-14 03:53:26', 'yes', 0.00),
(46, NULL, 'BLISTER TRAY P500 X T12 X L305', 'TRAY PANASONIC', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 3500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:26', '2026-09-14 03:53:26', 'yes', 0.00),
(47, NULL, 'BLISTER TRAY P500 x T18.5 x 365MM', 'TRAY PANASONIC', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 8500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:26', '2026-09-14 03:53:26', 'yes', 0.00),
(48, NULL, 'BLISTER TRAY P638 X T15 X L243 TEBAL 0.5 MM', 'TRAY PANASONIC', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, 'LEMBAR', 5050.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:26', '2026-09-14 03:53:26', 'yes', 0.00),
(49, NULL, 'CONTAINER RABBIT NO 6262 (335 x 168 x 100)', 'CONTAINER', NULL, NULL, NULL, 5628.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 37000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:26', '2026-09-14 03:53:26', 'yes', 0.00),
(50, NULL, 'CONTAINER RABBIT NO.2004 ( 620 x 430 x 250 )', 'CONTAINER', NULL, NULL, NULL, 66650.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 90000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:26', '2026-09-14 03:53:26', 'yes', 0.00),
(51, NULL, 'CONTAINER RABBIT NO.2008 ( 620 x 430 x 385 )', 'CONTAINER', NULL, NULL, NULL, 102641.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 115000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:26', '2026-09-14 03:53:26', 'yes', 0.00),
(52, NULL, 'CONTAINER RABBIT NO.2033 ( 620 x 430 x 200 )', 'CONTAINER', NULL, NULL, NULL, 53320.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 94000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:26', '2026-09-14 03:53:26', 'yes', 0.00),
(53, NULL, 'CONTAINER RABBIT NO.2055 ( 620 x 430 x 275 )', 'CONTAINER', NULL, NULL, NULL, 48000.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 110000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:26', '2026-09-14 03:53:26', 'yes', 0.00),
(54, NULL, 'CONTAINER RABBIT NO.2066 ( 620 x 430 x 310 )', 'CONTAINER', NULL, NULL, NULL, 82646.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 130000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:26', '2026-09-14 03:53:26', 'yes', 0.00),
(55, NULL, 'CONTAINER RABBIT NO.3004 ( 500 x 400 x 325 )', 'CONTAINER', NULL, NULL, NULL, 65000.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 102000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:27', '2026-09-14 03:53:27', 'yes', 0.00),
(56, NULL, 'CONTAINER RABBIT NO.3303  ( 600 x 400 x 200 )', 'CONTAINER', NULL, NULL, NULL, 48000.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 92000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:27', '2026-09-14 03:53:27', 'yes', 0.00),
(57, NULL, 'CONTAINER RABBIT NO.4066 ( 375 x 320 x 165 )', 'CONTAINER', NULL, NULL, NULL, 19800.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 71000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:27', '2026-09-14 03:53:27', 'yes', 0.00),
(58, NULL, 'CONTAINER RABBIT NO.4088 ( 425 x 290 x 205 )', 'CONTAINER', NULL, NULL, NULL, 25266.2500, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 72000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:27', '2026-09-14 03:53:27', 'yes', 0.00),
(59, NULL, 'CONTAINER RABBIT NO.6011 ( 415 x 285 x 160 )', 'CONTAINER', NULL, NULL, NULL, 18924.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 60000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(60, NULL, 'CONTAINER RABBIT NO.7007 ( 680 x 490 x 420 )', 'CONTAINER', NULL, NULL, NULL, 139944.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 185000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(61, NULL, 'CONTAINER RABBIT NO.7008 ( 840 x 630 x 460 )', 'CONTAINER', NULL, NULL, NULL, 243432.0000, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 280000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(62, NULL, 'CONTAINER RABBIT NO.7033 ( 745 x 515 x 350 )', 'CONTAINER', NULL, NULL, NULL, 134286.2500, 0, 0.00, NULL, 0.00, 0.00, 'PCS', 183000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(63, NULL, 'BOX + PARTISI 14 KOLOM + COVER 470*515*220 (HOUSING 6G7) ICHIKOH', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 220000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(64, NULL, 'BOX V + PARISI 6 KOLOM + COVER BEZEL 3MOA', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 149000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(65, NULL, 'IMPRA BOARD (500x560x330) TANPA SEKAT', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 100000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(66, NULL, 'IMPRA BOARD 420X430X190mm BEZEL/GARNISH/TRAY', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 86000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(67, NULL, 'IMPRA BOARD 425x252x310 mm Mirror Exp (type FU)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 100000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(68, NULL, 'IMPRA BOARD 460X300X120 TYPE YHA', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 68000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(69, NULL, 'IMPRA BOARD 465x325x185 (V Box) KOITO', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 72000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(70, NULL, 'IMPRA BOARD 465x325x185 GARNISH ITSP', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 72000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(71, NULL, 'IMPRA BOARD 540*410*170 COVER STERING YHA', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 95700.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(72, NULL, 'IMPRA BOARD 555X450X270 (Tray Full Tank XE611)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 171500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(73, NULL, 'IMPRA BOARD 565x465x310 mm Mirror Exp (type Smash)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 165000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(74, NULL, 'IMPRA BOARD 575x430x280 (Window Front V08)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 79500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(75, NULL, 'IMPRA BOARD 580*500*180 GRIP COMP THROTTLE', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 125000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:28', '2026-09-14 03:53:28', 'yes', 0.00),
(76, NULL, 'IMPRA BOARD 600x560x195 (Brace Cowling Lower)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 100000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(77, NULL, 'IMPRA BOARD 600x680x355 (Box Lugage)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 160000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(78, NULL, 'IMPRA BOARD 630x430x280 Housing RC/L 83', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 98000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(79, NULL, 'IMPRA BOARD 680x430x340 (PBox) KOITO', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 110000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(80, NULL, 'IMPRA BOARD 680x430x340 Body Koito (PBox) LIPAT', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 153000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(81, NULL, 'IMPRA BOARD 680x430x340 WARNA HIJAU CORNER PUTIH (AJI)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 98000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(82, NULL, 'IMPRA BOARD 680x460x340 TRAY COMP YHA (BIRU)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 110000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(83, NULL, 'IMPRA BOARD UK. 500x295x150 COVER RR', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 90000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(84, NULL, 'IMPRABOARD + PARTISI 20 KOLOM + COVER PLASTIK PANEL CONSOLE UPPER RR (LHD)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 323000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(85, NULL, 'IMPRABOARD + PARTISI 24 KOLOM FULL EVA + COVER PLASTIK', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 275000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(86, NULL, 'IMPRABOARD + PARTISI 35 KOLOM + COVER PLASTIK INNER HOUSING B LH P63', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 141500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(87, NULL, 'IMPRABOARD + PARTISI 35 KOLOM + COVER PLASTIK INNER HOUSING B RH P63', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 141500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(88, NULL, 'IMPRABOARD +PARTISI COVER STEERING BEARING 380*295*110', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 112000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(89, NULL, 'IMPRABOARD 2 LAPIS + PARITIS 20 KOLOM UK 740*550*330 (IY 753)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 356000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(90, NULL, 'IMPRABOARD 2 LAPIS + PARITIS 20 KOLOM UK 740*550*330 (KS PROJECT)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 356000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(91, NULL, 'IMPRABOARD 325x450x150mm BRACKET RCL-D 9D5', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 80000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(92, NULL, 'IMPRABOARD 325x450x150mm BRACKET RCL-T 9D5', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 80000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(93, NULL, 'IMPRABOARD 400*400*170 INNER LENS B (9B8)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 91500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:29', '2026-09-14 03:53:29', 'yes', 0.00),
(94, NULL, 'IMPRABOARD 460*320*350 HOUSING ILUMINATION LH D02A', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 114000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(95, NULL, 'IMPRABOARD 460*320*350 HOUSING ILUMINATION RH D02A', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 114000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(96, NULL, 'IMPRABOARD 460*320*350 LENS ILUMINATION R/L D02A', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 114000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(97, NULL, 'IMPRABOARD 460*360*285 INNER LENS A/B 6G7', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 105000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(98, NULL, 'IMPRABOARD 460*410*180 LEAR', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 99750.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(99, NULL, 'IMPRABOARD 465*325*185 WARNA HIJAU CORNER KUNING (CHAO LONG)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 65000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(100, NULL, 'IMPRABOARD 465*325*185 WARNA HIJAU CORNER PUTIH (AJI)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 72000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(101, NULL, 'IMPRABOARD 500*295*185 HEAD LAMP REFLEKTOR R/L', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 70000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(102, NULL, 'IMPRABOARD 500*295*185 REFLEKTOR R/L 2NO', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 70000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(103, NULL, 'IMPRABOARD 515 *490*150 HOLDER CUP ASSY ITSP', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 87600.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(104, NULL, 'IMPRABOARD 515*315*280 BODY R03 + LAYER + COVER', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 130000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(105, NULL, 'IMPRABOARD 560*400*185 BRACKET D (9B8)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 96500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(106, NULL, 'IMPRABOARD 560*400*235 BRACKET A (9B8)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 104500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(107, NULL, 'IMPRABOARD 560*400*235 BRACKET B (9B8)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 104500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(108, NULL, 'IMPRABOARD 560*400*235 INNER PANEL B (9B8)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 104500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(109, NULL, 'IMPRABOARD 560*400*260 EL1 FAMILY', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 110000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(110, NULL, 'IMPRABOARD 560*400*260 INNER LENS C P59 ICHIKOH', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 110000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:30', '2026-09-14 03:53:30', 'yes', 0.00),
(111, NULL, 'IMPRABOARD 560*400-260 EL9 FAMILY', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 110000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(112, NULL, 'IMPRABOARD 585x425x260 BOX LED BATERY & SPROKET', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 95000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(113, NULL, 'IMPRABOARD 620*440*245 ICHIKOH P59', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 123500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(114, NULL, 'IMPRABOARD 670*460*280 INNER LENS A/B 6G7', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 110000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(115, NULL, 'IMPRABOARD 675*325*275 HOLDER D26A (AJI)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 110000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(116, NULL, 'IMPRABOARD 720*380*340 UPPER LENS B3W', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 132000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(117, NULL, 'IMPRABOARD LENS K2VM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 95000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(118, NULL, 'IMPRABOARD REFLECTOR CLL 737D UK 10000x460x200', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 150000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(119, NULL, 'IMPRABOARD T5MM + COVER 2 SISI (REFLECTOR CLL 73)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 164000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(120, NULL, 'PARTISI 100 KOLOM + COVER PLASTIK + IMPRA T5 DIJAHIT BEZEL CAP A P63', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 99750.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(121, NULL, 'PARTISI 100 KOLOM + COVER PLASTIK + IMPRA T5 DIJAHIT BEZEL CAP B P63', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 108000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(122, NULL, 'IMPRA BOARD 680x430x340 BODY K1AL KOITO', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 105000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(123, NULL, 'IMPRABOARD 560*400*185 BRACKET C (9B8)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 96500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:31', '2026-09-14 03:53:31', 'yes', 0.00),
(124, NULL, 'PARTISI 10 KOLOM BRACKET D03B', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 48000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:32', '2026-09-14 03:53:32', 'yes', 0.00),
(125, NULL, 'PARTISI 10 KOLOM INNER PANEL B RL (9B8)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 72500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:32', '2026-09-14 03:53:32', 'yes', 0.00),
(126, NULL, 'PARTISI 15 KOLOM HOUSING RR RH/LH EL9 CROSS', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 34920.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:32', '2026-09-14 03:53:32', 'yes', 0.00),
(127, NULL, 'PARTISI 15 KOLOM LENS RR RH/LH EL9 CROSS', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 34920.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:32', '2026-09-14 03:53:32', 'yes', 0.00),
(128, NULL, 'PARTISI 25 KOLOM HOUSING ILUMINATION LH D02A', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 83000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:32', '2026-09-14 03:53:32', 'yes', 0.00),
(129, NULL, 'PARTISI 25 KOLOM HOUSING ILUMINATION RH D02A', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 83000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:32', '2026-09-14 03:53:32', 'yes', 0.00),
(130, NULL, 'PARTISI 25 KOLOM LENS ILUMINATION R/L D02A', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 185000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:32', '2026-09-14 03:53:32', 'yes', 0.00),
(131, NULL, 'PARTISI 42 KOLOM BRACKET C RL (9B8)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 108000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:32', '2026-09-14 03:53:32', 'yes', 0.00),
(132, NULL, 'PARTISI 5 KOLOM BRACKET A RL (9B8)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 46000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:32', '2026-09-14 03:53:32', 'yes', 0.00),
(133, NULL, 'PARTISI 5 KOLOM BRACKET B RL (9B8)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 47200.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:32', '2026-09-14 03:53:32', 'yes', 0.00),
(134, NULL, 'PARTISI 64 KOLOM BRACKET D RL (9B8)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 72500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:32', '2026-09-14 03:53:32', 'yes', 0.00),
(135, NULL, 'PARTISI 6 KOLOM EVA WARNA BIRU BRACKET B BLS', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 32500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:33', '2026-09-14 03:53:33', 'yes', 0.00),
(136, NULL, 'PARTISI 6 KOLOM EVA WARNA HITAM BRACKET A BLS', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 32500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:33', '2026-09-14 03:53:33', 'yes', 0.00),
(137, NULL, 'PARTISI 7 KOLOM REFLECTOR 5H45 R/L', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 25200.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:33', '2026-09-14 03:53:33', 'yes', 0.00),
(138, NULL, 'PARTISI 80 KOLOM 420*315*180 INNER LENS A/B 6G7', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 158000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:33', '2026-09-14 03:53:33', 'yes', 0.00),
(139, NULL, 'PARTISI 8 KOLOM UPPER LENS B3W', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 27000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:34', '2026-09-14 03:53:34', 'yes', 0.00),
(140, NULL, 'PARTISI 9 KOLOM BODY K2VM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 50000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:34', '2026-09-14 03:53:34', 'yes', 0.00),
(141, NULL, 'PARTISI BRACKET D03B LED HL R/L', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 48500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:35', '2026-09-14 03:53:35', 'yes', 0.00),
(142, NULL, 'PARTISI BRACKET RR EL1 R/L', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 204000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:35', '2026-09-14 03:53:35', 'yes', 0.00),
(143, NULL, 'PARTISI BRACKET YTB 12 KOLOM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 75600.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:35', '2026-09-14 03:53:35', 'yes', 0.00),
(144, NULL, 'PARTISI COVER 737 LH 6 KOLOM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 23000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:35', '2026-09-14 03:53:35', 'yes', 0.00),
(145, NULL, 'PARTISI COVER 737 RH 6 KOLOM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 23000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:35', '2026-09-14 03:53:35', 'yes', 0.00),
(146, NULL, 'PARTISI COVER A 5P45', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 33000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:35', '2026-09-14 03:53:35', 'yes', 0.00),
(147, NULL, 'PARTISI DIAL PLATE CHAOLONG 15 KOLOM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 28000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:35', '2026-09-14 03:53:35', 'yes', 0.00),
(148, NULL, 'PARTISI EXTENTION B WARNA BIRU', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 166000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:35', '2026-09-14 03:53:35', 'yes', 0.00),
(149, NULL, 'PARTISI EXTENTION C WARNA HITAM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 166000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:35', '2026-09-14 03:53:35', 'yes', 0.00),
(150, NULL, 'PARTISI GARN RR CTR LEAR', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 29950.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:35', '2026-09-14 03:53:35', 'yes', 0.00),
(151, NULL, 'PARTISI HOLDER 655B R/L', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 47000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(152, NULL, 'PARTISI INNER LENS BKU', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 125000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(153, NULL, 'PARTISI INNER LENS C P59 ICHIKOH', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 41500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(154, NULL, 'PARTISI INNER LENS D14 AJI 40 KOLOM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 166000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(155, NULL, 'PARTISI LENS BXY 6 KOLOM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 19000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(156, NULL, 'PARTISI LENS K3NA', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 58000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(157, NULL, 'PARTISI RCLD BRACKET 9D5', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 31000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(158, NULL, 'PARTISI RCLT BRACKET 9D5', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 36000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(159, NULL, 'PARTISI REFLECTOR B 5P45 6 kolom', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 60000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(160, NULL, 'PARTISI REFLECTOR B D74 RL', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 244000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(161, NULL, 'PARTISI REFLECTOR CLL 737D 10 KOLOM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 88000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(162, NULL, 'PARTISI REFLECTOR LEDHL 737', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 35000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(163, NULL, 'PARTISI UPPER LENS C1D4 6 KOLOM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 19000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:36', '2026-09-14 03:53:36', 'yes', 0.00),
(164, NULL, 'Partition 20K (420x290x175) Vbox Lens AJI', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 101000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:37', '2026-09-14 03:53:37', 'yes', 0.00),
(165, NULL, 'PP BOX +PARTISI T5 ( MIRROR ROOM ) WARNA BIRU TD', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 95000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:37', '2026-09-14 03:53:37', 'yes', 0.00),
(166, NULL, 'PARTISI 30 KOLOM INNER LENS C K3NA (WARNA BIRU) FULL KAIN TRICOAT', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 120000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:37', '2026-09-14 03:53:37', 'yes', 0.00),
(167, NULL, 'PARTISI 30 KOLOM INNER LENS D K3NA (WARNA HITAM) FULL KAIN TRICOAT', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 120000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:37', '2026-09-14 03:53:37', 'yes', 0.00),
(168, NULL, 'PARTISI 80 KOLOM INNER LENS C 5H45 LH (WARNA HITAM) FULL KAIN TRICOT', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 135000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:37', '2026-09-14 03:53:37', 'yes', 0.00),
(169, NULL, 'PARTISI 80 KOLOM INNER LENS C 5H45 RH (WARNA BIRU) FULL KAIN TRICOT', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 135000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:37', '2026-09-14 03:53:37', 'yes', 0.00),
(170, NULL, 'PARTISI INNER LENS C BLS RH 12KOLOM WARNA BIRU', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 60000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:37', '2026-09-14 03:53:37', 'yes', 0.00),
(171, NULL, 'PARTISI INNER LENS D BLS LH 12KOLOM WARNA HITAM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 60000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:37', '2026-09-14 03:53:37', 'yes', 0.00),
(172, NULL, 'PARTISI TRAY EXTENSION K2VM 17 KOLOM', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 175000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:37', '2026-09-14 03:53:37', 'yes', 0.00),
(173, NULL, 'PARTISI TRAY EXTENTION BYW T3 MM KUNING 4 KOLOM (NEW SIZE)', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 167000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:37', '2026-09-14 03:53:37', 'yes', 0.00),
(174, NULL, 'IMPRA BOARD 55.43*44*15 (cm ) CASE CHAIN', 'INFRABOARD', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 160000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:37', '2026-09-14 03:53:37', 'yes', 0.00),
(175, NULL, 'Carton Box 166N Lens-Export', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 17260.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(176, NULL, 'KARDUS (220X70X70) INSIDE', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 2160.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(177, NULL, 'KARDUS (23x21x9) R-4 SGP New Design', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 4850.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(178, NULL, 'KARDUS (280x140x100)+Layer Inner SIM-R2 Reg', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 3800.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(179, NULL, 'KARDUS (314x125x35) R-2 SGP New Design', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 2130.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(180, NULL, 'KARDUS (500X240X240) SGP OUTER INSIDE', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 13365.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(181, NULL, 'KARDUS (500X350X330) OUTER CMW', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 16000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(182, NULL, 'KARDUS (700x290x310) Outer DEM', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 10803.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(183, NULL, 'KARDUS (700x290x310) Outer IPG', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 16200.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(184, NULL, 'KARDUS (700x290x310) Outer R2', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 11980.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(185, NULL, 'KARDUS (780X400X570) SGP OUTER BESAR', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 26350.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(186, NULL, 'KARDUS 420X400X325 (Tail&Front Cover Cannet)', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 11550.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(187, NULL, 'KARDUS 590X455X155 (Bottom+Terminal)', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 11100.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(188, NULL, 'KARDUS ANTENA BESAR (470X315X160)', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 6580.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(189, NULL, 'KARDUS OUTER CBOX', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 8400.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:38', '2026-09-14 03:53:38', 'yes', 0.00),
(190, NULL, 'KARDUS OUTER SGP (400X350X230) GRIP COMP', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 13200.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(191, NULL, 'KARDUS OUTER SGP (500X350X330)', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 11220.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(192, NULL, 'KARDUS SGP (505X355X345) O/MIRROR R2', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 17078.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(193, NULL, 'KARTON BOX GARN, RR CTR A/R 540*410*170MM', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 9900.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(194, NULL, 'KARTON BOX PLASTIC LATCH COVER 540*410*170MM', 'BOX CARTON', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 9900.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(195, NULL, 'Layer 166N Lens-Export', 'LAYER', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 1906.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(196, NULL, 'LAYER 400x380 (Tail Cover&Front Cover)', 'LAYER', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 809.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(197, NULL, 'LAYER KARTON IMP 044 UK 670*590*4MM K125/M125/K125 CFLUTE', 'LAYER', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 3500.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(198, NULL, 'PARTISI + LAYER GARN, RR CTR A/R 530*400*70 MM', 'LAYER', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 18000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(199, NULL, 'PARTISI PLASTIC LATCH COVER 530*400*160MM', 'LAYER', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 16000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(200, NULL, 'PE PROTECTION TAPE PR901TDL 200mm x 200mm', 'PROTECTION', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 195280.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(201, NULL, 'PE PROTECTION TAPE PR915T 110mm x 200m', 'PROTECTION', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 87400.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(202, NULL, 'Protection Clear SM0105PECL 500mm x 200m (Winscreen)', 'PROTECTION', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 503750.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(203, NULL, 'PROTECTION CLEAR SM0505PECL 30mm x 200m', 'PROTECTION', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 29900.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(204, NULL, 'PROTECTION CLEAR SM0505PECL 40mm x 200m', 'PROTECTION', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 40997.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(205, NULL, 'PROTECTION CLEAR SM0505PECL SIZE 70mm x 200m', 'PROTECTION', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 69460.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(206, NULL, 'Protection Tape Embos 430mm x 200m (Window Visor)', 'PROTECTION', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 385250.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(207, NULL, 'SARUNG TANGAN KULIT ( ICHIKOH )', 'OTHERS', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 25000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:39', '2026-09-14 03:53:39', 'yes', 0.00),
(208, NULL, 'SARUNG TANGAN LAPIS PU COMET', 'OTHERS', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 15000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:40', '2026-09-14 03:53:40', 'yes', 0.00),
(209, NULL, 'SARUNG TANGAN PUTIH HALUS', 'OTHERS', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 26000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:40', '2026-09-14 03:53:40', 'yes', 0.00);
INSERT INTO `tbl_packing_cost` (`id`, `item_code`, `part_name`, `item_category`, `p`, `l`, `t`, `volume_cm3`, `max_capacity_gram`, `weight_gram`, `supplier_id`, `profit_percent`, `selling_price`, `unit`, `purchase_price`, `item_price`, `size_detail`, `returnable`, `lifetime_month`, `depreciation_type`, `revision_no`, `revision_remark`, `effective_date`, `status`, `created_by`, `created_at`, `updated_at`, `active_for_packing_standard`, `qty_per_kg`) VALUES
(210, NULL, 'SARUNG TANGAN WHITE POLYESTER ( GLO-INFINITI-WH-7 )', 'OTHERS', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 6000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:41', '2026-09-14 03:53:41', 'yes', 0.00),
(211, NULL, 'PLASTIK WRAPPING 500mm x 10mic x 300m', 'OTHERS', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 68000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:41', '2026-09-14 03:53:41', 'yes', 0.00),
(212, NULL, 'STRAPPING BAND', 'OTHERS', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 441000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:41', '2026-09-14 03:53:41', 'yes', 0.00),
(213, NULL, 'PP BAND BUCKLE 5/8 ( IKAT GESPER ) @2500Pcs', 'OTHERS', NULL, NULL, NULL, 0.0000, 0, 0.00, NULL, 0.00, 0.00, '', 385000.00, 0.00, '', 'no', 0, 'monthly', 0, NULL, '2026-09-14', 'active', 1, '2026-09-14 03:53:41', '2026-09-14 03:53:41', 'yes', 0.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_packing_costing`
--

CREATE TABLE `tbl_packing_costing` (
  `id` int(11) NOT NULL,
  `item_name` varchar(150) DEFAULT NULL,
  `p` decimal(10,2) DEFAULT NULL,
  `l` decimal(10,2) DEFAULT NULL,
  `t` decimal(10,2) DEFAULT NULL,
  `volume_cm3` decimal(18,4) DEFAULT NULL,
  `supplier_price` decimal(18,2) DEFAULT NULL,
  `profit_percent` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(18,2) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `revision_no` int(11) DEFAULT 0,
  `effective_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_packing_master`
--

CREATE TABLE `tbl_packing_master` (
  `id` int(11) NOT NULL,
  `packing_code` varchar(50) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `p` decimal(10,2) DEFAULT 0.00,
  `l` decimal(10,2) DEFAULT 0.00,
  `t` decimal(10,2) DEFAULT 0.00,
  `volume_cm3` decimal(18,2) DEFAULT 0.00,
  `supplier_price` decimal(18,2) DEFAULT 0.00,
  `profit_percent` decimal(10,2) DEFAULT 0.00,
  `selling_price` decimal(18,2) DEFAULT 0.00,
  `revision_no` int(11) DEFAULT 0,
  `effective_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_packing_options`
--

CREATE TABLE `tbl_packing_options` (
  `id` int(11) NOT NULL,
  `packing_standard_id` int(11) DEFAULT NULL,
  `packing_label` varchar(50) DEFAULT NULL,
  `p_cm` decimal(10,2) DEFAULT NULL,
  `l_cm` decimal(10,2) DEFAULT NULL,
  `t_cm` decimal(10,2) DEFAULT NULL,
  `volume_cm3` decimal(10,2) DEFAULT NULL,
  `harga_supplier` decimal(15,2) DEFAULT NULL,
  `profit_persen` decimal(5,2) DEFAULT NULL,
  `harga_jual` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_packing_standard`
--

CREATE TABLE `tbl_packing_standard` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `part_number` varchar(150) DEFAULT NULL,
  `part_name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `inner_item_id` int(11) DEFAULT NULL,
  `plastic_item_id` int(11) DEFAULT NULL,
  `box_item_id` int(11) DEFAULT NULL,
  `pallet_item_id` int(11) DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `revision_no` int(11) DEFAULT 0,
  `effective_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `p_cm` decimal(18,2) NOT NULL,
  `l_cm` decimal(18,2) DEFAULT 0.00,
  `t_cm` decimal(18,2) DEFAULT 0.00,
  `volume_cm3` decimal(18,4) DEFAULT 0.0000,
  `harga_supplier` decimal(18,2) DEFAULT 0.00,
  `profit_persen` decimal(5,2) DEFAULT 0.00,
  `harga_jual` decimal(18,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_packing_standard`
--

INSERT INTO `tbl_packing_standard` (`id`, `customer_id`, `part_number`, `part_name`, `description`, `inner_item_id`, `plastic_item_id`, `box_item_id`, `pallet_item_id`, `remark`, `revision_no`, `effective_date`, `status`, `created_by`, `created_at`, `updated_at`, `p_cm`, `l_cm`, `t_cm`, `volume_cm3`, `harga_supplier`, `profit_persen`, `harga_jual`) VALUES
(11, NULL, NULL, NULL, 'V_BOX', NULL, NULL, NULL, NULL, '', 0, NULL, 'active', NULL, '2026-09-07 09:49:24', '2026-09-07 09:49:24', 200.00, 170.00, 175.00, 5950000.0000, 56000.00, 10.00, 61600.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_packing_standard_detail`
--

CREATE TABLE `tbl_packing_standard_detail` (
  `id` int(11) NOT NULL,
  `packing_standard_id` int(11) DEFAULT NULL,
  `packing_cost_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `qty` decimal(18,2) DEFAULT NULL,
  `selling_price` decimal(18,2) DEFAULT NULL,
  `total_cost` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_pack_trans_items`
--

CREATE TABLE `tbl_pack_trans_items` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `type_system` enum('returnable','non-returnable') NOT NULL DEFAULT 'returnable',
  `standard_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `qty_month` int(11) NOT NULL DEFAULT 0,
  `assy_day` int(11) NOT NULL DEFAULT 0,
  `box_day` decimal(10,2) NOT NULL DEFAULT 0.00,
  `box_round` int(11) NOT NULL DEFAULT 0,
  `box_keeping` int(11) NOT NULL DEFAULT 0,
  `investment` decimal(15,2) NOT NULL DEFAULT 0.00,
  `qty_2years` int(11) NOT NULL DEFAULT 0,
  `depresiasi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_packing` decimal(15,2) NOT NULL DEFAULT 0.00,
  `transport_pcs` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_pack_trans_project`
--

CREATE TABLE `tbl_pack_trans_project` (
  `id` int(11) NOT NULL,
  `part_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `truck_p` int(11) DEFAULT 0,
  `truck_l` int(11) DEFAULT 0,
  `truck_t` int(11) DEFAULT 0,
  `truck_cost` decimal(15,2) DEFAULT 0.00,
  `transport_pcs_total` decimal(15,2) DEFAULT 0.00,
  `total_packing_pcs` decimal(10,2) DEFAULT 0.00,
  `is_read` tinyint(1) DEFAULT 0,
  `workflow_status` enum('new','complete') NOT NULL DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_purging_master`
--

CREATE TABLE `tbl_purging_master` (
  `id` int(11) NOT NULL,
  `mc_ton_min` int(11) NOT NULL,
  `mc_ton_max` int(11) NOT NULL,
  `purging_ori_kg` decimal(10,2) NOT NULL,
  `purging_cellpurg_kg` decimal(10,2) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_purging_master`
--

INSERT INTO `tbl_purging_master` (`id`, `mc_ton_min`, `mc_ton_max`, `purging_ori_kg`, `purging_cellpurg_kg`, `status`) VALUES
(1, 30, 90, 2.00, 1.00, 'active'),
(2, 100, 360, 4.00, 2.00, 'active'),
(3, 450, 650, 6.00, 3.00, 'active'),
(4, 850, 850, 8.00, 4.00, 'active');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_quotation`
--

CREATE TABLE `tbl_quotation` (
  `id` int(11) NOT NULL,
  `quotation_no` varchar(100) DEFAULT NULL,
  `revision_no` int(11) DEFAULT 0,
  `customer_id` int(11) DEFAULT NULL,
  `rate_draft_id` int(11) DEFAULT 0,
  `mmp_id` int(11) DEFAULT NULL,
  `type` varchar(20) DEFAULT 'internal',
  `part_number` varchar(150) DEFAULT NULL,
  `packing_standard_id` int(11) DEFAULT NULL,
  `part_name` varchar(255) DEFAULT NULL,
  `material_spec` varchar(255) DEFAULT NULL,
  `basic_price` decimal(18,2) DEFAULT 0.00,
  `model` varchar(255) DEFAULT NULL,
  `quotation_date` date DEFAULT NULL,
  `validity_days` int(11) DEFAULT 30,
  `currency` varchar(20) DEFAULT NULL,
  `exchange_rate_id` int(11) DEFAULT NULL,
  `exchange_rate_value` decimal(18,2) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `material_price` decimal(18,2) DEFAULT 0.00,
  `pigmen_cost` decimal(12,2) DEFAULT 0.00,
  `weight_per_pcs` decimal(18,2) DEFAULT 0.00,
  `part_weight` decimal(18,2) DEFAULT 0.00,
  `idr_price_kg` decimal(18,2) DEFAULT 0.00,
  `runner_weight` decimal(18,2) DEFAULT 0.00,
  `cycle_time` decimal(18,2) DEFAULT NULL,
  `cavity` int(11) DEFAULT 0,
  `mc_ton` decimal(18,2) DEFAULT 0.00,
  `purging` decimal(18,2) DEFAULT 0.00,
  `cellpurge_price` decimal(12,2) DEFAULT 0.00,
  `purging_ori_kg` decimal(10,2) DEFAULT 0.00,
  `purging_cellpurg_kg` decimal(10,2) DEFAULT 0.00,
  `total_purging_cost` decimal(12,2) DEFAULT 0.00,
  `dandori_minutes` decimal(18,2) DEFAULT 0.00,
  `machine_rate_id` int(11) DEFAULT NULL,
  `machine_rate_value` decimal(18,2) DEFAULT NULL,
  `rate_hour` decimal(18,2) DEFAULT 0.00,
  `purging_val` decimal(12,2) DEFAULT 0.00,
  `purging_cost_pcs` decimal(12,2) DEFAULT 0.00,
  `dandori_val` decimal(12,2) DEFAULT 0.00,
  `dandori` decimal(12,2) DEFAULT 0.00,
  `mold_mtn_rate` decimal(5,2) DEFAULT 0.00,
  `mold_mtn_cost_pcs` decimal(12,2) DEFAULT 0.00,
  `reject_percent` decimal(18,2) DEFAULT NULL,
  `other_process_type` varchar(50) DEFAULT NULL,
  `ct_other` decimal(10,2) DEFAULT 0.00,
  `rate_annealing` decimal(18,2) DEFAULT 0.00,
  `other_process_cost` decimal(10,2) DEFAULT 0.00,
  `transport_cost` decimal(18,2) DEFAULT NULL,
  `monthly_qty` int(11) DEFAULT NULL,
  `material_cost` decimal(18,2) DEFAULT NULL,
  `process_cost` decimal(18,2) DEFAULT NULL,
  `rejection_cost` decimal(18,2) DEFAULT NULL,
  `packing_cost` decimal(18,2) DEFAULT NULL,
  `cogs` decimal(18,2) DEFAULT NULL,
  `oh_percent` decimal(18,2) DEFAULT NULL,
  `oh_profit` decimal(18,2) DEFAULT 0.00,
  `profit_percent` decimal(18,2) DEFAULT NULL,
  `mold_maintenance` decimal(18,2) DEFAULT NULL,
  `qty_forecast_month` int(100) DEFAULT 0,
  `mold_price` decimal(15,2) DEFAULT 0.00,
  `depreciation_years` int(11) DEFAULT 1,
  `mold_depreciation_pcs` decimal(15,2) DEFAULT 0.00,
  `mold_cost_month` decimal(15,2) DEFAULT 0.00,
  `lumpsum_price` decimal(15,2) DEFAULT 0.00,
  `cr_mode` varchar(20) DEFAULT 'with_bl',
  `cr_base_val` decimal(15,2) DEFAULT 0.00,
  `cr_lta_years` int(11) DEFAULT 3,
  `cr_lta_pct_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cr_lta_pct_json`)),
  `cr_lta_res_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cr_lta_res_json`)),
  `cr_pct_bl` decimal(5,2) DEFAULT 0.00,
  `cr_final_cost` decimal(15,2) DEFAULT 0.00,
  `selling_price` decimal(18,2) DEFAULT NULL,
  `lumpsum_pcs` decimal(15,2) DEFAULT 0.00,
  `quotation_note` text DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `folder_name` varchar(150) DEFAULT '',
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_quotation_packaging`
--

CREATE TABLE `tbl_quotation_packaging` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `packing_standard_id` int(11) DEFAULT NULL,
  `item_name` varchar(150) DEFAULT NULL,
  `qty` decimal(18,2) DEFAULT NULL,
  `item_price` decimal(18,2) DEFAULT NULL,
  `returnable` enum('yes','no') DEFAULT 'no',
  `lifetime_month` int(11) DEFAULT 0,
  `depreciation_cost` decimal(18,2) DEFAULT NULL,
  `cost_per_pcs` decimal(18,6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_quotation_revision`
--

CREATE TABLE `tbl_quotation_revision` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `revision_no` int(11) DEFAULT NULL,
  `revision_reason` text DEFAULT NULL,
  `revised_by` int(11) DEFAULT NULL,
  `revised_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_rate_drafts`
--

CREATE TABLE `tbl_rate_drafts` (
  `id` int(11) NOT NULL,
  `draft_title` varchar(255) NOT NULL,
  `base_reference` varchar(255) NOT NULL DEFAULT 'BASED ON AKTUAL 2023',
  `status` enum('active','inactive') DEFAULT 'active',
  `manpower_rate_sec` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_rate_drafts`
--

INSERT INTO `tbl_rate_drafts` (`id`, `draft_title`, `base_reference`, `status`, `manpower_rate_sec`, `created_at`) VALUES
(16, '2026 TERMURAH', 'Bunga 5%, Listrik Up 5%, UMR 80%', 'active', 11.14, '2026-09-08 09:14:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_rate_master`
--

CREATE TABLE `tbl_rate_master` (
  `id` int(11) NOT NULL,
  `draft_id` int(11) DEFAULT NULL,
  `draft_name` varchar(150) NOT NULL DEFAULT 'BASED ON AKTUAL 2023',
  `machine_name` varchar(100) DEFAULT NULL,
  `tonnage` varchar(50) DEFAULT NULL,
  `rate_per_hour` decimal(18,2) DEFAULT NULL,
  `rate_per_second` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `utility_cost` decimal(18,2) DEFAULT 0.00,
  `maintenance_cost` decimal(18,2) DEFAULT 0.00,
  `labor_cost` decimal(18,2) DEFAULT 0.00,
  `depreciation_cost` decimal(18,2) DEFAULT 0.00,
  `purging_cost` decimal(18,2) DEFAULT 0.00,
  `demold_cost` decimal(18,2) DEFAULT 0.00,
  `revision_no` int(11) DEFAULT 0,
  `effective_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_rate_master`
--

INSERT INTO `tbl_rate_master` (`id`, `draft_id`, `draft_name`, `machine_name`, `tonnage`, `rate_per_hour`, `rate_per_second`, `utility_cost`, `maintenance_cost`, `labor_cost`, `depreciation_cost`, `purging_cost`, `demold_cost`, `revision_no`, `effective_date`, `status`, `created_by`, `created_at`) VALUES
(1, 1, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(2, 1, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(3, 1, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(4, 1, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(5, 1, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 100.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(6, 1, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(7, 1, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(8, 1, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1010.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(9, 1, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(10, 1, 'BASED ON AKTUAL 2023', 'M/C 250 Ton', '250', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(11, 1, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(12, 1, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(13, 1, 'BASED ON AKTUAL 2023', 'M/C 550 Ton', '550', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(14, 1, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(15, 1, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(16, 1, 'BASED ON AKTUAL 2023', 'M/C 1300 Ton', '1300', NULL, 100000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:01'),
(17, 2, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(18, 2, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(19, 2, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(20, 2, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(21, 2, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(22, 2, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(23, 2, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(24, 2, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(25, 2, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(26, 2, 'BASED ON AKTUAL 2023', 'M/C 250 Ton', '250', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(27, 2, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(28, 2, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(29, 2, 'BASED ON AKTUAL 2023', 'M/C 550 Ton', '550', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(30, 2, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(31, 2, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(32, 2, 'BASED ON AKTUAL 2023', 'M/C 1300 Ton', '1300', NULL, 100000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-23 01:36:41'),
(33, 3, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(34, 3, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(35, 3, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(36, 3, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 20002.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(37, 3, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(38, 3, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(39, 3, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(40, 3, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(41, 3, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(42, 3, 'BASED ON AKTUAL 2023', 'M/C 250 Ton', '250', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(43, 3, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(44, 3, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(45, 3, 'BASED ON AKTUAL 2023', 'M/C 550 Ton', '550', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(46, 3, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(47, 3, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 2000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 07:46:56'),
(48, 4, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(49, 4, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(50, 4, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(51, 4, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(52, 4, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(53, 4, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(54, 4, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(55, 4, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(56, 4, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(57, 4, 'BASED ON AKTUAL 2023', 'M/C 250 Ton', '250', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(58, 4, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(59, 4, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(60, 4, 'BASED ON AKTUAL 2023', 'M/C 550 Ton', '550', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(61, 4, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(62, 4, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:00:59'),
(63, 5, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(64, 5, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(65, 5, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(66, 5, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(67, 5, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(68, 5, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(69, 5, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(70, 5, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(71, 5, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(72, 5, 'BASED ON AKTUAL 2023', 'M/C 250 Ton', '250', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(73, 5, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(74, 5, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(75, 5, 'BASED ON AKTUAL 2023', 'M/C 550 Ton', '550', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(76, 5, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(77, 5, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:12:11'),
(78, 6, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(79, 6, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(80, 6, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(81, 6, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(82, 6, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(83, 6, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(84, 6, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(85, 6, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(86, 6, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(87, 6, 'BASED ON AKTUAL 2023', 'M/C 250 Ton', '250', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(88, 6, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(89, 6, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(90, 6, 'BASED ON AKTUAL 2023', 'M/C 550 Ton', '550', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(91, 6, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(92, 6, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:13:51'),
(93, 7, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(94, 7, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(95, 7, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(96, 7, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(97, 7, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(98, 7, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(99, 7, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(100, 7, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(101, 7, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(102, 7, 'BASED ON AKTUAL 2023', 'M/C 250 Ton', '250', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(103, 7, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(104, 7, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(105, 7, 'BASED ON AKTUAL 2023', 'M/C 550 Ton', '550', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(106, 7, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(107, 7, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:13'),
(108, 8, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(109, 8, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(110, 8, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(111, 8, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(112, 8, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(113, 8, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(114, 8, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(115, 8, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(116, 8, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(117, 8, 'BASED ON AKTUAL 2023', 'M/C 250 Ton', '250', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(118, 8, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(119, 8, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(120, 8, 'BASED ON AKTUAL 2023', 'M/C 550 Ton', '550', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(121, 8, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(122, 8, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:14:19'),
(123, 9, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(124, 9, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(125, 9, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(126, 9, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(127, 9, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(128, 9, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(129, 9, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(130, 9, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(131, 9, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(132, 9, 'BASED ON AKTUAL 2023', 'M/C 250 Ton', '250', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(133, 9, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(134, 9, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(135, 9, 'BASED ON AKTUAL 2023', 'M/C 550 Ton', '550', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(136, 9, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(137, 9, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:19:57'),
(138, 10, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(139, 10, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(140, 10, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(141, 10, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(142, 10, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(143, 10, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(144, 10, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(145, 10, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(146, 10, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(147, 10, 'BASED ON AKTUAL 2023', 'M/C 250 Ton', '250', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(148, 10, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(149, 10, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(150, 10, 'BASED ON AKTUAL 2023', 'M/C 550 Ton', '550', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(151, 10, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(152, 10, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 1200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:20:50'),
(153, 11, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(154, 11, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(155, 11, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(156, 11, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(157, 11, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(158, 11, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(159, 11, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(160, 11, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(161, 11, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(162, 11, 'BASED ON AKTUAL 2023', 'M/C 250 Ton', '250', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(163, 11, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(164, 11, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(165, 11, 'BASED ON AKTUAL 2023', 'M/C 550 Ton', '550', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(166, 11, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(167, 11, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 200.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-06-26 08:52:55'),
(168, 12, 'BASED ON AKTUAL 2023', 'M/C 30 Ton', '30', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:14'),
(169, 12, 'BASED ON AKTUAL 2023', 'M/C 40 Ton', '40', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:14'),
(170, 12, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:14'),
(171, 12, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:14'),
(172, 12, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 100.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:14'),
(173, 12, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 2100.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:15'),
(174, 12, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:15'),
(175, 12, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:15'),
(176, 12, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:15'),
(177, 12, 'BASED ON AKTUAL 2023', 'M/C 260 Ton', '260', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:15'),
(178, 12, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:15'),
(179, 12, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:15'),
(180, 12, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:15'),
(181, 12, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-01 02:46:15'),
(182, 13, 'BASED ON AKTUAL 2023', 'M/C 30 - 40 Ton', '35', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(183, 13, 'BASED ON AKTUAL 2023', 'M/C 30 Ton Vertikal', '31', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(184, 13, 'BASED ON AKTUAL 2023', 'M/C 40 Ton Vertikal', '41', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(185, 13, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(186, 13, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(187, 13, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(188, 13, 'BASED ON AKTUAL 2023', 'M/C 120 Ton', '120', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(189, 13, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(190, 13, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(191, 13, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(192, 13, 'BASED ON AKTUAL 2023', 'M/C 260 Ton', '260', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(193, 13, 'BASED ON AKTUAL 2023', 'M/C 350 Ton', '350', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(194, 13, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(195, 13, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(196, 13, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 10000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-07-02 09:11:15'),
(197, 14, 'BASED ON AKTUAL 2023', 'M/C 30 - 40 Ton', '35', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(198, 14, 'BASED ON AKTUAL 2023', 'M/C 30 Ton Vertikal', '31', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(199, 14, 'BASED ON AKTUAL 2023', 'M/C 40 Ton Vertikal', '41', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(200, 14, 'BASED ON AKTUAL 2023', 'M/C 60 Ton', '60', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(201, 14, 'BASED ON AKTUAL 2023', 'M/C 80 Ton', '80', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(202, 14, 'BASED ON AKTUAL 2023', 'M/C 100 Ton', '100', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(203, 14, 'BASED ON AKTUAL 2023', 'M/C 130 Ton', '130', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(204, 14, 'BASED ON AKTUAL 2023', 'M/C 170 Ton', '170', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(205, 14, 'BASED ON AKTUAL 2023', 'M/C 200 Ton', '200', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(206, 14, 'BASED ON AKTUAL 2023', 'M/C 220 Ton', '220', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(207, 14, 'BASED ON AKTUAL 2023', 'M/C 260 Ton', '260', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(208, 14, 'BASED ON AKTUAL 2023', 'M/C 280 Ton', '280', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(209, 14, 'BASED ON AKTUAL 2023', 'M/C 360 Ton', '360', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(210, 14, 'BASED ON AKTUAL 2023', 'M/C 450 Ton', '450', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(211, 14, 'BASED ON AKTUAL 2023', 'M/C 650 Ton', '650', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(212, 14, 'BASED ON AKTUAL 2023', 'M/C 850 Ton', '850', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-24 03:19:44'),
(213, 15, 'BASED ON AKTUAL 2023', 'M/C 30 - 40 TON', '35', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(214, 15, 'BASED ON AKTUAL 2023', 'M/C 30 TON VERTIKAL', '31', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(215, 15, 'BASED ON AKTUAL 2023', 'M/C 40 TON VERTIKAL', '41', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(216, 15, 'BASED ON AKTUAL 2023', 'M/C 60 TON', '60', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(217, 15, 'BASED ON AKTUAL 2023', 'M/C 80 TON', '80', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(218, 15, 'BASED ON AKTUAL 2023', 'M/C 100 TON', '100', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(219, 15, 'BASED ON AKTUAL 2023', 'M/C 130 TON', '130', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(220, 15, 'BASED ON AKTUAL 2023', 'M/C 170 TON', '170', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(221, 15, 'BASED ON AKTUAL 2023', 'M/C 200 TON', '200', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(222, 15, 'BASED ON AKTUAL 2023', 'M/C 220 TON', '220', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(223, 15, 'BASED ON AKTUAL 2023', 'M/C 260 TON', '260', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(224, 15, 'BASED ON AKTUAL 2023', 'M/C 280 TON', '280', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(225, 15, 'BASED ON AKTUAL 2023', 'M/C 360 TON', '360', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(226, 15, 'BASED ON AKTUAL 2023', 'M/C 450 TON', '450', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(227, 15, 'BASED ON AKTUAL 2023', 'M/C 650 TON', '650', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(228, 15, 'BASED ON AKTUAL 2023', 'M/C 850 TON', '850', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(229, 15, 'BASED ON AKTUAL 2023', 'M/C 1000 TON', '1000', NULL, 1000.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-08-28 08:18:32'),
(262, 16, 'BASED ON AKTUAL 2023', 'M/C 30 - 40 TON', '35', NULL, 17.0300, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(263, 16, 'BASED ON AKTUAL 2023', 'M/C 30 TON VERTIKAL', '31', NULL, 21.6300, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(264, 16, 'BASED ON AKTUAL 2023', 'M/C 40 TON VERTIKAL', '41', NULL, 21.3300, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(265, 16, 'BASED ON AKTUAL 2023', 'M/C 60 TON', '60', NULL, 18.9100, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(266, 16, 'BASED ON AKTUAL 2023', 'M/C 80 TON', '80', NULL, 19.0200, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(267, 16, 'BASED ON AKTUAL 2023', 'M/C 100 TON', '100', NULL, 20.0200, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(268, 16, 'BASED ON AKTUAL 2023', 'M/C 130 TON', '130', NULL, 22.8700, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(269, 16, 'BASED ON AKTUAL 2023', 'M/C 170 TON', '170', NULL, 23.0000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(270, 16, 'BASED ON AKTUAL 2023', 'M/C 200 TON', '200', NULL, 26.6400, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(271, 16, 'BASED ON AKTUAL 2023', 'M/C 220 TON', '220', NULL, 27.1600, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(272, 16, 'BASED ON AKTUAL 2023', 'M/C 260 TON', '260', NULL, 30.3500, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(273, 16, 'BASED ON AKTUAL 2023', 'M/C 280 TON', '280', NULL, 33.3600, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(274, 16, 'BASED ON AKTUAL 2023', 'M/C 360 TON', '360', NULL, 46.1600, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(275, 16, 'BASED ON AKTUAL 2023', 'M/C 450 TON', '450', NULL, 60.3600, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(276, 16, 'BASED ON AKTUAL 2023', 'M/C 650 TON', '650', NULL, 76.2700, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46'),
(277, 16, 'BASED ON AKTUAL 2023', 'M/C 850 TON', '850', NULL, 92.1600, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, NULL, 'active', NULL, '2026-09-08 03:22:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_supplier`
--

CREATE TABLE `tbl_supplier` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_transport_cost`
--

CREATE TABLE `tbl_transport_cost` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `keterangan` text NOT NULL,
  `biaya_sewa` decimal(15,2) NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_transport_cost`
--

INSERT INTO `tbl_transport_cost` (`id`, `customer_name`, `keterangan`, `biaya_sewa`, `is_archived`, `created_at`, `updated_at`) VALUES
(20, 'PT. Hongfa Electronic Blok.C', 'Fuso / BSD - Tanggerang Selatan', 4320844.00, 0, '2026-09-07 02:19:31', '2026-09-07 02:19:31'),
(21, 'PT. Panasonic Gobel Life Solutions Manufacturing Indonesia', 'Fuso / Cileungsi - Kab. Bogor', 3187023.00, 0, '2026-09-07 02:22:00', '2026-09-07 02:22:00'),
(23, 'PT. Bondor Indonesia Plant 1', 'Fuso / Jl. Olympic Raya, Kav A2-A, Sentul', 3113573.00, 0, '2026-09-07 02:47:09', '2026-09-07 02:47:09'),
(24, 'PT. Ichikoh Indonesia', 'Fuso / MM2100', 3073894.00, 0, '2026-09-07 02:50:17', '2026-09-07 02:50:17'),
(26, 'PT. Willfar Information Technology', 'Fuso / Delta Silicon', 2201340.00, 0, '2026-09-07 02:59:05', '2026-09-07 02:59:05'),
(28, 'PT. Dharma Precision Parts', 'APV Box / Jababeka', 422700.00, 0, '2026-09-07 03:24:47', '2026-09-07 03:24:47'),
(29, 'PT. Dharma Electrindo Manufacturing', 'APV Box / Jababeka', 404756.00, 0, '2026-09-07 03:31:24', '2026-09-07 03:31:24'),
(30, 'PT. BS Indonesia', 'APV Box / Jababeka', 408744.00, 0, '2026-09-07 04:38:38', '2026-09-07 04:38:38'),
(31, 'PT Indonesia Thai Summit Plastech', 'APV Box / GIIC', 478525.00, 0, '2026-09-07 08:14:12', '2026-09-07 08:14:12'),
(32, 'PT Indonesia Thai Summit Plastech', 'APV Box / KIIC - Karawang Barat', 577769.00, 0, '2026-09-07 08:15:30', '2026-09-07 08:15:30'),
(33, 'PT. Hongfa Electronic Blok.C', 'APV Box / BSD - Tanggerang Selatan', 1201102.00, 0, '2026-09-07 08:18:55', '2026-09-07 08:18:55'),
(34, 'PT. Trimitra Citrahasta', 'CDD / Delta Silicon', 1663413.00, 0, '2026-09-07 08:23:27', '2026-09-07 08:23:27'),
(35, 'PT. Chao Long Motor Parts Indonesia', 'CDD / Delta Silicon', 1641868.00, 0, '2026-09-07 08:24:01', '2026-09-07 08:24:01'),
(36, 'PT Toyo Denso Indonesia', 'CDD / MM2100', 1663413.00, 0, '2026-09-07 08:26:03', '2026-09-07 08:26:03'),
(37, 'PT. Minda Asean Automotiv', 'CDD / KIIC - Karawang Barat', 1856386.00, 0, '2026-09-07 08:49:20', '2026-09-07 08:49:20'),
(38, 'PT. Indonesia Koito', 'CDD / Kalihurip - Cikampek', 2070349.00, 0, '2026-09-07 08:51:10', '2026-09-07 08:51:10'),
(40, 'PT. Suzuki Indomobil Motor', 'CDD / Tambun', 1734358.00, 0, '2026-09-07 09:03:04', '2026-09-07 09:03:04'),
(41, 'PT. Suzuki Indomobil Motor- GIIC', 'CDD / GIIC - Deltamas', 1765968.00, 0, '2026-09-07 09:04:18', '2026-09-07 09:04:18'),
(42, 'PT. Cannet Elektrik Indonesia', 'CDD / Cikupa - Tanggerang', 2627923.00, 0, '2026-09-07 09:05:08', '2026-09-07 09:05:08'),
(43, 'PT. Cipta Mandiri Wirasakti', 'CDD / Cileungsi', 1967295.00, 0, '2026-09-07 09:06:20', '2026-09-07 09:06:20'),
(44, 'PT. Cipta Mandiri Wirasakti', 'CDD / Plumbon - Cirebon', 4279826.00, 0, '2026-09-07 09:07:04', '2026-09-07 09:07:04'),
(45, 'PT. Hanamaster', 'CDD / Bandung', 3029924.00, 0, '2026-09-07 09:07:45', '2026-09-07 09:07:45'),
(46, 'PT. Indonesia Thai Summit Plastech', 'CDD / KIIC - Karawang Barat', 1884431.00, 0, '2026-09-07 09:08:46', '2026-09-07 09:08:46'),
(47, '1	PT. Indonesia Thai Summit Plastech', 'CDD / GIIC - Deltamas', 1700004.00, 0, '2026-09-07 09:09:31', '2026-09-07 09:09:31'),
(48, 'PT. Astra Jouku Indonesia', 'CDD / KIM - Karawang Timur', 1934022.00, 0, '2026-09-07 09:14:01', '2026-09-07 09:14:01'),
(49, 'PT. Hyundai Motor Manufacturing Indonesia', 'CDD / GIIC - Deltamas', 1785249.00, 0, '2026-09-07 09:14:46', '2026-09-07 09:14:46'),
(50, 'PT. Dae In Tech Indonesia', 'CDD / MM2100', 1665340.00, 0, '2026-09-07 09:15:16', '2026-09-07 09:15:16'),
(51, 'PT. Hongfa Electronic Blok.C', 'CDD / BSD - Tanggerang Selatan', 2990117.00, 0, '2026-09-07 09:16:50', '2026-09-07 09:16:50'),
(52, 'PT. Willfar Information Technology', 'CDD / Delta Silicon', 1685031.00, 0, '2026-09-07 09:17:44', '2026-09-07 09:17:44'),
(53, 'PT. Astra Daihatsu Motor', 'CDD / Sunter - Jakarta Utara', 2325340.00, 0, '2026-09-07 09:20:32', '2026-09-07 09:20:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_transport_master`
--

CREATE TABLE `tbl_transport_master` (
  `id` int(11) NOT NULL,
  `transport_name` varchar(100) NOT NULL,
  `truck_type` varchar(100) DEFAULT NULL,
  `p` decimal(18,2) DEFAULT 0.00,
  `l` decimal(18,2) DEFAULT 0.00,
  `t` decimal(18,2) DEFAULT 0.00,
  `volume_cm3` decimal(18,4) DEFAULT 0.0000,
  `capacity_percent` decimal(18,2) DEFAULT 70.00,
  `truck_rental_cost` decimal(18,2) DEFAULT 0.00,
  `capacity_volume_cm3` decimal(18,4) DEFAULT 0.0000,
  `transport_rate_cm3` decimal(18,6) DEFAULT 0.000000,
  `remark` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','marketing','general manager','purchasing','general affair') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `session_token` varchar(255) DEFAULT NULL,
  `last_activity_seen_at` datetime DEFAULT NULL,
  `session_device_id` varchar(128) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `fullname`, `username`, `password`, `role`, `status`, `session_token`, `last_activity_seen_at`, `session_device_id`, `created_at`) VALUES
(1, 'Ahmad Rizqi Gustiansyah', 'admin', '$2y$10$HVnuElmbkyNpOATov.51YeSsaT/uMXCP70MCKv1aPR0cZABtqQE/2', 'admin', 'active', 'dc7bd43af40ad944c92748390e3a4e696209077027abfd5c30caf972908d8871', '2026-09-08 14:39:09', '0f25b202bed1dfd12fa616377be68d20e96308d57ffe8d9777fabb08595c0687', '2026-06-02 04:40:39'),
(4, 'Angela Natalia Nurdin', 'natalia', '$2y$10$/3wzOohQRMHP2xfHUQ0CPOhnork7On/sh1xlCSTUbJwRH8d7PBFJ.', 'general manager', 'active', NULL, '2026-09-08 09:22:14', NULL, '2026-06-23 09:38:46'),
(5, 'Iis Aisyah', 'iis', '$2y$10$w50r0vCVD2mivXIjdkJqBuMMHxY.N6OcpSLB..8CJ0jK9K9ivMxF6', 'purchasing', 'active', '660be06f1241d1b8516ddc115dc89176983f8eafdcef4b65dd7db48de17c8cd0', NULL, NULL, '2026-07-06 04:56:25'),
(7, 'Sandi Suwardi', 'sandi', '$2y$10$aTgdXTO456LL68oCUfumC.fDifvElHEi6YOcPGZ7IA4vxsp.yu.qW', 'general affair', 'active', '9e53c4a1e8fad7f48b90da5e1f75c33786fd877088c7ba85ec9943d1a39c12f9', '2026-09-03 08:20:39', NULL, '2026-07-06 05:54:57'),
(8, 'Norma Putri Kusuma Dewi', 'norma', '$2y$10$50dGKV40tUD/UHMQxQNSwO0myIW.fEJ1I67oUGK5yH3jGj/MuMb0G', 'marketing', 'active', 'a176e252d6e168b0fb7fe89602ec6418dba5757811b7a81d5ea9ba0a7f882b9d', '2026-09-07 16:45:05', '50432b72dbe27a32f868cb2a2a62ad24ad41e1d6f53dc7ad0a7042b394ca4fbe', '2026-08-12 05:55:06'),
(9, 'Budi Agus Wibowo', 'budi', '$2y$10$Tv7vgUGteSd4Uo1tP6wGgeVVqyIk94jlRiamJWVihfFn0vDMEYtKm', 'manager', 'active', NULL, NULL, NULL, '2026-08-20 06:54:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `role` enum('marketing','manager','admin') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `role`, `email`, `password`) VALUES
(1, 'STAFF', 'marketing', 'marketing@citraplastik.com', 'e10adc3949ba59abbe56e057f20f883e');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `part_number` (`part_number`);

--
-- Indeks untuk tabel `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `part_number` (`part_number`);

--
-- Indeks untuk tabel `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_activity_log`
--
ALTER TABLE `tbl_activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_audit_log`
--
ALTER TABLE `tbl_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indeks untuk tabel `tbl_customer`
--
ALTER TABLE `tbl_customer`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_dandori_master`
--
ALTER TABLE `tbl_dandori_master`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_exchange_rate`
--
ALTER TABLE `tbl_exchange_rate`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_login_log`
--
ALTER TABLE `tbl_login_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `tbl_master_tonnage`
--
ALTER TABLE `tbl_master_tonnage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tonnage_key` (`tonnage_key`);

--
-- Indeks untuk tabel `tbl_material_master`
--
ALTER TABLE `tbl_material_master`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indeks untuk tabel `tbl_mmp_det`
--
ALTER TABLE `tbl_mmp_det`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `mmp_id` (`mmp_id`);

--
-- Indeks untuk tabel `tbl_mmp_head`
--
ALTER TABLE `tbl_mmp_head`
  ADD PRIMARY KEY (`mmp_id`);

--
-- Indeks untuk tabel `tbl_mold_maintenance_master`
--
ALTER TABLE `tbl_mold_maintenance_master`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_packing_cost`
--
ALTER TABLE `tbl_packing_cost`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `part_name` (`part_name`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `idx_category` (`item_category`);

--
-- Indeks untuk tabel `tbl_packing_costing`
--
ALTER TABLE `tbl_packing_costing`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_packing_master`
--
ALTER TABLE `tbl_packing_master`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_packing_options`
--
ALTER TABLE `tbl_packing_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `packing_standard_id` (`packing_standard_id`);

--
-- Indeks untuk tabel `tbl_packing_standard`
--
ALTER TABLE `tbl_packing_standard`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_customer_part` (`customer_id`,`part_number`,`revision_no`),
  ADD UNIQUE KEY `inner_item_id_2` (`inner_item_id`,`plastic_item_id`,`box_item_id`,`pallet_item_id`),
  ADD UNIQUE KEY `description` (`description`),
  ADD KEY `inner_item_id` (`inner_item_id`),
  ADD KEY `plastic_item_id` (`plastic_item_id`),
  ADD KEY `box_item_id` (`box_item_id`),
  ADD KEY `pallet_item_id` (`pallet_item_id`),
  ADD KEY `idx_part_number` (`part_number`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `tbl_packing_standard_detail`
--
ALTER TABLE `tbl_packing_standard_detail`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_pack_trans_items`
--
ALTER TABLE `tbl_pack_trans_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_packing` (`project_id`);

--
-- Indeks untuk tabel `tbl_pack_trans_project`
--
ALTER TABLE `tbl_pack_trans_project`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_purging_master`
--
ALTER TABLE `tbl_purging_master`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_quotation`
--
ALTER TABLE `tbl_quotation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `exchange_rate_id` (`exchange_rate_id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `machine_rate_id` (`machine_rate_id`);

--
-- Indeks untuk tabel `tbl_quotation_packaging`
--
ALTER TABLE `tbl_quotation_packaging`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_id` (`quotation_id`);

--
-- Indeks untuk tabel `tbl_quotation_revision`
--
ALTER TABLE `tbl_quotation_revision`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_id` (`quotation_id`),
  ADD KEY `revised_by` (`revised_by`);

--
-- Indeks untuk tabel `tbl_rate_drafts`
--
ALTER TABLE `tbl_rate_drafts`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_rate_master`
--
ALTER TABLE `tbl_rate_master`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_supplier`
--
ALTER TABLE `tbl_supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_transport_cost`
--
ALTER TABLE `tbl_transport_cost`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_archived` (`is_archived`);

--
-- Indeks untuk tabel `tbl_transport_master`
--
ALTER TABLE `tbl_transport_master`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_transport_name` (`transport_name`);

--
-- Indeks untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tbl_activity_log`
--
ALTER TABLE `tbl_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=230;

--
-- AUTO_INCREMENT untuk tabel `tbl_audit_log`
--
ALTER TABLE `tbl_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `tbl_customer`
--
ALTER TABLE `tbl_customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT untuk tabel `tbl_dandori_master`
--
ALTER TABLE `tbl_dandori_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `tbl_exchange_rate`
--
ALTER TABLE `tbl_exchange_rate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_login_log`
--
ALTER TABLE `tbl_login_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;

--
-- AUTO_INCREMENT untuk tabel `tbl_master_tonnage`
--
ALTER TABLE `tbl_master_tonnage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `tbl_material_master`
--
ALTER TABLE `tbl_material_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_mmp_det`
--
ALTER TABLE `tbl_mmp_det`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT untuk tabel `tbl_mmp_head`
--
ALTER TABLE `tbl_mmp_head`
  MODIFY `mmp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `tbl_mold_maintenance_master`
--
ALTER TABLE `tbl_mold_maintenance_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `tbl_packing_cost`
--
ALTER TABLE `tbl_packing_cost`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT untuk tabel `tbl_packing_costing`
--
ALTER TABLE `tbl_packing_costing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_packing_master`
--
ALTER TABLE `tbl_packing_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_packing_options`
--
ALTER TABLE `tbl_packing_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_packing_standard`
--
ALTER TABLE `tbl_packing_standard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `tbl_packing_standard_detail`
--
ALTER TABLE `tbl_packing_standard_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_pack_trans_items`
--
ALTER TABLE `tbl_pack_trans_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT untuk tabel `tbl_pack_trans_project`
--
ALTER TABLE `tbl_pack_trans_project`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `tbl_purging_master`
--
ALTER TABLE `tbl_purging_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `tbl_quotation`
--
ALTER TABLE `tbl_quotation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT untuk tabel `tbl_quotation_packaging`
--
ALTER TABLE `tbl_quotation_packaging`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_quotation_revision`
--
ALTER TABLE `tbl_quotation_revision`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_rate_drafts`
--
ALTER TABLE `tbl_rate_drafts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `tbl_rate_master`
--
ALTER TABLE `tbl_rate_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=278;

--
-- AUTO_INCREMENT untuk tabel `tbl_supplier`
--
ALTER TABLE `tbl_supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_transport_cost`
--
ALTER TABLE `tbl_transport_cost`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT untuk tabel `tbl_transport_master`
--
ALTER TABLE `tbl_transport_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tbl_audit_log`
--
ALTER TABLE `tbl_audit_log`
  ADD CONSTRAINT `tbl_audit_log_ibfk_1` FOREIGN KEY (`changed_by`) REFERENCES `tbl_users` (`id`);

--
-- Ketidakleluasaan untuk tabel `tbl_login_log`
--
ALTER TABLE `tbl_login_log`
  ADD CONSTRAINT `tbl_login_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_material_master`
--
ALTER TABLE `tbl_material_master`
  ADD CONSTRAINT `tbl_material_master_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `tbl_supplier` (`id`);

--
-- Ketidakleluasaan untuk tabel `tbl_mmp_det`
--
ALTER TABLE `tbl_mmp_det`
  ADD CONSTRAINT `tbl_mmp_det_ibfk_1` FOREIGN KEY (`mmp_id`) REFERENCES `tbl_mmp_head` (`mmp_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_packing_cost`
--
ALTER TABLE `tbl_packing_cost`
  ADD CONSTRAINT `tbl_packing_cost_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `tbl_supplier` (`id`);

--
-- Ketidakleluasaan untuk tabel `tbl_packing_options`
--
ALTER TABLE `tbl_packing_options`
  ADD CONSTRAINT `tbl_packing_options_ibfk_1` FOREIGN KEY (`packing_standard_id`) REFERENCES `tbl_packing_standard` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_packing_standard`
--
ALTER TABLE `tbl_packing_standard`
  ADD CONSTRAINT `tbl_packing_standard_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customer` (`id`),
  ADD CONSTRAINT `tbl_packing_standard_ibfk_2` FOREIGN KEY (`inner_item_id`) REFERENCES `tbl_packing_cost` (`id`),
  ADD CONSTRAINT `tbl_packing_standard_ibfk_3` FOREIGN KEY (`plastic_item_id`) REFERENCES `tbl_packing_cost` (`id`),
  ADD CONSTRAINT `tbl_packing_standard_ibfk_4` FOREIGN KEY (`box_item_id`) REFERENCES `tbl_packing_cost` (`id`),
  ADD CONSTRAINT `tbl_packing_standard_ibfk_5` FOREIGN KEY (`pallet_item_id`) REFERENCES `tbl_packing_cost` (`id`);

--
-- Ketidakleluasaan untuk tabel `tbl_pack_trans_items`
--
ALTER TABLE `tbl_pack_trans_items`
  ADD CONSTRAINT `fk_project_packing` FOREIGN KEY (`project_id`) REFERENCES `tbl_pack_trans_project` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_quotation`
--
ALTER TABLE `tbl_quotation`
  ADD CONSTRAINT `tbl_quotation_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customer` (`id`),
  ADD CONSTRAINT `tbl_quotation_ibfk_2` FOREIGN KEY (`exchange_rate_id`) REFERENCES `tbl_exchange_rate` (`id`),
  ADD CONSTRAINT `tbl_quotation_ibfk_3` FOREIGN KEY (`material_id`) REFERENCES `tbl_material_master` (`id`),
  ADD CONSTRAINT `tbl_quotation_ibfk_4` FOREIGN KEY (`machine_rate_id`) REFERENCES `tbl_rate_master` (`id`);

--
-- Ketidakleluasaan untuk tabel `tbl_quotation_packaging`
--
ALTER TABLE `tbl_quotation_packaging`
  ADD CONSTRAINT `tbl_quotation_packaging_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `tbl_quotation` (`id`);

--
-- Ketidakleluasaan untuk tabel `tbl_quotation_revision`
--
ALTER TABLE `tbl_quotation_revision`
  ADD CONSTRAINT `tbl_quotation_revision_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `tbl_quotation` (`id`),
  ADD CONSTRAINT `tbl_quotation_revision_ibfk_2` FOREIGN KEY (`revised_by`) REFERENCES `tbl_users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
