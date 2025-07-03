<?php
// FILE: /app/controllers/admin/AuthController.php
// MÔ TẢ: Xử lý đăng nhập và đăng xuất.

namespace Admin;

class AuthController
{
    private $pdo;

    public function __construct($pdo)
    {
        if (session_status() == \PHP_SESSION_NONE) {
            session_start();
        }
        $this->pdo = $pdo;
    }

    public function login(): void
    {
        if (isset($_SESSION['admin_user_id'])) {
            header('Location: ' . \BASE_URL . '/admin/dashboard');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
            } else {
                try {
                    $stmt = $this->pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user['password'])) {
                        session_regenerate_id(true);
                        $_SESSION['admin_user_id'] = $user['id'];
                        $_SESSION['admin_username'] = $user['username'];
                        header('Location: ' . \BASE_URL . '/admin/dashboard');
                        exit;
                    } else {
                        $error = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
                    }
                } catch (\PDOException $e) {
                    $error = 'Lỗi hệ thống, không thể truy vấn CSDL.';
                }
            }
        }

        // Render view login (file này không dùng layout admin chung)
        $data = ['error' => $error];
        extract($data);
        require \ROOT_PATH . '/app/views/admin/pages/login.php';
    }

    public function logout(): void
    {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        header('Location: ' . \BASE_URL . '/admin/login');
        exit;
    }
}
