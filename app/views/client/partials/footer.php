<?php

// FILE: /app/views/partials/footer.php
// MÔ TẢ: File này chứa toàn bộ phần cuối của trang.

// SỬA LỖI: Khai báo lại các biến cần thiết cho footer.
// Trong một ứng dụng lớn hơn, dữ liệu này có thể được lấy từ CSDL hoặc file cấu hình.
$zalo_shop_phone = '0845115765';
$facebook_page_url = 'https://www.facebook.com/Phut89iPhone';
$store_address_1 = '41B Lê Duẩn, BMT';
$store_address_2 = '323 Phan Chu Trinh, BMT';
$store_phone = '02623 816 889';
$store_email = 'phamtuananh02dl@gmail.com';

?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div class="footer-col">
                <a href="<?php echo BASE_URL; ?>/" class="logo" style="font-size: 1.5rem; display: inline-block; margin-bottom: 0.5rem;"><?php echo e(SITE_NAME); ?></a>
                <p>Dịch Vụ CẦM ĐỒ PHÚT 89 uy tín tại Buôn Ma Thuột, nhận cầm điện thoại, laptop, xe máy, ô tô và cả nhà đất.</p>
                <p>Website phân phối sản phẩm đã qua sử dụng giá tốt, rõ nguồn gốc, bảo hành đầy đủ.</p>
                <p>Ngoài ra, còn bán điện thoại mới fullbox chính hãng nữa nha</p>
            </div>
            <div class="footer-col">
                <h4>Về chúng tôi</h4>
                <ul>
                    <li><a href="#">Giới thiệu</a></li>
                    <li><a href="#">Điều khoản dịch vụ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Hỗ trợ khách hàng</h4>
                <ul>
                    <li><a href="#">Hướng dẫn mua hàng</a></li>
                    <li><a href="#">Chính sách đổi trả</a></li>
                    <li><a href="#">Chính sách bảo hành</a></li>
                    <li><a href="#">Câu hỏi thường gặp</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Liên hệ</h4>
                <address style="font-style: normal;">
                    <p><i class="fas fa-map-marker-alt footer-icon"></i><?php echo $store_address_1; ?></p>
                    <p><i class="fas fa-map-marker-alt footer-icon"></i><?php echo $store_address_2; ?></p>
                    <p><i class="fas fa-phone-alt footer-icon"></i><a href="tel:<?php echo str_replace(' ', '', $store_phone); ?>"><?php echo $store_phone; ?></a></p>
                    <p><i class="fas fa-envelope footer-icon"></i><a href="mailto:<?php echo $store_email; ?>"><?php echo $store_email; ?></a></p>
                </address>
                <div class="footer-socials">
                    <a href="<?php echo $facebook_page_url; ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="https://zalo.me/<?php echo $zalo_shop_phone; ?>" target="_blank" rel="noopener noreferrer" aria-label="Zalo"><i class="fa-solid fa-comment-dots"></i></a>
                </div>
                <p>Cung cấp thiết kế web liên hệ zalo 0357975610 😘</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> <?php echo e(SITE_NAME); ?>. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<!-- Các nút liên hệ nổi -->
<div class="floating-contact">
    <a href="https://zalo.me/<?php echo $zalo_shop_phone; ?>" target="_blank" class="floating-btn zalo-float" title="Chat qua Zalo" aria-label="Chat qua Zalo">
        <i class="fa-solid fa-comment-dots"></i>
    </a>
    <a href="https://m.me/Phut89iPhone" target="_blank" class="floating-btn fb-float" title="Chat qua Messenger" aria-label="Chat qua Messenger">
        <i class="fab fa-facebook-messenger"></i>
    </a>
</div>

<!-- Nơi hiển thị thông báo toast -->
<div id="toast-notification"></div>
