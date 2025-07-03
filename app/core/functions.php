<?php

/**
 * Hàm tiện ích để mã hóa (escape) output HTML, tránh lỗi XSS.
 */
function e(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Chuyển đổi một chuỗi thành dạng slug thân thiện với URL.
 */
function generate_slug(string $str): string
{
    if (!$str) return '';
    $from = "àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ·/_,:;";
    $to   = "aaaaaaaaaaaaaaaaaeeeeeeeeeeeiiiiiooooooooooooooooouuuuuuuuuuuyyyyyd------";
    $str = mb_strtolower($str, 'UTF-8');
    $char_map = [];
    $from_chars = preg_split('//u', $from, -1, PREG_SPLIT_NO_EMPTY);
    $to_chars   = preg_split('//u', $to,   -1, PREG_SPLIT_NO_EMPTY);
    for ($i = 0; $i < count($from_chars); $i++) {
        $char_map[$from_chars[$i]] = $to_chars[$i];
    }
    $str = strtr($str, $char_map);
    $str = preg_replace('/[^a-z0-9 \-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}

/**
 * In ra 'selected' nếu hai giá trị bằng nhau, dùng cho thẻ <option>.
 */
function selected($currentValue, $optionValue): void
{
    if ((string)$currentValue === (string)$optionValue) {
        echo ' selected';
    }
}

/**
 * [CRITICAL FIX] Hiển thị một thẻ sản phẩm với logic giá và % giảm giá đầy đủ từ phiên bản cũ.
 */
function render_product_card(array $product): void
{
    $product_url = BASE_URL . '/san-pham/' . e($product['slug'] ?? 'loi-san-pham');
    $product_name = e($product['ten_san_pham'] ?? 'Sản phẩm không tên');
    $image_url = !empty($product['url_hinh_anh'])
        ? BASE_URL . '/uploads/products/' . e($product['url_hinh_anh'])
        : 'https://placehold.co/400x400/e0e0e0/333?text=No+Image';

    // Logic hiển thị giá và huy hiệu giảm giá
    $price_html = '';
    $sale_badge_html = '';

    $is_simple = ($product['loai_san_pham'] ?? 'simple') === 'simple';
    $sale_price = (float)($product['gia_khuyen_mai'] ?? 0);
    $original_price = (float)($product['gia_goc'] ?? 0);
    $display_price = (float)($product['display_price'] ?? 0);

    // Trường hợp 1: Sản phẩm đơn giản và có khuyến mãi hợp lệ
    if ($is_simple && $sale_price > 0 && $original_price > $sale_price) {
        $discount_percentage = round((($original_price - $sale_price) / $original_price) * 100);
        $sale_badge_html = "<div class=\"sale-badge\">-{$discount_percentage}%</div>";

        $price_html = '<div class="product-card-price has-sale">'
            . '<span class="sale-price">' . number_format($sale_price, 0, ',', '.') . ' ₫</span>'
            . '<span class="original-price">' . number_format($original_price, 0, ',', '.') . ' ₫</span>'
            . '</div>';
    }
    // Trường hợp 2: Sản phẩm biến thể hoặc không có khuyến mãi
    else {
        $price_to_show = $display_price > 0 ? $display_price : $original_price;
        $formatted_price = 'Giá: Liên hệ';
        if ($price_to_show > 0) {
            $formatted_price = number_format($price_to_show, 0, ',', '.') . ' ₫';
            if (($product['loai_san_pham'] ?? '') === 'variable') {
                $formatted_price = 'Từ ' . $formatted_price;
            }
        }
        $price_html = '<div class="product-card-price">' . $formatted_price . '</div>';
    }

    echo <<<HTML
    <div class="product-card">
        <a href="{$product_url}" class="product-card-image-wrapper">
            {$sale_badge_html}
            <img src="{$image_url}" alt="{$product_name}" class="product-card-image" loading="lazy">
        </a>
        <div class="product-card-content">
            <h3 class="product-card-title">
                <a href="{$product_url}">{$product_name}</a>
            </h3>
            {$price_html}
            <a href="{$product_url}" class="btn">Xem chi tiết</a>
        </div>
    </div>
    HTML;
}

/**
 * Tạo chuỗi HTML cho phân trang.
 */
function generate_pagination(string $base_url, int $total_pages, int $current_page): string
{
    if ($total_pages <= 1) return '';

    if (strpos($base_url, '?') === false) {
        $base_url .= '?';
    } elseif (substr($base_url, -1) !== '&' && substr($base_url, -1) !== '?') {
        $base_url .= '&';
    }

    $html = '<nav class="pagination">';

    if ($current_page > 1) {
        $html .= '<a href="' . $base_url . 'page=' . ($current_page - 1) . '">&laquo;</a>';
    }

    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="active">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $base_url . 'page=' . $i . '">' . $i . '</a>';
        }
    }

    if ($current_page < $total_pages) {
        $html .= '<a href="' . $base_url . 'page=' . ($current_page + 1) . '">&raquo;</a>';
    }

    $html .= '</nav>';
    return $html;
}

/**
 * Lấy icon FontAwesome cho một slug danh mục cụ thể.
 */
function get_icon_for_category(string $slug): string
{
    if (strpos($slug, 'dien-thoai') !== false) return 'fa-mobile-alt';
    if (strpos($slug, 'laptop') !== false) return 'fa-laptop';
    if (strpos($slug, 'o-to') !== false) return 'fa-car';
    if (strpos($slug, 'xe-may') !== false) return 'fa-motorcycle';
    if (strpos($slug, 'phu-kien') !== false) return 'fa-headphones';
    if (strpos($slug, 'may-tinh-bang') !== false) return 'fa-tablet-alt';
    return 'fa-tag'; // Icon mặc định
}
