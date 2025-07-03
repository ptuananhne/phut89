<?php
// FILE: /app/controllers/admin/AdminBaseController.php
// MÔ TẢ: Controller cha cho trang admin, đã sửa lỗi active menu.

namespace Admin;

class AdminBaseController
{
    protected $pdo;
    protected array $data = [];

    public function __construct($pdo)
    {
        if (session_status() == \PHP_SESSION_NONE) {
            session_start();
        }
        $this->pdo = $pdo;
        $this->checkAuth();
        $this->generateCsrfToken();
        $this->loadGlobalData();
    }

    private function checkAuth(): void
    {
        if (!isset($_SESSION['admin_user_id'])) {
            header('Location: ' . \BASE_URL . '/admin/login');
            exit;
        }
    }

    private function generateCsrfToken(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    protected function verifyCsrfToken($token): bool
    {
        if (empty($token)) return false;
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Nạp dữ liệu chung cho sidebar, header,...
    protected function loadGlobalData(): void
    {
        $this->data['csrf_token'] = $_SESSION['csrf_token'];

        // [SỬA LỖI] Lấy trang hiện tại để active menu sidebar
        // Logic này sẽ hoạt động đúng với các URL lồng nhau như /admin/products/edit/1
        $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri_segments = explode('/', trim($uri_path, '/'));
        // Ví dụ: URL là 'phut89.local/admin/products/edit', $uri_segments[1] sẽ là 'products'.
        $this->data['current_page'] = $uri_segments[1] ?? 'dashboard';
    }

    // Hàm render view cho trang admin
    protected function render(string $view, array $viewData = []): void
    {
        $this->data['page_title'] = $viewData['page_title'] ?? 'Trang Quản Trị';
        $data = array_merge($this->data, $viewData);
        extract($data);

        $viewPath = \ROOT_PATH . '/app/views/admin/' . $view . '.php';

        if (file_exists($viewPath)) {
            ob_start();
            require $viewPath;
            $content = ob_get_clean();
            require \ROOT_PATH . '/app/views/admin/layout.php';
        } else {
            die("Admin view not found: " . $viewPath);
        }
    }

    // Hàm trả về JSON cho các request AJAX
    protected function jsonResponse(bool $success, string $message = '', array $extraData = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
        ], $extraData));
        exit;
    }
}
