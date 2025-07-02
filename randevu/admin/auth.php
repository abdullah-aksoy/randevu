<?php
session_start();

// Kimlik doğrulama kontrolü
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

require_once '../config.php';

// Menü için aktif sayfayı belirle
$current_page = basename($_SERVER['PHP_SELF']);

// Çıkış işlemi
if (isset($_GET['logout'])) {
    // Oturumu temizle
    session_unset();
    session_destroy();
    
    // Giriş sayfasına yönlendir
    header('Location: index.php');
    exit();
}

/**
 * Randevu durumlarını Türkçe olarak döndürür ve HTML sınıfı ekler
 * @param string $durum Randevu durumu
 * @return string HTML formatlı durum metni
 */
function getDurumHtml($durum) {
    switch ($durum) {
        case 'beklemede':
            return '<span class="durum-beklemede">Beklemede</span>';
        case 'tamamlandi':
            return '<span class="durum-tamamlandi">Tamamlandı</span>';
        default:
            return '<span class="durum-beklemede">Beklemede</span>';
    }
}

/**
 * Hizmet tipi açıklamasını döndürür
 * @param string $hizmet_tipi Hizmet tipi kodu
 * @return string Hizmet tipi açıklaması
 */
function getHizmetAdi($hizmet_tipi, $hizmet_tipleri) {
    if (isset($hizmet_tipleri[$hizmet_tipi])) {
        return $hizmet_tipleri[$hizmet_tipi]['isim'];
    }
    return $hizmet_tipi;
}

/**
 * Tarih formatını değiştirir
 * @param string $tarih Y-m-d formatında tarih
 * @return string d.m.Y formatında tarih
 */
function formatTarih($tarih) {
    $date = new DateTime($tarih);
    return $date->format('d.m.Y');
}
?> 