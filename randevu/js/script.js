$(document).ready(function() {
    const hizmetTipi = $('#hizmetTipi');
    const randevuTarihi = $('#randevuTarihi');
    const saatSecimi = $('#saatSecimi');
    const randevuSaati = $('#randevuSaati');
    const randevuForm = $('#randevuForm');
    
    // Devre dışı edilecek tarihler
    let disabledDates = [];
    
    // DatePicker'ı yapılandır
    randevuTarihi.datepicker({
        dateFormat: 'dd.mm.yy',
        minDate: 0, // Bugünden itibaren
        firstDay: 1, // Pazartesi başlangıç
        monthNames: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
        monthNamesShort: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
        dayNames: ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'],
        dayNamesShort: ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'],
        dayNamesMin: ['Pz', 'Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct'],
        beforeShowDay: disableDates,
        onSelect: function(dateText) {
            if (hizmetTipi.val() === 'cam_filmi') {
                checkAvailableHours();
            }
        },
        showButtonPanel: false,
        nextText: '',
        prevText: '',
        changeMonth: true,
        changeYear: true
    });
    
    // Özel stil ayarlamaları - yazılar yerine simge kullanmak için
    function customizeButtons() {
        // Önceki ve sonraki butonlarını özelleştir
        $('.ui-datepicker-prev span').text('').parent().attr('title', '');
        $('.ui-datepicker-next span').text('').parent().attr('title', '');
    }
    
    // DatePicker her açıldığında butonları özelleştir
    $(document).on('focus', '#randevuTarihi', function() {
        customizeButtons();
    });
    
    // DatePicker ay veya yıl değiştirildiğinde butonları yeniden özelleştir
    $(document).on('click', '.ui-datepicker-next, .ui-datepicker-prev', function() {
        setTimeout(customizeButtons, 0);
    });

    // Tarihleri devre dışı bırakma fonksiyonu
    function disableDates(date) {
        // Pazar günleri kontrolü (0 = Pazar)
        if (date.getDay() === 0) {
            return [false, "disabled-date", "Pazar günleri randevu verilmemektedir"];
        }
        
        // Veritabanından gelen tarihler kontrolü
        const dateString = $.datepicker.formatDate('yy-mm-dd', date);
        if ($.inArray(dateString, disabledDates) !== -1) {
            return [false, "disabled-date", "Bu tarih için randevu dolu"];
        }
        
        return [true, "", ""];
    }
    
    // Hizmet tipine göre saat seçimini göster/gizle
    hizmetTipi.on('change', function() {
        if ($(this).val() === 'cam_filmi') {
            saatSecimi.show();
            randevuSaati.prop('required', true);
        } else {
            saatSecimi.hide();
            randevuSaati.prop('required', false);
        }
        
        // Tarih seçimi sıfırla
        randevuTarihi.val('');
        randevuSaati.val('');
        
        // Eğer hizmet tipi seçiliyse uygun tarihleri kontrol et
        if ($(this).val()) {
            checkAvailableDates();
        }
    });
    
    // Form gönderildiğinde
    randevuForm.on('submit', function(event) {
        // Seçilen tarih yasaklı mı kontrol et
        const selectedDate = randevuTarihi.val();
        if (selectedDate) {
            const parts = selectedDate.split('.');
            const formattedDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
            if ($.inArray(formattedDate, disabledDates) !== -1) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Seçilen tarih randevu için uygun değil!',
                    confirmButtonColor: '#4285f4'
                });
                return false;
            }
        }
        
        // Form normal şekilde gönderilecek, AJAX kullanmıyoruz
    });
    
    // Uygun tarihleri kontrol et
    function checkAvailableDates() {
        const selectedService = hizmetTipi.val();
        
        // Sunucudan veri al
        $.ajax({
            url: `check_dates.php?service=${selectedService}`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log("Sunucudan gelen veri:", data);
                
                // Devre dışı tarihler listesini temizle
                disabledDates = [];
                
                if (data.unavailableDates && Array.isArray(data.unavailableDates)) {
                    // JSON'dan gelen tarihleri doğrudan listeye ekle
                    disabledDates = [...data.unavailableDates];
                    console.log("Devre dışı bırakılacak tarihler:", disabledDates);
                    
                    // DatePicker'ı yenile - Bu tarih seçiciyi yeniden oluşturur ve devre dışı tarihleri günceller
                    randevuTarihi.datepicker("refresh");
                    
                    // Butonları hemen özelleştir
                    customizeButtons();
                } else {
                    console.error("Sunucudan gelen verilerde unavailableDates dizisi bulunamadı veya dizi değil!");
                }
            },
            error: function(xhr, status, error) {
                console.error('Hata:', error);
            }
        });
    }
    
    // Cam filmi için uygun saatleri kontrol et
    function checkAvailableHours() {
        const selectedDate = randevuTarihi.val();
        
        // Tarih formatını YYYY-MM-DD formatına dönüştür
        const parts = selectedDate.split('.');
        const formattedDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
        
        $.ajax({
            url: `check_hours.php?date=${formattedDate}`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Tüm saat seçeneklerini etkinleştir
                randevuSaati.find('option').each(function() {
                    if ($(this).val() !== '') {
                        $(this).prop('disabled', false);
                    }
                });
                
                // Mevcut olmayan saatleri devre dışı bırak
                if (data.unavailableHours && data.unavailableHours.length > 0) {
                    randevuSaati.find('option').each(function() {
                        if ($.inArray($(this).val(), data.unavailableHours) !== -1) {
                            $(this).prop('disabled', true);
                        }
                    });
                }
                
                // Eğer seçili saat artık mevcut değilse sıfırla
                if (randevuSaati.prop('selectedIndex') > 0 && randevuSaati.find('option:selected').prop('disabled')) {
                    randevuSaati.val('');
                }
            },
            error: function(xhr, status, error) {
                console.error('Hata:', error);
            }
        });
    }
    
    // Sayfa yüklendiğinde butonları özelleştir
    customizeButtons();
}); 