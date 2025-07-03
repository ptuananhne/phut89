<?php
// FILE: /app/views/admin/pages/dashboard.php
// MÔ TẢ: Giao diện trang Bảng điều khiển.
//        Dữ liệu được truyền từ DashboardController.php

?>
<div class="dashboard-stats">
    <a href="<?php echo BASE_URL; ?>/admin/products">
        <div class="stat-card">
            <h3><?php echo $total_products ?? 0; ?></h3>
            <p>Sản phẩm</p>
            <i class="fas fa-box-open"></i>
        </div>
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/categories">
        <div class="stat-card">
            <h3><?php echo $total_categories ?? 0; ?></h3>
            <p>Danh mục</p>
            <i class="fas fa-sitemap"></i>
        </div>
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/brands">
        <div class="stat-card">
            <h3><?php echo $total_brands ?? 0; ?></h3>
            <p>Thương hiệu</p>
            <i class="fas fa-copyright"></i>
        </div>
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/banners">
        <div class="stat-card">
            <h3><?php echo $total_banners ?? 0; ?></h3>
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
                <?php if (!empty($most_viewed_products)): ?>
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
