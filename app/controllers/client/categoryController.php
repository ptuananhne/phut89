<?php
// FILE: /app/controllers/client/CategoryController.php
// MÔ TẢ: Controller cho trang danh mục, đã được điền đầy đủ logic.

namespace Client;

class CategoryController extends ClientBaseController {
    public function show(string $slug): void {
        $category_slug = htmlspecialchars($slug);
        $brand_slug = htmlspecialchars($_GET['brand'] ?? '');
        $sort_option = htmlspecialchars($_GET['sort'] ?? 'view_desc');
        $current_page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$current_page) $current_page = 1;
        $offset = ($current_page - 1) * \PRODUCTS_PER_PAGE;
        
        $valid_sort_options = ['default', 'price_asc', 'price_desc', 'view_desc'];
        if (!in_array($sort_option, $valid_sort_options)) $sort_option = 'view_desc';

        $data = [
            'category' => null, 'products' => [], 'total_products' => 0, 'total_pages' => 0, 'brands' => [],
            'category_slug' => $category_slug, 'brand_slug' => $brand_slug, 'sort_option' => $sort_option, 'current_page' => $current_page
        ];

        try {
            $stmt_cat = $this->pdo->prepare("SELECT id, ten_danh_muc FROM danh_muc WHERE slug = ? AND is_active = 1");
            $stmt_cat->execute([$category_slug]);
            $category = $stmt_cat->fetch();

            if ($category) {
                $data['category'] = $category;
                $data['page_title'] = e($category['ten_danh_muc']);
                $category_id = $category['id'];
                
                $sql_parts = [
                    'select' => "SELECT sp.id, sp.slug, sp.ten_san_pham, sp.loai_san_pham, (CASE WHEN sp.loai_san_pham = 'variable' THEN (SELECT MIN(pv.gia) FROM product_variants pv WHERE pv.san_pham_id = sp.id AND pv.gia > 0) ELSE IF(sp.gia_khuyen_mai > 0, sp.gia_khuyen_mai, sp.gia_goc) END) as display_price, ha.url_hinh_anh, sp.luot_xem",
                    'count' => "SELECT COUNT(DISTINCT sp.id)",
                    'from' => " FROM san_pham sp LEFT JOIN hinh_anh_san_pham ha ON sp.id = ha.san_pham_id AND ha.la_anh_dai_dien = 1",
                    'where' => " WHERE sp.danh_muc_id = :category_id AND sp.is_active = 1",
                ];
                $params = ['category_id' => $category_id];

                if (!empty($brand_slug)) {
                    $stmt_brand_id = $this->pdo->prepare("SELECT id FROM thuong_hieu WHERE slug = ?");
                    $stmt_brand_id->execute([$brand_slug]);
                    if ($current_brand_id = $stmt_brand_id->fetchColumn()) {
                        $sql_parts['where'] .= " AND sp.thuong_hieu_id = :brand_id";
                        $params['brand_id'] = $current_brand_id;
                    } else { $data['brand_slug'] = ''; }
                }

                $stmt_count = $this->pdo->prepare($sql_parts['count'] . $sql_parts['from'] . $sql_parts['where']);
                $stmt_count->execute($params);
                $data['total_products'] = $stmt_count->fetchColumn();
                $data['total_pages'] = ceil($data['total_products'] / \PRODUCTS_PER_PAGE);

                $order_by = " ORDER BY ";
                switch ($sort_option) {
                    case 'price_asc': $order_by .= "display_price ASC, sp.ngay_tao DESC"; break;
                    case 'price_desc': $order_by .= "display_price DESC, sp.ngay_tao DESC"; break;
                    case 'view_desc': $order_by .= "sp.luot_xem DESC, sp.ngay_tao DESC"; break;
                    default: $order_by .= "sp.ngay_tao DESC"; break;
                }

                $sql = $sql_parts['select'] . $sql_parts['from'] . $sql_parts['where'] . " GROUP BY sp.id" . $order_by . " LIMIT :limit OFFSET :offset";
                $stmt_products = $this->pdo->prepare($sql);
                $stmt_products->bindValue(':limit', \PRODUCTS_PER_PAGE, \PDO::PARAM_INT);
                $stmt_products->bindValue(':offset', $offset, \PDO::PARAM_INT);
                foreach ($params as $key => &$val) { $stmt_products->bindParam($key, $val); }
                $stmt_products->execute();
                $data['products'] = $stmt_products->fetchAll();

                $stmt_brands = $this->pdo->prepare("SELECT DISTINCT th.ten_thuong_hieu, th.slug FROM thuong_hieu th JOIN danhmuc_thuonghieu dt ON th.id = dt.thuong_hieu_id WHERE dt.danh_muc_id = ? ORDER BY th.ten_thuong_hieu ASC");
                $stmt_brands->execute([$category_id]);
                $data['brands'] = $stmt_brands->fetchAll();
                
                $this->render('pages/category', $data);
            } else {
                $this->renderNotFound();
            }
        } catch (\PDOException $e) {
             if (\APP_ENV === 'development') error_log($e->getMessage());
             $this->render('pages/500', ['page_title' => 'Lỗi hệ thống']);
        }
    }
}
