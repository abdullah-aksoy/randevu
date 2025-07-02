<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['service'])) {
    $hizmetTipi = trim($_GET['service']);
    
    // Hizmet tipi kontrolü
    if (!array_key_exists($hizmetTipi, $hizmet_tipleri)) {
        jsonResponse(false, 'Geçersiz hizmet tipi.');
    }
    
    $hizmetBilgileri = $hizmet_tipleri[$hizmetTipi];
    $gunSayisi = $hizmetBilgileri['gun_sayisi'];
    
    $unavailableDates = [];
    
    // Tüm randevuları al
    $sql = "SELECT r.*, 
            CASE 
                WHEN r.hizmet_tipi = 'arac_kaplama' THEN 2
                ELSE 1
            END as gun_sayisi
            FROM randevular r
            WHERE r.randevu_durumu != 'iptal'";
    
    $stmt = $conn->query($sql);
    $randevular = $stmt->fetchAll();
    
    if (count($randevular) > 0) {
        foreach ($randevular as $row) {
            $mevcutHizmetTipi = $row['hizmet_tipi'];
            $mevcutGunSayisi = $row['gun_sayisi'];
            $mevcutBaslangicTarih = $row['randevu_tarihi'];
            
            // Eğer cam filmi seçiliyse ve bu kayıt da cam filmi ise farklı işlem yapacağız
            if ($hizmetTipi === 'cam_filmi' && $mevcutHizmetTipi === 'cam_filmi') {
                // Her tarih için saatlerin doluluk durumunu kontrol et
                for ($i = 0; $i < $mevcutGunSayisi; $i++) {
                    $tarih = date('Y-m-d', strtotime($mevcutBaslangicTarih . ' + ' . $i . ' days'));
                    
                    // Bu tarihte kaç adet cam filmi randevusu var?
                    $sql2 = "SELECT COUNT(*) FROM randevular 
                            WHERE randevu_tarihi = ? AND hizmet_tipi = 'cam_filmi' AND randevu_durumu != 'iptal'";
                    
                    $stmt2 = $conn->prepare($sql2);
                    $stmt2->execute([$tarih]);
                    $sayi = $stmt2->fetchColumn();
                    
                    // Eğer max günlük sayıya ulaşılmışsa tarihi engelle
                    if ($sayi >= $hizmet_tipleri['cam_filmi']['max_gunluk']) {
                        $unavailableDates[] = $tarih;
                    }
                }
                
                // Diğer hizmet tiplerinin tarihlerini engelle (TÜM SÜRE BOYUNCA)
                $sql2 = "SELECT r.randevu_tarihi, 
                        CASE 
                            WHEN r.hizmet_tipi = 'arac_kaplama' THEN 2
                            ELSE 1
                        END as gun_sayisi
                        FROM randevular r
                        WHERE r.hizmet_tipi != 'cam_filmi' AND r.randevu_durumu != 'iptal'";
                
                $stmt2 = $conn->query($sql2);
                $nonCamFilmiRandevulari = $stmt2->fetchAll();
                
                foreach ($nonCamFilmiRandevulari as $randevu) {
                    $baslangicTarih = $randevu['randevu_tarihi'];
                    $sure = $randevu['gun_sayisi'];
                    
                    // Randevu süresi boyunca tüm günleri engelle
                    for ($i = 0; $i < $sure; $i++) {
                        $tarih = date('Y-m-d', strtotime($baslangicTarih . ' + ' . $i . ' days'));
                        $unavailableDates[] = $tarih;
                    }
                }
            } 
            // Cam filmi dışındaki hizmetler için
            else if ($hizmetTipi !== 'cam_filmi') {
                // Randevu süresi kadar günleri hesapla ve engelle
                for ($i = 0; $i < $mevcutGunSayisi; $i++) {
                    $tarih = date('Y-m-d', strtotime($mevcutBaslangicTarih . ' + ' . $i . ' days'));
                    $unavailableDates[] = $tarih;
                }
            }
        }
    }
    
    // Cam filmi dışındaki hizmetler için cam filmi randevularının olduğu günleri de engelle
    if ($hizmetTipi !== 'cam_filmi') {
        $sql3 = "SELECT DISTINCT randevu_tarihi FROM randevular 
                WHERE hizmet_tipi = 'cam_filmi' AND randevu_durumu != 'iptal'";
        
        $stmt3 = $conn->query($sql3);
        $camFilmiTarihleri = $stmt3->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($camFilmiTarihleri as $tarih) {
            $unavailableDates[] = $tarih;
        }
    }
    
    // Tekrar eden tarihleri kaldır ve diziyi indeksleri sıfırla
    $unavailableDates = array_values(array_unique($unavailableDates));
    
    jsonResponse(true, 'Uygun olmayan tarihler başarıyla getirildi.', ['unavailableDates' => $unavailableDates]);
} else {
    jsonResponse(false, 'Geçersiz istek.');
}
?> 