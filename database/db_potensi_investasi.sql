-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: db_potensi_investasi
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `dokumentasi_potensi`
--

DROP TABLE IF EXISTS `dokumentasi_potensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dokumentasi_potensi` (
  `id_dokumentasi` int unsigned NOT NULL AUTO_INCREMENT,
  `id_potensi` int unsigned NOT NULL,
  `nama_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_file_asli` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_cover` tinyint(1) NOT NULL DEFAULT '0',
  `uploaded_by` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_dokumentasi`),
  KEY `fk_dokumentasi_user` (`uploaded_by`),
  KEY `idx_dokumentasi_potensi` (`id_potensi`),
  CONSTRAINT `fk_dokumentasi_potensi` FOREIGN KEY (`id_potensi`) REFERENCES `potensi_investasi` (`id_potensi`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dokumentasi_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dokumentasi_potensi`
--

LOCK TABLES `dokumentasi_potensi` WRITE;
/*!40000 ALTER TABLE `dokumentasi_potensi` DISABLE KEYS */;
/*!40000 ALTER TABLE `dokumentasi_potensi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kecamatan`
--

DROP TABLE IF EXISTS `kecamatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kecamatan` (
  `id_kecamatan` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_kecamatan` varchar(100) NOT NULL,
  `deskripsi` text,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kecamatan`),
  UNIQUE KEY `nama_kecamatan` (`nama_kecamatan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kecamatan`
--

LOCK TABLES `kecamatan` WRITE;
/*!40000 ALTER TABLE `kecamatan` DISABLE KEYS */;
INSERT INTO `kecamatan` VALUES (1,'Banjarbaru Selatan',NULL,1,'2026-10-08 02:03:08','2026-10-08 02:03:08'),(2,'Banjarbaru Utara',NULL,1,'2026-10-08 02:03:08','2026-10-08 02:03:08'),(3,'Cempaka',NULL,1,'2026-10-08 02:03:08','2026-10-08 02:03:08'),(4,'Landasan Ulin',NULL,1,'2026-10-08 02:03:08','2026-10-08 02:03:08'),(5,'Liang Anggang',NULL,1,'2026-10-08 02:03:08','2026-10-08 02:03:08');
/*!40000 ALTER TABLE `kecamatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kelurahan`
--

DROP TABLE IF EXISTS `kelurahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kelurahan` (
  `id_kelurahan` int unsigned NOT NULL AUTO_INCREMENT,
  `id_kecamatan` int unsigned NOT NULL,
  `nama_kelurahan` varchar(100) NOT NULL,
  `deskripsi` text,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kelurahan`),
  UNIQUE KEY `uk_kelurahan_kecamatan` (`id_kecamatan`,`nama_kelurahan`),
  CONSTRAINT `fk_kelurahan_kecamatan` FOREIGN KEY (`id_kecamatan`) REFERENCES `kecamatan` (`id_kecamatan`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kelurahan`
--

LOCK TABLES `kelurahan` WRITE;
/*!40000 ALTER TABLE `kelurahan` DISABLE KEYS */;
INSERT INTO `kelurahan` VALUES (1,1,'Guntung Paikat',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(2,1,'Kemuning',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(3,1,'Loktabat Selatan',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(4,1,'Sungai Besar',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(5,2,'Komet',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(6,2,'Loktabat Utara',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(7,2,'Mentaos',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(8,2,'Sungai Ulin',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(9,3,'Bangkal',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(10,3,'Cempaka',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(11,3,'Palam',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(12,3,'Sungai Tiung',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(13,4,'Guntung Manggis',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(14,4,'Guntung Payung',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(15,4,'Landasan Ulin Timur',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(16,4,'Syamsudin Noor',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(17,5,'Landasan Ulin Barat',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(18,5,'Landasan Ulin Selatan',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(19,5,'Landasan Ulin Tengah',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48'),(20,5,'Landasan Ulin Utara',NULL,1,'2026-10-08 02:03:48','2026-10-08 02:03:48');
/*!40000 ALTER TABLE `kelurahan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_aktivitas`
--

DROP TABLE IF EXISTS `log_aktivitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_aktivitas` (
  `id_log` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int unsigned DEFAULT NULL,
  `aktivitas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modul` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_referensi` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_log_user` (`id_user`),
  KEY `idx_log_modul` (`modul`),
  KEY `idx_log_created_at` (`created_at`),
  CONSTRAINT `fk_log_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_aktivitas`
--

LOCK TABLES `log_aktivitas` WRITE;
/*!40000 ALTER TABLE `log_aktivitas` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_aktivitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `potensi_investasi`
--

DROP TABLE IF EXISTS `potensi_investasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `potensi_investasi` (
  `id_potensi` int unsigned NOT NULL AUTO_INCREMENT,
  `kode_potensi` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kecamatan` int unsigned NOT NULL,
  `id_kelurahan` int unsigned DEFAULT NULL,
  `id_sektor` int unsigned NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `kondisi_eksisting` text COLLATE utf8mb4_unicode_ci,
  `peluang_investasi` text COLLATE utf8mb4_unicode_ci,
  `kendala` text COLLATE utf8mb4_unicode_ci,
  `rekomendasi` text COLLATE utf8mb4_unicode_ci,
  `sumber_data` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun_data` year DEFAULT NULL,
  `status_publikasi` tinyint(1) NOT NULL DEFAULT '0',
  `status_data` enum('aktif','arsip') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_by` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_potensi`),
  UNIQUE KEY `kode_potensi` (`kode_potensi`),
  KEY `fk_potensi_user` (`created_by`),
  KEY `idx_potensi_kecamatan` (`id_kecamatan`),
  KEY `idx_potensi_kelurahan` (`id_kelurahan`),
  KEY `idx_potensi_sektor` (`id_sektor`),
  KEY `idx_potensi_publikasi` (`status_publikasi`),
  CONSTRAINT `fk_potensi_kecamatan` FOREIGN KEY (`id_kecamatan`) REFERENCES `kecamatan` (`id_kecamatan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_potensi_kelurahan` FOREIGN KEY (`id_kelurahan`) REFERENCES `kelurahan` (`id_kelurahan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_potensi_sektor` FOREIGN KEY (`id_sektor`) REFERENCES `sektor` (`id_sektor`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_potensi_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `potensi_investasi`
--

LOCK TABLES `potensi_investasi` WRITE;
/*!40000 ALTER TABLE `potensi_investasi` DISABLE KEYS */;
INSERT INTO `potensi_investasi` VALUES (4,'POT-1792326313',5,18,4,'Pengembangan Kawasan Terpadu Pergudangan Modern & Pusat Logistik Penyangga IKN dan Kalimantan Selatan','Lahan siap bangun berstatus Kawasan Peruntukan Industri (KPI) seluas 15 hektar, terkoneksi langsung dengan Jalan Lingkar Utara/Selatan','Pembangunan cold storage, pusat distribusi barang konsumsi, pabrik kemasan, dan depo peti kemas.','Jaringan pipa PDAM belum sepenuhnya menjangkau titik dalam kawasan industri; elevasi beberapa titik lahan perlu pengurukan','Percepatan integrasi drainase kawasan oleh Dinas PUPR dan penambahan daya kapasitas trafo listrik dari PLN','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:25:13','2026-10-18 12:25:13'),(5,'POT-1792326383',4,16,8,'Pembangunan Hotel Transit Bintang 3/4 & Pusat Layanan MICE dekat Bandara Internasional Syamsudin Noor','Lahan strategis radius 2 km dari terminal bandara, akses jalan nasional (Jl. A. Yani Km 23–25), utilitas listrik dan jaringan internet sudah memadai','Hotel bisnis, ruang konvensi/ruang rapat skala sedang, restoran 24 jam, dan galeri pusat oleh-oleh khas Banjarbaru.','Tingginya harga pasaran lahan di sepanjang koridor utama jalan nasional.','Pemberian kemudahan insentif perizinan (PBG & SLF) melalui pelayanan terpadu DPMPTSP bagi investor perhotelan','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:26:23','2026-10-18 12:26:23'),(6,'POT-1792326466',3,10,6,'Pengembangan Destinasi Ekowisata Geopark Pendulangan Intan & Museum Budaya Cempaka','Lokasi penambangan intan tradisional yang masih aktif dan berpotensi menjadi cagar budaya; akses jalan utama beraspal mulus','Pembangunan fasilitas glamping resort, tempat pembuatan cenderamata intan/batu permata, restoran panorama perbukitan, serta area outbound','Perlunya penataan area kerja penambang tradisional agar tidak tumpang tindih dengan zona kenyamanan wisatawan','Pembentukan Kelompok Sadar Wisata (Pokdarwis) serta kemitraan UMKM lokal dengan investor pengembang pariwisata','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:27:46','2026-10-18 12:27:46'),(7,'POT-1792326574',1,2,5,'Pengembangan Wisata Kuliner Bantaran Sungai Kemuning & Pusat Industri Kreatif Muda','Bantaran sungai telah ditata rapi (Siring Kemuning), penerangan jalan umum lengkap, menjadi pusat keramaian warga di sore hingga malam hari','Kafe/restoran tematik kontainer, creative hub, dermaga perahu wisata air, dan sarana pertunjukan seni terbuka','eterbatasan area kantong parkir untuk kendaraan roda empat pada jam-jam sibuk (peak hours)','Pengadaan lahan parkir terpadu oleh Dishub/Pemko serta pengaturan lalu lintas searah di koridor siring','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:29:34','2026-10-18 12:29:34'),(8,'POT-1792326628',2,7,1,'Pengembangan Agrowisata Buah Lokal & Edufarm Budidaya Ikan Air Tawar Mentaos','Terdapat Balai Benih Ikan (BBI) Mentaos, kebun buah produktif, dekat dengan perkantoran Pemprov Kalsel dan pusat kota','Restoran keluarga berkonsep petik buah & pancing ikan, wisata edukasi pertanian bagi pelajar, dan pengolahan hasil perikanan','Debit air kolam berfluktuasi saat memasuki musim kemarau panjang','Pembangunan sumur bor dalam (deep well) dan instalasi pemutaran air terpadu (recirculating aquaculture system)','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:30:28','2026-10-18 12:30:28'),(9,'POT-1792326851',5,NULL,4,'Pengembangan kawasan industri, perkebunan, perdagangan barang dan jasa, serta jasa penyewaan alat berat','Wilayah strategis lintasan angkutan berat dan industri','Perusahaan jasa alat berat, pergudangan industri, dan penyedia barang/jasa pendukung','Permasalahan status dan ketersediaan lahan/tanah, serta strategi promosi yang belum optimal','Inventarisasi status lahan dan penyusunan strategi promosi investasi terpadu','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:34:11','2026-10-18 12:44:44'),(10,'POT-1792326971',5,18,6,'Pengembangan potensi wisata kawasan embung dan wisata berbasis sungai','Terdapat kawasan embung dan aliran sungai yang berpotensi menjadi objek wisata alami','Wahana rekreasi air, dermaga perahu wisata, dan wahana swafoto','Pengelolaan embung belum optimal, wisata sungai sangat bergantung tren masyarakat, serta kurang inovasi berkelanjutan','Penguatan tata kelola kawasan embung dan diversifikasi atraksi wisata sungai','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:36:11','2026-10-18 12:36:11'),(11,'POT-1792327058',5,20,6,'Pengembangan Embung Sidodadi untuk wisata pemancingan, penyelenggaraan event lomba memancing tahunan, dan kawasan kuliner','Embung Sidodadi sudah ada dan sering dikunjungi warga sekitar','Kolam pancing komersial, pujasera/lapak kuliner tepi embung, dan sponsor event berkala','Sarana penunjang lapak pedagang dan area pemancingan belum tertata rapi','Penataan kawasan lapak UMKM kuliner dan pembuatan kalender event lomba memancing tahunan','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:37:38','2026-10-18 12:37:38'),(12,'POT-1792327174',5,20,1,'Pengembangan agrowisata petik melon di wilayah perbukitan Abadi Makmur serta integrasi kelompok tani peternakan sapi','Kawasan perkebunan melon dan kelompok peternak sapi aktif di wilayah perbukitan','Wisata edukasi agrowisata petik buah, pengolahan hasil ternak, dan pupuk organik','Akses jalan menuju lokasi perkebunan serta integrasi pemasaran produk peternakan','Peningkatan prasarana jalan usaha tani dan pendampingan pemasaran wisata agro','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:39:34','2026-10-18 12:39:34'),(13,'POT-1792327240',5,20,8,'Pengembangan sektor perhotelan, penginapan, dan usaha jasa penunjang di sepanjang Jalan Trikora (dekat kantor Dinas Lingkungan Hidup)','Jalur arteri sekunder yang ramai dilintasi kendaraan antar-kota','Hotel bujet, restoran/rumah makan, dan tempat servis kendaraan','Ketersediaan dan kejelasan sertifikasi lahan tepi jalan utama','Kemudahan insentif perizinan bagi investor jasa dan perhotelan di sepanjang koridor Jalan Trikora','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:40:40','2026-10-18 12:40:40'),(14,'POT-1792327326',5,17,5,'Pengembangan investasi kafe/tempat nongkrong, sarana olahraga modern (seperti lapangan padel), dan investasi berbasis kelompok warga/RT','Pertumbuhan minat masyarakat pada kuliner kumpul dan tren olahraga baru','Penyewaan lapangan olahraga padel, tempat kumpul/kafe modern, dan usaha kelompok warga','Batasan jam operasional kafe dan potensi gesekan dengan lingkungan permukiman','Penyusunan regulasi jam operasional kafe yang kondusif dengan lingkungan setempat','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:42:06','2026-10-18 12:42:06'),(15,'POT-1792327409',5,17,14,'Revitalisasi kawasan Wisata Kampung Pelangi melalui penambahan fasilitas kebugaran/fitness serta bantuan rombong usaha UMKM','Objek wisata Kampung Pelangi saat ini cenderung mengalami penurunan daya tarik (stagnan)','Penyediaan sarana kebugaran outdoor, stan UMKM kuliner/sovenir, dan wahana mainan anak','Konsep objek wisata yang perlu pembaruan dan sarana pendukung yang aus','Bantuan rombong usaha UMKM serta pengadaan fasilitas fitness outdoor untuk menyegarkan daya tarik','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:43:29','2026-10-18 12:43:29'),(16,'POT-1792327470',1,1,7,'Pembangunan kawasan perumahan komersial (RT 24) dan perkantoran swasta/BUMN','Lingkungan perkotaan yang kondusif dan dekat pusat perkantoran/pemerintahan','Perumahan komersial menengah ke atas dan gedung perkantoran non-pabrik','Sektor industri/produksi tidak cocok dikembangkan karena dampak limbah dan lokasi berdekatan dengan permukiman','Penetapan zonasi khusus hunian komersial dan perkantoran ramah lingkungan','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:44:30','2026-10-18 12:44:30'),(17,'POT-1792327625',1,2,5,'Usaha rumah kos, rumah sewa, dan jasa penunjang kebutuhan mahasiswa (laundry, percetakan, kuliner)','Berdekatan dengan Kampus Universitas Lambung Mangkurat (ULM) dan Universitas Borneo Lestari','Rumah kos eksklusif/modern, pusat fotokopi/laundry, dan tempat makan mahasiswa',': Kawasan sudah padat permukiman sehingga ruang untuk pembangunan baru terbatas','Optimalisasi renovasi bangunan eksisting menjadi rumah kos atau pusat layanan mahasiswa','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:47:05','2026-10-18 12:47:05'),(18,'POT-1792327778',2,NULL,6,'Pengembangan kawasan perumahan perkotaan, usaha kuliner, kafe, serta sarana/prasarana olahraga','Berada di pusat kota dengan tingkat mobilitas dan konsumsi warga yang tinggi','Pusat kuliner malam, sarana mini soccer/futsal/badminton, dan kafe tematik','Tingginya harga sewa/beli tanah di area pusat kota','Penataan zonasi usaha kuliner serta fasilitas kemudahan berusaha','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:49:38','2026-10-18 12:49:38'),(19,'POT-1792327891',2,7,6,'Pengembangan pusat usaha kuliner dan kafe di sepanjang Jalan Pangeran Suriansyah','Koridor jalan favorit masyarakat untuk tempat kumpul dan santai','Restoran keluarga, kafe estetik, dan kedai makanan cepat saji','Ketersediaan lahan parkir yang terbatas saat jam ramai (peak hours)','Pengaturan area parkir khusus dan penataan estetika tempat usaha','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:51:31','2026-10-18 12:51:31'),(20,'POT-1792327945',2,7,1,'Pemanfaatan lahan bekas rencana Sport Center milik Pemprov Kalsel untuk kegiatan pertanian melalui sistem pinjam pakai','Lahan belum tergarap milik Pemerintah Provinsi Kalsel','Kebun hortikultura komersial, tanaman hidroponik, dan tempat pelatihan tani','Prosedur administrasi dan kepastian jangka waktu pinjam pakai lahan pemprov','Fasilitasi kerja sama MoU pinjam pakai lahan antara kelompok tani/investor dengan Pemprov Kalsel','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:52:25','2026-10-18 12:52:25'),(21,'POT-1792328057',3,9,7,'Pengembangan kawasan perumahan (subsidi maupun komersial) yang tumbuh pesat di Kecamatan Cempaka','Wilayah perluasan pembangunan kota dengan ketersediaan lahan luas','Perumahan subsidi (FLPP), perumahan komersial, dan toko/ruko sembako komplek','Kelengkapan jaringan infrastruktur publik (air bersih dan penerangan jalan) di beberapa lokasi permukiman baru','Peningkatan penyediaan Prasarana, Sarana, dan Utilitas Umum (PSU) oleh pengembang dan pemerintah','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-18 12:54:17','2026-10-18 12:54:17'),(22,'POT-1792417522',1,1,1,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2026,0,'aktif',4,'2026-10-19 13:45:22','2026-10-19 13:45:22'),(23,'POT-1792417537',1,2,2,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2026,0,'aktif',4,'2026-10-19 13:45:37','2026-10-19 13:45:37'),(24,'POT-1792417570',1,3,3,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2026,0,'aktif',4,'2026-10-19 13:46:10','2026-10-19 13:46:10'),(25,'POT-1792417591',1,4,4,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2026,0,'aktif',4,'2026-10-19 13:46:31','2026-10-19 13:46:39'),(26,'POT-1792417628',2,5,5,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2026,0,'aktif',4,'2026-10-19 13:47:08','2026-10-19 13:47:08'),(27,'POT-1792417642',2,6,6,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2026,0,'aktif',4,'2026-10-19 13:47:22','2026-10-19 13:47:22'),(28,'POT-1792417670',2,7,7,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2025,0,'aktif',4,'2026-10-19 13:47:50','2026-10-19 13:47:50'),(29,'POT-1792417700',2,8,8,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2025,0,'aktif',4,'2026-10-19 13:48:20','2026-10-19 13:48:20'),(30,'POT-1792417733',3,9,9,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2025,0,'aktif',4,'2026-10-19 13:48:53','2026-10-19 13:48:53'),(31,'POT-1792417757',3,10,14,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2026,0,'aktif',4,'2026-10-19 13:49:17','2026-10-19 13:49:17'),(32,'POT-1792417772',3,11,17,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2026,0,'aktif',4,'2026-10-19 13:49:32','2026-10-19 13:49:32'),(33,'POT-1792417789',3,12,18,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2025,0,'aktif',4,'2026-10-19 13:49:49','2026-10-19 13:49:49'),(34,'POT-1792417821',4,13,19,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2025,0,'aktif',4,'2026-10-19 13:50:21','2026-10-19 13:50:21'),(35,'POT-1792417837',4,14,20,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2025,0,'aktif',4,'2026-10-19 13:50:37','2026-10-19 13:50:37'),(36,'POT-1792417966',4,16,21,'Wilayah ini memiliki beberapa potensi investasi yang dapat dikembangkan, di antaranya pada sektor aerocity dan logistik. Kawasan bandara yang bernilai strategis dinilai berpotensi dikembangkan sebagai pusat transportasi, pergudangan, dan kawasan komersial yang mendukung pertumbuhan ekonomi daerah.','Dummy','Dummy','Dummy','Dummy','Notulen Rapat Koordinasi 2026',2026,0,'aktif',4,'2026-10-19 13:52:46','2026-10-19 13:52:46'),(37,'POT-1792418013',4,15,22,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2026,0,'aktif',4,'2026-10-19 13:53:33','2026-10-19 13:53:33'),(38,'POT-1792418028',5,17,1,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2026,0,'aktif',4,'2026-10-19 13:53:48','2026-10-19 13:53:48'),(39,'POT-1792418045',5,18,2,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2025,0,'aktif',4,'2026-10-19 13:54:05','2026-10-19 13:54:05'),(40,'POT-1792418059',5,19,3,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2025,0,'aktif',4,'2026-10-19 13:54:19','2026-10-19 13:54:19'),(41,'POT-1792418078',5,20,4,'Dummy','Dummy','Dummy','Dummy','Dummy','Dummy',2025,0,'aktif',4,'2026-10-19 13:54:38','2026-10-19 13:54:38');
/*!40000 ALTER TABLE `potensi_investasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sektor`
--

DROP TABLE IF EXISTS `sektor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sektor` (
  `id_sektor` int unsigned NOT NULL AUTO_INCREMENT,
  `kode_sektor` varchar(10) NOT NULL,
  `nama_sektor` varchar(150) NOT NULL,
  `deskripsi` text,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sektor`),
  UNIQUE KEY `kode_sektor` (`kode_sektor`),
  UNIQUE KEY `nama_sektor` (`nama_sektor`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sektor`
--

LOCK TABLES `sektor` WRITE;
/*!40000 ALTER TABLE `sektor` DISABLE KEYS */;
INSERT INTO `sektor` VALUES (1,'S01','Pertanian','',1,'2026-10-08 02:04:18','2026-10-19 13:32:08'),(2,'S02','Peternakan',NULL,1,'2026-10-08 02:04:18','2026-10-08 02:04:18'),(3,'S03','Perikanan',NULL,1,'2026-10-08 02:04:18','2026-10-08 02:04:18'),(4,'S04','Industri','',1,'2026-10-08 02:04:18','2026-10-19 13:36:21'),(5,'S05','Perdagangan','-',1,'2026-10-08 02:04:18','2026-10-19 13:44:15'),(6,'S06','Pariwisata','',1,'2026-10-08 02:04:18','2026-10-19 13:36:43'),(7,'S07','Perumahan','',1,'2026-10-08 02:04:18','2026-10-19 13:35:45'),(8,'S08','Perhotelan','',1,'2026-10-08 02:04:18','2026-10-19 13:35:26'),(9,'S09','Pergudangan','-',1,'2026-10-08 02:04:18','2026-10-19 13:39:06'),(14,'S10','Kerajinan dan Ekonomi Kreatif','-',1,'2026-10-14 12:53:04','2026-10-14 12:53:04'),(17,'S11','Perkebunan','-',1,'2026-10-19 13:32:26','2026-10-19 13:32:26'),(18,'S12','Properti','-',1,'2026-10-19 13:36:02','2026-10-19 13:36:02'),(19,'S13','Kuliner','-',1,'2026-10-19 13:37:12','2026-10-19 13:37:12'),(20,'S14','Jasa','-',1,'2026-10-19 13:38:19','2026-10-19 13:38:19'),(21,'S15','Aerocity','-',1,'2026-10-19 13:40:19','2026-10-19 13:40:19'),(22,'S16','Logistik','-',1,'2026-10-19 13:40:52','2026-10-19 13:40:52');
/*!40000 ALTER TABLE `sektor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pegawai','kadis','kabid') NOT NULL DEFAULT 'pegawai',
  `no_hp` varchar(20) DEFAULT NULL,
  `instansi` varchar(150) DEFAULT NULL,
  `status` enum('pending','aktif','nonaktif') NOT NULL DEFAULT 'pending',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'Habel','Habel','habelkhafi@gmail.com','$2y$10$VFLly3jRG1q1SAK4if8TvenGwLxOsQUk8j2uVLHZ5E0d77kZdCGg.','admin','082255668778','Uniska (Magang)','aktif','2026-10-24 10:28:35','2026-10-12 12:23:57','2026-10-24 02:28:35'),(4,'putih suci','putih','putih@gmail.com','$2y$10$UAuzS8OqmynijhSfNyLKo.9jjz282rjMVKqledW76LyXe7/TfosFq','admin',NULL,NULL,'aktif','2026-10-23 21:07:26','2026-10-18 12:18:41','2026-10-23 13:07:26'),(5,'Kanafi, S.IP., M.M..','Kanafi','kanafi@gmail.com','$2y$10$yb8q8YlZyGP2tM/Q.Ck/nuHFDM4HVeEW2h/gdvci1gxXXOdEHGqtS','kadis',NULL,'Kepala Dinas','aktif','2026-10-23 21:00:54','2026-10-23 05:47:01','2026-10-23 13:02:34'),(6,'Iqbaliansyah','iqbal','Iqbal@gmail.com','$2y$10$osgHkNd4arKJSTaZQSzoeu9MIoPy0o0b.MPGWs4crrlbL5FafGcr2','kabid','081234567890',NULL,'aktif','2026-10-24 10:21:33','2026-10-23 05:56:15','2026-10-24 02:21:33'),(7,'Daffa','Keish','daffakeish@gmail.com','$2y$10$SPsQeC9hqYYVjmhwykkVFOd6ykGOvyPehp6qRb.8NVnydabhQdi4q','pegawai',NULL,'Umum dan Kepegawaian','aktif','2026-10-24 10:12:34','2026-10-24 02:12:21','2026-10-24 02:12:34');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-24 10:40:22
