<?php
// FILE: /app/bootstrap.php
// MÔ TẢ: Tệp khởi tạo, nạp tài nguyên và kết nối cho ứng dụng.

// Khởi động session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Nạp file cấu hình
// Thao tác này sẽ định nghĩa hằng số ROOT_PATH cho toàn bộ ứng dụng.
require_once dirname(__DIR__) . '/config/config.php';

// 2. Nạp file chứa các hàm tiện ích
require_once ROOT_PATH . '/app/core/functions.php';

// 3. [SỬA LỖI] Nạp các Base Controller một cách tường minh
// Thao tác này đảm bảo các lớp cha luôn có sẵn trước khi các lớp con cần đến chúng,
// khắc phục lỗi "Class not found".
$adminBaseControllerPath = ROOT_PATH . '/app/controllers/admin/AdminBaseController.php';
if (file_exists($adminBaseControllerPath)) {
    require_once $adminBaseControllerPath;
}

// Giả định có một ClientBaseController tương tự cho trang khách hàng
$clientBaseControllerPath = ROOT_PATH . '/app/controllers/client/ClientBaseController.php';
if (file_exists($clientBaseControllerPath)) {
    require_once $clientBaseControllerPath;
}


// 4. Thiết lập Autoloader
// Cơ chế này sẽ tự động nạp các file class khi chúng được gọi.
spl_autoload_register(function ($className) {
    // Thay thế ký tự namespace '\' bằng ký tự thư mục '/'
    $classPath = str_replace('\\', '/', $className);

    // Ánh xạ tiền tố namespace tới đúng thư mục controllers
    $classPath = str_replace('Client/', 'controllers/client/', $classPath);
    $classPath = str_replace('Admin/', 'controllers/admin/', $classPath);

    // Tạo đường dẫn đầy đủ tới file class
    $file = ROOT_PATH . '/app/' . $classPath . '.php';

    // Nếu file tồn tại, nạp nó
    if (file_exists($file)) {
        require_once $file;
    }
});


// 5. Khởi tạo kết nối đến Cơ sở dữ liệu (PDO)
global $pdo; // Biến kết nối toàn cục
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Báo lỗi khi có exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Trả về dạng mảng kết hợp
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Nếu không kết nối được, hiển thị thông báo lỗi thân thiện
    die('Không thể kết nối đến cơ sở dữ liệu. Lỗi: ' . $e->getMessage());
}
