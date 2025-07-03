<?php
// FILE: /app/controllers/admin/DashboardController.php
// MÔ TẢ: Controller cho trang Bảng điều khiển (trang chủ admin).

namespace Admin;

class DashboardController extends AdminBaseController
{

    public function index(): void
    {
        $data = [
            'page_title' => 'Bảng điều khiển',
            'total_products' => 0,
            'total_categories' => 0,
            'total_brands' => 0,
            'total_banners' => 0,
            'most_viewed_products' => [],
        ];
        try {
            $simple_products_count = $this->pdo->query("SELECT COUNT(*) FROM san_pham WHERE loai_san_pham = 'simple'")->fetchColumn();
            $variant_products_count = $this->pdo->query("SELECT COUNT(*) FROM product_variants")->fetchColumn();

            $data['total_products'] = $simple_products_count + $variant_products_count;
            $data['total_categories'] = $this->pdo->query("SELECT COUNT(*) FROM danh_muc")->fetchColumn();
            $data['total_brands'] = $this->pdo->query("SELECT COUNT(*) FROM thuong_hieu")->fetchColumn();
            $data['total_banners'] = $this->pdo->query("SELECT COUNT(*) FROM banners")->fetchColumn();
            $data['most_viewed_products'] = $this->pdo->query("SELECT ten_san_pham, luot_xem FROM san_pham ORDER BY luot_xem DESC LIMIT 5")->fetchAll();
        } catch (\PDOException $e) {
            $_SESSION['message'] = 'Không thể tải dữ liệu thống kê. Lỗi: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }

        $this->render('pages/dashboard', $data);
    }
}
