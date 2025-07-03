<?php
// FILE: /app/controllers/admin/BrandController.php
// MÔ TẢ: Controller quản lý Thương hiệu.

namespace Admin;

class BrandController extends AdminBaseController
{

    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $this->handlePost();
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $this->handleDelete();
        }

        $id_edit = (int)($_GET['id_edit'] ?? 0);
        $brand_edit = null;
        if ($id_edit > 0) {
            $stmt = $this->pdo->prepare("SELECT * FROM thuong_hieu WHERE id = ?");
            $stmt->execute([$id_edit]);
            $brand_edit = $stmt->fetch();
        }

        $data = [
            'page_title' => 'Quản lý Thương hiệu',
            'all_brands' => $this->pdo->query("SELECT * FROM thuong_hieu ORDER BY ten_thuong_hieu ASC")->fetchAll(),
            'brand_edit' => $brand_edit,
            'id_edit' => $id_edit,
        ];

        $this->render('pages/brands', $data);
    }

    private function handlePost()
    {
        if (!$this->verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['message'] = 'Lỗi xác thực CSRF!';
            $_SESSION['message_type'] = 'danger';
            header('Location: ' . \BASE_URL . '/admin/brands');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $ten_thuong_hieu = trim($_POST['ten_thuong_hieu']);
        $slug = !empty(trim($_POST['slug'])) ? trim($_POST['slug']) : generate_slug($ten_thuong_hieu);

        if (empty($ten_thuong_hieu) || empty($slug)) {
            $_SESSION['message'] = 'Tên thương hiệu và Slug không được để trống.';
            $_SESSION['message_type'] = 'danger';
        } else {
            try {
                if ($id > 0) {
                    $stmt = $this->pdo->prepare("UPDATE thuong_hieu SET ten_thuong_hieu = ?, slug = ? WHERE id = ?");
                    $stmt->execute([$ten_thuong_hieu, $slug, $id]);
                    $_SESSION['message'] = 'Cập nhật thương hiệu thành công!';
                } else {
                    $stmt = $this->pdo->prepare("INSERT INTO thuong_hieu (ten_thuong_hieu, slug) VALUES (?, ?)");
                    $stmt->execute([$ten_thuong_hieu, $slug]);
                    $_SESSION['message'] = 'Thêm thương hiệu thành công!';
                }
                $_SESSION['message_type'] = 'success';
            } catch (\PDOException $e) {
                $_SESSION['message'] = ($e->getCode() == 23000) ? 'Lỗi: Tên thương hiệu hoặc Slug đã tồn tại.' : 'Lỗi CSDL: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
        header('Location: ' . \BASE_URL . '/admin/brands');
        exit;
    }

    private function handleDelete()
    {
        $id = (int)$_GET['id'];
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM san_pham WHERE thuong_hieu_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['message'] = 'Không thể xóa thương hiệu đã có sản phẩm.';
            $_SESSION['message_type'] = 'danger';
        } else {
            $this->pdo->beginTransaction();
            try {
                $this->pdo->prepare("DELETE FROM danhmuc_thuonghieu WHERE thuong_hieu_id = ?")->execute([$id]);
                $this->pdo->prepare("DELETE FROM thuong_hieu WHERE id = ?")->execute([$id]);
                $this->pdo->commit();
                $_SESSION['message'] = 'Xóa thương hiệu thành công!';
                $_SESSION['message_type'] = 'success';
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                $_SESSION['message'] = 'Lỗi khi xóa thương hiệu: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
        header('Location: ' . \BASE_URL . '/admin/brands');
        exit;
    }
}
