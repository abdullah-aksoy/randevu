# Araç Bakım Randevu Sistemi

Bu proje, araç bakım hizmetleri için online randevu alınmasını ve yönetilmesini sağlayan bir web uygulamasıdır.

## Özellikler

- Kullanıcılar kolayca randevu oluşturabilir.
- Farklı hizmet tipleri için (ör. cam filmi, araç kaplama, elektronik ürünler) randevu alınabilir 🚗.
- Takvim üzerinden uygun gün ve saat seçimi, dolu tarihler/pazar günleri devre dışı bırakılır 📅.
- Google reCAPTCHA ile spam koruması.
- Yönetici panelinden (admin) randevuları listeleme, durum güncelleme ve Excel’e aktarım 🌟.
- Modern ve mobil uyumlu arayüz (Bootstrap 5, DataTables, SweetAlert2).

## Teknolojiler

- **Backend:** PHP
- **Frontend:** HTML5, CSS3 (Bootstrap), JavaScript (jQuery, jQuery UI)
- **Kütüphaneler:** SweetAlert2, DataTables, Google reCAPTCHA, SheetJS

## Kurulum

1. Proje dosyalarını kopyalayın ya da indirin.
2. Gerekirse `config.php` ve hizmet tanımlarını düzenleyin.
3. Sunucuya yükleyin, gerekli PHP uzantılarını kurun.
4. Google reCAPTCHA anahtarlarınızı `submit.php`'ye ekleyin.
5. Veritabanı bağlantısı ve tabloları oluşturun (`setup.php` ile otomatik kurulum yapılabilir).
6. Admin paneline `/admin` klasöründen erişin.

## Admin Paneli Giriş Bilgileri 🔑

Kurulum sonrası varsayılan yönetici hesabı:

- **Kullanıcı Adı:** admin
- **Şifre:** admin123

> Güvenlik için ilk girişten sonra şifreyi değiştirmeniz önerilir.

## Kullanım

- Ana sayfadan hizmet ve tarih seçerek randevu alabilirsiniz.
- Admin panelinden tüm randevuları görüntüleyebilir ve dışa aktarabilirsiniz.

## Lisans

Bu proje açık kaynaklıdır. Lisans belirtilmemiştir, kullanım ve paylaşım için sahibiyle iletişime geçiniz.

---
