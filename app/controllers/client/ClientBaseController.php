<?php
// FILE: /app/controllers/client/ClientBaseController.php
namespace Client;

class ClientBaseController
{
    protected $pdo;
    protected array $data = [];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->loadGlobalData();
    }

    protected function loadGlobalData(): void
    {
        try {
            $this->data['menu_categories'] = $this->pdo->query("SELECT slug, ten_danh_muc FROM danh_muc WHERE is_active = 1 ORDER BY vi_tri ASC, id ASC")->fetchAll();
            $current_path = strtok($_SERVER['REQUEST_URI'], '?');
            $this->data['current_category_slug'] = '';
            if (preg_match('/\/danh-muc\/([a-zA-Z0-9-]+)/', $current_path, $matches)) {
                $this->data['current_category_slug'] = $matches[1];
            }
        } catch (\PDOException $e) {
            $this->data['menu_categories'] = [];
        }
    }

    protected function render(string $view, array $viewData = []): void
    {
        $data = array_merge($this->data, $viewData);
        extract($data);

        $viewPath = \ROOT_PATH . '/app/views/client/' . $view . '.php';

        if (file_exists($viewPath)) {
            ob_start();
            require $viewPath;
            $content = ob_get_clean();
            require \ROOT_PATH . '/app/views/client/layout.php';
        } else {
            $this->renderNotFound();
        }
    }

    public function renderNotFound(): void
    {
        http_response_code(404);
        $this->render('pages/404', ['page_title' => '404 - Không tìm thấy trang']);
    }
}
