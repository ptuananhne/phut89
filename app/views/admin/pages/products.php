<?php
// FILE: /app/views/admin/pages/products.php
// MÔ TẢ: View hiển thị danh sách sản phẩm. Đã sửa lại đường dẫn cho nút Sửa.
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Bộ lọc Sản phẩm</h2>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo \BASE_URL; ?>/admin/products" class="filter-form">
            <div class="form-grid">
                <div class="form-group"><label for="product_name">Tên sản phẩm</label><input type="text" name="product_name" id="product_name" class="form-control" value="<?php echo e($filter_name); ?>"></div>
                <div class="form-group"><label for="filter_category">Danh mục</label>
                    <select name="category" id="filter_category" class="form-control">
                        <option value="">Tất cả danh mục</option>
                        <?php foreach ($all_categories as $cat) : ?><option value="<?php echo $cat['id']; ?>" <?php selected($filter_category, $cat['id']); ?>><?php echo e($cat['ten_danh_muc']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label for="filter_brand">Thương hiệu</label>
                    <select name="brand" id="filter_brand" class="form-control" data-current-brand="<?php echo $filter_brand; ?>">
                        <option value="">Tất cả thương hiệu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="identifier">Tìm theo Mã định danh</label>
                    <input type="text" name="identifier" id="identifier" class="form-control" value="<?php echo e($filter_identifier); ?>" placeholder="SKU, IMEI, Serial...">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Lọc</button><a href="<?php echo \BASE_URL; ?>/admin/products" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Danh sách Sản phẩm (<?php echo count($products); ?>)</h2>
        <a href="<?php echo \BASE_URL; ?>/admin/products/add" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm mới</a>
    </div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá</th>
                    <th>Danh mục</th>
                    <th>Thương hiệu</th>
                    <th>Loại</th>
                    <th>Trạng thái</th>
                    <th class="actions">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products): foreach ($products as $product): ?>
                        <tr>
                            <td><img src="<?php echo \BASE_URL . '/uploads/products/thumbs/' . e($product['url_hinh_anh'] ?? 'placeholder.png'); ?>" class="thumbnail" alt="Ảnh sản phẩm" loading="lazy"></td>
                            <td><?php echo e($product['ten_san_pham']); ?></td>
                            <td>
                                <?php // Logic hiển thị giá ... 
                                ?>
                            </td>
                            <td><?php echo e($product['ten_danh_muc']); ?></td>
                            <td><?php echo e($product['ten_thuong_hieu']); ?></td>
                            <td><span class="status <?php echo $product['loai_san_pham'] === 'variable' ? 'status-info' : 'status-secondary'; ?>"><?php echo $product['loai_san_pham'] === 'variable' ? 'Có biến thể' : 'Đơn giản'; ?></span></td>
                            <td>
                                <button class="status-toggle-btn status <?php echo $product['is_active'] ? 'active' : 'inactive'; ?>" data-id="<?php echo $product['id']; ?>" data-type="san_pham" data-current-status="<?php echo $product['is_active']; ?>" data-csrf="<?php echo e($csrf_token); ?>">
                                    <?php echo $product['is_active'] ? 'Hoạt động' : 'Ẩn'; ?>
                                </button>
                            </td>
                            <td class="actions">
                                <!-- SỬA LỖI Ở ĐÂY: Thêm "s" vào "products" -->
                                <a href="<?php echo \BASE_URL; ?>/admin/products/edit/<?php echo $product['id']; ?>" class="btn btn-secondary" title="Sửa"><i class="fas fa-edit"></i></a>
                                <form action="<?php echo \BASE_URL; ?>/admin/products" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                    <button type="submit" class="btn btn-danger" title="Xóa"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">Không tìm thấy sản phẩm nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    // Javascript cho việc lọc thương hiệu theo danh mục
    const allBrandsForFilter = <?php echo json_encode($all_brands, JSON_NUMERIC_CHECK); ?>;
    const categoryBrandsMapForFilter = <?php echo json_encode($category_brands_map, JSON_NUMERIC_CHECK); ?>;
    // ... (phần còn lại của JS)
</script>