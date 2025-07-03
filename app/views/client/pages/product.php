<?php
// FILE: /app/views/pages/product.php
// MÔ TẢ: Giao diện chi tiết sản phẩm đã được thiết kế lại theo bố cục mới.

if (isset($product)) {
    $product_url = BASE_URL . "/san-pham/" . e($product['slug']);
    $zalo_message = "Chào shop, tôi quan tâm đến sản phẩm: " . e($product['ten_san_pham']) . ". Link: " . $product_url;
    $messenger_message = "Chào shop, tôi quan tâm đến sản phẩm: " . e($product['ten_san_pham']) . ". Link: " . $product_url;
}

?>

<?php if (isset($product)): ?>

    <main class="container">
        <!-- ===== KHU VỰC CHÍNH (Trên) ===== -->
        <div class="product-page-layout">
            <!-- ===== CỘT BÊN TRÁI: THƯ VIỆN ẢNH ===== -->
            <div class="product-gallery-container">
                <div class="product-gallery">
                    <div class="main-image">
                        <img id="mainProductImage" src="<?php echo !empty($all_images) ? BASE_URL . '/uploads/products/' . e($all_images[0]['url_hinh_anh']) : 'https://placehold.co/600x600/f1f1f1/333?text=No+Image'; ?>" alt="<?php echo e($product['ten_san_pham']); ?>">
                    </div>
                    <?php if (count($all_images) > 1): ?>
                        <div class="thumbnail-images" id="thumbnailContainer">
                            <?php foreach ($all_images as $image): ?>
                                <?php $thumb_path = !empty($image['url_hinh_anh']) ? 'thumbs/' . e($image['url_hinh_anh']) : e($image['url_hinh_anh']); ?>
                                <img src="<?php echo BASE_URL . '/uploads/products/' . $thumb_path; ?>"
                                    data-full-src="<?php echo BASE_URL . '/uploads/products/' . e($image['url_hinh_anh']); ?>"
                                    alt="Thumbnail <?php echo e($product['ten_san_pham']); ?>"
                                    class="<?php echo $image === $all_images[0] ? 'active' : ''; ?>">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===== CỘT BÊN PHẢI: THÔNG TIN & HÀNH ĐỘNG ===== -->
            <div class="product-details-container">
                <div class="breadcrumbs">
                    <a href="<?php echo BASE_URL; ?>/">Trang chủ</a>
                    <span>›</span>
                    <a href="<?php echo BASE_URL; ?>/danh-muc/<?php echo e($product['category_slug']); ?>"><?php echo e($product['ten_danh_muc']); ?></a>
                    <span>›</span>
                    <span class="active"><?php echo e($product['ten_san_pham']); ?></span>
                </div>

                <h1 class="product-title" id="productName"><?php echo e($product['ten_san_pham']); ?></h1>

                <div class="price-section">
                    <div class="price-box">
                        <span class="sale-price" id="productPrice">
                            <?php echo ($product['loai_san_pham'] === 'simple') ? number_format($product['gia_khuyen_mai'] > 0 ? $product['gia_khuyen_mai'] : $product['gia_goc'], 0, ',', '.') . ' ₫' : '0 ₫'; ?>
                        </span>
                        <span class="original-price" id="productOldPrice">
                            <?php if ($product['loai_san_pham'] === 'simple' && $product['gia_khuyen_mai'] > 0 && $product['gia_goc'] > $product['gia_khuyen_mai']): ?>
                                <?php echo number_format($product['gia_goc'], 0, ',', '.'); ?> ₫
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="discount-wrapper" id="discountPercentageWrapper"></div>
                </div>

                <p class="stock-info">Tình trạng:
                    <span class="stock-status <?php echo ($product['so_luong_ton'] > 0 || $product['loai_san_pham'] === 'variable') ? 'in-stock' : 'out-of-stock'; ?>" id="stockStatus">
                        <?php echo ($product['loai_san_pham'] === 'simple' && $product['so_luong_ton'] <= 0) ? 'Hết hàng' : 'Còn hàng'; ?>
                    </span>
                </p>

                <?php if ($product['loai_san_pham'] === 'variable'): ?>
                    <div class="variant-options" id="variantOptionsContainer">
                        <!-- JS sẽ render các tùy chọn vào đây -->
                    </div>
                <?php endif; ?>

                <div class="contact-actions">
                    <p class="contact-title">Tư vấn & Mua hàng:</p>
                    <a href="https://zalo.me/0845115765" target="_blank" class="btn-contact zalo" data-copy-text="<?php echo e($zalo_message); ?>">
                        <i class="fa-brands fa-rocketchat"></i>
                        <div class="text"><span>Chat qua Zalo</span><small>Tự động sao chép link</small></div>
                    </a>
                    <a href="https://m.me/Phut89iPhone?text=<?php echo urlencode($messenger_message); ?>" target="_blank" class="btn-contact messenger">
                        <i class="fa-brands fa-facebook-messenger"></i>
                        <div class="text"><span>Chat qua Messenger</span><small>Gửi link sản phẩm</small></div>
                    </a>
                    <a href="tel:02623816889" class="btn-contact btn-call">
                        <i class="fa-solid fa-phone-volume"></i>
                        <div class="text"><span>Gọi 0262.381.6889</span><small>Hỗ trợ nhanh</small></div>
                    </a>
                </div>
            </div>
        </div>

        <!-- ===== KHU VỰC THÔNG TIN BỔ SUNG (Dưới) ===== -->
        <div class="product-extra-info">
            <div class="product-bottom-layout">
                <!-- Cột trái: Mô tả và thông số -->
                <div class="product-promo-wrapper">
                    <div class="promo-info-box">
                        <p class="promo-title"><i class="fa-solid fa-gift"></i> Ưu đãi đặc biệt</p>
                        <ul class="promo-list">
                            <li><i class="fa-solid fa-circle-check"></i> Hỗ trợ <strong>THU CŨ - ĐỔI MỚI</strong> trợ giá lên đến 2 triệu.</li>
                            <li><i class="fa-solid fa-circle-check"></i> Thu máy cũ <strong>GIÁ CAO</strong> đến gần 💯 giá trị máy đang bán.</li>
                            <li><i class="fa-solid fa-circle-check"></i> Hỗ trợ g.ó.p <strong>0Đ</strong>, phí lên đời cực thấp.</li>
                            <li><i class="fa-solid fa-circle-check"></i> Mua Online <strong>Freeship</strong> + giảm thêm + giao nhanh.</li>
                            <li><i class="fa-solid fa-circle-check"></i> Bảo hành <strong>1 ĐỔI 1</strong> đến 15 tháng.</li>
                        </ul>
                    </div>
                </div>
                <div class="product-description-wrapper">
                    <div class="product-tabs">
                        <div class="tab-headers">
                            <button class="tab-header" data-tab="description">Mô Tả Sản Phẩm</button>
                            <button class="tab-header active" data-tab="specs">Thông Số Kỹ Thuật</button>
                        </div>
                        <div class="tab-content" id="description">
                            <?php echo !empty($product['mo_ta_chi_tiet']) ? nl2br(e($product['mo_ta_chi_tiet'])) : '<p>Chưa có mô tả chi tiết cho sản phẩm này.</p>'; ?>
                        </div>
                        <div class="tab-content active" id="specs">
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
                    </div>
                </div>

                <!-- Cột phải: Ưu đãi đặc biệt -->

            </div>

            <?php if (!empty($related_products)): ?>
                <section class="related-products-section">
                    <div class="section-header">
                        <h2 class="section-title">Sản phẩm liên quan</h2>
                    </div>
                    <div class="product-grid">
                        <?php foreach ($related_products as $related): ?>
                            <?php render_product_card($related); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <?php if ($product['loai_san_pham'] === 'variable' && isset($variants_data_json) && $variants_data_json !== 'null' && $variants_data_json !== '[]'): ?>
        <script id="variantsData" type="application/json">
            <?php echo $variants_data_json; ?>
        </script>
    <?php endif; ?>

<?php else: ?>
    <div class="container">
        <p>Không tìm thấy sản phẩm.</p>
    </div>
<?php endif; ?>