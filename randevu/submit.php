<?php
require_once 'config.php';

// POST verileri alınıyor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // reCAPTCHA doğrulama
    $recaptcha_secret = "SECRET_KEY_BURAYA";
    $recaptcha_response = $_POST['g-recaptcha-response'];
    
    // reCAPTCHA doğrulama isteği
    // $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$recaptcha_secret.'&response='.$recaptcha_response);
    // $response_data = json_decode($verify_response);
    
    // if (!$response_data->success) {
    //     jsonResponse(false, 'Lütfen robot olmadığınızı doğrulayın.');
    // }
    
    $adSoyad = trim($_POST['adSoyad']);
    $telefon = trim($_POST['telefon']);
    $plaka = trim($_POST['plaka']);
    $hizmetTipi = trim($_POST['hizmetTipi']);
    $randevuTarihi = trim($_POST['randevuTarihi']);
    $aciklama = trim($_POST['aciklama']);
    $randevuSaati = isset($_POST['randevuSaati']) ? trim($_POST['randevuSaati']) : null;
    
    // Geçerlilik kontrolleri
    if (empty($adSoyad) || empty($telefon) || empty($plaka) || empty($hizmetTipi) || empty($randevuTarihi)) {
        sweetAlertResponse(false, 'Lütfen tüm zorunlu alanları doldurun.');
    }
    
    // Plaka formatı kontrolü
    if (!preg_match('/^[0-9]{2}[A-Za-z]{1,3}[0-9]{2,4}$/', str_replace(' ', '', $plaka))) {
        sweetAlertResponse(false, 'Geçersiz plaka formatı.');
    }
    
    // Hizmet tipi kontrolü
    if (!array_key_exists($hizmetTipi, $hizmet_tipleri)) {
        sweetAlertResponse(false, 'Geçersiz hizmet tipi.');
    }
    
    // Saat kontrolü (Cam filmi için)
    if ($hizmetTipi === 'cam_filmi' && empty($randevuSaati)) {
        sweetAlertResponse(false, 'Cam Filmi hizmeti için randevu saati seçmelisiniz.');
    }
    
    // Tarih formatı kontrolü ve düzeltme
    $tarihFormatliMi = false;

    // GG.AA.YYYY formatına uygun mu kontrol et
    if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $randevuTarihi)) {
        // GG.AA.YYYY formatından YYYY-MM-DD formatına dönüştür
        $tarihParcalari = explode('.', $randevuTarihi);
        if (count($tarihParcalari) === 3) {
            $gun = $tarihParcalari[0];
            $ay = $tarihParcalari[1];
            $yil = $tarihParcalari[2];
            
            // Formatı değiştir
            $randevuTarihi = "$yil-$ay-$gun";
            $tarihFormatliMi = true;
        }
    } 
    // YYYY-MM-DD formatına uygun mu kontrol et
    elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $randevuTarihi)) {
        $tarihFormatliMi = true;
    }
    
    if (!$tarihFormatliMi) {
        sweetAlertResponse(false, 'Geçersiz tarih formatı.');
    }
    
    // Randevu tarihi bugünden önce olmamalı
    $bugun = date('Y-m-d');
    if ($randevuTarihi < $bugun) {
        sweetAlertResponse(false, 'Geçmiş bir tarih için randevu alamazsınız.');
    }
    
    // Hizmet bilgileri
    $hizmetBilgileri = $hizmet_tipleri[$hizmetTipi];
    $gunSayisi = $hizmetBilgileri['gun_sayisi'];
    
    // Randevu uygunluğu kontrolü
    if ($hizmetTipi === 'cam_filmi') {
        // Cam filmi için saat kontrolü
        $sql = "SELECT COUNT(*) as sayi FROM randevular 
                WHERE randevu_tarihi = ? AND randevu_saati = ? AND randevu_durumu != 'iptal'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$randevuTarihi, $randevuSaati]);
        $sayi = $stmt->fetchColumn();
        
        if ($sayi > 0) {
            sweetAlertResponse(false, 'Bu tarih ve saat için randevu dolu.');
        }
        
        // Aynı gün başka hizmet tipi var mı kontrolü
        $sql = "SELECT COUNT(*) as sayi FROM randevular 
                WHERE randevu_tarihi = ? AND hizmet_tipi != 'cam_filmi' AND randevu_durumu != 'iptal'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$randevuTarihi]);
        $sayi = $stmt->fetchColumn();
        
        if ($sayi > 0) {
            sweetAlertResponse(false, 'Bu tarihte başka hizmet tipi için randevu bulunmaktadır.');
        }
        
        // Günlük cam filmi randevu sayısı kontrolü
        $sql = "SELECT COUNT(*) as sayi FROM randevular 
                WHERE randevu_tarihi = ? AND hizmet_tipi = 'cam_filmi' AND randevu_durumu != 'iptal'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$randevuTarihi]);
        $sayi = $stmt->fetchColumn();
        
        if ($sayi >= $hizmetBilgileri['max_gunluk']) {
            sweetAlertResponse(false, 'Bu tarih için maksimum cam filmi randevu sayısına ulaşıldı.');
        }
    } else {
        // Diğer hizmet tipleri için tarih kontrolü
        
        // Seçilen tarih aralığı
        $baslangicTarihi = $randevuTarihi;
        $bitisTarihi = date('Y-m-d', strtotime($randevuTarihi . ' + ' . ($gunSayisi - 1) . ' days'));
        
        // Cam filmi randevusu var mı kontrolü
        $sql = "SELECT COUNT(*) as sayi FROM randevular 
                WHERE randevu_tarihi BETWEEN ? AND ? AND hizmet_tipi = 'cam_filmi' AND randevu_durumu != 'iptal'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$baslangicTarihi, $bitisTarihi]);
        $sayi = $stmt->fetchColumn();
        
        if ($sayi > 0) {
            sweetAlertResponse(false, 'Bu tarih aralığında cam filmi randevusu bulunmaktadır.');
        }
        
        // Diğer hizmet tipleri için randevu var mı kontrolü
        $sql = "SELECT r.*, 
                CASE 
                    WHEN r.hizmet_tipi = 'arac_kaplama' THEN 2
                    ELSE 1
                END as gun_sayisi
                FROM randevular r
                JOIN (
                    SELECT 'on_uc_para' as tip, 1 as gun_sayisi
                    UNION SELECT 'arac_kaplama' as tip, 2 as gun_sayisi
                    UNION SELECT 'body_kit' as tip, 1 as gun_sayisi
                    UNION SELECT 'elektronik_urunler' as tip, 1 as gun_sayisi
                ) ht ON r.hizmet_tipi = ht.tip
                WHERE r.randevu_durumu != 'iptal'";
        
        $stmt = $conn->query($sql);
        $randevular = $stmt->fetchAll();
        
        $tarihCakisiyor = false;
        foreach ($randevular as $row) {
            $randevuBaslangic = $row['randevu_tarihi'];
            $randevuBitis = date('Y-m-d', strtotime($row['randevu_tarihi'] . ' + ' . ($row['gun_sayisi'] - 1) . ' days'));
            
            // Tarih çakışması kontrolü
            if (
                ($baslangicTarihi <= $randevuBitis && $bitisTarihi >= $randevuBaslangic) ||
                ($randevuBaslangic <= $bitisTarihi && $randevuBitis >= $baslangicTarihi)
            ) {
                $tarihCakisiyor = true;
                break;
            }
        }
        
        if ($tarihCakisiyor) {
            sweetAlertResponse(false, 'Bu tarih aralığında başka bir randevu bulunmaktadır.');
        }
    }
    
    // Randevu kaydetme
    $sql = "INSERT INTO randevular (ad_soyad, telefon, plaka, hizmet_tipi, randevu_tarihi, randevu_saati, aciklama, olusturma_tarihi, randevu_durumu) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'beklemede')";
    
    $stmt = $conn->prepare($sql);
    
    try {
        $stmt->execute([$adSoyad, $telefon, $plaka, $hizmetTipi, $randevuTarihi, $randevuSaati, $aciklama]);
        sweetAlertResponse(true, 'Randevunuz başarıyla oluşturuldu. Onay bekleyen randevunuz en kısa sürede değerlendirilecektir.');
    } catch (PDOException $e) {
        sweetAlertResponse(false, 'Randevu oluşturulurken bir hata oluştu: ' . $e->getMessage());
    }
} else {
    sweetAlertResponse(false, 'Geçersiz istek.');
}

// SweetAlert ile cevap döndürmek için yardımcı fonksiyon
function sweetAlertResponse($success, $message, $data = []) {
    $icon = $success ? 'success' : 'error';
    $title = $success ? 'Başarılı!' : 'Hata!';
    
    echo '<!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Randevu Sonucu</title>
        <link rel="stylesheet" href="css/style.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "' . $icon . '",
                    title: "' . $title . '",
                    text: "' . $message . '",
                    confirmButtonText: "Tamam",
                    confirmButtonColor: "#4285f4"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "index.php";
                    }
                });
            });
        </script>
    </body>
    </html>';
    exit;
}
?> 