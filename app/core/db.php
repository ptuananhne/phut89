<?php

// FILE: /app/core/db.php
// MÔ TẢ: File này chịu trách nhiệm duy nhất cho việc kết nối đến cơ sở dữ liệu.

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Tạo một đối tượng PDO để có thể tái sử dụng trong toàn bộ ứng dụng
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Nếu kết nối thất bại, hiển thị lỗi một cách an toàn
    if (APP_ENV === 'development') {
        die("LỖI KẾT NỐI CSDL: " . $e->getMessage());
    } else {
        die("Lỗi hệ thống. Vui lòng thử lại sau.");
    }
}
