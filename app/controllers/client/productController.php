<?php
// FILE: /app/controllers/client/ProductController.php
// MÔ TẢ: Controller cho trang chi tiết sản phẩm, đã được điền đầy đủ logic.

namespace Client;

class ProductController extends ClientBaseController {
    public function show(string $slug): void {
        $slug = htmlspecialchars($slug);
        
        try {
            $stmt_product = $this->pdo->prepare("SELECT sp.*, dm.ten_danh_muc, dm.slug AS category_slug, th.ten_thuong_hieu FROM san_pham sp LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id LEFT JOIN thuong_hieu th ON sp.thuong_hieu_id = th.id WHERE sp.slug = ? AND sp.is_active = 1");
            $stmt_product->execute([$slug]);
            $product = $stmt_product->fetch(\PDO::FETCH_ASSOC);

            if (!$product) {
                $this->renderNotFound();
                return;
            }

            $product_id = $product['id'];
            $this->pdo->prepare("UPDATE san_pham SET luot_xem = luot_xem + 1 WHERE id = ?")->execute([$product_id]);
            
            $stmt_all_images = $this->pdo->prepare("SELECT id, url_hinh_anh, la_anh_dai_dien FROM hinh_anh_san_pham WHERE san_pham_id = ? ORDER BY la_anh_dai_dien DESC, id ASC");
            $stmt_all_images->execute([$product_id]);
            $all_images = $stmt_all_images->fetchAll(\PDO::FETCH_ASSOC);

            $variants_data_json = 'null';
            if ($product['loai_san_pham'] === 'variable') {
                $variants_data = $this->getVariantsData($product_id, $product['ten_san_pham'], $all_images);
                if ($variants_data) {
                    $variants_data_json = json_encode($variants_data, JSON_UNESCAPED_UNICODE);
                }
            }

            $stmt_attrs = $this->pdo->prepare("SELECT tt.ten_thuoc_tinh, gtt.gia_tri FROM gia_tri_thuoc_tinh gtt JOIN thuoc_tinh tt ON gtt.thuoc_tinh_id = tt.id WHERE gtt.san_pham_id = ? ORDER BY tt.id");
            $stmt_attrs->execute([$product_id]);
            $attributes = $stmt_attrs->fetchAll(\PDO::FETCH_ASSOC);
            
            $related_products = [];
            if ($product['danh_muc_id']) {
                $stmt_related = $this->pdo->prepare("SELECT sp.slug, sp.ten_san_pham, ha.url_hinh_anh, sp.loai_san_pham, (CASE WHEN sp.loai_san_pham = 'variable' THEN (SELECT MIN(pv.gia) FROM product_variants pv WHERE pv.san_pham_id = sp.id AND pv.gia > 0) ELSE IF(sp.gia_khuyen_mai > 0, sp.gia_khuyen_mai, sp.gia_goc) END) as display_price FROM san_pham sp LEFT JOIN hinh_anh_san_pham ha ON sp.id = ha.san_pham_id AND ha.la_anh_dai_dien = 1 WHERE sp.danh_muc_id = ? AND sp.id != ? AND sp.is_active = 1 GROUP BY sp.id ORDER BY RAND() LIMIT 4");
                $stmt_related->execute([$product['danh_muc_id'], $product_id]);
                $related_products = $stmt_related->fetchAll(\PDO::FETCH_ASSOC);
            }
            
            $data = [
                'page_title' => e($product['ten_san_pham']),
                'page_description' => e($product['mo_ta_ngan']),
                'product' => $product,
                'attributes' => $attributes,
                'related_products' => $related_products,
                'all_images' => $all_images,
                'variants_data_json' => $variants_data_json,
                'add_product_detail_js' => true
            ];
            
            $this->render('pages/product', $data);

        } catch (\PDOException $e) {
            if (\APP_ENV === 'development') error_log($e->getMessage());
            $this->render('pages/500', ['page_title' => 'Lỗi hệ thống']);
        }
    }
    
    private function getVariantsData($product_id, $product_name, $all_images) {
        $stmt_variants = $this->pdo->prepare("SELECT * FROM product_variants WHERE san_pham_id = ?");
        $stmt_variants->execute([$product_id]);
        $variants = $stmt_variants->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($variants)) return null;

        $variant_ids = array_column($variants, 'id');
        $placeholders = implode(',', array_fill(0, count($variant_ids), '?'));

        $stmt_variant_values = $this->pdo->prepare("SELECT vov.variant_id, gtt.id as value_id, gtt.thuoc_tinh_id as option_id, gtt.gia_tri, gtt.hinh_anh FROM variant_option_values vov JOIN gia_tri_thuoc_tinh_bien_the gtt ON vov.value_id = gtt.id WHERE vov.variant_id IN ($placeholders)");
        $stmt_variant_values->execute($variant_ids);
        $variant_value_map_raw = $stmt_variant_values->fetchAll(\PDO::FETCH_ASSOC);
        if(empty($variant_value_map_raw)) return null;
        
        $option_ids = array_unique(array_column($variant_value_map_raw, 'option_id'));
        $stmt_options = $this->pdo->prepare("SELECT id, ten_thuoc_tinh as name FROM thuoc_tinh_bien_the WHERE id IN (".implode(',', array_fill(0, count($option_ids), '?')).")");
        $stmt_options->execute(array_values($option_ids));
        $options = $stmt_options->fetchAll(\PDO::FETCH_ASSOC);

        $variant_value_map = [];
        foreach ($variant_value_map_raw as $row) { $variant_value_map[$row['variant_id']][] = $row['value_id']; }
        $variants_for_json = array_map(fn($v) => ['id' => (int)$v['id'], 'options' => $variant_value_map[$v['id']] ?? [], 'price' => (int)$v['gia'], 'old_price' => (int)$v['gia_khuyen_mai'], 'stock' => (int)$v['so_luong_ton'], 'image_id' => $v['hinh_anh_id'] ? (int)$v['hinh_anh_id'] : null], $variants);
        $unique_option_values = [];
        foreach ($variant_value_map_raw as $row) { $unique_option_values[$row['value_id']] = $row; }
        $option_values_for_json = array_map(fn($r) => ['id' => (int)$r['value_id'], 'option_id' => (int)$r['option_id'], 'value' => $r['gia_tri'], 'image' => $r['hinh_anh'] ? \BASE_URL . '/uploads/swatches/' . e($r['hinh_anh']) : null], array_values($unique_option_values));
        $images_for_json = array_map(fn($img) => ['id' => (int)$img['id'], 'url' => \BASE_URL . '/uploads/products/' . e($img['url_hinh_anh']), 'thumb' => \BASE_URL . '/uploads/products/thumbs/' . e($img['url_hinh_anh'])], $all_images);

        return ['productName' => e($product_name), 'options' => $options, 'optionValues' => $option_values_for_json, 'variants' => $variants_for_json, 'images' => $images_for_json];
    }
}