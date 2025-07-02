<?php
require_once 'auth.php';

// Tüm randevuları getir, filtreleme ve sayfalama DataTables tarafından yapılacak
$sql = "SELECT * FROM randevular WHERE randevu_durumu != 'iptal' ORDER BY randevu_tarihi DESC, olusturma_tarihi DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$randevular = $stmt->fetchAll();

// Durum güncelleme işlemi
if (isset($_POST['durum_guncelle']) && isset($_POST['randevu_id']) && isset($_POST['yeni_durum'])) {
    $randevu_id = intval($_POST['randevu_id']);
    $yeni_durum = $_POST['yeni_durum'];
    
    // Geçerli durumlar
    $gecerli_durumlar = ['beklemede', 'tamamlandi'];
    
    if (in_array($yeni_durum, $gecerli_durumlar)) {
        $sql = "UPDATE randevular SET randevu_durumu = :durum WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':durum', $yeni_durum, PDO::PARAM_STR);
        $stmt->bindParam(':id', $randevu_id, PDO::PARAM_INT);
        
        try {
            $stmt->execute();
            $mesaj = "Randevu durumu başarıyla güncellendi.";
            $mesaj_tur = "success";
        } catch (PDOException $e) {
            $mesaj = "Durum güncellenirken bir hata oluştu: " . $e->getMessage();
            $mesaj_tur = "error";
        }
        
        // Sayfayı yeniden yükle
        header("Location: randevular.php?mesaj=$mesaj&mesaj_tur=$mesaj_tur");
        exit();
    }
}

