<?php

// FILE: /app/views/partials/header.php
// MÔ TẢ: File này chứa toàn bộ phần đầu của trang (logo, thanh tìm kiếm, menu).
//        Nó được nạp vào bởi file layout.php.

$current_path = strtok($_SERVER['REQUEST_URI'], '?');

?>
<header class="site-header" id="site-header">
    <div class="container header-content">
        <a href="<?php echo BASE_URL; ?>/" class="logo"><?php echo e(SITE_NAME); ?></a>

        <form action="<?php echo BASE_URL; ?>/tim-kiem" method="GET" class="search-form">
            <input type="search" name="q" placeholder="Tìm kiếm sản phẩm..." required value="<?php echo e($_GET['q'] ?? ''); ?>" aria-label="Tìm kiếm sản phẩm">
            <button type="submit" aria-label="Tìm kiếm"><i class="fas fa-search"></i></button>
        </form>

        <nav class="main-nav">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/" class="<?php echo ($current_path == rtrim(BASE_URL, '/') . '/' || $current_path == BASE_URL . '/index.php') ? 'active' : ''; ?>">Trang Chủ</a></li>
                <li><a href="#" class="<?php echo (strpos($current_path, 'camdo.php') !== false) ? 'active' : ''; ?>">Cầm Đồ</a></li>
                <li><a href="#" class="<?php echo (strpos($current_path, 'suachua.php') !== false) ? 'active' : ''; ?>">Sửa Chữa</a></li>
                <li><a href="#" class="<?php echo (strpos($current_path, 'gioithieu.php') !== false) ? 'active' : ''; ?>">Giới Thiệu</a></li>
                <li><a href="#" class="<?php echo (strpos($current_path, 'hotro.php') !== false) ? 'active' : ''; ?>">Hỗ Trợ</a></li>
            </ul>
        </nav>

        <button class="hamburger-btn" id="hamburger-btn" aria-label="Mở menu">
            <span class="line"></span><span class="line"></span><span class="line"></span>
        </button>
    </div>
</header>

<nav class="mobile-nav" id="mobile-nav">
    <div class="mobile-nav-header">
        <h3 class="mobile-nav-title">Menu</h3>
        <button class="mobile-nav-close" id="mobile-nav-close" aria-label="Đóng menu">&times;</button>
    </div>
    <div class="mobile-nav-body">
        <ul>
            <li>
                <a href="<?php echo BASE_URL; ?>/" class="<?php echo ($current_path == rtrim(BASE_URL, '/') . '/' || $current_path == BASE_URL . '/index.php') ? 'active' : ''; ?>">
                    <i class="fas fa-home fa-fw"></i><span>Trang Chủ</span>
                </a>
            </li>

            <li class="nav-heading">Dịch Vụ & Thông Tin</li>
            <li><a href="#"><i class="fas fa-hand-holding-usd fa-fw"></i><span>Cầm Đồ</span></a></li>
            <li><a href="#"><i class="fas fa-tools fa-fw"></i><span>Sửa Chữa</span></a></li>
            <li><a href="#"><i class="fas fa-info-circle fa-fw"></i><span>Giới Thiệu</span></a></li>
            <li><a href="#"><i class="fas fa-headset fa-fw"></i><span>Hỗ Trợ</span></a></li>

            <li class="nav-heading">Danh Mục Sản Phẩm</li>
            <?php if (!empty($menu_categories)): ?>
                <?php foreach ($menu_categories as $category) : ?>
                    <li>
                        <a href="<?php echo BASE_URL . '/danh-muc/' . e($category['slug']); ?>" class="<?php echo ($current_category_slug === $category['slug']) ? 'active' : ''; ?>">
                            <i class="fas <?php echo get_icon_for_category($category['slug']); ?> fa-fw"></i>
                            <span><?php echo e($category['ten_danh_muc']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="mobile-nav-overlay" id="mobile-nav-overlay"></div>

