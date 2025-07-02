-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1:3306
-- Üretim Zamanı: 16 Haz 2025, 19:01:47
-- Sunucu sürümü: 10.11.10-MariaDB-log
-- PHP Sürümü: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `u549971977_randevu`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin_kullanicilar`
--

CREATE TABLE `admin_kullanicilar` (
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `ad_soyad` varchar(100) NOT NULL,
  `son_giris` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Tablo döküm verisi `admin_kullanicilar`
--

INSERT INTO `admin_kullanicilar` (`id`, `kullanici_adi`, `sifre`, `ad_soyad`, `son_giris`) VALUES
(1, 'admin', '$2y$10$4rlM3wnwbEBOVp4XSq7NnOdAayxB7WddtPUx00P/z0rAvKvsnLcC6', 'Admin Kullanıcı', '2025-05-16 19:50:14');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `randevular`
--

CREATE TABLE `randevular` (
  `id` int(11) NOT NULL,
  `ad_soyad` varchar(100) NOT NULL,
  `telefon` varchar(20) NOT NULL,
  `plaka` varchar(15) NOT NULL,
  `hizmet_tipi` enum('on_uc_para','arac_kaplama','cam_filmi','body_kit','elektronik_urunler') NOT NULL,
  `randevu_tarihi` date NOT NULL,
  `randevu_saati` varchar(20) DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `olusturma_tarihi` datetime NOT NULL,
  `randevu_durumu` enum('beklemede','onaylandi','tamamlandi','iptal') NOT NULL DEFAULT 'beklemede'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Tablo döküm verisi `randevular`
--

INSERT INTO `randevular` (`id`, `ad_soyad`, `telefon`, `plaka`, `hizmet_tipi`, `randevu_tarihi`, `randevu_saati`, `aciklama`, `olusturma_tarihi`, `randevu_durumu`) VALUES
(12, 'fassa', '123214', '34abc34', 'on_uc_para', '2025-05-19', '', '', '2025-05-16 19:56:40', 'beklemede'),
(13, '14', 'qwrqwr', '34abc34', 'arac_kaplama', '2025-05-21', '', '124124', '2025-05-16 19:57:10', 'beklemede');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `admin_kullanicilar`
--
ALTER TABLE `admin_kullanicilar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kullanici_adi` (`kullanici_adi`);

--
-- Tablo için indeksler `randevular`
--
ALTER TABLE `randevular`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `admin_kullanicilar`
--
ALTER TABLE `admin_kullanicilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `randevular`
--
ALTER TABLE `randevular`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
