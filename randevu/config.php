<?php
// Veritabanı bağlantı bilgileri
$db_host = 'localhost';
$db_user = 'u549971977_randevu';
$db_pass = 'Z3@x9hwH';
$db_name = 'u549971977_randevu';
error_reporting(0);
try {
    // PDO bağlantısı
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $conn = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Hizmet tipi bilgileri
$hizmet_tipleri = [
    'on_uc_para' => [
        'isim' => 'Ön Uç Para',
        'gun_sayisi' => 1,
        'cam_filmi_uyumlu' => false
    ],
    'arac_kaplama' => [
        'isim' => 'Araç Kaplama',
        'gun_sayisi' => 2,
        'cam_filmi_uyumlu' => false
    ],
    'cam_filmi' => [
        'isim' => 'Cam Filmi',
        'gun_sayisi' => 1,
        'cam_filmi_uyumlu' => true,
        'saatler' => ['09:30-13:00', '13:00-16:30', '16:30-20:00'],
        'max_gunluk' => 3
    ],
    'body_kit' => [
        'isim' => 'Body Kit',
        'gun_sayisi' => 1,
        'cam_filmi_uyumlu' => false
    ],
    'elektronik_urunler' => [
        'isim' => 'Elektronik Ürünler',
        'gun_sayisi' => 1,
        'cam_filmi_uyumlu' => false
    ]
];

// JSON cevabı döndürmek için yardımcı fonksiyon
function jsonResponse($success, $message, $data = []) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?> 