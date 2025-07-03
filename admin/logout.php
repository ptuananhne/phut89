<?php
// Luôn bắt đầu session trước khi thao tác với nó
session_start();

// Hủy tất cả các biến trong session
$_SESSION = array();

// Xóa cookie của session nếu nó tồn tại
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Cuối cùng, hủy hoàn toàn session
session_destroy();

// Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit;
