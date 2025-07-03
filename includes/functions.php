<?php

/**
 * Hàm tiện ích để escape HTML output, tránh lỗi XSS.
 *
 * @param string|null $string Chuỗi cần escape.
 * @return string Chuỗi đã được làm sạch.
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Chuyển đổi một chuỗi thành dạng slug thân thiện với URL.
 *
 * @param string $str Chuỗi cần chuyển đổi.
 * @return string Slug đã được tạo.
 */
function generate_slug($str)
{
    if (!$str) {
        return '';
    }

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
    $str = trim($str, '-');

    return $str;
}

/**
 * In ra 'selected' nếu hai giá trị bằng nhau.
 */
function selected($currentValue, $optionValue)
{
    if ((string)$currentValue === (string)$optionValue) {
        echo 'selected';
    }
}

/**
 * In ra 'checked' nếu hai giá trị bằng nhau.
 */
function checked($currentValue, $optionValue)
{
    if ((string)$currentValue === (string)$optionValue) {
        echo 'checked';
    }
}

/**
 * Chuyển hướng an toàn.
 */
function safe_redirect(string $location): void
{
    header("Location: " . BASE_URL . $location);
    exit;
}

/**
 * [UPDATED] Hiển thị một thẻ sản phẩm với logic giá mới.
 */
function render_product_card(array $product): void
{
    // Giá hiển thị đã được tính toán trong câu lệnh SQL chính
    $display_price = $product['display_price'] ?? 0;

    $formatted_price = 'Giá: Liên hệ';
    if ($display_price > 0) {
        $formatted_price = number_format($display_price, 0, ',', '.') . ' VNĐ';
        // Thêm chữ "Từ" cho sản phẩm có biến thể để cho biết đây là giá khởi điểm
        if (isset($product['loai_san_pham']) && $product['loai_san_pham'] === 'variable') {
            $formatted_price = 'Từ ' . $formatted_price;
        }
    }

    $product_url = BASE_URL . '/san-pham/' . e($product['slug'] ?? 'loi-san-pham');
    $product_name = e($product['ten_san_pham'] ?? 'Sản phẩm không tên');
    $image_url = !empty($product['url_hinh_anh'])
        ? BASE_URL . '/uploads/products/' . e($product['url_hinh_anh'])
        : 'https://placehold.co/400x400/e0e0e0/333?text=No+Image';

    echo <<<HTML
    <div class="product-card">
        <a href="{$product_url}" class="product-card-image-wrapper">
            <img src="{$image_url}" alt="{$product_name}" class="product-card-image" loading="lazy">
        </a>
        <div class="product-card-content">
            <h3 class="product-card-title">
                <a href="{$product_url}">{$product_name}</a>
            </h3>
            <p class="product-card-price">{$formatted_price}</p>
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
