<?php
$page_title = 'Quản lý Banner';
require_once 'includes/header.php';

$upload_dir = ROOT_PATH . '/uploads/banners/';

// ===== XỬ LÝ FORM (THÊM) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['message'] = 'Lỗi xác thực CSRF!';
        $_SESSION['message_type'] = 'danger';
        header('Location: banners.php');
        exit;
    }

    $tieu_de = $_POST['tieu_de'] ?? '';
    $lien_ket = $_POST['lien_ket'] ?? '';
    $trang_thai = (int)($_POST['trang_thai'] ?? 1);
    $vi_tri = (int)($_POST['vi_tri'] ?? 0);
    $file_name = '';

    // Kiểm tra file upload
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name_tmp = uniqid() . '-' . basename($_FILES['hinh_anh']['name']);
        if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $upload_dir . $file_name_tmp)) {
            $file_name = $file_name_tmp;
        } else {
            $_SESSION['message'] = 'Lỗi khi di chuyển file đã tải lên. Vui lòng kiểm tra quyền ghi của thư mục uploads.';
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = 'Lỗi: Vui lòng chọn một hình ảnh hợp lệ.';
        $_SESSION['message_type'] = 'danger';
    }

    if (!empty($file_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO banners (tieu_de, hinh_anh, lien_ket, trang_thai, vi_tri) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$tieu_de, $file_name, $lien_ket, $trang_thai, $vi_tri]);
            $_SESSION['message'] = 'Thêm banner thành công!';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Lỗi khi thêm banner vào CSDL: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            @unlink($upload_dir . $file_name);
        }
    }
    header('Location: banners.php');
    exit;
}

// ===== XỬ LÝ XÓA =====
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT hinh_anh FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    if ($img = $stmt->fetchColumn()) {
        $file_path = $upload_dir . $img;
        if (file_exists($file_path)) @unlink($file_path);
    }
    $pdo->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
    $_SESSION['message'] = 'Xóa banner thành công!';
    $_SESSION['message_type'] = 'success';
    header('Location: banners.php');
    exit;
}

$banners = $pdo->query("SELECT * FROM banners ORDER BY vi_tri ASC, id DESC")->fetchAll();
?>

<div class="form-grid" style="align-items: flex-start;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Thêm Banner mới</h2>
        </div>
        <div class="card-body">
            <form action="banners.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                <div class="form-group">
                    <label for="tieu_de">Tiêu đề (không bắt buộc)</label>
                    <input type="text" name="tieu_de" id="tieu_de" class="form-control" placeholder="Ví dụ: Siêu Sale Hè">
                </div>
                <div class="form-group">
                    <label for="lien_ket">Đường dẫn liên kết (ví dụ: /danh-muc/dien-thoai)</label>
                    <input type="text" name="lien_ket" id="lien_ket" class="form-control">
                </div>
                <div class="form-group">
                    <label for="hinh_anh">Hình ảnh (bắt buộc)</label>
                    <input type="file" name="hinh_anh" id="hinh_anh" class="form-control" required accept="image/*">
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="vi_tri">Thứ tự hiển thị</label>
                        <input type="number" name="vi_tri" id="vi_tri" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label for="trang_thai">Trạng thái</label>
                        <select name="trang_thai" id="trang_thai" class="form-control">
                            <option value="1" selected>Hiển thị</option>
                            <option value="0">Ẩn</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Thêm Banner</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Danh sách Banner</h2>
        </div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Trạng thái</th>
                        <th class="actions">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($banners): ?>
                        <?php foreach ($banners as $banner): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo BASE_URL . '/uploads/banners/' . e($banner['hinh_anh'] ?? 'placeholder.png'); ?>" class="thumbnail" alt="Banner">
                                </td>
                                <td><?php echo e($banner['tieu_de']); ?></td>
                                <td>
                                    <button
                                        class="status-toggle-btn status <?php echo $banner['trang_thai'] ? 'active' : 'inactive'; ?>"
                                        data-id="<?php echo $banner['id']; ?>"
                                        data-type="banner"
                                        data-current-status="<?php echo $banner['trang_thai']; ?>">
                                        <?php echo $banner['trang_thai'] ? 'Hiển thị' : 'Ẩn'; ?>
                                    </button>
                                </td>
                                <td class="actions">
                                    <a href="banners.php?action=delete&id=<?php echo $banner['id']; ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa banner này?');" title="Xóa"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">Chưa có banner nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>