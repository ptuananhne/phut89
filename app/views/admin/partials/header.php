<?php
// FILE: /app/views/admin/partials/header.php
// MÔ TẢ: Header và Sidebar cho trang Admin, đã được sửa lỗi logic active menu.

// Biến $current_page được cung cấp bởi AdminBaseController
?>
<aside class="sidebar">
    <h1 class="logo"><a href="<?php echo BASE_URL; ?>/admin"><?php echo e(SITE_NAME); ?></a></h1>
    <nav class="main-nav">
        <ul>
            <li><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="<?php echo ($current_page === 'dashboard' || $current_page === 'admin') ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i><span>Bảng điều khiển</span></a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/products" class="<?php echo ($current_page === 'products') ? 'active' : ''; ?>"><i class="fas fa-box-open"></i><span>Sản phẩm</span></a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/categories" class="<?php echo ($current_page === 'categories') ? 'active' : ''; ?>"><i class="fas fa-sitemap"></i><span>Danh mục</span></a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/attributes" class="<?php echo ($current_page === 'attributes') ? 'active' : ''; ?>"><i class="fas fa-tags"></i><span>Thuộc tính</span></a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/brands" class="<?php echo ($current_page === 'brands') ? 'active' : ''; ?>"><i class="fas fa-copyright"></i><span>Thương hiệu</span></a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/banners" class="<?php echo ($current_page === 'banners') ? 'active' : ''; ?>"><i class="fas fa-images"></i><span>Banners</span></a></li>
        </ul>
    </nav>
    <div class="user-info">
        <p>Xin chào, <strong><?php echo e($_SESSION['admin_username'] ?? 'Admin'); ?></strong></p>
        <a href="<?php echo BASE_URL; ?>/admin/logout" class="btn btn-danger">Đăng xuất</a>
    </div>
</aside>
<header class="main-header">
    <button id="sidebar-toggle" class="sidebar-toggle-btn"><i class="fas fa-bars"></i></button>
    <h2><?php echo e($page_title ?? 'Bảng điều khiển'); ?></h2>
</header>