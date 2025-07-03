<div class="search-results-page">
    <div class="page-title">
        <h1>Kết quả tìm kiếm</h1>
        <?php if (!empty($search_query)): ?>
            <p>Cho từ khóa: "<strong><?php echo e($search_query); ?></strong>"</p>
            <p class="results-count">Tìm thấy <?php echo $total_products; ?> sản phẩm</p>
        <?php endif; ?>
    </div>

    <section class="product-grid">
        <?php if (isset($error_message)): ?>
            <p class='grid-full-width'><?php echo $error_message; ?></p>
        <?php elseif (!empty($search_query)): ?>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <?php render_product_card($product); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h2>Không tìm thấy sản phẩm nào</h2>
                    <p>Rất tiếc, chúng tôi không tìm thấy sản phẩm nào khớp với từ khóa của bạn. Vui lòng thử lại với từ khóa khác.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-keyboard"></i>
                <h2>Vui lòng nhập từ khóa</h2>
                <p>Sử dụng thanh tìm kiếm phía trên để tìm sản phẩm bạn mong muốn.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php
    if (isset($total_pages) && $total_pages > 1) {
        $base_pagination_url = BASE_URL . '/timkiem?q=' . urlencode($search_query) . '&';
        echo generate_pagination($base_pagination_url, $total_pages, $current_page);
    }
    ?>
</div>

