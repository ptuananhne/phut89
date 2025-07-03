<?php
$page_title = 'Bảng điều khiển';
require_once 'includes/header.php';

// Lấy các thống kê nhanh từ CSDL
try {
    // [UPDATED] Logic đếm sản phẩm mới
    // 1. Đếm số lượng sản phẩm đơn giản
    $simple_products_count = $pdo->query("SELECT COUNT(*) FROM san_pham WHERE loai_san_pham = 'simple'")->fetchColumn();

    // 2. Đếm tổng số lượng biến thể của tất cả sản phẩm có biến thể
    $variant_products_count = $pdo->query("SELECT COUNT(*) FROM product_variants")->fetchColumn();

    // 3. Tổng số sản phẩm là tổng của hai loại trên
    $total_products = $simple_products_count + $variant_products_count;

    // Các thống kê khác giữ nguyên
    $total_categories = $pdo->query("SELECT COUNT(*) FROM danh_muc")->fetchColumn();
    $total_brands = $pdo->query("SELECT COUNT(*) FROM thuong_hieu")->fetchColumn();
    $total_banners = $pdo->query("SELECT COUNT(*) FROM banners")->fetchColumn();

    // Lấy 5 sản phẩm có lượt xem cao nhất
    $most_viewed_products = $pdo->query("SELECT ten_san_pham, luot_xem FROM san_pham ORDER BY luot_xem DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    // Xử lý lỗi nếu không thể truy vấn
    $total_products = $total_categories = $total_brands = $total_banners = 0;
    $most_viewed_products = [];
    // Hiển thị lỗi nếu ở môi trường development
    if (defined('APP_ENV') && APP_ENV === 'development') {
        display_session_message(); // Hiển thị các thông báo session khác nếu có
        echo '<div class="alert alert-danger">Lỗi CSDL: Không thể tải dữ liệu thống kê. ' . $e->getMessage() . '</div>';
    } else {
        $_SESSION['message'] = 'Không thể tải dữ liệu thống kê.';
        $_SESSION['message_type'] = 'danger';
    }
}
?>

<div class="dashboard-stats">
    <a href="products.php">
        <div class="stat-card">
            <h3><?php echo $total_products; ?></h3>
            <p>Sản phẩm</p>
            <i class="fas fa-box-open"></i>
        </div>
    </a>
    <a href="categories.php">
        <div class="stat-card">
            <h3><?php echo $total_categories; ?></h3>
            <p>Danh mục</p>
            <i class="fas fa-sitemap"></i>
        </div>
    </a>
    <a href="brands.php">
        <div class="stat-card">
            <h3><?php echo $total_brands; ?></h3>
            <p>Thương hiệu</p>
            <i class="fas fa-copyright"></i>
        </div>
    </a>
    <a href="banners.php">
        <div class="stat-card">
            <h3><?php echo $total_banners; ?></h3>
            <p>Banners</p>
            <i class="fas fa-images"></i>
        </div>
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Sản phẩm được xem nhiều nhất</h2>
    </div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>Tên sản phẩm</th>
                    <th style="text-align: right;">Lượt xem</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($most_viewed_products): ?>
                    <?php foreach ($most_viewed_products as $product): ?>
                        <tr>
                            <td><?php echo e($product['ten_san_pham']); ?></td>
                            <td style="text-align: right;"><?php echo number_format($product['luot_xem']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align: center;">Chưa có dữ liệu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>