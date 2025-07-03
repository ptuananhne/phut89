<?php
// FILE: /app/controllers/client/SearchController.php
// MÔ TẢ: Controller cho trang tìm kiếm, đã được điền đầy đủ logic.

namespace Client;

class SearchController extends ClientBaseController
{
    public function index(): void
    {
        $search_query = trim(htmlspecialchars($_GET['q'] ?? ''));
        $current_page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$current_page) $current_page = 1;
        $offset = ($current_page - 1) * \PRODUCTS_PER_PAGE;

        $data = [
            'search_query' => $search_query,
            'products' => [],
            'total_products' => 0,
            'total_pages' => 0,
            'current_page' => $current_page,
            'page_title' => "Tìm kiếm cho '" . e($search_query) . "'"
        ];

        if (!empty($search_query)) {
            try {
                $search_terms = array_filter(explode(' ', $search_query));
                if (!empty($search_terms)) {
                    $search_conditions = [];
                    $params = [];
                    foreach ($search_terms as $term) {
                        $search_conditions[] = "sp.ten_san_pham LIKE ?";
                        $params[] = '%' . $term . '%';
                    }
                    $where_clause = implode(' AND ', $search_conditions);

                    $count_sql = "SELECT COUNT(DISTINCT sp.id) FROM san_pham sp WHERE ($where_clause) AND sp.is_active = 1";
                    $stmt_count = $this->pdo->prepare($count_sql);
                    $stmt_count->execute($params);
                    $data['total_products'] = $stmt_count->fetchColumn();
                    $data['total_pages'] = ceil($data['total_products'] / \PRODUCTS_PER_PAGE);

                    $sql = "SELECT sp.id, sp.slug, sp.ten_san_pham, sp.loai_san_pham, (CASE WHEN sp.loai_san_pham = 'variable' THEN (SELECT MIN(pv.gia) FROM product_variants pv WHERE pv.san_pham_id = sp.id AND pv.gia > 0) ELSE IF(sp.gia_khuyen_mai > 0, sp.gia_khuyen_mai, sp.gia_goc) END) as display_price, ha.url_hinh_anh FROM san_pham sp LEFT JOIN hinh_anh_san_pham ha ON sp.id = ha.san_pham_id AND ha.la_anh_dai_dien = 1 WHERE ($where_clause) AND sp.is_active = 1 GROUP BY sp.id ORDER BY sp.luot_xem DESC, sp.ngay_tao DESC LIMIT ? OFFSET ?";
                    $stmt = $this->pdo->prepare($sql);

                    $i = 1;
                    foreach ($params as $p) {
                        $stmt->bindValue($i++, $p);
                    }
                    $stmt->bindValue($i++, \PRODUCTS_PER_PAGE, \PDO::PARAM_INT);
                    $stmt->bindValue($i, $offset, \PDO::PARAM_INT);

                    $stmt->execute();
                    $data['products'] = $stmt->fetchAll();
                }
            } catch (\PDOException $e) {
                $data['error_message'] = "Lỗi cơ sở dữ liệu.";
                if (\APP_ENV === 'development') error_log($e->getMessage());
            }
        }
        $this->render('pages/search', $data);
    }
}
