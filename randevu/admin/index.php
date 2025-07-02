<?php
session_start();
require_once '../config.php';

// Kullanıcı zaten giriş yapmışsa randevular sayfasına yönlendir
if (isset($_SESSION['admin_id'])) {
    header('Location: randevular.php');
    exit();
}

$hata_mesaji = '';

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_adi = trim($_POST['kullanici_adi']);
    $sifre = trim($_POST['sifre']);
    
    // Kullanıcı adı ve şifre kontrolü
    if (empty($kullanici_adi) || empty($sifre)) {
        $hata_mesaji = 'Lütfen tüm alanları doldurun.';
    } else {
        // Kullanıcı bilgilerini veritabanından al
        $sql = "SELECT * FROM admin_kullanicilar WHERE kullanici_adi = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$kullanici_adi]);
        $kullanici = $stmt->fetch();
        
        if ($kullanici) {
            // Şifre kontrolü
            if (password_verify($sifre, $kullanici['sifre'])) {
                // Oturum bilgilerini kaydet
                $_SESSION['admin_id'] = $kullanici['id'];
                $_SESSION['admin_kullanici_adi'] = $kullanici['kullanici_adi'];
                $_SESSION['admin_ad_soyad'] = $kullanici['ad_soyad'];
                
                // Son giriş zamanını güncelle
                $sql = "UPDATE admin_kullanicilar SET son_giris = NOW() WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$kullanici['id']]);
                
                // Randevular sayfasına yönlendir
                header('Location: randevular.php');
                exit();
            } else {
                $hata_mesaji = 'Geçersiz kullanıcı adı veya şifre.';
            }
        } else {
            $hata_mesaji = 'Geçersiz kullanıcı adı veya şifre.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - Araç Bakım Randevu Sistemi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="text-center mb-4">
                <i class="fas fa-car-alt fa-3x text-primary mb-3"></i>
                <h1>Admin Girişi</h1>
                <p class="text-muted">Araç Bakım Randevu Sistemi Yönetim Paneli</p>
            </div>
            
            <?php if (!empty($hata_mesaji)): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $hata_mesaji; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="kullanici_adi">
                        <i class="fas fa-user me-2"></i>Kullanıcı Adı
                    </label>
                    <input type="text" id="kullanici_adi" name="kullanici_adi" required 
                           class="form-control" placeholder="Kullanıcı adınızı girin">
                </div>
                
                <div class="form-group">
                    <label for="sifre">
                        <i class="fas fa-lock me-2"></i>Şifre
                    </label>
                    <input type="password" id="sifre" name="sifre" required 
                           class="form-control" placeholder="Şifrenizi girin">
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                </button>
            </form>
            
            <div class="form-footer">
                <a href="../index.php">
                    <i class="fas fa-arrow-left me-1"></i>
                    Ana Sayfaya Dön
                </a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS (Popper.js ve jQuery dahil) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 