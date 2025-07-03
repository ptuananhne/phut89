<?php
// FILE: /app/controllers/client/homecontroller.php

namespace client;

class homecontroller extends Clientbasecontroller
{
    /**
     * Phương thức này sẽ được gọi khi người dùng truy cập vào trang chủ.
     * Nó lấy tất cả dữ liệu cần thiết (banners, sản phẩm nổi bật, sản phẩm theo danh mục)
     * và truyền chúng cho view để hiển thị.
     */
    public function index(): void
    {
        $data = [
            'banners' => [],
            'featured_products' => [],
            'categories_with_products' => []
        ];

        try {
            // Lấy banner
            $data['banners'] = $this->pdo->query("SELECT * FROM banners WHERE trang_thai = 1 ORDER BY vi_tri ASC, id DESC")->fetchAll();

            // Lấy sản phẩm nổi bật
            $sql_featured = "SELECT sp.id, sp.slug, sp.ten_san_pham, sp.loai_san_pham, 
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
            $data['featured_products'] = $this->pdo->query($sql_featured)->fetchAll();

            // Lấy sản phẩm theo từng danh mục để hiển thị trên trang chủ
            $categories = $this->pdo->query("SELECT * FROM danh_muc WHERE is_active = 1 ORDER BY vi_tri ASC, id ASC")->fetchAll();
            foreach ($categories as $category) {
                $stmt_products = $this->pdo->prepare("SELECT sp.id, sp.slug, sp.ten_san_pham, sp.loai_san_pham, 
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
                $stmt_products->execute([$category['id']]);
                if ($products_by_cat = $stmt_products->fetchAll()) {
                    $data['categories_with_products'][] = ['category_info' => $category, 'products' => $products_by_cat];
                }
            }
        } catch (\PDOException $e) {
            // Ghi lại lỗi nếu ở môi trường development
            if (\APP_ENV === 'development') {
                error_log($e->getMessage());
            }
        }

        $data['page_title'] = 'Trang chủ';
        $this->render('pages/home', $data);
    }
}
