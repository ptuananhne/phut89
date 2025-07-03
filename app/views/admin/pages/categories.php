<?php
// FILE: /app/views/admin/pages/categories.php
// MÔ TẢ: Giao diện trang quản lý Danh mục.
// Dữ liệu được truyền từ CategoryController.php

?>
<style>
    .sort-handle { cursor: grab; text-align: center; width: 40px; color: #aaa; }
    .sort-handle-col { width: 40px; }
    .table-sortable .sortable-ghost { opacity: 0.4; background: #f0f0f0; }
    .checkbox-item-wrapper { display: flex; align-items: center; gap: 5px; }
    .btn-delete-term { background: none; border: none; color: #dc3545; cursor: pointer; font-size: 1.2rem; line-height: 1; padding: 0 5px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; }
    .btn-delete-term:hover { background-color: #f8d7da; }
</style>

<div class="form-grid" style="align-items: flex-start;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?php echo $id_edit ? 'Sửa Danh mục' : 'Thêm Danh mục mới'; ?></h2>
        </div>
        <div class="card-body">
            <form id="category-form" action="<?php echo BASE_URL; ?>/admin/categories" method="POST">
                <input type="hidden" name="action" value="<?php echo $id_edit ? 'edit' : 'add'; ?>">
                <input type="hidden" name="id" value="<?php echo $id_edit; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

                <div class="form-group">
                    <label for="name_for_slug">Tên danh mục</label>
                    <input type="text" class="form-control" name="ten_danh_muc" id="name_for_slug" value="<?php echo e($category_edit['ten_danh_muc'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="slug">Slug (URL)</label>
                    <input type="text" class="form-control" name="slug" id="slug" value="<?php echo e($category_edit['slug'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Thương hiệu áp dụng</label>
                    <div class="form-group-checkbox-grid">
                        <?php foreach ($all_brands as $brand): ?>
                            <div class="checkbox-item-wrapper">
                                <input type="checkbox" name="brands[]" value="<?php echo $brand['id']; ?>" id="brand_<?php echo $brand['id']; ?>" <?php if (in_array($brand['id'], $linked_brands)) echo 'checked'; ?>>
                                <label for="brand_<?php echo $brand['id']; ?>"><?php echo e($brand['ten_thuong_hieu']); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Thuộc tính Thông số kỹ thuật</label>
                    <div class="form-group-checkbox-grid">
                        <?php foreach ($all_attributes as $attribute): ?>
                            <div class="checkbox-item-wrapper">
                                <input type="checkbox" name="attributes[]" value="<?php echo $attribute['id']; ?>" id="attr_<?php echo $attribute['id']; ?>" <?php if (in_array($attribute['id'], $linked_attributes)) echo 'checked'; ?>>
                                <label for="attr_<?php echo $attribute['id']; ?>"><?php echo e($attribute['ten_thuoc_tinh']); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><?php echo $id_edit ? 'Cập nhật' : 'Thêm mới'; ?></button>
                <?php if ($id_edit): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/categories" class="btn btn-secondary">Hủy</a>
                <?php endif; ?>
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
                    <?php if (!empty($all_categories)): ?>
                        <?php foreach ($all_categories as $cat): ?>
                            <tr data-id="<?php echo $cat['id']; ?>">
                                <td class="sort-handle"><i class="fas fa-bars"></i></td>
                                <td><?php echo e($cat['ten_danh_muc']); ?></td>
                                <td><?php echo e($cat['slug']); ?></td>
                                <td class="actions">
                                    <a href="<?php echo BASE_URL; ?>/admin/categories?id_edit=<?php echo $cat['id']; ?>" class="btn btn-secondary" title="Sửa"><i class="fas fa-edit"></i></a>
                                    <form action="<?php echo BASE_URL; ?>/admin/categories" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa? Thao tác này có thể ảnh hưởng đến các sản phẩm liên quan.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                        <button type="submit" class="btn btn-danger" title="Xóa"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Chưa có danh mục nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p><small><i>Mẹo: Giữ và kéo icon <i class="fas fa-bars"></i> để thay đổi thứ tự.</i></small></p>
        </div>
    </div>
</div>
<script>
    // Truyền dữ liệu cần thiết cho JavaScript
    const csrfToken = '<?php echo e($csrf_token); ?>';
    const ajaxUrl_updateOrder = '<?php echo BASE_URL; ?>/admin/ajax/update_category_order';
</script>
