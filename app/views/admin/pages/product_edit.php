<?php
// FILE: /app/views/admin/pages/product_edit.php

if (!function_exists('checked')) {
    function checked($current_value, $checked_value)
    {
        if ((string)$current_value === (string)$checked_value) {
            echo 'checked="checked"';
        }
    }
}
if (!function_exists('selected')) {
    function selected($current_value, $select_value)
    {
        if ((string)$current_value === (string)$select_value) {
            echo 'selected';
        }
    }
}
if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
?>
<form id="product-form" action="<?php echo BASE_URL; ?>/admin/ajax/handle/save_product" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">

    <div class="form-grid-layout">
        <div class="form-main-col">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label for="danh_muc_id">Danh mục sản phẩm</label>
                        <select name="danh_muc_id" id="danh_muc_id" class="form-control" <?php if ($is_edit) echo 'disabled'; ?>>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($all_categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php selected($product['danh_muc_id'] ?? '', $cat['id']); ?>><?php echo e($cat['ten_danh_muc']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($is_edit): ?>
                            <small class="text-muted">Không thể thay đổi danh mục của sản phẩm đã tạo.</small>
                            <input type="hidden" name="danh_muc_id" value="<?php echo e($product['danh_muc_id']); ?>" />
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="product-form-main-content" class="<?php if (!$is_edit && empty($product['danh_muc_id'])) echo 'form-section-hidden'; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin chung</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name_for_slug">Tên sản phẩm</label>
                            <input type="text" class="form-control" name="ten_san_pham" id="name_for_slug" value="<?php echo e($product['ten_san_pham'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="slug">Đường dẫn URL</label>
                            <input type="text" class="form-control" name="slug" id="slug" value="<?php echo e($product['slug'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="brand-select">Thương hiệu</label>
                            <select name="thuong_hieu_id" id="brand-select" class="form-control" data-current-brand-id="<?php echo e($product['thuong_hieu_id'] ?? 0); ?>">
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Mô tả ngắn</label>
                            <textarea class="form-control" name="mo_ta_ngan"><?php echo e($product['mo_ta_ngan'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Mô tả chi tiết</label>
                            <textarea class="form-control" name="mo_ta_chi_tiet" style="min-height: 200px;"><?php echo e($product['mo_ta_chi_tiet'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Dữ liệu sản phẩm</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Loại sản phẩm</label>
                            <select name="loai_san_pham" id="product_type" class="form-control" <?php if ($is_edit) echo 'disabled'; ?>>
                                <option value="simple" <?php selected($product['loai_san_pham'] ?? 'simple', 'simple'); ?>>Sản phẩm đơn giản</option>
                                <option value="variable" <?php selected($product['loai_san_pham'] ?? 'simple', 'variable'); ?>>Sản phẩm có biến thể</option>
                            </select>
                            <?php if ($is_edit): ?>
                                <small class="text-muted">Không thể thay đổi loại sản phẩm.</small>
                                <input type="hidden" name="loai_san_pham" value="<?php echo e($product['loai_san_pham']); ?>" />
                            <?php endif; ?>
                        </div>

                        <div id="simple-product-data" class="form-section-hidden">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Giá gốc</label>
                                    <input type="number" class="form-control" name="gia_goc" value="<?php echo e($product['gia_goc'] ?? 0); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Giá khuyến mãi</label>
                                    <input type="number" class="form-control" name="gia_khuyen_mai" value="<?php echo e($product['gia_khuyen_mai'] ?? 0); ?>">
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Số lượng tồn kho</label>
                                    <input type="number" class="form-control" name="so_luong_ton" value="<?php echo e($stock_quantity); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Mã định danh (SKU, IMEI...)</label>
                                    <input type="text" class="form-control" name="ma_dinh_danh_duy_nhat" value="<?php echo e($product['ma_dinh_danh_duy_nhat'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div id="variable-product-data" class="form-section-hidden">
                            <div class="form-group">
                                <label>Thuộc tính cho biến thể</label>
                                <p><small class="text-muted">Chọn các nhóm thuộc tính mà sản phẩm này có (ví dụ: Màu sắc, Kích thước).</small></p>
                                <select id="variant-attributes-select" class="form-control select2" multiple>
                                    <?php foreach ($all_variant_attributes as $attr): ?>
                                        <option
                                            value="<?php echo $attr['id']; ?>"
                                            data-name="<?php echo e($attr['ten_thuoc_tinh']); ?>">
                                            <?php echo e($attr['ten_thuoc_tinh']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <hr>
                            <div id="variants-manager">
                                <div class="variants-manager-header">
                                    <h4>Các biến thể</h4>
                                    <button type="button" id="add-variant-btn" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm biến thể</button>
                                </div>
                                <div id="variants-table-container">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Các lựa chọn thuộc tính</th>
                                                <th>Giá</th>
                                                <th>Giá KM</th>
                                                <th>Mã định danh *</th>
                                                <th>Số lượng tồn</th>
                                                <th>Ảnh</th>
                                                <th class="actions"></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Thông số kỹ thuật</h3>
                    </div>
                    <div id="tech-specs-container" class="card-body form-grid">
                        <p class="text-muted">Chọn một danh mục để hiển thị các thuộc tính kỹ thuật tương ứng.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-side-col">
            <div id="product-form-side-content" class="<?php if (!$is_edit && empty($product['danh_muc_id'])) echo 'form-section-hidden'; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Xuất bản</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="is_active" class="form-control">
                                <option value="1" <?php selected($product['is_active'] ?? 1, 1); ?>>Hoạt động (Hiển thị)</option>
                                <option value="0" <?php selected($product['is_active'] ?? 1, 0); ?>>Nháp (Ẩn)</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Lưu sản phẩm</button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Hình ảnh</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tải ảnh mới</label>
                            <input type="file" class="form-control" name="hinh_anh[]" id="hinh_anh" multiple accept="image/*">
                        </div>
                        <div id="image-gallery">
                            <div class="form-group">
                                <label>Ảnh hiện tại</label>
                                <div class="image-preview-container">
                                    <p class="text-muted">Chưa có ảnh nào.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div id="custom-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title" class="modal-title">Tiêu đề</h3>
        </div>
        <div class="modal-body">
            <p id="modal-body-text">Nội dung.</p>
        </div>
        <div id="modal-footer" class="modal-footer">
            <button id="modal-close-btn" class="btn btn-secondary">Đóng</button>
            <button id="modal-confirm-btn" class="btn btn-primary">Xác nhận</button>
        </div>
    </div>
</div>

<template id="variant-row-template">
    <tr>
        <td class="variant-options-cell"></td>
        <td><input type="number" class="form-control" name="variants[__INDEX__][gia]" placeholder="Giá"></td>
        <td><input type="number" class="form-control" name="variants[__INDEX__][gia_khuyen_mai]" placeholder="Giá KM"></td>
        <td><textarea class="form-control variant-identifier-input" name="variants[__INDEX__][unique_identifiers]" placeholder="Mỗi mã một dòng..." required></textarea></td>
        <td><input type="number" class="form-control" name="variants[__INDEX__][so_luong_ton]" placeholder="Tự động" title="Số lượng sẽ tự động đếm theo Mã định danh."></td>
        <td>
            <select class="form-control variant-image-select" name="variants[__INDEX__][hinh_anh_id]">
                <option value="">-- Ảnh chung --</option>
            </select>
        </td>
        <td class="actions">
            <input type="hidden" class="variant-id-input" name="variants[__INDEX__][id]" value="0">
            <input type="hidden" class="variant-options-input" name="variants[__INDEX__][options_flat]">
            <button type="button" class="btn btn-danger btn-sm delete-variant-btn" title="Xóa biến thể">&times;</button>
        </td>
    </tr>
</template>

<script>
    const AppData = {
        isEditMode: <?php echo json_encode($is_edit); ?>,
        product: <?php echo json_encode($product); ?>,
        productImages: <?php echo json_encode($images, JSON_NUMERIC_CHECK); ?>,
        existingVariants: <?php echo json_encode($variants, JSON_NUMERIC_CHECK); ?>,
        product_attributes: <?php echo json_encode($product_attributes, JSON_NUMERIC_CHECK); ?>,
        selectedVariantAttributes: <?php echo json_encode($selected_variant_attributes, JSON_NUMERIC_CHECK); ?>,
        allBrands: <?php echo json_encode($all_brands, JSON_NUMERIC_CHECK); ?>,
        allTechSpecs: <?php echo json_encode($all_tech_specs_attributes, JSON_NUMERIC_CHECK); ?>,
        all_variant_attributes: <?php echo json_encode($all_variant_attributes, JSON_NUMERIC_CHECK); ?>,
        categoryBrandsMap: <?php echo json_encode($category_brands_map, JSON_NUMERIC_CHECK); ?>,
        categoryTechSpecsMap: <?php echo json_encode($category_attributes_map, JSON_NUMERIC_CHECK); ?>,
        attributeValuesMap: <?php echo json_encode($attribute_values_map, JSON_NUMERIC_CHECK); ?>,
    };
</script>