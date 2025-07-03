<?php
require_once __DIR__ . '/includes/header.php';

// Lấy và làm sạch các tham số từ URL
$category_slug = filter_var($_GET['slug'] ?? '', FILTER_SANITIZE_STRING);
$brand_slug = filter_var($_GET['brand'] ?? '', FILTER_SANITIZE_STRING);
$sort_option = $_GET['sort'] ?? 'view_desc';
$current_page = isset($_GET['page']) && filter_var($_GET['page'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    ? (int)$_GET['page']
    : 1;
$offset = ($current_page - 1) * PRODUCTS_PER_PAGE;

$valid_sort_options = ['default', 'price_asc', 'price_desc', 'view_desc'];
if (!in_array($sort_option, $valid_sort_options)) {
    $sort_option = 'view_desc';
}

if (empty($category_slug)) {
    safe_redirect('/');
}

$category = null;
$products = [];
$total_products = 0;
$total_pages = 0;
$brands = [];

try {
    // Lấy thông tin danh mục
    $stmt_cat = $pdo->prepare("SELECT id, ten_danh_muc FROM danh_muc WHERE slug = ? AND is_active = 1");
    $stmt_cat->execute([$category_slug]);
    $category = $stmt_cat->fetch();

    if ($category) {
        $category_id = $category['id'];
        $page_title = htmlspecialchars($category['ten_danh_muc']);

        // [UPDATED] SQL query to correctly determine the display price for all product types
        $base_sql_select = "
            SELECT 
                sp.id, sp.slug, sp.ten_san_pham, sp.loai_san_pham, 
                (
                    CASE
                        WHEN sp.loai_san_pham = 'variable' THEN (SELECT MIN(pv.gia) FROM product_variants pv WHERE pv.san_pham_id = sp.id AND pv.gia > 0)
                        ELSE IF(sp.gia_khuyen_mai > 0, sp.gia_khuyen_mai, sp.gia_goc)
                    END
                ) as display_price,
                ha.url_hinh_anh, 
                sp.luot_xem
        ";
        $base_sql_count = "SELECT COUNT(DISTINCT sp.id)";
        $base_sql_from = " FROM san_pham sp LEFT JOIN hinh_anh_san_pham ha ON sp.id = ha.san_pham_id AND ha.la_anh_dai_dien = 1";
        $base_sql_where = " WHERE sp.danh_muc_id = :category_id AND sp.is_active = 1";

        $params = ['category_id' => $category_id];

        // Áp dụng bộ lọc thương hiệu
        if (!empty($brand_slug)) {
            $stmt_brand_id = $pdo->prepare("SELECT id FROM thuong_hieu WHERE slug = ?");
            $stmt_brand_id->execute([$brand_slug]);
            if ($current_brand_id = $stmt_brand_id->fetchColumn()) {
                $base_sql_where .= " AND sp.thuong_hieu_id = :brand_id";
                $params['brand_id'] = $current_brand_id;
            } else {
                $brand_slug = '';
            }
        }

        // Đếm tổng số sản phẩm
        $count_sql = $base_sql_count . $base_sql_from . $base_sql_where;
        $stmt_count = $pdo->prepare($count_sql);
        $stmt_count->execute($params);
        $total_products = $stmt_count->fetchColumn();
        $total_pages = ceil($total_products / PRODUCTS_PER_PAGE);

        // Thêm điều kiện sắp xếp
        $order_by = " ORDER BY ";
        switch ($sort_option) {
            case 'price_asc':
                $order_by .= "display_price ASC, sp.ngay_tao DESC";
                break;
            case 'price_desc':
                $order_by .= "display_price DESC, sp.ngay_tao DESC";
                break;
            case 'view_desc':
                $order_by .= "sp.luot_xem DESC, sp.ngay_tao DESC";
                break;
            default: // 'default' tương ứng với Mới nhất
                $order_by .= "sp.ngay_tao DESC";
                break;
        }

        // Thêm phân trang và thực thi truy vấn lấy sản phẩm
        $limit_offset = " LIMIT :limit OFFSET :offset";
        $sql = $base_sql_select . $base_sql_from . $base_sql_where . " GROUP BY sp.id" . $order_by . $limit_offset;

        $stmt_products = $pdo->prepare($sql);
        $stmt_products->bindValue(':limit', PRODUCTS_PER_PAGE, PDO::PARAM_INT);
        $stmt_products->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => &$val) {
            $stmt_products->bindParam($key, $val);
        }
        $stmt_products->execute();
        $products = $stmt_products->fetchAll();

        // Lấy danh sách thương hiệu trong danh mục này để hiển thị bộ lọc
        $stmt_brands = $pdo->prepare("SELECT DISTINCT th.ten_thuong_hieu, th.slug
                                      FROM thuong_hieu th
                                      JOIN danhmuc_thuonghieu dt ON th.id = dt.thuong_hieu_id
                                      WHERE dt.danh_muc_id = ? ORDER BY th.ten_thuong_hieu ASC");
        $stmt_brands->execute([$category_id]);
        $brands = $stmt_brands->fetchAll();
    } else {
        http_response_code(404);
        $page_title = "404 - Không tìm thấy danh mục";
    }
} catch (PDOException $e) {
    http_response_code(500);
    $error_message = "<p>Lỗi cơ sở dữ liệu.</p>";
    if (defined('APP_ENV') && APP_ENV === 'development') {
        error_log($e->getMessage());
    }
}
?>

<?php if ($category): ?>
    <div class="breadcrumbs">
        <a href="<?php echo BASE_URL; ?>/">Trang chủ</a>
        <span>&gt;</span>
        <span class="active"><?php echo htmlspecialchars($category['ten_danh_muc']); ?></span>
    </div>

    <div class="filter-bar">
        <?php if (!empty($brands)): ?>
            <div class="brand-filter">
                <span class="filter-label">Thương hiệu:</span>
                <a href="<?php echo BASE_URL; ?>/danh-muc/<?php echo htmlspecialchars($category_slug); ?>?sort=<?php echo $sort_option; ?>" class="filter-btn <?php echo empty($brand_slug) ? 'active' : ''; ?>">Tất cả</a>
                <?php foreach ($brands as $brand): ?>
                    <a href="<?php echo BASE_URL; ?>/danh-muc/<?php echo htmlspecialchars($category_slug); ?>?brand=<?php echo htmlspecialchars($brand['slug']); ?>&sort=<?php echo $sort_option; ?>" class="filter-btn <?php echo ($brand['slug'] == $brand_slug) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($brand['ten_thuong_hieu']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="sort-filter">
            <form method="GET" action="<?php echo BASE_URL; ?>/danh-muc/<?php echo htmlspecialchars($category_slug); ?>" id="sortForm">
                <input type="hidden" name="brand" value="<?php echo htmlspecialchars($brand_slug); ?>">
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
        $base_url = BASE_URL . "/danh-muc/" . htmlspecialchars($category_slug) . "?" . $base_url_params . "&";
        echo generate_pagination($base_url, $total_pages, $current_page);
    }
    ?>

<?php elseif (isset($error_message)): ?>
    <div class="page-title">
        <h1>Lỗi</h1>
        <?php echo $error_message; ?>
    </div>
<?php else: ?>
    <div class="page-title">
        <h1>Không tìm thấy danh mục</h1>
        <p>Danh mục bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>