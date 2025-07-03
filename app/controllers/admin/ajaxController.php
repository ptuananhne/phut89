<?php

namespace Admin;

class AjaxController extends AdminBaseController
{
    public function handle(string $action): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Phương thức không hợp lệ.');
        }

        $input = [];
        if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        }
        $post_data = array_merge($_POST, $input);

        if (!$this->verifyCsrfToken($post_data['csrf_token'] ?? '')) {
            $this->jsonResponse(false, 'Lỗi xác thực CSRF!', ['http_code' => 403]);
        }

        try {
            $this->pdo->beginTransaction();

            switch ($action) {
                case 'save_product':
                    $this->saveProduct();
                    break;
                case 'delete_image':
                    $this->deleteImage($post_data);
                    break;
                case 'update_category_order':
                    $this->updateCategoryOrder($post_data);
                    break;
                case 'toggle_status':
                    $this->toggleStatus($post_data);
                    break;
                case 'delete_taxonomy_term':
                    $this->deleteTaxonomyTerm($post_data);
                    break;

                case 'add_attribute':
                    $this->addAttribute($post_data);
                    break;
                case 'update_attribute':
                    $this->updateAttribute($post_data);
                    break;
                case 'delete_attribute':
                    $this->deleteAttribute($post_data);
                    break;
                case 'add_value':
                    $this->addValue($post_data);
                    break;
                case 'update_value':
                    $this->updateValue($post_data);
                    break;
                case 'delete_value':
                    $this->deleteValue($post_data);
                    break;

                default:
                    $this->jsonResponse(false, 'Hành động không xác định.');
            }

            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("AJAX Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            $this->jsonResponse(false, 'Lỗi: ' . $e->getMessage(), ['http_code' => 500]);
        }
    }

    private function handleImageUpload($file, $product_id)
    {
        $upload_dir = \ROOT_PATH . '/public/uploads/products/';
        $thumb_dir = $upload_dir . 'thumbs/';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0777, true);
        }
        if (!is_dir($thumb_dir)) {
            @mkdir($thumb_dir, 0777, true);
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            return;
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid('prod_') . '-' . time() . '.' . $extension;
        $main_path = $upload_dir . $new_filename;
        $thumb_path = $thumb_dir . $new_filename;
        if (!move_uploaded_file($file['tmp_name'], $main_path)) {
            return;
        }
        copy($main_path, $thumb_path);
        $stmt = $this->pdo->prepare("INSERT INTO hinh_anh_san_pham (san_pham_id, url_hinh_anh) VALUES (?, ?)");
        $stmt->execute([$product_id, $new_filename]);
    }

    private function saveVariants($product_id, $submitted_variants)
    {
        $submitted_variant_ids = [];
        if (!empty($submitted_variants)) {
            foreach ($submitted_variants as $variant_data) {
                $variant_id = (int)($variant_data['id'] ?? 0);
                $so_luong_ton = count(array_filter(explode("\n", $variant_data['unique_identifiers'] ?? ''), 'trim'));

                $variant_sql_data = [
                    'san_pham_id' => $product_id,
                    'gia' => (float)($variant_data['gia'] ?? 0),
                    'gia_khuyen_mai' => empty($variant_data['gia_khuyen_mai']) ? null : (float)$variant_data['gia_khuyen_mai'],
                    'so_luong_ton' => $so_luong_ton,
                    'unique_identifiers' => $variant_data['unique_identifiers'] ?? null,
                    'hinh_anh_id' => empty($variant_data['hinh_anh_id']) ? null : (int)$variant_data['hinh_anh_id']
                ];

                if ($variant_id > 0) {
                    $variant_sql_data['id'] = $variant_id;
                    $this->pdo->prepare("UPDATE product_variants SET san_pham_id=:san_pham_id, gia=:gia, gia_khuyen_mai=:gia_khuyen_mai, so_luong_ton=:so_luong_ton, unique_identifiers=:unique_identifiers, hinh_anh_id=:hinh_anh_id WHERE id=:id")->execute($variant_sql_data);
                } else {
                    $this->pdo->prepare("INSERT INTO product_variants (san_pham_id, gia, gia_khuyen_mai, so_luong_ton, unique_identifiers, hinh_anh_id) VALUES (:san_pham_id, :gia, :gia_khuyen_mai, :so_luong_ton, :unique_identifiers, :hinh_anh_id)")->execute($variant_sql_data);
                    $variant_id = $this->pdo->lastInsertId();
                }
                $submitted_variant_ids[] = $variant_id;

                $this->pdo->prepare("DELETE FROM variant_option_values WHERE variant_id = ?")->execute([$variant_id]);
                $option_values = explode(',', $variant_data['options_flat'] ?? '');
                if (!empty($option_values[0])) {
                    $stmt_vov = $this->pdo->prepare("INSERT INTO variant_option_values (variant_id, value_id) VALUES (?, ?)");
                    foreach ($option_values as $value_id) $stmt_vov->execute([$variant_id, (int)$value_id]);
                }
            }
        }

        if (!empty($submitted_variant_ids)) {
            $placeholders = implode(',', array_fill(0, count($submitted_variant_ids), '?'));
            $params = array_merge([$product_id], $submitted_variant_ids);
            $this->pdo->prepare("DELETE FROM product_variants WHERE san_pham_id = ? AND id NOT IN ({$placeholders})")->execute($params);
        } else {
            $this->pdo->prepare("DELETE FROM product_variants WHERE san_pham_id = ?")->execute([$product_id]);
        }
    }

    private function saveProduct()
    {
        $id = (int)($_POST['id'] ?? 0);
        $is_edit = $id > 0;
        $product_type = $_POST['loai_san_pham'] ?? 'simple';
        $slug = !empty(trim($_POST['slug'])) ? trim($_POST['slug']) : generate_slug($_POST['ten_san_pham']);
        $thuong_hieu_id = (int)($_POST['thuong_hieu_id'] ?? 0);

        $product_data = [
            'ten_san_pham' => $_POST['ten_san_pham'],
            'slug' => $slug,
            'mo_ta_ngan' => $_POST['mo_ta_ngan'] ?? '',
            'mo_ta_chi_tiet' => $_POST['mo_ta_chi_tiet'] ?? '',
            'danh_muc_id' => (int)$_POST['danh_muc_id'],
            'thuong_hieu_id' => $thuong_hieu_id,
            'is_active' => (int)$_POST['is_active'],
            'loai_san_pham' => $product_type,
            'ma_dinh_danh_duy_nhat' => ($product_type === 'simple' && !empty($_POST['ma_dinh_danh_duy_nhat'])) ? $_POST['ma_dinh_danh_duy_nhat'] : null,
            'gia_goc' => ($product_type === 'simple') ? (float)($_POST['gia_goc'] ?? 0) : null,
            'gia_khuyen_mai' => ($product_type === 'simple' && !empty($_POST['gia_khuyen_mai'])) ? (float)$_POST['gia_khuyen_mai'] : null,
            'so_luong_ton' => ($product_type === 'simple') ? (int)($_POST['so_luong_ton'] ?? 0) : null,
        ];
        if ($is_edit) {
            $product_data['id'] = $id;
            $sql = "UPDATE san_pham SET ten_san_pham=:ten_san_pham, slug=:slug, mo_ta_ngan=:mo_ta_ngan, mo_ta_chi_tiet=:mo_ta_chi_tiet, danh_muc_id=:danh_muc_id, thuong_hieu_id=:thuong_hieu_id, is_active=:is_active, loai_san_pham=:loai_san_pham, ma_dinh_danh_duy_nhat=:ma_dinh_danh_duy_nhat, gia_goc=:gia_goc, gia_khuyen_mai=:gia_khuyen_mai, so_luong_ton=:so_luong_ton WHERE id=:id";
        } else {
            $sql = "INSERT INTO san_pham (ten_san_pham, slug, mo_ta_ngan, mo_ta_chi_tiet, danh_muc_id, thuong_hieu_id, is_active, loai_san_pham, ma_dinh_danh_duy_nhat, gia_goc, gia_khuyen_mai, so_luong_ton) VALUES (:ten_san_pham, :slug, :mo_ta_ngan, :mo_ta_chi_tiet, :danh_muc_id, :thuong_hieu_id, :is_active, :loai_san_pham, :ma_dinh_danh_duy_nhat, :gia_goc, :gia_khuyen_mai, :so_luong_ton)";
        }
        $this->pdo->prepare($sql)->execute($product_data);
        if (!$is_edit) $id = $this->pdo->lastInsertId();

        if (isset($_FILES['hinh_anh']) && is_array($_FILES['hinh_anh']['name'])) {
            foreach ($_FILES['hinh_anh']['name'] as $key => $name) {
                if ($_FILES['hinh_anh']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = ['name' => $name, 'type' => $_FILES['hinh_anh']['type'][$key], 'tmp_name' => $_FILES['hinh_anh']['tmp_name'][$key]];
                    $this->handleImageUpload($file, $id);
                }
            }
        }
        $main_image_id = (int)($_POST['main_image'] ?? 0);
        $this->pdo->prepare("UPDATE hinh_anh_san_pham SET la_anh_dai_dien = 0 WHERE san_pham_id = ?")->execute([$id]);
        if ($main_image_id > 0) {
            $this->pdo->prepare("UPDATE hinh_anh_san_pham SET la_anh_dai_dien = 1 WHERE id = ? AND san_pham_id = ?")->execute([$main_image_id, $id]);
        }

        if ($product_type === 'variable') {
            $this->saveVariants($id, $_POST['variants'] ?? []);
        } else {
            $this->pdo->prepare("DELETE FROM product_variants WHERE san_pham_id = ?")->execute([$id]);
        }

        $this->pdo->prepare("DELETE FROM gia_tri_thuoc_tinh WHERE san_pham_id = ?")->execute([$id]);
        if (isset($_POST['attributes']) && is_array($_POST['attributes'])) {
            $stmt_attr = $this->pdo->prepare("INSERT INTO gia_tri_thuoc_tinh (san_pham_id, thuoc_tinh_id, gia_tri) VALUES (?, ?, ?)");
            foreach ($_POST['attributes'] as $thuoc_tinh_id => $gia_tri) {
                if (!empty(trim($gia_tri))) $stmt_attr->execute([$id, (int)$thuoc_tinh_id, trim($gia_tri)]);
            }
        }

        $all_images_latest = $this->pdo->query("SELECT id, url_hinh_anh, la_anh_dai_dien FROM hinh_anh_san_pham WHERE san_pham_id = $id ORDER BY la_anh_dai_dien DESC, id ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $this->jsonResponse(true, 'Lưu sản phẩm thành công!', [
            'redirect_url' => \BASE_URL . '/admin/products/edit/' . $id,
            'is_new_product' => !$is_edit,
            'product_id' => $id,
            'all_images' => $all_images_latest
        ]);
    }

    private function deleteImage($data)
    {
        $img_id = (int)($data['img_id'] ?? 0);
        if ($img_id <= 0) throw new \Exception("Dữ liệu không hợp lệ.");
        $stmt_img = $this->pdo->prepare("SELECT url_hinh_anh FROM hinh_anh_san_pham WHERE id = ?");
        $stmt_img->execute([$img_id]);
        $img_file = $stmt_img->fetchColumn();
        if ($img_file) {
            @unlink(\ROOT_PATH . '/public/uploads/products/' . $img_file);
            @unlink(\ROOT_PATH . '/public/uploads/products/thumbs/' . $img_file);
        }
        $this->pdo->prepare("DELETE FROM hinh_anh_san_pham WHERE id = ?")->execute([$img_id]);
        $this->jsonResponse(true, 'Xóa ảnh thành công.');
    }

    private function updateCategoryOrder($data)
    {
        $order = json_decode($data['order'] ?? '[]', true);
        if (!empty($order) && is_array($order)) {
            $stmt = $this->pdo->prepare("UPDATE danh_muc SET vi_tri = ? WHERE id = ?");
            foreach ($order as $position => $id) {
                $stmt->execute([$position, (int)$id]);
            }
        }
        $this->jsonResponse(true, 'Cập nhật thứ tự thành công.');
    }

    private function toggleStatus($data)
    {
        $id = (int)($data['id'] ?? 0);
        $type = $data['type'] ?? '';
        $current_status = (int)($data['current_status'] ?? 0);
        if ($id <= 0 || !in_array($type, ['san_pham', 'banner'])) {
            throw new \Exception("Dữ liệu không hợp lệ.");
        }
        $table_name = ($type === 'san_pham') ? 'san_pham' : 'banners';
        $status_column = ($type === 'san_pham') ? 'is_active' : 'trang_thai';
        $new_status = $current_status == 1 ? 0 : 1;
        $stmt = $this->pdo->prepare("UPDATE {$table_name} SET {$status_column} = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        $this->jsonResponse(true, 'Cập nhật trạng thái thành công', ['new_status' => $new_status]);
    }

    private function deleteTaxonomyTerm($data)
    {
        $term_id = (int)($data['id'] ?? 0);
        $type = $data['type'] ?? '';
        if ($term_id <= 0 || !in_array($type, ['brand', 'attribute'])) throw new \Exception("Dữ liệu không hợp lệ.");
        if ($type === 'brand') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM san_pham WHERE thuong_hieu_id = ?");
            $stmt->execute([$term_id]);
            if ($stmt->fetchColumn() > 0) throw new \Exception("Không thể xóa thương hiệu đã có sản phẩm.");
            $this->pdo->prepare("DELETE FROM thuong_hieu WHERE id = ?")->execute([$term_id]);
        } elseif ($type === 'attribute') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM gia_tri_thuoc_tinh WHERE thuoc_tinh_id = ?");
            $stmt->execute([$term_id]);
            if ($stmt->fetchColumn() > 0) throw new \Exception("Không thể xóa thuộc tính đã có sản phẩm sử dụng.");
            $this->pdo->prepare("DELETE FROM thuoc_tinh WHERE id = ?")->execute([$term_id]);
        }
        $this->jsonResponse(true, 'Xóa thành công.');
    }

    private function addAttribute($data)
    {
        $name = trim($data['name'] ?? '');
        if (empty($name)) throw new \Exception("Tên thuộc tính không được để trống.");
        $stmt = $this->pdo->prepare("INSERT INTO thuoc_tinh_bien_the (ten_thuoc_tinh) VALUES (?)");
        $stmt->execute([$name]);
        $this->jsonResponse(true, 'Thêm thành công', ['id' => $this->pdo->lastInsertId(), 'name' => $name]);
    }

    private function updateAttribute($data)
    {
        $id = (int)($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        if (empty($name) || $id === 0) throw new \Exception("Dữ liệu không hợp lệ.");
        $this->pdo->prepare("UPDATE thuoc_tinh_bien_the SET ten_thuoc_tinh = ? WHERE id = ?")->execute([$name, $id]);
        $this->jsonResponse(true, 'Cập nhật thành công.');
    }

    private function deleteAttribute($data)
    {
        $id = (int)($data['id'] ?? 0);
        if ($id === 0) throw new \Exception("ID không hợp lệ.");
        $this->pdo->prepare("DELETE FROM thuoc_tinh_bien_the WHERE id = ?")->execute([$id]);
        $this->jsonResponse(true, 'Xóa thành công.');
    }

    private function addValue($data)
    {
        $attr_id = (int)($data['attribute_id'] ?? 0);
        $value = trim($data['value'] ?? '');
        if (empty($value) || $attr_id === 0) throw new \Exception("Dữ liệu không hợp lệ.");
        $stmt = $this->pdo->prepare("INSERT INTO gia_tri_thuoc_tinh_bien_the (thuoc_tinh_id, gia_tri) VALUES (?, ?)");
        $stmt->execute([$attr_id, $value]);
        $this->jsonResponse(true, 'Thêm giá trị thành công', ['id' => $this->pdo->lastInsertId(), 'value' => $value]);
    }

    private function updateValue($data)
    {
        $id = (int)($data['id'] ?? 0);
        $value = trim($data['value'] ?? '');
        if (empty($value) || $id === 0) throw new \Exception("Dữ liệu không hợp lệ.");
        $this->pdo->prepare("UPDATE gia_tri_thuoc_tinh_bien_the SET gia_tri = ? WHERE id = ?")->execute([$value, $id]);
        $this->jsonResponse(true, 'Cập nhật giá trị thành công.');
    }

    private function deleteValue($data)
    {
        $id = (int)($data['id'] ?? 0);
        if ($id === 0) throw new \Exception("ID không hợp lệ.");
        $this->pdo->prepare("DELETE FROM gia_tri_thuoc_tinh_bien_the WHERE id = ?")->execute([$id]);
        $this->jsonResponse(true, 'Xóa giá trị thành công.');
    }
}
