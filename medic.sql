-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2019 at 04:07 AM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.0.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medic`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `buy_per_week`
-- (See below for the actual view)
--
CREATE TABLE `buy_per_week` (
`tgl` date
,`total` double
);

-- --------------------------------------------------------

--
-- Table structure for table `cashflow`
--

CREATE TABLE `cashflow` (
  `id_transaksi` varchar(50) DEFAULT NULL,
  `tgl_transaksi` datetime DEFAULT NULL,
  `keterangan` varchar(200) DEFAULT NULL,
  `uang_masuk` double DEFAULT NULL,
  `uang_keluar` double DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cashflow`
--

INSERT INTO `cashflow` (`id_transaksi`, `tgl_transaksi`, `keterangan`, `uang_masuk`, `uang_keluar`, `created_at`, `updated_at`) VALUES
('IDPEN313969', '2019-11-29 12:37:00', 'Penjualan Barang', 65200, 0, '2019-11-29 12:38:12', '2019-11-29 12:38:12'),
('IDPEN34231', '2019-11-29 12:49:00', 'Penjualan Barang', 48000, 0, '2019-11-29 12:50:06', '2019-11-29 12:50:06'),
('IDPEM830821', '2019-12-01 09:45:00', 'Pembelian Barang', 0, 40000, '2019-12-01 09:48:34', '2019-12-01 09:48:34'),
('IDM1576377803356', '2019-12-15 09:43:23', 'Input Modal Awal Kasir', 100000, 0, '2019-12-15 09:43:23', '2019-12-15 09:43:23');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pembelian`
--

CREATE TABLE `detail_pembelian` (
  `id_pembelian` varchar(30) DEFAULT NULL,
  `id_obat` varchar(20) DEFAULT NULL,
  `jumlah` double DEFAULT NULL,
  `id_satuan_grosir` varchar(20) NOT NULL,
  `harga_beli` double DEFAULT NULL,
  `diskon` double NOT NULL,
  `retur` double NOT NULL,
  `harga_dasar` double NOT NULL,
  `id_satuan_dasar` varchar(20) NOT NULL,
  `margin_1` double NOT NULL,
  `margin_2` double NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `detail_pembelian`
--

INSERT INTO `detail_pembelian` (`id_pembelian`, `id_obat`, `jumlah`, `id_satuan_grosir`, `harga_beli`, `diskon`, `retur`, `harga_dasar`, `id_satuan_dasar`, `margin_1`, `margin_2`, `created_at`, `updated_at`) VALUES
('IDPEM830821', 'IDO54824', 10, 'IDS3612', 1000, 50, 0, 10000, 'IDS3612', 40, 30, '2019-12-01 02:48:33', '0000-00-00 00:00:00'),
('IDPEM830821', 'IDO76952', 10, 'IDS4015', 5000, 30, 0, 13000, 'IDS4015', 10, 10, '2019-12-01 02:48:33', '0000-00-00 00:00:00');

--
-- Triggers `detail_pembelian`
--
DELIMITER $$
CREATE TRIGGER `tambahStok` AFTER INSERT ON `detail_pembelian` FOR EACH ROW BEGIN
   DECLARE stok_obat double;
   DECLARE tgl date;
   DECLARE no varchar(30);
   UPDATE obat set stok = stok + new.jumlah, harga = new.harga_dasar, margin_1 = (new.margin_1/100), margin_2 = (new.margin_2/100) where id_obat = new.id_obat;
   SET stok_obat = (SELECT stok FROM obat WHERE id_obat = new.id_obat);
   SET tgl = (SELECT DATE(tgl_pembelian) FROM pembelian WHERE id_pembelian = new.id_pembelian);
   SET no = (SELECT no_faktur FROM pembelian WHERE id_pembelian = new.id_pembelian);

   IF(EXISTS(SELECT * FROM log_stok WHERE tgl_stok = tgl AND id_obat = new.id_obat)) THEN
     UPDATE log_stok SET masuk = masuk + new.jumlah, sisa = stok_obat, harga_beli = new.harga_beli,
     no_faktur = no WHERE id_obat = new.id_obat and tgl_stok = tgl;
   ELSE
     INSERT INTO log_stok VALUES(tgl,new.id_obat,no,new.harga_beli,null,new.jumlah,0,stok_obat);
   END IF;
  END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_penjualan`
