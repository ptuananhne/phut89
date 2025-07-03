<?php
// FILE: /app/views/admin/pages/brands.php
// MÔ TẢ: Giao diện trang quản lý Thương hiệu.
//        Dữ liệu $all_brands, $brand_edit, $id_edit, $csrf_token
//        được truyền từ BrandController.php

?>
<div class="form-grid" style="align-items: flex-start;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?php echo $id_edit ? 'Sửa Thương hiệu' : 'Thêm Thương hiệu mới'; ?></h2>
        </div>
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>/admin/brands" method="POST">
                <input type="hidden" name="action" value="<?php echo $id_edit ? 'edit' : 'add'; ?>">
                <input type="hidden" name="id" value="<?php echo $id_edit; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

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
                    <a href="<?php echo BASE_URL; ?>/admin/brands" class="btn btn-secondary">Hủy</a>
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
                    <?php if (!empty($all_brands)): ?>
                        <?php foreach ($all_brands as $brand): ?>
                            <tr>
                                <td><?php echo $brand['id']; ?></td>
                                <td><?php echo e($brand['ten_thuong_hieu']); ?></td>
                                <td><?php echo e($brand['slug']); ?></td>
                                <td class="actions">
                                    <a href="<?php echo BASE_URL; ?>/admin/brands?id_edit=<?php echo $brand['id']; ?>" class="btn btn-secondary" title="Sửa"><i class="fas fa-edit"></i></a>
                                    
                                    <form action="<?php echo BASE_URL; ?>/admin/brands" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thương hiệu này?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $brand['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                        <button type="submit" class="btn btn-danger" title="Xóa"><i class="fas fa-trash"></i></button>
                                    </form>
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
