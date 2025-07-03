<?php
// ajax_handler.php

// Luôn trả về JSON, kể cả khi có lỗi nghiêm trọng
header('Content-Type: application/json');

// Hàm xử lý lỗi tập trung
function handle_fatal_error(Throwable $e)
{
    global $pdo;
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500); // Lỗi máy chủ nội bộ
    // Ghi lại lỗi chi tiết để debug
    error_log("AJAX Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    // Trả về thông báo lỗi thân thiện với người dùng
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi ở phía máy chủ. Vui lòng thử lại.']);
    exit;
}
set_exception_handler('handle_fatal_error');
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Toàn bộ logic được bọc trong khối try-catch để bắt tất cả lỗi
try {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/functions.php';

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/auth.php';

    // Hàm xử lý tải lên hình ảnh
    function handle_image_upload($file, $product_id)
    {
        global $pdo;
        $upload_dir = ROOT_PATH . '/uploads/products/';
        $thumb_dir = $upload_dir . 'thumbs/';
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0777, true)) throw new Exception("Lỗi: Không thể tạo thư mục uploads. Vui lòng kiểm tra quyền ghi.");
        }
        if (!is_dir($thumb_dir)) {
            if (!@mkdir($thumb_dir, 0777, true)) throw new Exception("Lỗi: Không thể tạo thư mục thumbs. Vui lòng kiểm tra quyền ghi.");
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) throw new Exception("Định dạng file không hợp lệ: " . e($file['name']));

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid('prod_') . '-' . time() . '.' . $extension;
        $main_path = $upload_dir . $new_filename;
        $thumb_path = $thumb_dir . $new_filename;

        if (!move_uploaded_file($file['tmp_name'], $main_path)) throw new Exception("Không thể di chuyển file đã tải lên: " . e($file['name']));

        // Kiểm tra thư viện GD trước khi xử lý ảnh
        if (extension_loaded('gd') && function_exists('imagecreatefromjpeg')) {
            list($width, $height) = getimagesize($main_path);
            $thumb = imagecreatetruecolor(200, 200);
            $source = null;
            switch ($file['type']) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($main_path);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($main_path);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($main_path);
                    break;
                case 'image/webp':
                    $source = imagecreatefromwebp($main_path);
                    break;
            }
            if ($source) {
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, 200, 200, $width, $height);
                imagejpeg($thumb, $thumb_path, 90);
                imagedestroy($thumb);
                imagedestroy($source);
            } else {
                copy($main_path, $thumb_path);
            }
        } else {
            // Nếu không có GD, chỉ copy ảnh gốc
            copy($main_path, $thumb_path);
        }

        $stmt = $pdo->prepare("INSERT INTO hinh_anh_san_pham (san_pham_id, url_hinh_anh) VALUES (?, ?)");
        $stmt->execute([$product_id, $new_filename]);
        return ['id' => (int)$pdo->lastInsertId(), 'url_hinh_anh' => $new_filename, 'la_anh_dai_dien' => 0];
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Phương thức không hợp lệ.');

    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => 'Hành động không xác định.'];
    $protected_actions = ['save_product', 'delete_image', 'toggle_status', 'delete_taxonomy_term', 'add_attribute', 'update_attribute', 'delete_attribute', 'add_value', 'update_value', 'delete_value'];

    if (in_array($action, $protected_actions)) {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            throw new Exception('Lỗi xác thực CSRF! Vui lòng tải lại trang.');
        }
    }

    $pdo->beginTransaction();

    switch ($action) {
        case 'save_product':
            $id = (int)($_POST['id'] ?? 0);
            $is_edit = $id > 0;
            $product_type = $_POST['loai_san_pham'] ?? 'simple';
            $slug = !empty(trim($_POST['slug'])) ? trim($_POST['slug']) : generate_slug($_POST['ten_san_pham']);

            // Xử lý thương hiệu có thể tạo mới
            $thuong_hieu_input = $_POST['thuong_hieu_id'] ?? 0;
            $thuong_hieu_id = null;
            if (is_numeric($thuong_hieu_input) && $thuong_hieu_input > 0) {
                $thuong_hieu_id = (int)$thuong_hieu_input;
            } elseif (!empty(trim($thuong_hieu_input))) {
                $brand_name = trim($thuong_hieu_input);
                $stmt_check = $pdo->prepare("SELECT id FROM thuong_hieu WHERE ten_thuong_hieu = ?");
                $stmt_check->execute([$brand_name]);
                if ($existing_brand_id = $stmt_check->fetchColumn()) {
                    $thuong_hieu_id = $existing_brand_id;
                } else {
                    $new_brand_slug = generate_slug($brand_name);
                    $stmt_insert = $pdo->prepare("INSERT INTO thuong_hieu (ten_thuong_hieu, slug) VALUES (?, ?)");
                    $stmt_insert->execute([$brand_name, $new_brand_slug]);
                    $thuong_hieu_id = $pdo->lastInsertId();
                }
            }

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
            $pdo->prepare($sql)->execute($product_data);
            if (!$is_edit) $id = $pdo->lastInsertId();

            if (isset($_FILES['hinh_anh']) && is_array($_FILES['hinh_anh']['name'])) {
                foreach ($_FILES['hinh_anh']['name'] as $key => $name) {
                    if ($_FILES['hinh_anh']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = ['name' => $name, 'type' => $_FILES['hinh_anh']['type'][$key], 'tmp_name' => $_FILES['hinh_anh']['tmp_name'][$key], 'error' => $_FILES['hinh_anh']['error'][$key], 'size' => $_FILES['hinh_anh']['size'][$key]];
                        handle_image_upload($file, $id);
                    }
                }
            }

            $main_image_id = (int)($_POST['main_image'] ?? 0);
            $pdo->prepare("UPDATE hinh_anh_san_pham SET la_anh_dai_dien = 0 WHERE san_pham_id = ?")->execute([$id]);
            if ($main_image_id > 0) {
                $pdo->prepare("UPDATE hinh_anh_san_pham SET la_anh_dai_dien = 1 WHERE id = ? AND san_pham_id = ?")->execute([$main_image_id, $id]);
            } else {
                $first_img_id = $pdo->query("SELECT id FROM hinh_anh_san_pham WHERE san_pham_id = $id ORDER BY id ASC LIMIT 1")->fetchColumn();
                if ($first_img_id) $pdo->prepare("UPDATE hinh_anh_san_pham SET la_anh_dai_dien = 1 WHERE id = ?")->execute([$first_img_id]);
            }

            if ($product_type === 'variable') {
                $submitted_variants = $_POST['variants'] ?? [];
                $submitted_variant_ids = [];
                if (!empty($submitted_variants)) {
                    foreach ($submitted_variants as $variant_data) {
                        $variant_id = (int)($variant_data['id'] ?? 0);

                        $identifiers_text = trim($variant_data['unique_identifiers'] ?? '');
                        if (empty($identifiers_text)) throw new Exception("Mã định danh là bắt buộc cho mỗi biến thể.");

                        $identifier_count = count(array_filter(explode("\n", $identifiers_text), 'trim'));
                        $stock_submitted = trim($variant_data['so_luong_ton'] ?? '');
                        if ($stock_submitted !== '' && (int)$stock_submitted !== $identifier_count) {
                            $options_text = $variant_data['options_text'] ?? 'chưa xác định';
                            throw new Exception("Lỗi ở biến thể '{$options_text}': Số lượng tồn ({$stock_submitted}) không khớp với số lượng Mã định danh ({$identifier_count}).");
                        }

                        $so_luong_ton = $identifier_count;

                        $variant_sql_data = [
                            'san_pham_id' => $id,
                            'ma_bien_the' => null,
                            'gia' => (float)($variant_data['gia'] ?? 0),
                            'gia_khuyen_mai' => empty($variant_data['gia_khuyen_mai']) ? null : (float)$variant_data['gia_khuyen_mai'],
                            'so_luong_ton' => $so_luong_ton,
                            'unique_identifiers' => empty($identifiers_text) ? null : $identifiers_text,
                            'hinh_anh_id' => empty($variant_data['hinh_anh_id']) ? null : (int)$variant_data['hinh_anh_id']
                        ];
                        if ($variant_id > 0) {
                            $variant_sql_data['id'] = $variant_id;
                            $pdo->prepare("UPDATE product_variants SET san_pham_id=:san_pham_id, ma_bien_the=:ma_bien_the, gia=:gia, gia_khuyen_mai=:gia_khuyen_mai, so_luong_ton=:so_luong_ton, unique_identifiers=:unique_identifiers, hinh_anh_id=:hinh_anh_id WHERE id=:id")->execute($variant_sql_data);
                        } else {
                            $pdo->prepare("INSERT INTO product_variants (san_pham_id, ma_bien_the, gia, gia_khuyen_mai, so_luong_ton, unique_identifiers, hinh_anh_id) VALUES (:san_pham_id, :ma_bien_the, :gia, :gia_khuyen_mai, :so_luong_ton, :unique_identifiers, :hinh_anh_id)")->execute($variant_sql_data);
                            $variant_id = $pdo->lastInsertId();
                        }
                        $submitted_variant_ids[] = $variant_id;
                        $pdo->prepare("DELETE FROM variant_option_values WHERE variant_id = ?")->execute([$variant_id]);
                        $option_values = explode(',', $variant_data['options_flat'] ?? '');
                        if (!empty($option_values[0])) {
                            $stmt_vov = $pdo->prepare("INSERT INTO variant_option_values (variant_id, value_id) VALUES (?, ?)");
                            foreach ($option_values as $value_id) $stmt_vov->execute([$variant_id, (int)$value_id]);
                        }
                    }
                }
                if ($is_edit) {
                    if (!empty($submitted_variant_ids)) {
                        $placeholders = implode(',', array_fill(0, count($submitted_variant_ids), '?'));
                        $params = array_merge([$id], $submitted_variant_ids);
                        $pdo->prepare("DELETE FROM product_variants WHERE san_pham_id = ? AND id NOT IN ({$placeholders})")->execute($params);
                    } else {
                        $pdo->prepare("DELETE FROM product_variants WHERE san_pham_id = ?")->execute([$id]);
                    }
                }
            } else {
                $pdo->prepare("DELETE FROM product_variants WHERE san_pham_id = ?")->execute([$id]);
            }

            $pdo->prepare("DELETE FROM gia_tri_thuoc_tinh WHERE san_pham_id = ?")->execute([$id]);
            if (isset($_POST['attributes']) && is_array($_POST['attributes'])) {
                $stmt_attr = $pdo->prepare("INSERT INTO gia_tri_thuoc_tinh (san_pham_id, thuoc_tinh_id, gia_tri) VALUES (?, ?, ?)");
                foreach ($_POST['attributes'] as $thuoc_tinh_id => $gia_tri) {
                    if (!empty(trim($gia_tri))) $stmt_attr->execute([$id, (int)$thuoc_tinh_id, trim($gia_tri)]);
                }
            }
            $response = ['success' => true, 'message' => 'Lưu sản phẩm thành công!', 'redirect_url' => 'product_edit.php?id=' . $id, 'is_new_product' => !$is_edit, 'product_id' => $id];
            break;

        case 'delete_image':
            $img_id = (int)($_POST['img_id'] ?? 0);
            $product_id = (int)($_POST['product_id'] ?? 0);
            if ($img_id <= 0 || $product_id <= 0) throw new Exception("Dữ liệu không hợp lệ.");
            $stmt_img = $pdo->prepare("SELECT url_hinh_anh, la_anh_dai_dien FROM hinh_anh_san_pham WHERE id = ? AND san_pham_id = ?");
            $stmt_img->execute([$img_id, $product_id]);
            $img = $stmt_img->fetch(PDO::FETCH_ASSOC);
            if (!$img) throw new Exception("Không tìm thấy ảnh hoặc bạn không có quyền xóa.");
            @unlink(ROOT_PATH . '/uploads/products/' . $img['url_hinh_anh']);
            @unlink(ROOT_PATH . '/uploads/products/thumbs/' . $img['url_hinh_anh']);
            $pdo->prepare("DELETE FROM hinh_anh_san_pham WHERE id = ?")->execute([$img_id]);
            if ($img['la_anh_dai_dien'] == 1) {
                $first_img_id = $pdo->query("SELECT id FROM hinh_anh_san_pham WHERE san_pham_id = $product_id ORDER BY id ASC LIMIT 1")->fetchColumn();
                if ($first_img_id) $pdo->prepare("UPDATE hinh_anh_san_pham SET la_anh_dai_dien = 1 WHERE id = ?")->execute([$first_img_id]);
            }
            $response = ['success' => true, 'message' => 'Xóa ảnh thành công.'];
            break;

        case 'delete_taxonomy_term':
            $term_id = (int)($_POST['id'] ?? 0);
            $type = $_POST['type'] ?? '';
            if ($term_id <= 0 || !in_array($type, ['brand', 'attribute'])) throw new Exception("Dữ liệu không hợp lệ.");
            if ($type === 'brand') {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM san_pham WHERE thuong_hieu_id = ?");
                $stmt->execute([$term_id]);
                if ($stmt->fetchColumn() > 0) throw new Exception("Không thể xóa thương hiệu đã có sản phẩm.");
                $pdo->prepare("DELETE FROM thuong_hieu WHERE id = ?")->execute([$term_id]);
            } elseif ($type === 'attribute') {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM gia_tri_thuoc_tinh WHERE thuoc_tinh_id = ?");
                $stmt->execute([$term_id]);
                if ($stmt->fetchColumn() > 0) throw new Exception("Không thể xóa thuộc tính đã có sản phẩm sử dụng.");
                $pdo->prepare("DELETE FROM thuoc_tinh WHERE id = ?")->execute([$term_id]);
            }
            $response = ['success' => true, 'message' => 'Xóa thành công.'];
            break;

        case 'add_attribute':
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) throw new Exception("Tên thuộc tính không được để trống.");
            $stmt = $pdo->prepare("INSERT INTO thuoc_tinh_bien_the (ten_thuoc_tinh) VALUES (?)");
            $stmt->execute([$name]);
            $response = ['success' => true, 'id' => $pdo->lastInsertId(), 'name' => $name];
            break;

        case 'update_attribute':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            if (empty($name) || $id === 0) throw new Exception("Dữ liệu không hợp lệ.");
            $pdo->prepare("UPDATE thuoc_tinh_bien_the SET ten_thuoc_tinh = ? WHERE id = ?")->execute([$name, $id]);
            $response = ['success' => true];
            break;

        case 'delete_attribute':
            $id = (int)($_POST['id'] ?? 0);
            if ($id === 0) throw new Exception("ID không hợp lệ.");
            $pdo->prepare("DELETE FROM thuoc_tinh_bien_the WHERE id = ?")->execute([$id]);
            $response = ['success' => true];
            break;

        case 'add_value':
            $attr_id = (int)($_POST['attribute_id'] ?? 0);
            $value = trim($_POST['value'] ?? '');
            if (empty($value) || $attr_id === 0) throw new Exception("Dữ liệu không hợp lệ.");
            $stmt = $pdo->prepare("INSERT INTO gia_tri_thuoc_tinh_bien_the (thuoc_tinh_id, gia_tri) VALUES (?, ?)");
            $stmt->execute([$attr_id, $value]);
            $response = ['success' => true, 'id' => $pdo->lastInsertId(), 'value' => $value];
            break;

        case 'update_value':
            $id = (int)($_POST['id'] ?? 0);
            $value = trim($_POST['value'] ?? '');
            if (empty($value) || $id === 0) throw new Exception("Dữ liệu không hợp lệ.");
            $pdo->prepare("UPDATE gia_tri_thuoc_tinh_bien_the SET gia_tri = ? WHERE id = ?")->execute([$value, $id]);
            $response = ['success' => true];
            break;

        case 'delete_value':
            $id = (int)($_POST['id'] ?? 0);
            if ($id === 0) throw new Exception("ID không hợp lệ.");
            $pdo->prepare("DELETE FROM gia_tri_thuoc_tinh_bien_the WHERE id = ?")->execute([$id]);
            $response = ['success' => true];
            break;
    }

    if ($pdo->inTransaction()) {
        $pdo->commit();
    }

    if ($action === 'save_product') {
        $all_images_latest = $pdo->query("SELECT id, url_hinh_anh, la_anh_dai_dien FROM hinh_anh_san_pham WHERE san_pham_id = $id ORDER BY la_anh_dai_dien DESC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $response['all_images'] = $all_images_latest;
    }

    echo json_encode($response);
} catch (Throwable $e) {
    handle_fatal_error($e);
}
