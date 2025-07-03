<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['admin_user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Bảo vệ chống tấn công CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token()
{
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// 3. Hàm hiển thị thông báo session
function display_session_message()
{
    if (isset($_SESSION['message'])) {
        $message = htmlspecialchars($_SESSION['message']);
        $message_type = htmlspecialchars($_SESSION['message_type'] ?? 'info');
        echo '<div class="alert alert-' . $message_type . '">' . $message . '</div>';

        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}
