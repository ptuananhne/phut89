<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// XỬ LÝ AJAX CẬP NHẬT THỨ TỰ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action']) && $input['action'] === 'update_order' && isset($input['order']) && is_array($input['order'])) {
        $order = $input['order'];
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE danh_muc SET vi_tri = ? WHERE id = ?");
            foreach ($order as $position => $id) {
                $stmt->execute([$position, (int)$id]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.']);
    }
    exit;
}

if (session_status() == PHP_SESSION_NONE) session_start();
$page_title = 'Quản lý Danh mục';
require_once 'includes/header.php';

// [UPDATED] XỬ LÝ FORM THÊM/SỬA - Bổ sung logic thêm thương hiệu mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['message'] = 'Lỗi xác thực CSRF!';
        $_SESSION['message_type'] = 'danger';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $ten_danh_muc = trim($_POST['ten_danh_muc']);
        $slug = !empty(trim($_POST['slug'])) ? trim($_POST['slug']) : generate_slug($ten_danh_muc);
        $brands = $_POST['brands'] ?? [];
        $attributes = $_POST['attributes'] ?? [];

        // [NEW] Lấy dữ liệu từ ô nhập thương hiệu và thuộc tính mới
        $new_brand_name = trim($_POST['new_brand_name'] ?? '');
        $new_attribute_name = trim($_POST['new_attribute_name'] ?? '');

        $pdo->beginTransaction();
        try {
            // [NEW] Xử lý thêm thương hiệu mới nếu có
            if (!empty($new_brand_name)) {
                $stmt_check = $pdo->prepare("SELECT id FROM thuong_hieu WHERE ten_thuong_hieu = ?");
                $stmt_check->execute([$new_brand_name]);
                if (!$existing_brand_id = $stmt_check->fetchColumn()) {
                    // Nếu chưa tồn tại, thêm mới
                    $new_brand_slug = generate_slug($new_brand_name);
                    $pdo->prepare("INSERT INTO thuong_hieu (ten_thuong_hieu, slug) VALUES (?, ?)")->execute([$new_brand_name, $new_brand_slug]);
                    $brands[] = $pdo->lastInsertId(); // Tự động chọn thương hiệu vừa thêm
                } elseif (!in_array($existing_brand_id, $brands)) {
                    // Nếu đã tồn tại nhưng chưa được chọn, thì chọn nó
                    $brands[] = $existing_brand_id;
                }
            }

            // Xử lý thêm thuộc tính mới (giữ nguyên)
            if (!empty($new_attribute_name)) {
                $stmt_check = $pdo->prepare("SELECT id FROM thuoc_tinh WHERE ten_thuoc_tinh = ?");
                $stmt_check->execute([$new_attribute_name]);
                if (!$existing_attr_id = $stmt_check->fetchColumn()) {
                    $pdo->prepare("INSERT INTO thuoc_tinh (ten_thuoc_tinh) VALUES (?)")->execute([$new_attribute_name]);
                    $attributes[] = $pdo->lastInsertId();
                } elseif (!in_array($existing_attr_id, $attributes)) {
                    $attributes[] = $existing_attr_id;
                }
            }

            // Xử lý lưu danh mục (giữ nguyên)
            if ($id > 0) {
                $pdo->prepare("UPDATE danh_muc SET ten_danh_muc = ?, slug = ? WHERE id = ?")->execute([$ten_danh_muc, $slug, $id]);
                $_SESSION['message'] = 'Cập nhật danh mục thành công!';
            } else {
                $max_pos = $pdo->query("SELECT MAX(vi_tri) FROM danh_muc")->fetchColumn() ?? -1;
                $pdo->prepare("INSERT INTO danh_muc (ten_danh_muc, slug, vi_tri) VALUES (?, ?, ?)")->execute([$ten_danh_muc, $slug, $max_pos + 1]);
                $id = $pdo->lastInsertId();
                $_SESSION['message'] = 'Thêm danh mục mới thành công!';
            }

            // Xử lý liên kết (giữ nguyên)
            $pdo->prepare("DELETE FROM danhmuc_thuonghieu WHERE danh_muc_id = ?")->execute([$id]);
            if (!empty($brands)) {
                $stmt = $pdo->prepare("INSERT INTO danhmuc_thuonghieu (danh_muc_id, thuong_hieu_id) VALUES (?, ?)");
                foreach ($brands as $brand_id) $stmt->execute([$id, (int)$brand_id]);
            }
            $pdo->prepare("DELETE FROM danhmuc_thuoc_tinh WHERE danh_muc_id = ?")->execute([$id]);
            if (!empty($attributes)) {
                $stmt = $pdo->prepare("INSERT INTO danhmuc_thuoc_tinh (danh_muc_id, thuoc_tinh_id) VALUES (?, ?)");
                foreach ($attributes as $attr_id) $stmt->execute([$id, (int)$attr_id]);
            }

            $pdo->commit();
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['message'] = 'Đã xảy ra lỗi: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    }
    header('Location: categories.php' . ($id_edit ? '?id_edit=' . $id_edit : ''));
    exit;
}


