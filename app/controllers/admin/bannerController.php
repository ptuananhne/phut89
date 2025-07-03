<?php
// FILE: /app/controllers/admin/BannerController.php
// MÔ TẢ: Controller quản lý Banner.

namespace Admin;

class BannerController extends AdminBaseController
{
    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
            $this->handleAdd();
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $this->handleDelete();
        }

        $data = [
            'page_title' => 'Quản lý Banner',
            'banners' => $this->pdo->query("SELECT * FROM banners ORDER BY vi_tri ASC, id DESC")->fetchAll()
        ];

        $this->render('pages/banners', $data);
    }

    private function handleAdd()
    {
        if (!$this->verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['message'] = 'Lỗi xác thực CSRF!';
            $_SESSION['message_type'] = 'danger';
        } else {
            $tieu_de = $_POST['tieu_de'] ?? '';
            $lien_ket = $_POST['lien_ket'] ?? '';
            $trang_thai = (int)($_POST['trang_thai'] ?? 1);
            $vi_tri = (int)($_POST['vi_tri'] ?? 0);
            $file_name = '';
            $upload_dir = \ROOT_PATH . '/public/uploads/banners/';

            if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file_name_tmp = uniqid() . '-' . basename($_FILES['hinh_anh']['name']);
                if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $upload_dir . $file_name_tmp)) {
                    $file_name = $file_name_tmp;
                } else {
                    $_SESSION['message'] = 'Lỗi khi di chuyển file đã tải lên.';
                    $_SESSION['message_type'] = 'danger';
                }
            } else {
                $_SESSION['message'] = 'Lỗi: Vui lòng chọn một hình ảnh hợp lệ.';
                $_SESSION['message_type'] = 'danger';
            }

            if (!empty($file_name)) {
                try {
                    $stmt = $this->pdo->prepare("INSERT INTO banners (tieu_de, hinh_anh, lien_ket, trang_thai, vi_tri) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$tieu_de, $file_name, $lien_ket, $trang_thai, $vi_tri]);
                    $_SESSION['message'] = 'Thêm banner thành công!';
                    $_SESSION['message_type'] = 'success';
                } catch (\PDOException $e) {
                    $_SESSION['message'] = 'Lỗi khi thêm banner vào CSDL: ' . $e->getMessage();
                    $_SESSION['message_type'] = 'danger';
                    @unlink($upload_dir . $file_name);
                }
            }
        }
        header('Location: ' . \BASE_URL . '/admin/banners');
        exit;
    }

    private function handleDelete()
    {
        $id = (int)$_GET['id'];
        $upload_dir = \ROOT_PATH . '/public/uploads/banners/';
        $stmt = $this->pdo->prepare("SELECT hinh_anh FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        if ($img = $stmt->fetchColumn()) {
            $file_path = $upload_dir . $img;
            if (file_exists($file_path)) @unlink($file_path);
        }
        $this->pdo->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
        $_SESSION['message'] = 'Xóa banner thành công!';
        $_SESSION['message_type'] = 'success';
        header('Location: ' . \BASE_URL . '/admin/banners');
        exit;
    }
}
