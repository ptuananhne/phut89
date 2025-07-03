<?php if (isset($error_message)): ?>
    <div class="page-title">
        <h1>Lỗi</h1>
        <?php echo $error_message; ?>
    </div>
<?php elseif ($category): ?>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>/">Trang chủ</a>
        <span>&gt;</span>
        <span class="active"><?php echo e($category['ten_danh_muc']); ?></span>
    </div>

    <div class="filter-bar">
        <?php if (!empty($brands)): ?>
            <div class="brand-filter">
                <span class="filter-label">Thương hiệu:</span>
                <a href="<?php echo BASE_URL; ?>/danh-muc/<?php echo e($category_slug); ?>?sort=<?php echo $sort_option; ?>" class="filter-btn <?php echo empty($brand_slug) ? 'active' : ''; ?>">Tất cả</a>
                <?php foreach ($brands as $brand): ?>
                    <a href="<?php echo BASE_URL; ?>/danh-muc/<?php echo e($category_slug); ?>?brand=<?php echo e($brand['slug']); ?>&sort=<?php echo $sort_option; ?>" class="filter-btn <?php echo ($brand['slug'] == $brand_slug) ? 'active' : ''; ?>">
                        <?php echo e($brand['ten_thuong_hieu']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="sort-filter">
            <form method="GET" action="<?php echo BASE_URL; ?>/danh-muc/<?php echo e($category_slug); ?>" id="sortForm">
                <input type="hidden" name="brand" value="<?php echo e($brand_slug); ?>">
                <label for="sort">Sắp xếp:</label>
                <select name="sort" id="sort" onchange="document.getElementById('sortForm').submit()">
                    <option value="view_desc" <?php selected('view_desc', $sort_option); ?>>Xem nhiều nhất</option>
                    <option value="default" <?php selected('default', $sort_option); ?>>Mới nhất</option>
                    <option value="price_asc" <?php selected('price_asc', $sort_option); ?>>Giá: Thấp đến cao</option>
                    <option value="price_desc" <?php selected('price_desc', $sort_option); ?>>Giá: Cao đến thấp</option>
                </select>
            </form>
        </div>
    </div>

    <section class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <?php render_product_card($product); ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class='grid-full-width'>Không có sản phẩm nào phù hợp với lựa chọn của bạn.</p>
        <?php endif; ?>
    </section>

    <?php
    if ($total_pages > 1) {
        $base_url_params = "brand=" . urlencode($brand_slug) . "&sort=" . urlencode($sort_option);
        $base_url = BASE_URL . "/danh-muc/" . e($category_slug) . "?" . $base_url_params . "&";
        echo generate_pagination($base_url, $total_pages, $current_page);
    }
    ?>
<?php endif; ?>