// XỬ LÝ XÓA DANH MỤC
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM san_pham WHERE danh_muc_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['message'] = 'Không thể xóa danh mục đã có sản phẩm.';
        $_SESSION['message_type'] = 'danger';
    } else {
        $pdo->prepare("DELETE FROM danh_muc WHERE id = ?")->execute([$id]);
        $_SESSION['message'] = 'Xóa danh mục thành công!';
        $_SESSION['message_type'] = 'success';
    }
    header('Location: categories.php');
    exit;
}

// LẤY DỮ LIỆU ĐỂ HIỂN THỊ
$id_edit = (int)($_GET['id_edit'] ?? 0);
$category_edit = null;
$linked_brands = [];
$linked_attributes = [];
if ($id_edit > 0) {
    $stmt = $pdo->prepare("SELECT * FROM danh_muc WHERE id = ?");
    $stmt->execute([$id_edit]);
    $category_edit = $stmt->fetch();
    if ($category_edit) {
        $linked_brands = $pdo->query("SELECT thuong_hieu_id FROM danhmuc_thuonghieu WHERE danh_muc_id = $id_edit")->fetchAll(PDO::FETCH_COLUMN);
        $linked_attributes = $pdo->query("SELECT thuoc_tinh_id FROM danhmuc_thuoc_tinh WHERE danh_muc_id = $id_edit")->fetchAll(PDO::FETCH_COLUMN);
    }
}
$all_categories = $pdo->query("SELECT * FROM danh_muc ORDER BY vi_tri ASC")->fetchAll();
$all_brands = $pdo->query("SELECT id, ten_thuong_hieu FROM thuong_hieu ORDER BY ten_thuong_hieu")->fetchAll();
$all_attributes = $pdo->query("SELECT id, ten_thuoc_tinh FROM thuoc_tinh ORDER BY ten_thuoc_tinh")->fetchAll();
?>
<style>
    .sort-handle {
        cursor: grab;
        text-align: center;
        width: 40px;
        color: #aaa;
    }

    .sort-handle-col {
        width: 40px;
    }

    .table-sortable .sortable-ghost {
        opacity: 0.4;
        background: #f0f0f0;
    }

    .checkbox-item-wrapper {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .btn-delete-term {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: 1.2rem;
        line-height: 1;
        padding: 0 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .btn-delete-term:hover {
        background-color: #f8d7da;
    }
</style>

<div class="form-grid" style="align-items: flex-start;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?php echo $id_edit ? 'Sửa Danh mục' : 'Thêm Danh mục mới'; ?></h2>
        </div>
        <div class="card-body">
            <form id="category-form" action="categories.php" method="POST">
                <input type="hidden" name="action" value="<?php echo $id_edit ? 'edit' : 'add'; ?>">
                <input type="hidden" name="id" value="<?php echo $id_edit; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="form-group"><label for="name_for_slug">Tên danh mục</label><input type="text" class="form-control" name="ten_danh_muc" id="name_for_slug" value="<?php echo e($category_edit['ten_danh_muc'] ?? ''); ?>" required></div>
                <div class="form-group"><label for="slug">Slug (URL)</label><input type="text" class="form-control" name="slug" id="slug" value="<?php echo e($category_edit['slug'] ?? ''); ?>" required></div>

                <div class="form-group">
                    <label>Thương hiệu áp dụng</label>
                    <div class="form-group-checkbox-grid">
                        <?php foreach ($all_brands as $brand): ?>
                            <div class="checkbox-item-wrapper">
                                <input type="checkbox" name="brands[]" value="<?php echo $brand['id']; ?>" id="brand_<?php echo $brand['id']; ?>" <?php if (in_array($brand['id'], $linked_brands)) echo 'checked'; ?>>
                                <label for="brand_<?php echo $brand['id']; ?>"><?php echo e($brand['ten_thuong_hieu']); ?></label>
                                <button type="button" class="btn-delete-term" data-term-id="<?php echo $brand['id']; ?>" data-term-type="brand" data-term-name="<?php echo e($brand['ten_thuong_hieu']); ?>" title="Xóa vĩnh viễn thương hiệu này">&times;</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- [NEW] Form group for adding a new brand -->
                <div class="form-group">
                    <label for="new_brand_name">Hoặc thêm thương hiệu mới</label>
                    <input type="text" id="new_brand_name" name="new_brand_name" class="form-control" placeholder="Ví dụ: Apple, Samsung...">
                    <small>Thương hiệu này sẽ được tạo và tự động chọn khi bạn lưu.</small>
                </div>


                <div class="form-group">
                    <label>Thuộc tính áp dụng</label>
                    <div class="form-group-checkbox-grid">
                        <?php foreach ($all_attributes as $attribute): ?>
                            <div class="checkbox-item-wrapper">
                                <input type="checkbox" name="attributes[]" value="<?php echo $attribute['id']; ?>" id="attr_<?php echo $attribute['id']; ?>" <?php if (in_array($attribute['id'], $linked_attributes)) echo 'checked'; ?>>
                                <label for="attr_<?php echo $attribute['id']; ?>"><?php echo e($attribute['ten_thuoc_tinh']); ?></label>
                                <button type="button" class="btn-delete-term" data-term-id="<?php echo $attribute['id']; ?>" data-term-type="attribute" data-term-name="<?php echo e($attribute['ten_thuoc_tinh']); ?>" title="Xóa vĩnh viễn thuộc tính này">&times;</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group"><label for="new_attribute_name">Hoặc thêm thuộc tính mới</label><input type="text" id="new_attribute_name" name="new_attribute_name" class="form-control" placeholder="Ví dụ: Chất liệu, Bảo hành..."><small>Thuộc tính này sẽ được tạo và tự động chọn khi bạn lưu.</small></div>
                <button type="submit" class="btn btn-primary"><?php echo $id_edit ? 'Cập nhật' : 'Thêm mới'; ?></button>
                <?php if ($id_edit): ?><a href="categories.php" class="btn btn-secondary">Hủy</a><?php endif; ?>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Danh sách Danh mục</h2>
        </div>
        <div class="card-body">
            <table class="table-sortable">
                <thead>
                    <tr>
                        <th class="sort-handle-col">Thứ tự</th>
                        <th>Tên</th>
                        <th>Slug</th>
                        <th class="actions">Hành động</th>
                    </tr>
                </thead>
                <tbody id="sortable-categories">
                    <?php foreach ($all_categories as $cat): ?>
                        <tr data-id="<?php echo $cat['id']; ?>">
                            <td class="sort-handle"><i class="fas fa-bars"></i></td>
                            <td><?php echo e($cat['ten_danh_muc']); ?></td>
                            <td><?php echo e($cat['slug']); ?></td>
                            <td class="actions">
                                <a href="categories.php?id_edit=<?php echo $cat['id']; ?>" class="btn btn-secondary" title="Sửa"><i class="fas fa-edit"></i></a>
                                <a href="categories.php?action=delete&id=<?php echo $cat['id']; ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa?');" title="Xóa"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><small><i>Mẹo: Giữ và kéo icon <i class="fas fa-bars"></i> để thay đổi thứ tự.</i></small></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>