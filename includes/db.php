<?php
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config.php';
}

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    if (APP_ENV === 'development') {
        die("LỖI KẾT NỐI CSDL: " . $e->getMessage());
    } else {
        die("Lỗi hệ thống. Vui lòng thử lại sau.");
    }
}
