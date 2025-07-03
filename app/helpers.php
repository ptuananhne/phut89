<?php
// FILE: /app/helpers.php
// MÔ TẢ: Chứa các hàm tiện ích có thể gọi ở bất kỳ đâu trong ứng dụng.

if (!function_exists('e')) {
    /**
     * Hàm escape HTML để chống tấn công XSS.
     *
     * @param string|null $text Chuỗi cần escape.
     * @return string Chuỗi đã được escape.
     */
    function e(?string $text): string
    {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('generate_slug')) {
    /**
     * Hàm tạo slug thân thiện cho URL từ một chuỗi.
     *
     * @param string $text Chuỗi đầu vào.
     * @return string Slug đã được tạo.
     */
    function generate_slug(string $text): string
    {
        // Chuyển tất cả thành chữ thường
        $text = mb_strtolower($text, 'UTF-8');
        // Bỏ dấu tiếng Việt
        $text = str_replace(
            ['á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'đ', 'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'í', 'ì', 'ỉ', 'ĩ', 'ị', 'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ'],
            ['a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'y', 'y', 'y', 'y', 'y'],
            $text
        );
        // Xóa các ký tự đặc biệt
        $text = preg_replace('/[^a-z0-9 -]/', '', $text);
        // Thay thế khoảng trắng và gạch ngang lặp lại bằng một gạch ngang duy nhất
        $text = preg_replace('/([\s-]+)/', '-', $text);
        // Cắt bỏ gạch ngang ở đầu và cuối
        $text = trim($text, '-');
        return $text;
    }
}

if (!function_exists('dd')) {
    /**
     * Hàm "Dump and Die" để debug.
     * In ra dữ liệu một cách dễ đọc và dừng thực thi chương trình.
     *
     * @param mixed $data Dữ liệu cần in ra.
     * @return void
     */
    function dd(...$data): void
    {
        echo '<pre style="background-color: #1d1d1d; color: #cdcdcd; padding: 15px; border-radius: 5px; line-height: 1.5; font-family: monospace;">';
        foreach ($data as $item) {
            print_r($item);
            echo "\n";
        }
        echo '</pre>';
        die();
    }
}
