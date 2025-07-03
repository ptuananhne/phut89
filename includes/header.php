<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Lấy danh sách danh mục
try {
    $sql = "SELECT slug, ten_danh_muc FROM danh_muc WHERE is_active = 1 ORDER BY vi_tri ASC, id ASC";
    $menu_categories = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    $menu_categories = [];
    if (APP_ENV === 'development') {
        error_log("Menu categories query failed: " . $e->getMessage());
    }
}

$current_path = strtok($_SERVER['REQUEST_URI'], '?');
$current_category_slug = '';
if (preg_match('/\/danh-muc\/([a-zA-Z0-9-]+)/', $current_path, $matches)) {
    $current_category_slug = $matches[1];
}

function get_icon_for_category($slug)
{
    if (strpos($slug, 'dien-thoai') !== false) return 'fa-mobile-alt';
    if (strpos($slug, 'laptop') !== false) return 'fa-laptop';
    if (strpos($slug, 'o-to') !== false) return 'fa-car';
    if (strpos($slug, 'xe-may') !== false) return 'fa-motorcycle';
    if (strpos($slug, 'phu-kien') !== false) return 'fa-headphones';
    if (strpos($slug, 'may-tinh-bang') !== false) return 'fa-tablet-alt';
    return 'fa-tag'; // Icon mặc định
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? e(SITE_NAME); ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Phút 89 - Cung cấp sản phẩm công nghệ và phương tiện di chuyển hàng đầu.'; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <header class="site-header" id="site-header">
        <div class="container header-content">
            <a href="<?php echo BASE_URL; ?>/" class="logo"><?php echo e(SITE_NAME); ?></a>

            <form action="<?php echo BASE_URL; ?>/timkiem.php" method="GET" class="search-form">
                <input type="search" name="q" placeholder="Tìm kiếm sản phẩm..." required value="<?php echo e($_GET['q'] ?? ''); ?>" aria-label="Tìm kiếm sản phẩm">
                <button type="submit" aria-label="Tìm kiếm"><i class="fas fa-search"></i></button>
            </form>

            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/" class="<?php echo ($current_path == BASE_URL . '/index.php' || rtrim($current_path, '/') == rtrim(parse_url(BASE_URL, PHP_URL_PATH), '/')) ? 'active' : ''; ?>">Trang Chủ</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/camdo.php" class="<?php echo (strpos($current_path, 'camdo.php') !== false) ? 'active' : ''; ?>">Cầm Đồ</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/suachua.php" class="<?php echo (strpos($current_path, 'suachua.php') !== false) ? 'active' : ''; ?>">Sửa Chữa</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/gioithieu.php" class="<?php echo (strpos($current_path, 'gioithieu.php') !== false) ? 'active' : ''; ?>">Giới Thiệu</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/hotro.php" class="<?php echo (strpos($current_path, 'hotro.php') !== false) ? 'active' : ''; ?>">Hỗ Trợ</a></li>
                </ul>
            </nav>

            <button class="hamburger-btn" id="hamburger-btn" aria-label="Mở menu"><span class="line"></span><span class="line"></span><span class="line"></span></button>
        </div>
    </header>

    <!-- SỬA ĐỔI: Cấu trúc menu di động được thiết kế lại -->
    <nav class="mobile-nav" id="mobile-nav">
        <div class="mobile-nav-header">
            <h3 class="mobile-nav-title">Menu</h3>
            <button class="mobile-nav-close" id="mobile-nav-close" aria-label="Đóng menu">&times;</button>
        </div>
        <div class="mobile-nav-body">
            <ul>
                <li>
                    <a href="<?php echo BASE_URL; ?>/" class="<?php echo ($current_path == BASE_URL . '/index.php' || rtrim($current_path, '/') == rtrim(parse_url(BASE_URL, PHP_URL_PATH), '/')) ? 'active' : ''; ?>">
                        <i class="fas fa-home fa-fw"></i><span>Trang Chủ</span>
                    </a>
                </li>

                <li class="nav-heading">Dịch Vụ & Thông Tin</li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/camdo.php" class="<?php echo (strpos($current_path, 'camdo.php') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-hand-holding-usd fa-fw"></i><span>Cầm Đồ</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/suachua.php" class="<?php echo (strpos($current_path, 'suachua.php') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-tools fa-fw"></i><span>Sửa Chữa</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/gioithieu.php" class="<?php echo (strpos($current_path, 'gioithieu.php') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-info-circle fa-fw"></i><span>Giới Thiệu</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/hotro.php" class="<?php echo (strpos($current_path, 'hotro.php') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-headset fa-fw"></i><span>Hỗ Trợ</span>
                    </a>
                </li>

                <li class="nav-heading">Danh Mục Sản Phẩm</li>

                <?php foreach ($menu_categories as $category) : ?>
                    <li>
                        <a href="<?php echo BASE_URL . '/danh-muc/' . e($category['slug']); ?>" class="<?php echo ($current_category_slug === $category['slug']) ? 'active' : ''; ?>">
                            <i class="fas <?php echo get_icon_for_category($category['slug']); ?> fa-fw"></i>
                            <span><?php echo e($category['ten_danh_muc']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
    <div class="mobile-nav-overlay" id="mobile-nav-overlay"></div>

    <div class="container page-wrapper">
        <?php if (!empty($menu_categories)): ?>
            <aside class="category-sidebar">
                <h3 class="sidebar-title">Danh Mục Sản Phẩm</h3>
                <ul class="sidebar-menu">
                    <?php foreach ($menu_categories as $category) : ?>
                        <li>
                            <a href="<?php echo BASE_URL . '/danh-muc/' . e($category['slug']); ?>" class="<?php echo ($current_category_slug === $category['slug']) ? 'active' : ''; ?>">
                                <?php echo e($category['ten_danh_muc']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        <?php endif; ?>

        <main class="main-content">
            <?php // Nội dung chính của trang sẽ bắt đầu từ đây 
            ?>