<?php
// FILE: /public/index.php
// MÔ TẢ: Bộ định tuyến chính, giữ nguyên client và bổ sung đầy đủ cho admin.

require_once '../app/bootstrap.php';
global $pdo;
$url = trim($_GET['url'] ?? '', '/');

// Định nghĩa tất cả các đường dẫn của ứng dụng
$routes = [
    // === Client Routes (GIỮ NGUYÊN TỪ BẢN GỐC CỦA BẠN) ===
    '' => ['controller' => 'Client\HomeController', 'method' => 'index'],
    'san-pham/(?P<slug>[a-zA-Z0-9-]+)' => ['controller' => 'Client\ProductController', 'method' => 'show'],
    'danh-muc/(?P<slug>[a-zA-Z0-9-]+)' => ['controller' => 'Client\CategoryController', 'method' => 'show'],
    'tim-kiem' => ['controller' => 'Client\SearchController', 'method' => 'index'],
    'cam-do' => ['controller' => 'Client\PageController', 'method' => 'pawn'],
    'sua-chua' => ['controller' => 'Client\PageController', 'method' => 'repair'],
    'gioi-thieu' => ['controller' => 'Client\PageController', 'method' => 'about'],
    'ho-tro' => ['controller' => 'Client\PageController', 'method' => 'support'],


    // === Admin Routes (BỔ SUNG ĐẦY ĐỦ CÁC ROUTE CÒN THIẾU) ===
    'admin/login' => ['controller' => 'Admin\AuthController', 'method' => 'login'],
    'admin/logout' => ['controller' => 'Admin\AuthController', 'method' => 'logout'],
    'admin' => ['controller' => 'Admin\DashboardController', 'method' => 'index'],
    'admin/dashboard' => ['controller' => 'Admin\DashboardController', 'method' => 'index'],
    'admin/products' => ['controller' => 'Admin\ProductController', 'method' => 'index'],
    'admin/products/add' => ['controller' => 'Admin\ProductController', 'method' => 'add'],
    'admin/products/edit/(?P<id>\d+)' => ['controller' => 'Admin\ProductController', 'method' => 'edit'],
    'admin/categories' => ['controller' => 'Admin\CategoryController', 'method' => 'index'],
    'admin/brands' => ['controller' => 'Admin\BrandController', 'method' => 'index'],
    'admin/banners' => ['controller' => 'Admin\BannerController', 'method' => 'index'],
    'admin/attributes' => ['controller' => 'Admin\AttributeController', 'method' => 'index'],
    'admin/ajax/(?P<action>[a-zA-Z0-9_-]+)' => ['controller' => 'Admin\AjaxController', 'method' => 'handle'],
];

// Logic điều hướng giữ nguyên như file gốc của bạn
$route_found = false;
foreach ($routes as $pattern => $handler) {
    if (preg_match('#^' . $pattern . '$#', $url, $matches)) {
        $controllerName = $handler['controller'];
        $methodName = $handler['method'];
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        if (class_exists($controllerName)) {
            $controller = new $controllerName($pdo);
            if (method_exists($controller, $methodName)) {
                call_user_func_array([$controller, $methodName], $params);
                $route_found = true;
                break;
            }
        }
    }
}

// Xử lý khi không tìm thấy route nào khớp
if (!$route_found) {
    header("HTTP/1.0 404 Not Found");
    // (new Client\ClientBaseController($pdo))->renderNotFound(); // Logic gốc của bạn
    // Hoặc hiển thị một trang 404 thân thiện
    echo 'Lỗi 404: Trang bạn tìm kiếm không tồn tại.';
    exit;
}
