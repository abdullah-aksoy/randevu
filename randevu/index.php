<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Araç Bakım Randevu Sistemi</title>
    <link rel="stylesheet" href="css/style.css?v=<?=time()?>">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- jQuery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <!-- jQuery UI DatePicker Türkçe -->
    <script src="https://raw.githubusercontent.com/jquery/jquery-ui/main/ui/i18n/datepicker-tr.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Randevu Oluştur</h1>
            <form id="randevuForm" action="submit.php" method="POST">
                <div class="form-group">
                    <label for="adSoyad">Ad Soyad</label>
                    <input type="text" id="adSoyad" name="adSoyad" required>
                </div>
                
                <div class="form-group">
                    <label for="telefon">Telefon Numarası</label>
                    <input type="tel" id="telefon" name="telefon" required>
                </div>
                
                <div class="form-group">
                    <label for="plaka">Araç Plakası</label>
                    <input type="text" id="plaka" name="plaka" required placeholder="34ABC123">
                </div>
                
                <div class="form-group">
                    <label for="hizmetTipi">Hizmet Tipi</label>
                    <select id="hizmetTipi" name="hizmetTipi" required>
                        <option value="">Seçiniz</option>
                        <option value="on_uc_para">KOMPLE PPF KAPLAMA</option>
                        <option value="arac_kaplama">ÖN ÜÇ PPF KAPLAMA</option>
                        <option value="cam_filmi">PROFESYONEL CAM FİLMİ</option>
                        <option value="body_kit">BODYKİT</option>
                        <option value="elektronik_urunler">ELEKTRONİK ÜRÜNLER</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="randevuTarihi">Randevu Tarihi</label>
                    <input type="text" id="randevuTarihi" class="date-picker" name="randevuTarihi" required readonly placeholder="Tarih seçiniz">
                </div>
                
                <div class="form-group" id="saatSecimi" style="display: none;">
                    <label for="randevuSaati">Randevu Saati</label>
                    <select id="randevuSaati" name="randevuSaati">
                        <option value="">Seçiniz</option>
                        <option value="09:30-13:00">09:30 - 13:00</option>
                        <option value="13:00-16:30">13:00 - 16:30</option>
                        <option value="16:30-20:00">16:30 - 20:00</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="aciklama">Randevu Notu</label>
                    <textarea id="aciklama" name="aciklama" rows="4"></textarea>
                </div>
                
                <!-- <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="SITE_KEY_BURAYA"></div>
                </div> -->
                
                <button type="submit" class="btn">Randevu Oluştur</button>
            </form>
        </div>
    </div>
    
    <div class="iletisim-butonlari">
        <a href="tel:05396242604" class="iletisim-btn telefon">
            <i class="fas fa-phone"></i>
        </a>
        <a href="https://wa.me/905396242604" class="iletisim-btn whatsapp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
    
    <script src="js/script.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html> 