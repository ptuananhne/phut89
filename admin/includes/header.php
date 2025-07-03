<?php /* === Nội dung cho admin/includes/header.php === */ ?>
<?php
require_once __DIR__ . '/../auth.php';

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'Trang Quản trị'); ?> - <?php echo e(SITE_NAME); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Thêm CSS cho Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/admin_style.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <h1 class="logo"><?php echo e(SITE_NAME); ?></h1>
            <nav class="main-nav">
                <ul>
                    <li>
                        <a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i><span>Bảng điều khiển</span>
                        </a>
                    </li>
                    <li>
                        <a href="products.php" class="<?php echo in_array($current_page, ['products.php', 'product_edit.php']) ? 'active' : ''; ?>">
                            <i class="fas fa-box-open"></i><span>Sản phẩm</span>
                        </a>
                    </li>
                    <li>
                        <a href="categories.php" class="<?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">
                            <i class="fas fa-sitemap"></i><span>Danh mục</span>
                        </a>
                    </li>
                    <!-- Thêm link Quản lý thuộc tính -->
                     <li>
                        <a href="attributes.php" class="<?php echo $current_page === 'attributes.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tags"></i><span>Thuộc tính</span>
                        </a>
                    </li>
                    <li>
                        <a href="brands.php" class="<?php echo $current_page === 'brands.php' ? 'active' : ''; ?>">
                            <i class="fas fa-copyright"></i><span>Thương hiệu</span>
                        </a>
                    </li>
                    <li>
                        <a href="banners.php" class="<?php echo $current_page === 'banners.php' ? 'active' : ''; ?>">
                            <i class="fas fa-images"></i><span>Banners</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="user-info">
                <p>Xin chào, <strong><?php echo e($_SESSION['admin_username']); ?></strong></p>
                <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
            </div>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <h2><?php echo e($page_title ?? 'Bảng điều khiển'); ?></h2>
            </header>
            <div class="content-body">
                <?php display_session_message(); ?>