--

CREATE TABLE `detail_penjualan` (
  `id_penjualan` varchar(30) DEFAULT NULL,
  `id_obat` varchar(20) DEFAULT NULL,
  `jumlah` double DEFAULT NULL,
  `harga_jual` double DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `detail_penjualan`
--

INSERT INTO `detail_penjualan` (`id_penjualan`, `id_obat`, `jumlah`, `harga_jual`, `created_at`, `updated_at`) VALUES
('IDPEN34231', 'IDO54824', 2, 17000, '2019-11-29 05:50:06', '0000-00-00 00:00:00'),
('IDPEN34231', 'IDO15242', 2, 7000, '2019-11-29 05:50:06', '0000-00-00 00:00:00');

--
-- Triggers `detail_penjualan`
--
DELIMITER $$
CREATE TRIGGER `kurangStok` AFTER INSERT ON `detail_penjualan` FOR EACH ROW BEGIN
  DECLARE stok_obat double;
  DECLARE tgl date;
  UPDATE obat set stok = stok - new.jumlah where id_obat = new.id_obat;
  SET stok_obat = (SELECT stok FROM obat WHERE id_obat = new.id_obat);
  SET tgl = (SELECT DATE(tgl_penjualan) FROM penjualan WHERE id_penjualan = new.id_penjualan);

  IF(EXISTS(SELECT * FROM log_stok WHERE tgl_stok = tgl AND id_obat = new.id_obat)) THEN
    UPDATE log_stok SET keluar = keluar + new.jumlah, sisa = stok_obat, harga_jual = new.harga_jual
    WHERE id_obat = new.id_obat and tgl_stok = tgl;
  ELSE
    INSERT INTO log_stok VALUES(tgl,new.id_obat,null,null,new.harga_jual,0,new.jumlah,stok_obat);
  END IF;
 END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_penjualan_racikan`
--

CREATE TABLE `detail_penjualan_racikan` (
  `id_penjualan_racikan` varchar(30) DEFAULT NULL,
  `id_obat_racikan` varchar(20) DEFAULT NULL,
  `jumlah` double DEFAULT NULL,
  `harga_jual` double DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dokter`
--

CREATE TABLE `dokter` (
  `id_dokter` varchar(30) NOT NULL,
  `nama_dokter` varchar(200) DEFAULT NULL,
  `alamat` varchar(200) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dokter`
--

INSERT INTO `dokter` (`id_dokter`, `nama_dokter`, `alamat`, `telp`, `created_at`, `updated_at`) VALUES
('IDD17037', 'dr. Sally Sendiri', 'Lawang', '0929223', '2019-11-02 09:46:26', '2019-11-02 16:46:26'),
('IDD78364', 'dr. Arifin', 'Malang', '09238232', '2019-11-02 16:43:48', '2019-11-02 16:43:48'),
('IDD80374', 'dr. mega', 'puskesmas jabung', '-', '2019-11-14 14:29:02', '2019-11-14 14:29:02'),
('IDD81513', 'dr. Griezman', 'Spanyol', '092322', '2019-11-30 18:58:10', '2019-11-30 18:58:10'),
('IDD88456', 'yotin bayu merryani', 'jl. raya pakis kembar no.2', '082257372675', '2019-11-14 14:28:31', '2019-11-14 14:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` varchar(20) NOT NULL,
  `nama_kategori` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `created_at`, `updated_at`) VALUES
('IDK4676', 'Khusus', '2019-10-01 13:42:49', '2019-10-01 13:42:49'),
('IDK7365', 'Umum', '2019-10-01 13:43:09', '2019-10-01 13:43:09');

-- --------------------------------------------------------

--
-- Table structure for table `log_stok`
--

CREATE TABLE `log_stok` (
  `tgl_stok` date DEFAULT NULL,
  `id_obat` varchar(20) DEFAULT NULL,
  `no_faktur` varchar(30) DEFAULT NULL,
  `harga_beli` double DEFAULT NULL,
  `harga_jual` double DEFAULT NULL,
  `masuk` double DEFAULT NULL,
  `keluar` double DEFAULT NULL,
  `sisa` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `log_stok`
--

INSERT INTO `log_stok` (`tgl_stok`, `id_obat`, `no_faktur`, `harga_beli`, `harga_jual`, `masuk`, `keluar`, `sisa`) VALUES
('2019-10-26', 'IDO15242', 'F67898', 5000, 7000, 24, 3, 141),
('2019-10-26', 'IDO89662', 'F67898', 12000, 18600, 18, 3, 125),
('2019-10-27', 'IDO15242', '766564', 10000, NULL, 10, 0, 151),
('2019-10-31', 'IDO15242', 'F678991', 2500, 7000, 100, 4, 247),
('2019-10-31', 'IDO76952', 'F678991', 3000, 1500, 50, 31, 19),
('2019-10-31', 'IDO89662', NULL, NULL, 18600, 0, 2, 123),
('2019-11-05', 'IDO15242', 'F0002', 3000, NULL, 10, 0, 257),
('2019-11-17', 'IDO89662', NULL, NULL, 18600, 0, 9, 114),
('2019-11-23', 'IDO89662', '1200091', 10000, NULL, 3, 0, 117),
('2019-11-23', 'IDO15242', '1200091', 20000, NULL, 8, 0, 265),
('2019-11-24', 'IDO54824', '4343902', 10000, 20400, 10, 10, 100),
('2019-11-24', 'IDO76952', NULL, NULL, 1500, 0, 5, 14),
('2019-11-26', 'IDO54824', 'F02930293', 5000, NULL, 5, 0, 105),
('2019-11-26', 'IDO76952', 'F02930293', 10000, NULL, 7, 0, 21),
('2019-11-29', 'IDO54824', NULL, NULL, 17000, 0, 4, 101),
('2019-11-29', 'IDO76952', NULL, NULL, 15600, 0, 2, 19),
('2019-11-29', 'IDO15242', NULL, NULL, 7000, 0, 2, 263),
('2019-12-01', 'IDO54824', 'F0001', 1000, NULL, 10, 0, 111),
('2019-12-01', 'IDO76952', 'F0001', 5000, NULL, 10, 0, 29);

-- --------------------------------------------------------

--
-- Table structure for table `modal`
--

CREATE TABLE `modal` (
  `id_modal` varchar(30) NOT NULL,
  `tgl_modal` date DEFAULT NULL,
  `nilai_modal` double DEFAULT NULL,
  `id_user` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `modal`
--

INSERT INTO `modal` (`id_modal`, `tgl_modal`, `nilai_modal`, `id_user`, `created_at`, `updated_at`) VALUES
('IDM1571483360384', '2019-10-19', 50000, 'ID123', '2019-11-24 08:31:37', '2019-10-19 11:09:20'),
('IDM1574559273125', '2019-11-24', 500000, 'ID123', '2019-11-24 08:34:33', '2019-11-24 08:34:33'),
('IDM1574588807572', '2019-11-24', 100000, 'ID123', '2019-11-24 16:46:47', '2019-11-24 16:46:47'),
('IDM1574588901225', '2019-11-24', 50000, 'ID123', '2019-11-24 16:48:21', '2019-11-24 16:48:21'),
('IDM1576377803356', '2019-12-15', 100000, 'ID123', '2019-12-15 09:43:23', '2019-12-15 09:43:23');

-- --------------------------------------------------------

--
-- Table structure for table `obat`
--

CREATE TABLE `obat` (
  `id_obat` varchar(20) NOT NULL,
  `nama_obat` varchar(200) DEFAULT NULL,
  `stok` double DEFAULT NULL,
  `id_satuan` varchar(20) DEFAULT NULL,
  `id_kategori` varchar(20) DEFAULT NULL,
  `harga` double DEFAULT NULL,
  `margin_1` double DEFAULT NULL,
  `margin_2` double DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `obat`
--

INSERT INTO `obat` (`id_obat`, `nama_obat`, `stok`, `id_satuan`, `id_kategori`, `harga`, `margin_1`, `margin_2`, `created_at`, `updated_at`) VALUES
('IDO15242', 'Paramex', 263, 'IDS4015', 'IDK7365', 5000, 0.2, 0.2, '2019-11-29 05:50:06', '2019-10-02 08:59:10'),
('IDO54824', 'Viks Formula 44 25 ml', 111, 'IDS3612', 'IDK7365', 10000, 0.4, 0.3, '2019-12-01 02:48:33', '2019-11-24 08:36:22'),
('IDO76952', 'Bodrex', 29, 'IDS4015', 'IDK7365', 13000, 0.1, 0.1, '2019-12-01 02:48:33', '2019-11-25 17:18:25'),
('IDO89662', 'Saridon', 117, 'IDS3612', 'IDK7365', 15500, 0.1, 0.1, '2019-11-23 02:57:21', '2019-10-01 14:41:31');

-- --------------------------------------------------------

--
-- Table structure for table `obat_racikan`
--

CREATE TABLE `obat_racikan` (
  `id_obat_racikan` varchar(20) NOT NULL,
  `nama_obat_racikan` varchar(200) DEFAULT NULL,
  `stok` double NOT NULL,
  `id_kategori` varchar(20) DEFAULT NULL,
  `id_satuan` varchar(20) DEFAULT NULL,
  `harga` double DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `obat_racikan`
--

INSERT INTO `obat_racikan` (`id_obat_racikan`, `nama_obat_racikan`, `stok`, `id_kategori`, `id_satuan`, `harga`, `created_at`, `updated_at`) VALUES
('IDOR55254', 'Oralit', 100, 'IDK4676', 'IDS4015', 13000, '2019-11-02 16:51:34', '2019-11-02 16:51:34'),
('IDOR93150', 'Obat Batuk', 200, 'IDK7365', 'IDS4015', 15000, '2019-11-02 09:54:42', '2019-11-02 16:54:42');

-- --------------------------------------------------------

--
-- Table structure for table `pasien`
--

CREATE TABLE `pasien` (
  `id_pasien` varchar(20) NOT NULL,
  `nama_pasien` varchar(200) DEFAULT NULL,
  `alamat` varchar(300) DEFAULT NULL,
  `umur` int(11) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pasien`
--

INSERT INTO `pasien` (`id_pasien`, `nama_pasien`, `alamat`, `umur`, `telp`, `created_at`, `updated_at`) VALUES
('IDPS21002', 'Gunawan', 'Surabaya', 33, '09829839', '2019-11-02 16:38:53', '2019-11-02 16:38:53'),
('IDPS37824', 'hari husada', 'jl. raya paksi kembar no.2', 30, '082257372675', '2019-11-14 14:31:27', '2019-11-14 14:31:27'),
('IDPS85771', 'Sulastri', 'Jabung', 100, '09329839', '2019-11-30 18:58:45', '2019-11-30 18:58:45'),
('IDPS95988', 'Gunadir', 'Malang', 30, '09289323', '2019-11-02 09:47:32', '2019-11-02 16:47:32');

-- --------------------------------------------------------

--
-- Table structure for table `pembelian`
--

CREATE TABLE `pembelian` (
  `id_pembelian` varchar(30) NOT NULL,
  `tgl_pembelian` datetime DEFAULT NULL,
  `no_faktur` varchar(30) DEFAULT NULL,
  `invoice` varchar(30) DEFAULT NULL,
  `id_supplier` varchar(20) DEFAULT NULL,
  `jenis_transaksi` varchar(10) DEFAULT NULL,
  `tgl_jatuh_tempo` date DEFAULT NULL,
  `id_user` varchar(20) DEFAULT NULL,
  `is_return` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pembelian`
--

INSERT INTO `pembelian` (`id_pembelian`, `tgl_pembelian`, `no_faktur`, `invoice`, `id_supplier`, `jenis_transaksi`, `tgl_jatuh_tempo`, `id_user`, `is_return`, `created_at`, `updated_at`) VALUES
('IDPEM830821', '2019-12-01 09:45:00', 'F0001', 'INV4343', 'IDS2469', '1', '2019-12-01', 'ID122', 0, '2019-12-01 09:48:33', '2019-12-01 09:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `id_penjualan` varchar(30) NOT NULL,
  `tgl_penjualan` datetime DEFAULT NULL,
  `id_user` varchar(20) DEFAULT NULL,
  `id_pasien` varchar(30) NOT NULL,
  `id_dokter` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `tgl_penjualan`, `id_user`, `id_pasien`, `id_dokter`, `created_at`, `updated_at`) VALUES
('IDPEN34231', '2019-11-29 12:49:00', 'ID122', 'IDPS37824', 'IDD17037', '2019-11-29 12:50:06', '2019-11-29 12:50:06');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan_racikan`
--

CREATE TABLE `penjualan_racikan` (
  `id_penjualan_racikan` varchar(30) NOT NULL,
  `tgl_penjualan_racikan` datetime DEFAULT NULL,
  `id_user` varchar(20) DEFAULT NULL,
  `id_dokter` varchar(20) DEFAULT NULL,
  `id_pasien` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `perweek`
-- (See below for the actual view)
--
CREATE TABLE `perweek` (
`tgl` date
,`total` double
);

-- --------------------------------------------------------

--
-- Table structure for table `satuan`
--

CREATE TABLE `satuan` (
  `id_satuan` varchar(20) NOT NULL,
  `nama_satuan` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `satuan`
--

INSERT INTO `satuan` (`id_satuan`, `nama_satuan`, `created_at`, `updated_at`) VALUES
('IDS3612', 'Botol', '2019-10-01 13:29:51', '2019-10-01 13:29:51'),
('IDS4015', 'Tablet', '2019-10-01 13:29:33', '2019-10-01 13:29:33'),
('IDS5698', 'Strip', '2019-11-24 08:37:20', '2019-11-24 08:37:20');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` varchar(20) NOT NULL,
  `nama_supplier` varchar(200) DEFAULT NULL,
  `alamat` varchar(300) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `alamat`, `created_at`, `updated_at`) VALUES
('IDS2469', 'Sumiyati', 'Blimbing, Kota Malang', '2019-10-02 00:12:30', '2019-10-02 00:12:30'),
('IDS2873', 'Jupri', 'Pakisaji, Kab. Malang', '2019-10-02 00:11:00', '2019-10-02 00:11:00'),
('IDS5410', 'Sundari', 'Pakisaji', '2019-12-01 18:43:13', '2019-12-01 18:43:13'),
('IDS8341', 'Supriyadi', 'Turen', '2019-12-01 18:50:13', '2019-12-01 18:50:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` varchar(20) NOT NULL,
  `nama_user` varchar(200) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(300) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `token` varchar(300) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_user`, `username`, `password`, `status`, `token`, `created_at`, `updated_at`) VALUES
('ID122', 'Zakaria', 'jack', 'eyJpdiI6IktwelpsQlwvUCtqR0RRcUVabEJoayt3PT0iLCJ2YWx1ZSI6IjZxSFZxUE1mSVJtVE50T1Y4T1A2M0E9PSIsIm1hYyI6IjQ0MmFiYjViOTczNGE1ZThmNmU3MzFiOTE0MDEwNTI4ZmE1ZDViMmYyZTJkMDdkMjc2OTYxYmEyM2RlOTFlZGEifQ==', 'owner', 'f80c238dc7fb186d3191cda132fd3b8fc1b06766', '2019-11-23 12:34:12', '2019-11-23 19:34:12'),
('ID123', 'Jupri', 'juprix', 'eyJpdiI6Iks4S3hTQjE3K1FQRngrVGk3c0VPUVE9PSIsInZhbHVlIjoibTdqSFZDS09QUlNsVDNXbUpjbFpWQT09IiwibWFjIjoiMDEzOTU2Y2FkNTFjOWU3ODlhZDJkZGNhMDg3NmI0YTk4MThlNDYwOTUzNjg1MWFmMTM2MjNhN2I3YzVhYjZlOCJ9', 'kasir', '9d1b63479398d73eb776b60c688801cc717780f5', '2019-11-24 01:34:23', '2019-11-24 08:34:23');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_produk_beli`
-- (See below for the actual view)
--
CREATE TABLE `v_produk_beli` (
`id_obat` varchar(20)
,`nama_obat` varchar(200)
,`jumlah_obat` double
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_produk_jual`
-- (See below for the actual view)
--
CREATE TABLE `v_produk_jual` (
`id_obat` varchar(20)
,`nama_obat` varchar(200)
,`jumlah_obat` double
);

-- --------------------------------------------------------

--
-- Structure for view `buy_per_week`
--
DROP TABLE IF EXISTS `buy_per_week`;

CREATE VIEW `buy_per_week`  AS  select cast(`c`.`tgl_transaksi` as date) AS `tgl`,sum(`c`.`uang_keluar`) AS `total` from `cashflow` `c` where (`c`.`id_transaksi` like 'IDPEM%') group by cast(`c`.`tgl_transaksi` as date) order by `c`.`tgl_transaksi` ;

-- --------------------------------------------------------

--
-- Structure for view `perweek`
--
DROP TABLE IF EXISTS `perweek`;

CREATE VIEW `perweek`  AS  select cast(`c`.`tgl_transaksi` as date) AS `tgl`,sum(`c`.`uang_masuk`) AS `total` from `cashflow` `c` where (`c`.`id_transaksi` like 'IDPEN%') group by cast(`c`.`tgl_transaksi` as date) order by `c`.`tgl_transaksi` ;

-- --------------------------------------------------------

--
-- Structure for view `v_produk_beli`
--
DROP TABLE IF EXISTS `v_produk_beli`;

CREATE VIEW `v_produk_beli`  AS  select `d`.`id_obat` AS `id_obat`,`o`.`nama_obat` AS `nama_obat`,sum(`d`.`jumlah`) AS `jumlah_obat` from (`detail_pembelian` `d` left join `obat` `o` on((`o`.`id_obat` = `d`.`id_obat`))) group by `d`.`id_obat` order by sum(`d`.`jumlah`) desc ;

-- --------------------------------------------------------

--
-- Structure for view `v_produk_jual`
--
DROP TABLE IF EXISTS `v_produk_jual`;

CREATE VIEW `v_produk_jual`  AS  select `d`.`id_obat` AS `id_obat`,`o`.`nama_obat` AS `nama_obat`,sum(`d`.`jumlah`) AS `jumlah_obat` from (`detail_penjualan` `d` left join `obat` `o` on((`o`.`id_obat` = `d`.`id_obat`))) group by `d`.`id_obat` order by sum(`d`.`jumlah`) desc ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD KEY `id_pembelian` (`id_pembelian`,`id_obat`),
  ADD KEY `detail_pembelian_ibfk_2` (`id_obat`),
  ADD KEY `id_satuan_grosir` (`id_satuan_grosir`),
  ADD KEY `id_satuan_dasar` (`id_satuan_dasar`);

--
-- Indexes for table `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD KEY `id_penjualan` (`id_penjualan`,`id_obat`),
  ADD KEY `detail_penjualan_ibfk_2` (`id_obat`);

--
-- Indexes for table `detail_penjualan_racikan`
--
ALTER TABLE `detail_penjualan_racikan`
  ADD KEY `id_penjualan_racikan` (`id_penjualan_racikan`,`id_obat_racikan`),
  ADD KEY `id_obat_racikan` (`id_obat_racikan`);

--
-- Indexes for table `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id_dokter`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `log_stok`
--
ALTER TABLE `log_stok`
  ADD KEY `id_obat` (`id_obat`);

--
-- Indexes for table `modal`
--
ALTER TABLE `modal`
  ADD PRIMARY KEY (`id_modal`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `obat`
--
ALTER TABLE `obat`
  ADD PRIMARY KEY (`id_obat`),
  ADD KEY `id_satuan` (`id_satuan`,`id_kategori`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `obat_racikan`
--
ALTER TABLE `obat_racikan`
  ADD PRIMARY KEY (`id_obat_racikan`),
  ADD KEY `id_satuan` (`id_satuan`,`id_kategori`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id_pasien`);

--
-- Indexes for table `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`id_pembelian`),
  ADD KEY `id_user` (`id_user`,`id_supplier`),
  ADD KEY `id_supplier` (`id_supplier`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `penjualan_racikan`
--
ALTER TABLE `penjualan_racikan`
  ADD PRIMARY KEY (`id_penjualan_racikan`),
  ADD KEY `id_user` (`id_user`,`id_dokter`,`id_pasien`),
  ADD KEY `id_pasien` (`id_pasien`),
  ADD KEY `id_dokter` (`id_dokter`);

--
-- Indexes for table `satuan`
--
ALTER TABLE `satuan`
  ADD PRIMARY KEY (`id_satuan`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD CONSTRAINT `detail_pembelian_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `pembelian` (`id_pembelian`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_pembelian_ibfk_2` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_pembelian_ibfk_3` FOREIGN KEY (`id_satuan_grosir`) REFERENCES `satuan` (`id_satuan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_pembelian_ibfk_4` FOREIGN KEY (`id_satuan_dasar`) REFERENCES `satuan` (`id_satuan`);

--
-- Constraints for table `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id_penjualan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_penjualan_racikan`
--
ALTER TABLE `detail_penjualan_racikan`
  ADD CONSTRAINT `detail_penjualan_racikan_ibfk_1` FOREIGN KEY (`id_penjualan_racikan`) REFERENCES `penjualan_racikan` (`id_penjualan_racikan`),
  ADD CONSTRAINT `detail_penjualan_racikan_ibfk_2` FOREIGN KEY (`id_obat_racikan`) REFERENCES `obat_racikan` (`id_obat_racikan`);

--
-- Constraints for table `modal`
--
ALTER TABLE `modal`
  ADD CONSTRAINT `modal_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `obat`
--
ALTER TABLE `obat`
  ADD CONSTRAINT `obat_ibfk_1` FOREIGN KEY (`id_satuan`) REFERENCES `satuan` (`id_satuan`),
  ADD CONSTRAINT `obat_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Constraints for table `obat_racikan`
--
ALTER TABLE `obat_racikan`
  ADD CONSTRAINT `obat_racikan_ibfk_1` FOREIGN KEY (`id_satuan`) REFERENCES `satuan` (`id_satuan`),
  ADD CONSTRAINT `obat_racikan_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Constraints for table `pembelian`
--
ALTER TABLE `pembelian`
  ADD CONSTRAINT `pembelian_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`),
  ADD CONSTRAINT `pembelian_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `penjualan_racikan`
--
ALTER TABLE `penjualan_racikan`
  ADD CONSTRAINT `penjualan_racikan_ibfk_1` FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`),
  ADD CONSTRAINT `penjualan_racikan_ibfk_2` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`),
  ADD CONSTRAINT `penjualan_racikan_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
