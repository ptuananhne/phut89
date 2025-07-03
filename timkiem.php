<?php
require_once __DIR__ . '/includes/header.php';

$search_query = trim($_GET['q'] ?? '');
$current_page = isset($_GET['page']) && filter_var($_GET['page'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    ? (int)$_GET['page']
    : 1;
$offset = ($current_page - 1) * PRODUCTS_PER_PAGE;

$products = [];
$total_products = 0;
$total_pages = 0;

if (!empty($search_query)) {
    try {
        $search_terms = explode(' ', $search_query);
        $search_conditions = [];
        $params = [];
        foreach ($search_terms as $term) {
            if (!empty($term)) {
                $search_conditions[] = "sp.ten_san_pham LIKE ?";
                $params[] = '%' . $term . '%';
            }
        }
        $where_clause = implode(' AND ', $search_conditions);

        // Đếm tổng số sản phẩm
        $count_sql = "SELECT COUNT(DISTINCT sp.id) FROM san_pham sp WHERE ({$where_clause}) AND sp.is_active = 1";
        $stmt_count = $pdo->prepare($count_sql);
        $stmt_count->execute($params);
        $total_products = $stmt_count->fetchColumn();
        $total_pages = ceil($total_products / PRODUCTS_PER_PAGE);

        // [UPDATED] Lấy sản phẩm cho trang hiện tại với logic giá mới
        $sql = "
            SELECT 
                sp.id, sp.slug, sp.ten_san_pham, sp.loai_san_pham,
                (CASE
                    WHEN sp.loai_san_pham = 'variable' THEN (SELECT MIN(pv.gia) FROM product_variants pv WHERE pv.san_pham_id = sp.id AND pv.gia > 0)
                    ELSE IF(sp.gia_khuyen_mai > 0, sp.gia_khuyen_mai, sp.gia_goc)
                END) as display_price,
                ha.url_hinh_anh
            FROM san_pham sp
            LEFT JOIN hinh_anh_san_pham ha ON sp.id = ha.san_pham_id AND ha.la_anh_dai_dien = 1
            WHERE ({$where_clause}) AND sp.is_active = 1
            GROUP BY sp.id
            ORDER BY sp.luot_xem DESC, sp.ngay_tao DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $pdo->prepare($sql);

        $i = 1;
        foreach ($params as $p) {
            $stmt->bindValue($i++, $p);
        }
        $stmt->bindValue($i++, PRODUCTS_PER_PAGE, PDO::PARAM_INT);
        $stmt->bindValue($i, $offset, PDO::PARAM_INT);

        $stmt->execute();
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "<p class='grid-full-width'>Lỗi cơ sở dữ liệu.</p>";
        if (defined('APP_ENV') && APP_ENV === 'development') {
            error_log($e->getMessage());
        }
    }
}
$page_title = "Kết quả tìm kiếm cho '" . htmlspecialchars($search_query) . "'";
?>

<div class="search-results-page">
    <div class="page-title">
        <h1>Kết quả tìm kiếm</h1>
        <?php if (!empty($search_query)): ?>
            <p>Cho từ khóa: "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
            <p class="results-count">Tìm thấy <?php echo $total_products; ?> sản phẩm</p>
        <?php endif; ?>
    </div>

    <section class="product-grid">
        <?php if (isset($error_message)): ?>
            <?php echo $error_message; ?>
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
    if ($total_pages > 1) {
        $base_pagination_url = BASE_URL . '/timkiem?q=' . urlencode($search_query) . '&';
        echo generate_pagination($base_pagination_url, $total_pages, $current_page);
    }
    ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>