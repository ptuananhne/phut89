<?php
require_once __DIR__ . '/includes/header.php';

// --- LẤY DỮ LIỆU CƠ BẢN ---
$slug = filter_var($_GET['slug'] ?? '', FILTER_SANITIZE_STRING);
if (empty($slug)) {
    safe_redirect('/');
}

$variants_data_json = 'null';
$product = null;
$attributes = [];
$related_products = [];

try {
    // 1. Lấy thông tin sản phẩm cha
    $stmt_product = $pdo->prepare("
        SELECT sp.*, dm.ten_danh_muc, dm.slug AS category_slug, th.ten_thuong_hieu
        FROM san_pham sp
        LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id
        LEFT JOIN thuong_hieu th ON sp.thuong_hieu_id = th.id
        WHERE sp.slug = ? AND sp.is_active = 1
    ");
    $stmt_product->execute([$slug]);
    $product = $stmt_product->fetch(PDO::FETCH_ASSOC);

    // 2. Nếu không tìm thấy sản phẩm, hiển thị 404
    if (!$product) {
        http_response_code(404);
        $page_title = "404 - Không tìm thấy sản phẩm";
        echo "<h1>404 Not Found</h1><p>Sản phẩm bạn tìm kiếm không tồn tại.</p>";
        require_once __DIR__ . '/includes/footer.php';
        exit;
    }

    $product_id = $product['id'];
    $category_id = $product['danh_muc_id'];
    $page_title = e($product['ten_san_pham']);
    $page_description = e($product['mo_ta_ngan']);

    // 3. Cập nhật lượt xem
    $pdo->prepare("UPDATE san_pham SET luot_xem = luot_xem + 1 WHERE id = ?")->execute([$product_id]);

    // 4. Lấy toàn bộ ảnh của sản phẩm cha (cho gallery)
    $stmt_all_images = $pdo->prepare("SELECT id, url_hinh_anh FROM hinh_anh_san_pham WHERE san_pham_id = ? ORDER BY la_anh_dai_dien DESC, id ASC");
    $stmt_all_images->execute([$product_id]);
    $all_images = $stmt_all_images->fetchAll(PDO::FETCH_ASSOC);

    // 5. XỬ LÝ SẢN PHẨM CÓ BIẾN THỂ
    if ($product['loai_san_pham'] === 'variable') {
        $stmt_variants = $pdo->prepare("SELECT * FROM product_variants WHERE san_pham_id = ?");
        $stmt_variants->execute([$product_id]);
        $variants = $stmt_variants->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($variants)) {
            $variant_ids = array_column($variants, 'id');
            $placeholders = implode(',', array_fill(0, count($variant_ids), '?'));

            $stmt_variant_values = $pdo->prepare("
                SELECT vov.variant_id, gtt.id as value_id, gtt.thuoc_tinh_id as option_id, gtt.gia_tri, gtt.hinh_anh
                FROM variant_option_values vov
                JOIN gia_tri_thuoc_tinh_bien_the gtt ON vov.value_id = gtt.id
                WHERE vov.variant_id IN ($placeholders)
            ");
            $stmt_variant_values->execute($variant_ids);
            $variant_value_map_raw = $stmt_variant_values->fetchAll(PDO::FETCH_ASSOC);

            $option_ids = array_unique(array_column($variant_value_map_raw, 'option_id'));
            $options = [];
            if (!empty($option_ids)) {
                $placeholders_opts = implode(',', array_fill(0, count($option_ids), '?'));
                $stmt_options = $pdo->prepare("SELECT id, ten_thuoc_tinh as name FROM thuoc_tinh_bien_the WHERE id IN ($placeholders_opts)");
                $stmt_options->execute(array_values($option_ids));
                $options = $stmt_options->fetchAll(PDO::FETCH_ASSOC);
            }

            $variant_value_map = [];
            foreach ($variant_value_map_raw as $row) {
                $variant_value_map[$row['variant_id']][] = $row['value_id'];
            }

            $variants_for_json = array_map(function ($variant) use ($variant_value_map) {
                return [
                    'id' => (int)$variant['id'],
                    'options' => $variant_value_map[$variant['id']] ?? [],
                    'price' => (int)$variant['gia'],
                    'old_price' => (int)$variant['gia_khuyen_mai'],
                    'stock' => (int)$variant['so_luong_ton'],
                    'image_id' => $variant['hinh_anh_id'] ? (int)$variant['hinh_anh_id'] : null,
                ];
            }, $variants);

            // SỬA LỖI: Đảm bảo các giá trị tùy chọn là duy nhất
            $unique_option_values = [];
            foreach ($variant_value_map_raw as $row) {
                $unique_option_values[$row['value_id']] = $row;
            }
            $option_values_for_json = array_map(function ($row) {
                return [
                    'id' => (int)$row['value_id'],
                    'option_id' => (int)$row['option_id'],
                    'value' => $row['gia_tri'],
                    'image' => $row['hinh_anh'] ? BASE_URL . '/uploads/swatches/' . e($row['hinh_anh']) : null
                ];
            }, array_values($unique_option_values));

            $images_for_json = array_map(function ($img) {
                $thumb_path = !empty($img['url_hinh_anh']) ? 'thumbs/' . e($img['url_hinh_anh']) : e($img['url_hinh_anh']);
                return [
                    'id' => (int)$img['id'],
                    'url' => BASE_URL . '/uploads/products/' . e($img['url_hinh_anh']),
                    'thumb' => BASE_URL . '/uploads/products/' . $thumb_path
                ];
            }, $all_images);

            $variants_data = [
                'productName' => e($product['ten_san_pham']),
                'options' => $options,
                'optionValues' => $option_values_for_json,
                'variants' => $variants_for_json,
                'images' => $images_for_json
            ];
            $variants_data_json = json_encode($variants_data, JSON_UNESCAPED_UNICODE);
        }
    }

    // 6. Lấy thông số kỹ thuật (dùng cấu trúc cũ)
    $stmt_attrs = $pdo->prepare("
        SELECT tt.ten_thuoc_tinh, gtt.gia_tri
        FROM gia_tri_thuoc_tinh gtt
        JOIN thuoc_tinh tt ON gtt.thuoc_tinh_id = tt.id
        WHERE gtt.san_pham_id = ? ORDER BY tt.id
    ");
    $stmt_attrs->execute([$product_id]);
    $attributes = $stmt_attrs->fetchAll(PDO::FETCH_ASSOC);

    // 7. Lấy sản phẩm liên quan
    if ($category_id) {
        $stmt_related = $pdo->prepare("
            SELECT sp.slug, sp.ten_san_pham, sp.gia_goc, sp.gia_khuyen_mai, ha.url_hinh_anh
            FROM san_pham sp
            LEFT JOIN hinh_anh_san_pham ha ON sp.id = ha.san_pham_id AND ha.la_anh_dai_dien = 1
            WHERE sp.danh_muc_id = ? AND sp.id != ? AND sp.is_active = 1
            GROUP BY sp.id
            ORDER BY RAND()
            LIMIT 4
        ");
        $stmt_related->execute([$category_id, $product_id]);
        $related_products = $stmt_related->fetchAll(PDO::FETCH_ASSOC);
    }

    // 8. Chuẩn bị các biến cho nút liên hệ (giữ từ code gốc)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $product_url = $protocol . "://" . $host . BASE_URL . "/san-pham/" . $product['slug'];
    $zalo_message = "Chào shop, tôi quan tâm đến sản phẩm: " . $product['ten_san_pham'] . ". Link: " . $product_url;
    $messenger_message = "Chào shop, tôi quan tâm đến sản phẩm: " . $product['ten_san_pham'] . ". Link: " . $product_url;
} catch (PDOException $e) {
    http_response_code(500);
    $page_title = "Lỗi hệ thống";
    echo "<h1>Lỗi hệ thống</h1><p>Đã xảy ra lỗi khi tải dữ liệu sản phẩm.</p>";
    if (APP_ENV === 'development') {
        error_log($e->getMessage());
    }
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
?>

<div class="breadcrumbs">
    <a href="<?php echo BASE_URL; ?>/">Trang chủ</a>
    <span>&gt;</span>
    <a href="<?php echo BASE_URL; ?>/danh-muc/<?php echo e($product['category_slug']); ?>"><?php echo e($product['ten_danh_muc']); ?></a>
    <span>&gt;</span>
    <span class="active"><?php echo e($product['ten_san_pham']); ?></span>
</div>

<div class="product-detail-layout">
    <div class="product-gallery">
        <div class="main-image">
            <img id="mainProductImage" src="<?php echo !empty($all_images) ? BASE_URL . '/uploads/products/' . e($all_images[0]['url_hinh_anh']) : 'https://placehold.co/600x600/f1f1f1/333?text=No+Image'; ?>" alt="<?php echo e($product['ten_san_pham']); ?>">
        </div>
        <div class="thumbnail-images" id="thumbnailContainer">
            <?php foreach ($all_images as $image): ?>
                <?php $thumb_path = !empty($image['url_hinh_anh']) ? 'thumbs/' . e($image['url_hinh_anh']) : e($image['url_hinh_anh']); ?>
                <img src="<?php echo BASE_URL . '/uploads/products/' . $thumb_path; ?>"
                    data-full-src="<?php echo BASE_URL . '/uploads/products/' . e($image['url_hinh_anh']); ?>"
                    alt="Thumbnail"
                    class="<?php echo $image === $all_images[0] ? 'active' : ''; ?>">
            <?php endforeach; ?>
        </div>
    </div>

    <div class="product-info">
        <h1 class="product-title" id="productName"><?php echo e($product['ten_san_pham']); ?></h1>

        <div class="price-box">
            <span class="sale-price" id="productPrice">
                <?php echo ($product['loai_san_pham'] === 'simple') ? number_format($product['gia_khuyen_mai'] > 0 ? $product['gia_khuyen_mai'] : $product['gia_goc'], 0, ',', '.') . ' VNĐ' : '0 VNĐ'; ?>
            </span>
            <?php if ($product['loai_san_pham'] === 'simple' && $product['gia_khuyen_mai'] > 0): ?>
                <span class="original-price" id="productOldPrice"><?php echo number_format($product['gia_goc'], 0, ',', '.'); ?> VNĐ</span>
            <?php else: ?>
                <span class="original-price" id="productOldPrice"></span>
            <?php endif; ?>
        </div>

        <p><strong>Tình trạng:</strong>
            <span class="stock-status <?php echo ($product['so_luong_ton'] > 0 || $product['loai_san_pham'] === 'variable') ? 'in-stock' : 'out-of-stock'; ?>" id="stockStatus">
                <?php echo ($product['loai_san_pham'] === 'simple' && $product['so_luong_ton'] <= 0) ? 'Hết hàng' : 'Còn hàng'; ?>
            </span>
        </p>

        <?php if ($product['loai_san_pham'] === 'variable'): ?>
            <div class="variant-options" id="variantOptionsContainer"></div>
        <?php endif; ?>


    </div>

</div>
<div class="contact-buy">
    <div class="contact-actions">
        <a href="https://zalo.me/0845115765" target="_blank" class="btn-contact zalo" data-copy-text="<?php echo e($zalo_message); ?>">
            <i class="fa-solid fa-comment-dots"></i>
            <div class="text"><span>Chat qua Zalo</span><small>Tự động sao chép link</small></div>
        </a>
        <a href="https://m.me/Phut89iPhone?text=<?php echo urlencode($messenger_message); ?>" target="_blank" class="btn-contact messenger">
            <i class="fab fa-facebook-messenger"></i>
            <div class="text"><span>Chat qua Messenger</span><small>Gửi link sản phẩm</small></div>
        </a>
        <a href="tel:02623816889" class="btn-contact btn-call">
            <i class="fas fa-phone-alt"></i>
            <div class="text"><span>Gọi 02623816889</span><small>Hỗ trợ nhanh</small></div>
        </a>
    </div>

    <div class="promo-info" style="margin-top: 1.5rem; font-size: 0.9rem; line-height: 1.8;">
        <p>• Hỗ trợ THU CŨ - ĐỔI MỚI trợ giá lên đến 2 triệu </p>
        <p>• Thu máy cũ GIÁ CAO đến gần 💯 giá trị máy đang bán ra </p>
        <p>• Hỗ trợ G.Ó.P đưa trước 0 đồng, phí lên đời máy mới </p>
        <p>• Mua Online Freeship + giảm thêm + giao nhanh tận nhà</p>
        <p>• Bảo hành 1 ĐỔI 1 lên đến 6 tháng với máy cũ, 15 tháng với máy mới</p>
        <p>🏠 Cầm Đồ PHÚT89 của hàng ĐIỆN THOẠI IPHONE cũ giá rẻ bmt dak lak !</p>
    </div>
</div>
<div class="product-tabs">
    <div class="tab-headers">
        <button class="tab-header" data-tab="specs">Thông Số Kỹ Thuật</button>
        <button class="tab-header active" data-tab="description">Mô Tả Sản Phẩm</button>
    </div>
    <div class="tab-content active" id="description">
        <div class="tab-content" id="specs">
            <?php if (!empty($attributes)): ?>
                <table>
                    <tbody>
                        <?php foreach ($attributes as $attr): ?>
                            <tr>
                                <th><?php echo e($attr['ten_thuoc_tinh']); ?></th>
                                <td><?php echo e($attr['gia_tri']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Chưa có thông số kỹ thuật cho sản phẩm này.</p>
            <?php endif; ?>
        </div>
        <?php echo !empty($product['mo_ta_chi_tiet']) ? nl2br(e($product['mo_ta_chi_tiet'])) : '<p>Chưa có mô tả chi tiết cho sản phẩm này.</p>'; ?>
    </div>

</div>

<?php if (!empty($related_products)): ?>
    <section class="category-showcase" style="margin-top: 2rem;">
        <div class="category-showcase-header">
            <h2 class="section-title">Sản phẩm liên quan</h2>
        </div>
        <div class="product-grid">
            <?php foreach ($related_products as $related): ?>
                <?php render_product_card($related); ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($product['loai_san_pham'] === 'variable' && $variants_data_json !== 'null'): ?>
    <script id="variantsData" type="application/json">
        <?php echo $variants_data_json; ?>
    </script>
<?php endif; ?>

<!-- SỬA LỖI: Nạp file JS riêng biệt cho trang sản phẩm -->
<script src="<?php echo BASE_URL; ?>/assets/js/product-detail.js?v=<?php echo time(); ?>"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>