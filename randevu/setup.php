<?php
// Veritabanı bağlantı bilgileri
$db_host = 'localhost';
$db_user = 'u549971977_randevu';
$db_pass = 'Z3@x9hwH';

try {
    // MySQL sunucusuna bağlan (veritabanı adı olmadan)
    $conn = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Veritabanını oluştur
    $sql = "CREATE DATABASE IF NOT EXISTS u549971977_randevu CHARACTER SET utf8 COLLATE utf8_general_ci";
    $conn->exec($sql);
    echo "Veritabanı başarıyla oluşturuldu.<br>";
    
    // Veritabanını seç
    $conn = new PDO("mysql:host=$db_host;dbname=u549971977_randevu;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tablonun varlığını kontrol et ve yoksa oluştur
    $sql = "CREATE TABLE IF NOT EXISTS randevular (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        ad_soyad VARCHAR(100) NOT NULL,
        telefon VARCHAR(20) NOT NULL,
        plaka VARCHAR(15) NOT NULL,
        hizmet_tipi ENUM('on_uc_para', 'arac_kaplama', 'cam_filmi', 'body_kit','elektronik_urunler') NOT NULL,
        randevu_tarihi DATE NOT NULL,
        randevu_saati VARCHAR(20) DEFAULT NULL,
        aciklama TEXT,
        olusturma_tarihi DATETIME NOT NULL,
        randevu_durumu ENUM('beklemede', 'onaylandi', 'tamamlandi', 'iptal') NOT NULL DEFAULT 'beklemede'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    $conn->exec($sql);
    echo "Tablo başarıyla oluşturuldu.<br>";
    
    // Örnek veriler ekle - önce verileri silelim (yeniden çalıştırma durumunda)
    $conn->exec("TRUNCATE TABLE randevular");
    
    // Örnek veriler
    $sql = "INSERT INTO randevular (ad_soyad, telefon, plaka, hizmet_tipi, randevu_tarihi, randevu_saati, aciklama, olusturma_tarihi, randevu_durumu)
    VALUES 
    ('Ahmet Yılmaz', '555-123-4567', '34ABC123', 'on_uc_para', DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, 'Ön uç para hizmeti', NOW(), 'beklemede'),
    ('Mehmet Demir', '555-987-6543', '06DEF456', 'arac_kaplama', DATE_ADD(CURDATE(), INTERVAL 10 DAY), NULL, 'Araç kaplama hizmeti', NOW(), 'onaylandi'),
    ('Ayşe Kaya', '555-456-7890', '35GHI789', 'cam_filmi', DATE_ADD(CURDATE(), INTERVAL 15 DAY), '09:30-13:00', 'Cam filmi hizmeti', NOW(), 'tamamlandi'),
    ('Fatma Şahin', '555-789-0123', '01JKL012', 'cam_filmi', DATE_ADD(CURDATE(), INTERVAL 15 DAY), '13:00-16:30', 'Cam filmi hizmeti', NOW(), 'beklemede'),
    ('Ali Öztürk', '555-321-6547', '16MNO345', 'body_kit', DATE_ADD(CURDATE(), INTERVAL 20 DAY), NULL, 'Body kit hizmeti', NOW(), 'iptal')";
    
    $conn->exec($sql);
    echo "Örnek veriler başarıyla eklendi.<br>";
    
    // Admin kullanıcı tablosu oluştur
    $sql = "CREATE TABLE IF NOT EXISTS admin_kullanicilar (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        kullanici_adi VARCHAR(50) NOT NULL UNIQUE,
        sifre VARCHAR(255) NOT NULL,
        ad_soyad VARCHAR(100) NOT NULL,
        son_giris DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    $conn->exec($sql);
    echo "Admin kullanıcı tablosu başarıyla oluşturuldu.<br>";
    
    // Varsayılan admin kullanıcısı ekle (şifre: admin123)
    $varsayilan_sifre = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Önce admin kullanıcısının var olup olmadığını kontrol et
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_kullanicilar WHERE kullanici_adi = ?");
    $stmt->execute(['admin']);
    $admin_var_mi = $stmt->fetchColumn();
    
    if (!$admin_var_mi) {
        $stmt = $conn->prepare("INSERT INTO admin_kullanicilar (kullanici_adi, sifre, ad_soyad) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $varsayilan_sifre, 'Admin Kullanıcı']);
        echo "Varsayılan admin kullanıcısı başarıyla eklendi.<br>";
    } else {
        echo "Admin kullanıcısı zaten mevcut.<br>";
    }
    
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage() . "<br>";
}

// Bağlantıyı kapat
$conn = null;

echo "<br>Kurulum tamamlandı!<br>";
echo "<a href='index.php'>Ana Sayfaya Git</a> | <a href='admin/'>Admin Paneline Git</a>";
?> 