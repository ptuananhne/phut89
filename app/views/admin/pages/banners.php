<?php
// FILE: /app/views/admin/pages/banners.php
// MÔ TẢ: Giao diện trang quản lý Banner.
//        Dữ liệu $banners và $csrf_token được truyền từ BannerController.php

?>
<div class="form-grid" style="align-items: flex-start;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Thêm Banner mới</h2>
        </div>
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>/admin/banners" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

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
                    <?php if (!empty($banners)): ?>
                        <?php foreach ($banners as $banner): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo BASE_URL . '/uploads/banners/' . e($banner['hinh_anh']); ?>" class="thumbnail" alt="Banner">
                                </td>
                                <td><?php echo e($banner['tieu_de']); ?></td>
                                <td>
                                    <form action="<?php echo BASE_URL; ?>/admin/banners" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                        <button type="submit" class="status <?php echo $banner['trang_thai'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $banner['trang_thai'] ? 'Hiển thị' : 'Ẩn'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="actions">
                                    <form action="<?php echo BASE_URL; ?>/admin/banners" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa banner này?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                        <button type="submit" class="btn btn-danger" title="Xóa"><i class="fas fa-trash"></i></button>
                                    </form>
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
