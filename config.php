<?php

// --- 1. CẤU HÌNH DATABASE ---
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'phut89');
define('DB_USER', 'root');
define('DB_PASS', '');

// --- 2. CẤU HÌNH WEBSITE ---
define('SITE_NAME', 'Phút 89');
define('ROOT_PATH', __DIR__); // Đường dẫn vật lý trên ổ đĩa, không thay đổi

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

$subfolder = '/phut89';

define('BASE_URL', $protocol . $host . $subfolder);


// --- 4. CẤU HÌNH MÔI TRƯỜNG & CHỨC NĂNG ---
define('APP_ENV', 'development');
define('PRODUCTS_PER_PAGE', 8);
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bật/Tắt hiển thị lỗi
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// KHÔNG CÓ DẤU NGOẶC NHỌN '}' Ở ĐÂY