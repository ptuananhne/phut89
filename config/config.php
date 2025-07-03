<?php
// FILE: /config/config.php
// MÔ TẢ: Tệp cấu hình trung tâm cho toàn bộ ứng dụng.

// --- 1. CẤU HÌNH KẾT NỐI DATABASE ---
// Thay đổi các thông số này cho phù hợp với môi trường của bạn.
define('DB_HOST', '127.0.0.1');      // Thường là 'localhost' hoặc '127.0.0.1'
define('DB_NAME', 'phut89');         // Tên database của bạn
define('DB_USER', 'root');           // Tên người dùng database
define('DB_PASS', '');               // Mật khẩu database

// --- 2. CẤU HÌNH ĐƯỜNG DẪN & URL ---
// Đường dẫn tuyệt đối đến thư mục gốc của dự án (thư mục chứa 'app', 'config', 'public').
define('ROOT_PATH', dirname(__DIR__));

// Tự động xác định BASE_URL (http://yourdomain.com)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
// Nếu dự án của bạn nằm trong một thư mục con, hãy điền tên thư mục đó ở đây.
// Ví dụ: '/my-project'. Để trống nếu chạy trên domain chính hoặc virtual host.
$subfolder = '';
define('BASE_URL', $protocol . $host . $subfolder);

// --- 3. CẤU HÌNH MÔI TRƯỜNG & THÔNG SỐ KHÁC ---
define('APP_ENV', 'development');    // 'development' để hiển thị lỗi, 'production' để ẩn lỗi
define('SITE_NAME', 'Phút 89');      // Tên website của bạn
define('PRODUCTS_PER_PAGE', 8);      // Số sản phẩm trên mỗi trang danh mục

// Thiết lập múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// --- 4. CẤU HÌNH HIỂN THỊ LỖI ---
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}
