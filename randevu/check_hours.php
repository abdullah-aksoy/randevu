<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['date'])) {
    $randevuTarihi = trim($_GET['date']);
    
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
        jsonResponse(false, 'Geçersiz tarih formatı.');
    }
    
    // Tarih geçerlilik kontrolü
    $bugun = date('Y-m-d');
    if ($randevuTarihi < $bugun) {
        jsonResponse(false, 'Geçmiş bir tarih için randevu alamazsınız.');
    }
    
    // Diğer hizmet tipi randevuları kontrolü
    $sql = "SELECT COUNT(*) FROM randevular 
            WHERE randevu_tarihi = ? AND hizmet_tipi != 'cam_filmi' AND randevu_durumu != 'iptal'";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$randevuTarihi]);
    $sayi = $stmt->fetchColumn();
    
    if ($sayi > 0) {
        // Eğer başka hizmet tipi varsa cam filmi randevusu alınamaz
        jsonResponse(true, 'Bu tarihte başka hizmet tipi randevusu var.', ['unavailableHours' => $hizmet_tipleri['cam_filmi']['saatler']]);
    }
    
    // Cam filmi randevuları için dolu saatleri kontrol et
    $sql = "SELECT randevu_saati FROM randevular 
            WHERE randevu_tarihi = ? AND hizmet_tipi = 'cam_filmi' AND randevu_durumu != 'iptal'";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$randevuTarihi]);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $unavailableHours = [];
    
    foreach ($rows as $row) {
        $unavailableHours[] = $row;
    }
    
    // Günlük cam filmi randevu sayısı kontrolü
    $sql = "SELECT COUNT(*) FROM randevular 
            WHERE randevu_tarihi = ? AND hizmet_tipi = 'cam_filmi' AND randevu_durumu != 'iptal'";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$randevuTarihi]);
    $sayi = $stmt->fetchColumn();
    
    // Eğer maksimum sayıya ulaşıldıysa tüm saatler dolu
    if ($sayi >= $hizmet_tipleri['cam_filmi']['max_gunluk']) {
        jsonResponse(true, 'Bu tarih için maksimum cam filmi randevu sayısına ulaşıldı.', ['unavailableHours' => $hizmet_tipleri['cam_filmi']['saatler']]);
    }
    
    jsonResponse(true, 'Uygun olmayan saatler başarıyla getirildi.', ['unavailableHours' => $unavailableHours]);
} else {
    jsonResponse(false, 'Geçersiz istek.');
}
?> 