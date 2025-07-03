<?php
$page_title = 'Trang chủ';
require_once __DIR__ . '/includes/header.php';

try {
    $banners = $pdo->query("SELECT * FROM banners WHERE trang_thai = 1 ORDER BY vi_tri ASC, id DESC")->fetchAll();
} catch (PDOException $e) {
    $banners = [];
}
?>

<?php if (!empty($banners)): ?>
    <?php if (count($banners) === 1): ?>
        <div class="single-banner-container">
            <a href="<?php echo e($banners[0]['lien_ket']); ?>" title="<?php echo e($banners[0]['tieu_de']); ?>">
                <img src="<?php echo BASE_URL; ?>/uploads/banners/<?php echo e($banners[0]['hinh_anh']); ?>"
                    alt="<?php echo e($banners[0]['tieu_de']); ?>"
                    style="width:100%; display:block; border-radius: var(--radius-md, 8px);">
            </a>
        </div>
    <?php else: ?>
        <div class="slideshow-container">
            <?php foreach ($banners as $index => $banner): ?>
                <div class="mySlides fade">
                    <a href="<?php echo e($banner['lien_ket']); ?>" title="<?php echo e($banner['tieu_de']); ?>">
                        <img src="<?php echo BASE_URL; ?>/uploads/banners/<?php echo e($banner['hinh_anh']); ?>"
                            alt="<?php echo e($banner['tieu_de']); ?>"
                            loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                    </a>
                </div>
            <?php endforeach; ?>
            <a class="prev" onclick="plusSlides(-1)">❮</a>
            <a class="next" onclick="plusSlides(1)">❯</a>
            <div class="dots-container" style="text-align:center">
                <?php foreach ($banners as $index => $banner): ?>
                    <span class="dot" onclick="currentSlide(<?php echo $index + 1; ?>)"></span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>


<div class="page-title">
    <h1>Chào mừng đến với <?php echo e(SITE_NAME); ?></h1>
</div>

<section class="category-showcase">
    <div class="category-showcase-header">
        <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
    </div>
    <div class="product-carousel-container">
        <div class="product-carousel-wrapper">
            <div class="product-grid">
                <?php
                try {
                    // [UPDATED] Query for featured products with new price logic
                    $sql = "SELECT sp.id, sp.slug, sp.ten_san_pham, sp.loai_san_pham, 
                                (CASE
                                    WHEN sp.loai_san_pham = 'variable' THEN (SELECT MIN(pv.gia) FROM product_variants pv WHERE pv.san_pham_id = sp.id AND pv.gia > 0)
                                    ELSE IF(sp.gia_khuyen_mai > 0, sp.gia_khuyen_mai, sp.gia_goc)
                                END) as display_price, 
                                ha.url_hinh_anh
                            FROM san_pham sp
                            LEFT JOIN hinh_anh_san_pham ha ON sp.id = ha.san_pham_id AND ha.la_anh_dai_dien = 1
                            WHERE sp.is_active = 1
                            GROUP BY sp.id
                            ORDER BY sp.luot_xem DESC, sp.ngay_tao DESC
                            LIMIT 12";
                    $stmt = $pdo->query($sql);
                    $featured_products = $stmt->fetchAll();

                    if (!empty($featured_products)) {
                        foreach ($featured_products as $product) {
                            render_product_card($product);
                        }
                    } else {
                        echo "<p class='grid-full-width'>Không có sản phẩm nổi bật nào để hiển thị.</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p class='grid-full-width'>Lỗi khi tải sản phẩm.</p>";
                    if (defined('APP_ENV') && APP_ENV === 'development') {
                        error_log($e->getMessage());
                    }
                }
                ?>
            </div>
        </div>
        <button class="carousel-nav-btn prev" aria-label="Sản phẩm trước">❮</button>
        <button class="carousel-nav-btn next" aria-label="Sản phẩm tiếp theo">❯</button>
    </div>
</section>

<?php
try {
    $categories = $pdo->query("SELECT * FROM danh_muc WHERE is_active = 1 ORDER BY vi_tri ASC, id ASC")->fetchAll();

    foreach ($categories as $category):
        // [UPDATED] Query for products by category with new price logic
        $stmt = $pdo->prepare("SELECT sp.id, sp.slug, sp.ten_san_pham, sp.loai_san_pham, 
                                   (CASE
                                       WHEN sp.loai_san_pham = 'variable' THEN (SELECT MIN(pv.gia) FROM product_variants pv WHERE pv.san_pham_id = sp.id AND pv.gia > 0)
                                       ELSE IF(sp.gia_khuyen_mai > 0, sp.gia_khuyen_mai, sp.gia_goc)
                                   END) as display_price,
                                   ha.url_hinh_anh
                               FROM san_pham sp
                               LEFT JOIN hinh_anh_san_pham ha ON sp.id = ha.san_pham_id AND ha.la_anh_dai_dien = 1
                               WHERE sp.danh_muc_id = ? AND sp.is_active = 1
                               GROUP BY sp.id
                               ORDER BY sp.luot_xem DESC, sp.ngay_tao DESC LIMIT 7");
        $stmt->execute([$category['id']]);
        $products_by_cat = $stmt->fetchAll();

        if (empty($products_by_cat)) continue;
?>
        <section class="category-showcase">
            <div class="category-showcase-header">
                <h2 class="section-title"><?php echo e($category['ten_danh_muc']); ?></h2>
                <a href="<?php echo BASE_URL; ?>/danh-muc/<?php echo e($category['slug']); ?>" class="view-all-link">
                    Xem tất cả <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="product-carousel-container">
                <div class="product-carousel-wrapper">
                    <div class="product-grid">
                        <?php foreach ($products_by_cat as $product): ?>
                            <?php render_product_card($product); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button class="carousel-nav-btn prev" aria-label="Sản phẩm trước">❮</button>
                <button class="carousel-nav-btn next" aria-label="Sản phẩm tiếp theo">❯</button>
            </div>
        </section>
<?php endforeach;
} catch (PDOException $e) {
    echo "<p>Lỗi khi tải danh mục sản phẩm.</p>";
    if (defined('APP_ENV') && APP_ENV === 'development') {
        error_log($e->getMessage());
    }
}
?>

<section class="map-section">
    <h2 class="section-title" style="text-align: center;">Hệ thống Cửa hàng <?php echo e(SITE_NAME); ?></h2>
    <div class="map-tabs">
        <button class="map-tab-btn active" data-map="map1">Chi nhánh 1: 41B Lê Duẩn, BMT</button>
        <button class="map-tab-btn" data-map="map2">Chi nhánh 2: 323 Phan Chu Trinh, BMT</button>
    </div>
    <div class="map-content">
        <div id="map1" class="map-pane active">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3892.684959077661!2d108.0410607!3d12.6686496!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31721d185f5a77a1%3A0xc7ccfe798e4d37a6!2zQ-G6p20gxJDhu5MgUGjDunQgODk!5e0!3m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Bản đồ chi nhánh 1"></iframe>
        </div>
        <div id="map2" class="map-pane">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3290.111758933799!2d108.05111430806635!3d12.691476296416534!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3171f7d90106403f%3A0xcb0a19e88c19dd51!2zMzIzIFBoYW4gQ2h1IFRyaW5oLCBUw6NuIEzhu6NpLCBCdcO0biBNYSBUaHVvYywgxJDhuq9rIEzhuqdjIDYzMDAwMCwgVmlF4buHdCBOYW0!5e0!3m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Bản đồ chi nhánh 2"></iframe>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>