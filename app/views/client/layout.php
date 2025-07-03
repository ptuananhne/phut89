<?php
// FILE: /app/views/client/layout.php
// MÔ TẢ: Layout chính cho trang khách hàng.

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? e(SITE_NAME); ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Phút 89 - Cung cấp sản phẩm công nghệ và phương tiện di chuyển hàng đầu.'; ?>">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/main.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/favicon.png">
</head>
<body>
    <?php 
    // Cập nhật đường dẫn để trỏ vào thư mục partials của client
    require_once __DIR__ . '/partials/header.php'; 
    ?>

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
            <?php echo $content; ?>
        </main>
    </div>

    <?php 
    // Cập nhật đường dẫn để trỏ vào thư mục partials của client
    require_once __DIR__ . '/partials/footer.php'; 
    ?>
    
    <script type="module" src="<?php echo BASE_URL; ?>/assets/js/app.js?v=<?php echo filemtime(ROOT_PATH . '/public/assets/js/app.js'); ?>"></script>
</body>
</html>