// Randevu silme işlemi
if (isset($_POST['randevu_sil']) && isset($_POST['randevu_id'])) {
    $randevu_id = intval($_POST['randevu_id']);
    
    $sql = "DELETE FROM randevular WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $randevu_id, PDO::PARAM_INT);
    
    try {
        $stmt->execute();
        $mesaj = "Randevu başarıyla silindi.";
        $mesaj_tur = "success";
    } catch (PDOException $e) {
        $mesaj = "Randevu silinirken bir hata oluştu: " . $e->getMessage();
        $mesaj_tur = "error";
    }
    
    // Sayfayı yeniden yükle
    header("Location: randevular.php?mesaj=$mesaj&mesaj_tur=$mesaj_tur");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Yönetimi - Araç Bakım Randevu Sistemi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-header">
        <div class="container-fluid">
            <nav class="admin-nav">
                <div class="logo-container">
                    <i class="fas fa-tools me-2"></i>
                    <h2>Araç Bakım Randevu Yönetimi</h2>
                </div>
                <div class="admin-menu">
                    <a href="randevular.php" class="<?php echo $current_page === 'randevular.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt me-1"></i> Randevular
                    </a>
                    <a href="?logout=1">
                        <i class="fas fa-sign-out-alt me-1"></i> Çıkış Yap
                    </a>
                </div>
            </nav>
        </div>
    </div>
    
    <div class="container admin-container">
        <?php if (isset($_GET['mesaj'])): ?>
            <div class="notification <?php echo $_GET['mesaj_tur'] ?? 'info'; ?>">
                <i class="<?php echo $_GET['mesaj_tur'] === 'success' ? 'fas fa-check-circle' : 'fas fa-info-circle'; ?> me-2"></i>
                <?php echo $_GET['mesaj']; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Randevular</h2>
                <div class="export-buttons">
                    <button id="btnExportExcel" class="btn btn-sm btn-outline-success me-2">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                    <button id="btnPrint" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-print me-1"></i> Yazdır
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table id="randevularTable" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tarih/Saat</th>
                            <th>Müşteri</th>
                            <th>Telefon</th>
                            <th>Plaka</th>
                            <th>Hizmet</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                            <th>Not</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($randevular as $row): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td data-order="<?php echo $row['randevu_tarihi']; ?>">
                                    <i class="far fa-calendar-alt me-1"></i><?php echo formatTarih($row['randevu_tarihi']); ?>
                                    <?php if (!empty($row['randevu_saati'])): ?>
                                        <br><small><i class="far fa-clock me-1"></i><?php echo $row['randevu_saati']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['ad_soyad']); ?></td>
                                <td><?php echo htmlspecialchars($row['telefon']); ?></td>
                                <td><?php echo htmlspecialchars($row['plaka']); ?></td>
                                <td><?php echo getHizmetAdi($row['hizmet_tipi'], $hizmet_tipleri); ?></td>
                                <td><?php echo getDurumHtml($row['randevu_durumu']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-primary btn-sm" onclick="durumGuncelle(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="randevuSil(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($row['aciklama'])): ?>
                                        <span class="d-inline-block text-truncate" style="max-width: 150px;" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($row['aciklama']); ?>">
                                            <?php echo htmlspecialchars($row['aciklama']); ?>
                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted">Belirtilmemiş</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Durum Güncelleme Modal -->
    <div id="durumModal" class="modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fas fa-edit me-2"></i>Randevu Durumunu Güncelle</h3>
                    <button type="button" class="btn-close" onclick="closeModal('durumModal')"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="randevu_id" id="durum_randevu_id">
                        <input type="hidden" name="durum_guncelle" value="1">
                        
                        <div class="form-group mb-3">
                            <label for="yeni_durum" class="form-label"><i class="fas fa-tasks me-1"></i>Durum:</label>
                            <select name="yeni_durum" id="yeni_durum" class="form-select" required>
                                <option value="beklemede">Beklemede</option>
                                <option value="tamamlandi">Tamamlandı</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('durumModal')">
                            <i class="fas fa-times me-1"></i>İptal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Randevu Silme Modal -->
    <div id="silModal" class="modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fas fa-trash-alt me-2"></i>Randevuyu Sil</h3>
                    <button type="button" class="btn-close" onclick="closeModal('silModal')"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="randevu_id" id="sil_randevu_id">
                        <input type="hidden" name="randevu_sil" value="1">
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Bu randevuyu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('silModal')">
                            <i class="fas fa-times me-1"></i>İptal
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i>Sil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <!-- SheetJS - Excel Dışa Aktarma -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // DataTables başlatma
            let table = $('#randevularTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json',
                },
                responsive: true,
                order: [[0, 'desc']], // ID'ye göre azalan sıralama
                scrollX: true,
                scrollCollapse: true,
                autoWidth: false,
                pageLength: 25, // Sayfa başına gösterilecek kayıt sayısı
                columnDefs: [
                    { width: '40px', targets: 0, className: 'text-center' }, // ID sütunu
                    { width: '120px', targets: 1 }, // Tarih sütunu
                    { width: '140px', targets: 2 }, // Müşteri
                    { width: '120px', targets: 3 }, // Telefon
                    { width: '90px', targets: 4 }, // Plaka
                    { width: '120px', targets: 5 }, // Hizmet
                    { width: '110px', targets: 6, className: 'text-center' }, // Durum
                    { width: '90px', targets: 7, className: 'text-center' }, // İşlemler
                    { width: '140px', targets: 8 }  // Not
                ],
                dom: '<"row d-flex justify-content-between mt-2 mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 text-end"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function() {
                    // Tablo yüklendikten sonra genişliği düzenle
                    $(window).on('resize', function() {
                        table.columns.adjust().draw();
                    });
                    // Sayfa yüklendikten sonra bir kez ayarla
                    setTimeout(function() {
                        table.columns.adjust().draw();
                    }, 100);
                }
            });
            
            // Tabloyu nowrap class'ı ile sarmala
            $('#randevularTable').addClass('nowrap');
            
            // Tooltip başlatma
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Excel'e aktar
            $('#btnExportExcel').on('click', function() {
                exportTableToExcel();
            });
            
            // Yazdır
            $('#btnPrint').on('click', function() {
                printTable();
            });
        });
        
        // Tabloyu Excel'e aktarma
        function exportTableToExcel() {
            // Durum hücrelerini düzelt
            let tableData = [];
            let headers = [];
            
            // Başlıkları al
            $('#randevularTable thead th').each(function() {
                headers.push($(this).text());
            });
            tableData.push(headers);
            
            // Satırları al
            $('#randevularTable tbody tr').each(function() {
                let rowData = [];
                
                $(this).find('td').each(function(index) {
                    // İşlemler sütununu hariç tut
                    if (index === 7) { // İşlemler sütunu (0'dan başlayarak)
                        rowData.push(''); // Boş bir değer ekle
                    } else if (index === 6) { // Durum sütunu
                        // HTML etiketlerini kaldır ve sadece durum metnini al
                        let statusText = $(this).text().trim();
                        rowData.push(statusText);
                    } else {
                        // Diğer sütunlar için normal metin
                        rowData.push($(this).text().trim());
                    }
                });
                
                tableData.push(rowData);
            });
            
            // Tablo boş kontrol et
            if (tableData.length <= 1) {
                alert('Dışa aktarılacak veri bulunamadı!');
                return;
            }
            
            try {
                // Excel dosyası oluştur
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(tableData);
                
                // Çalışma sayfasını çalışma kitabına ekle
                XLSX.utils.book_append_sheet(wb, ws, 'Randevular');
                
                // Dosyayı indir
                saveAsExcelFile(wb, 'Randevular');
            } catch (error) {
                console.error('Excel dışa aktarma hatası:', error);
                alert('Excel dosyası oluşturulurken bir hata oluştu: ' + error.message);
            }
        }
        
        // Excel dosyasını kaydet
        function saveAsExcelFile(wb, fileName) {
            try {
                const excelBuffer = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
                const data = new Blob([excelBuffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8' });
                
                const url = window.URL.createObjectURL(data);
                const a = document.createElement('a');
                document.body.appendChild(a);
                a.href = url;
                a.download = fileName + '_' + new Date().toLocaleDateString('tr-TR') + '.xlsx';
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            } catch (error) {
                console.error('Excel kaydetme hatası:', error);
                alert('Excel dosyası kaydedilirken bir hata oluştu: ' + error.message);
            }
        }
        
        // Tabloyu yazdır
        function printTable() {
            let printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Randevular</title>
                    <meta charset="UTF-8">
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                        th { background-color: #f2f2f2; }
                        .print-header { text-align: center; margin-bottom: 20px; }
                        .status-beklemede { color: #856404; background-color: #fff3cd; padding: 3px 6px; border-radius: 4px; }
                        .status-tamamlandi { color: #155724; background-color: #d4edda; padding: 3px 6px; border-radius: 4px; }
                        @media print {
                            table { page-break-inside: auto; }
                            tr { page-break-inside: avoid; page-break-after: auto; }
                            thead { display: table-header-group; }
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h1>Randevular</h1>
                        <p>Tarih: ${new Date().toLocaleDateString('tr-TR')}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tarih/Saat</th>
                                <th>Müşteri</th>
                                <th>Telefon</th>
                                <th>Plaka</th>
                                <th>Hizmet</th>
                                <th>Durum</th>
                                <th>Not</th>
                            </tr>
                        </thead>
                        <tbody>
            `);
            
            $('#randevularTable tbody tr').each(function() {
                printWindow.document.write('<tr>');
                
                $(this).find('td').each(function(index) {
                    // İşlemler sütununu hariç tut
                    if (index !== 7) { // İşlemler sütunu (0'dan başlayarak)
                        if (index === 6) { // Durum sütunu
                            let durumText = $(this).text().trim();
                            let durumClass = "";
                            
                            if (durumText.includes("Beklemede")) {
                                durumClass = "status-beklemede";
                            } else if (durumText.includes("Tamamlandı")) {
                                durumClass = "status-tamamlandi";
                            } else {
                                durumClass = "status-beklemede";
                            }
                            
                            printWindow.document.write(`<td><span class="${durumClass}">${durumText}</span></td>`);
                        } else {
                            printWindow.document.write(`<td>${$(this).html()}</td>`);
                        }
                    }
                });
                
                printWindow.document.write('</tr>');
            });
            
            printWindow.document.write(`
                        </tbody>
                    </table>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            // Yazdırma işlemi ve gecikmeli kapatma
            setTimeout(function() {
                printWindow.print();
                printWindow.close();
            }, 1000);
        }
        
        function durumGuncelle(id) {
            document.getElementById('durum_randevu_id').value = id;
            document.getElementById('durumModal').style.display = 'block';
        }
        
        function randevuSil(id) {
            document.getElementById('sil_randevu_id').value = id;
            document.getElementById('silModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Modal dışına tıklayınca kapatma 
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };

        // Bildirim mesajını belirli bir süre sonra kaldır
        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.notification');
            if (notifications.length > 0) {
                setTimeout(() => {
                    notifications.forEach(notification => {
                        notification.style.display = 'none';
                    });
                }, 5000);
            }
        });
    </script>
</body>
</html>