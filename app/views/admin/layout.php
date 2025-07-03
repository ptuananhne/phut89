<?php
// FILE: /app/views/admin/layout.php
// MÔ TẢ: Đây là file khung giao diện chính cho toàn bộ trang admin.
//        Nó bao gồm header, sidebar, footer và nạp nội dung của từng trang cụ thể vào biến $content.
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'Trang Quản trị'); ?> - <?php echo e(SITE_NAME); ?></title>

    <!-- Libraries CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Main Admin CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/admin-assets/css/admin_style.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <h1 class="logo"><?php echo e(SITE_NAME); ?></h1>
            <nav class="main-nav">
                <ul>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="<?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i><span>Bảng điều khiển</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/products" class="<?php echo in_array($current_page, ['products', 'product_edit']) ? 'active' : ''; ?>">
                            <i class="fas fa-box-open"></i><span>Sản phẩm</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/categories" class="<?php echo ($current_page === 'categories') ? 'active' : ''; ?>">
                            <i class="fas fa-sitemap"></i><span>Danh mục</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/attributes" class="<?php echo ($current_page === 'attributes') ? 'active' : ''; ?>">
                            <i class="fas fa-tags"></i><span>Thuộc tính</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/brands" class="<?php echo ($current_page === 'brands') ? 'active' : ''; ?>">
                            <i class="fas fa-copyright"></i><span>Thương hiệu</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/banners" class="<?php echo ($current_page === 'banners') ? 'active' : ''; ?>">
                            <i class="fas fa-images"></i><span>Banners</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="user-info">
                <p>Xin chào, <strong><?php echo e($_SESSION['admin_username']); ?></strong></p>
                <a href="<?php echo BASE_URL; ?>/admin/logout" class="btn btn-danger">Đăng xuất</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h2><?php echo e($page_title ?? 'Bảng điều khiển'); ?></h2>
            </header>
            <div class="content-body">
                <?php
                // Hiển thị thông báo (nếu có)
                if (isset($_SESSION['message'])) {
                    $message = e($_SESSION['message']);
                    $message_type = e($_SESSION['message_type'] ?? 'info');
                    echo '<div class="alert alert-' . $message_type . '">' . $message . '</div>';
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                }

                // Nạp nội dung chính của trang
                echo $content ?? '';
                ?>
            </div>
        </main>
    </div>

    <!-- Libraries JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <!-- Main Admin JS -->
    <script>
        // Truyền các biến PHP quan trọng sang cho Javascript
        const AppConfig = {
            BASE_URL: '<?php echo BASE_URL; ?>',
            CSRF_TOKEN: '<?php echo $csrf_token ?? ''; ?>'
        };
    </script>
    <script src="<?php echo BASE_URL; ?>/admin-assets/js/admin_main.js?v=<?php echo time(); ?>"></script>
</body>

</html>