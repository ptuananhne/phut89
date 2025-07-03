<?php
// FILE: /app/controllers/admin/CategoryController.php
namespace Admin;

class CategoryController extends AdminBaseController
{
    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'add' || $action === 'edit') {
                $this->handlePost();
            } elseif ($action === 'delete') {
                $this->handleDelete();
            }
        }

        $id_edit = (int)($_GET['id_edit'] ?? 0);
        $category_edit = null;
        $linked_brands = [];
        $linked_attributes = [];

        if ($id_edit > 0) {
            $stmt = $this->pdo->prepare("SELECT * FROM danh_muc WHERE id = ?");
            $stmt->execute([$id_edit]);
            $category_edit = $stmt->fetch();

            if ($category_edit) {
                $linked_brands = $this->pdo->query("SELECT thuong_hieu_id FROM danhmuc_thuonghieu WHERE danh_muc_id = $id_edit")->fetchAll(\PDO::FETCH_COLUMN);
                $linked_attributes = $this->pdo->query("SELECT thuoc_tinh_id FROM danhmuc_thuoc_tinh WHERE danh_muc_id = $id_edit")->fetchAll(\PDO::FETCH_COLUMN);
            }
        }

        $data = [
            'page_title' => 'Quản lý Danh mục',
            'all_categories' => $this->pdo->query("SELECT * FROM danh_muc ORDER BY vi_tri ASC")->fetchAll(),
            'all_brands' => $this->pdo->query("SELECT id, ten_thuong_hieu FROM thuong_hieu ORDER BY ten_thuong_hieu")->fetchAll(),
            'all_attributes' => $this->pdo->query("SELECT id, ten_thuoc_tinh FROM thuoc_tinh ORDER BY ten_thuoc_tinh")->fetchAll(),
            'category_edit' => $category_edit,
            'id_edit' => $id_edit,
            'linked_brands' => $linked_brands,
            'linked_attributes' => $linked_attributes,
        ];

        $this->render('pages/categories', $data);
    }

    private function handlePost()
    {
        if (!$this->verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['message'] = 'Lỗi xác thực CSRF!';
            $_SESSION['message_type'] = 'danger';
        } else {
            $id = (int)($_POST['id'] ?? 0);
            $ten_danh_muc = trim($_POST['ten_danh_muc']);
            $slug = !empty(trim($_POST['slug'])) ? trim($_POST['slug']) : generate_slug($ten_danh_muc);
            $brands = $_POST['brands'] ?? [];
            $attributes = $_POST['attributes'] ?? [];
            $new_brand_name = trim($_POST['new_brand_name'] ?? '');
            $new_attribute_name = trim($_POST['new_attribute_name'] ?? '');

            $this->pdo->beginTransaction();
            try {
                if (!empty($new_brand_name)) {
                    $stmt_check = $this->pdo->prepare("SELECT id FROM thuong_hieu WHERE ten_thuong_hieu = ?");
                    $stmt_check->execute([$new_brand_name]);
                    if (!$existing_brand_id = $stmt_check->fetchColumn()) {
                        $new_brand_slug = generate_slug($new_brand_name);
                        $this->pdo->prepare("INSERT INTO thuong_hieu (ten_thuong_hieu, slug) VALUES (?, ?)")->execute([$new_brand_name, $new_brand_slug]);
                        $brands[] = $this->pdo->lastInsertId();
                    } elseif (!in_array($existing_brand_id, $brands)) {
                        $brands[] = $existing_brand_id;
                    }
                }

                if (!empty($new_attribute_name)) {
                    $stmt_check = $this->pdo->prepare("SELECT id FROM thuoc_tinh WHERE ten_thuoc_tinh = ?");
                    $stmt_check->execute([$new_attribute_name]);
                    if (!$existing_attr_id = $stmt_check->fetchColumn()) {
                        $this->pdo->prepare("INSERT INTO thuoc_tinh (ten_thuoc_tinh) VALUES (?)")->execute([$new_attribute_name]);
                        $attributes[] = $this->pdo->lastInsertId();
                    } elseif (!in_array($existing_attr_id, $attributes)) {
                        $attributes[] = $existing_attr_id;
                    }
                }

                if ($id > 0) {
                    $this->pdo->prepare("UPDATE danh_muc SET ten_danh_muc = ?, slug = ? WHERE id = ?")->execute([$ten_danh_muc, $slug, $id]);
                    $_SESSION['message'] = 'Cập nhật danh mục thành công!';
                } else {
                    $max_pos = $this->pdo->query("SELECT MAX(vi_tri) FROM danh_muc")->fetchColumn() ?? -1;
                    $this->pdo->prepare("INSERT INTO danh_muc (ten_danh_muc, slug, vi_tri) VALUES (?, ?, ?)")->execute([$ten_danh_muc, $slug, $max_pos + 1]);
                    $id = $this->pdo->lastInsertId();
                    $_SESSION['message'] = 'Thêm danh mục mới thành công!';
                }

                $this->pdo->prepare("DELETE FROM danhmuc_thuonghieu WHERE danh_muc_id = ?")->execute([$id]);
                if (!empty($brands)) {
                    $stmt_brand_link = $this->pdo->prepare("INSERT INTO danhmuc_thuonghieu (danh_muc_id, thuong_hieu_id) VALUES (?, ?)");
                    foreach ($brands as $brand_id) $stmt_brand_link->execute([$id, (int)$brand_id]);
                }

                $this->pdo->prepare("DELETE FROM danhmuc_thuoc_tinh WHERE danh_muc_id = ?")->execute([$id]);
                if (!empty($attributes)) {
                    $stmt_attr_link = $this->pdo->prepare("INSERT INTO danhmuc_thuoc_tinh (danh_muc_id, thuoc_tinh_id) VALUES (?, ?)");
                    foreach ($attributes as $attr_id) $stmt_attr_link->execute([$id, (int)$attr_id]);
                }

                $this->pdo->commit();
                $_SESSION['message_type'] = 'success';
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                $_SESSION['message'] = 'Đã xảy ra lỗi: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
        header('Location: ' . \BASE_URL . '/admin/categories' . ($id > 0 ? '?id_edit=' . $id : ''));
        exit;
    }

    private function handleDelete()
    {
        if (!$this->verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['message'] = 'Lỗi xác thực CSRF!';
            $_SESSION['message_type'] = 'danger';
        } else {
            $id = (int)($_POST['id'] ?? 0);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM san_pham WHERE danh_muc_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['message'] = 'Không thể xóa danh mục đã có sản phẩm.';
                $_SESSION['message_type'] = 'danger';
            } else {
                $this->pdo->prepare("DELETE FROM danh_muc WHERE id = ?")->execute([$id]);
                $_SESSION['message'] = 'Xóa danh mục thành công!';
                $_SESSION['message_type'] = 'success';
            }
        }
        header('Location: ' . \BASE_URL . '/admin/categories');
        exit;
    }
}
