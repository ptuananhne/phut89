<?php
$page_title = 'Quản lý Thương hiệu';
require_once 'includes/header.php';

// ===== XỬ LÝ FORM (THÊM/SỬA) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['message'] = 'Lỗi xác thực CSRF!';
        $_SESSION['message_type'] = 'danger';
        header('Location: brands.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $ten_thuong_hieu = trim($_POST['ten_thuong_hieu']);
    $slug = !empty(trim($_POST['slug'])) ? trim($_POST['slug']) : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $ten_thuong_hieu)));

    if (empty($ten_thuong_hieu) || empty($slug)) {
        $_SESSION['message'] = 'Tên thương hiệu và Slug không được để trống.';
        $_SESSION['message_type'] = 'danger';
    } else {
        try {
            if ($id > 0) { // Sửa
                $stmt = $pdo->prepare("UPDATE thuong_hieu SET ten_thuong_hieu = ?, slug = ? WHERE id = ?");
                $stmt->execute([$ten_thuong_hieu, $slug, $id]);
                $_SESSION['message'] = 'Cập nhật thương hiệu thành công!';
            } else { // Thêm
                $stmt = $pdo->prepare("INSERT INTO thuong_hieu (ten_thuong_hieu, slug) VALUES (?, ?)");
                $stmt->execute([$ten_thuong_hieu, $slug]);
                $_SESSION['message'] = 'Thêm thương hiệu thành công!';
            }
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Lỗi trùng lặp
                $_SESSION['message'] = 'Lỗi: Tên thương hiệu hoặc Slug đã tồn tại.';
            } else {
                $_SESSION['message'] = 'Lỗi CSDL: ' . $e->getMessage();
            }
            $_SESSION['message_type'] = 'danger';
        }
    }
    header('Location: brands.php');
    exit;
}

// ===== XỬ LÝ XÓA =====
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM san_pham WHERE thuong_hieu_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['message'] = 'Không thể xóa thương hiệu đã có sản phẩm.';
        $_SESSION['message_type'] = 'danger';
    } else {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("DELETE FROM danhmuc_thuonghieu WHERE thuong_hieu_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM thuong_hieu WHERE id = ?")->execute([$id]);
            $pdo->commit();
            $_SESSION['message'] = 'Xóa thương hiệu thành công!';
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['message'] = 'Lỗi khi xóa thương hiệu: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    }
    header('Location: brands.php');
    exit;
}

// ===== LẤY DỮ LIỆU ĐỂ HIỂN THỊ =====
$id_edit = (int)($_GET['id_edit'] ?? 0);
$brand_edit = null;

if ($id_edit > 0) {
    $stmt = $pdo->prepare("SELECT * FROM thuong_hieu WHERE id = ?");
    $stmt->execute([$id_edit]);
    $brand_edit = $stmt->fetch();
}
$all_brands = $pdo->query("SELECT * FROM thuong_hieu ORDER BY ten_thuong_hieu ASC")->fetchAll();
?>

<div class="form-grid" style="align-items: flex-start;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?php echo $id_edit ? 'Sửa Thương hiệu' : 'Thêm Thương hiệu mới'; ?></h2>
        </div>
        <div class="card-body">
            <form action="brands.php" method="POST">
                <input type="hidden" name="action" value="<?php echo $id_edit ? 'edit' : 'add'; ?>">
                <input type="hidden" name="id" value="<?php echo $id_edit; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

                <div class="form-group">
                    <label for="name_for_slug">Tên thương hiệu</label>
                    <input type="text" class="form-control" name="ten_thuong_hieu" id="name_for_slug" value="<?php echo e($brand_edit['ten_thuong_hieu'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="slug">Slug (URL)</label>
                    <input type="text" class="form-control" name="slug" id="slug" value="<?php echo e($brand_edit['slug'] ?? ''); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary"><?php echo $id_edit ? 'Cập nhật' : 'Thêm mới'; ?></button>
                <?php if ($id_edit): ?>
                    <a href="brands.php" class="btn btn-secondary">Hủy</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Danh sách Thương hiệu</h2>
        </div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Slug</th>
                        <th class="actions">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($all_brands): ?>
                        <?php foreach ($all_brands as $brand): ?>
                            <tr>
                                <td><?php echo $brand['id']; ?></td>
                                <td><?php echo e($brand['ten_thuong_hieu']); ?></td>
                                <td><?php echo e($brand['slug']); ?></td>
                                <td class="actions">
                                    <a href="brands.php?id_edit=<?php echo $brand['id']; ?>" class="btn btn-secondary" title="Sửa"><i class="fas fa-edit"></i></a>
                                    <a href="brands.php?action=delete&id=<?php echo $brand['id']; ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa?');" title="Xóa"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">Chưa có thương hiệu nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>