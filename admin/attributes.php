<?php
$page_title = 'Quản lý Thuộc tính Biến thể';
require_once 'includes/header.php';

// Lấy tất cả thuộc tính và giá trị để truyền cho JavaScript
$attributes = $pdo->query("
    SELECT tt.id, tt.ten_thuoc_tinh, GROUP_CONCAT(gtt.id, '::', gtt.gia_tri SEPARATOR '||') as `values`
    FROM thuoc_tinh_bien_the tt
    LEFT JOIN gia_tri_thuoc_tinh_bien_the gtt ON tt.id = gtt.thuoc_tinh_id
    GROUP BY tt.id
    ORDER BY tt.ten_thuoc_tinh
")->fetchAll(PDO::FETCH_ASSOC);

$csrf_token = csrf_token();
?>

<style>

</style>

<div class="attributes-container">
    <!-- Cột trái: Danh sách các thuộc tính -->
    <div class="attributes-master card">
        <div class="card-header">
            <h3 class="card-title">Thuộc tính</h3>
        </div>
        <div class="card-body">
            <ul id="attributes-list" class="attributes-list">
                <!-- JS will populate this -->
            </ul>
        </div>
        <div class="card-footer">
            <form id="add-attribute-form">
                <div class="form-group">
                    <label for="new-attribute-name">Thêm thuộc tính mới</label>
                    <input type="text" id="new-attribute-name" class="form-control" placeholder="Ví dụ: Màu sắc, RAM..." required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Thêm</button>
            </form>
        </div>
    </div>

    <!-- Cột phải: Chi tiết giá trị của thuộc tính được chọn -->
    <div class="attributes-detail card">
        <div class="card-header">
            <h3 id="detail-title" class="card-title">Giá trị</h3>
        </div>
        <div class="card-body" id="detail-body">
            <div class="attributes-detail-placeholder">
                <p>Chọn một thuộc tính bên trái để quản lý các giá trị của nó.</p>
            </div>
        </div>
    </div>
</div>

<!-- Template for attribute item -->
<template id="attribute-item-template">
    <li data-id="__ATTR_ID__">
        <span class="attribute-name" title="Nhấp để sửa tên">__ATTR_NAME__</span>
        <button class="btn-inline-delete delete-attribute" title="Xóa thuộc tính này">&times;</button>
    </li>
</template>

<!-- Template for detail view -->
<template id="attribute-detail-template">
    <form id="add-value-form" class="form-group">
        <label for="new-value-name">Thêm giá trị mới cho <strong class="text-primary">__ATTR_NAME__</strong></label>
        <div class="input-group">
            <input type="text" id="new-value-name" class="form-control" placeholder="Ví dụ: Xanh, 128GB..." required>
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary">Thêm</button>
            </div>
        </div>
    </form>
    <hr>
    <p><strong>Các giá trị hiện có:</strong></p>
    <ul id="values-list" class="values-list">
        <!-- JS will populate this -->
    </ul>
</template>

<!-- Template for value item -->
<template id="value-item-template">
    <li data-id="__VALUE_ID__">
        <span class="value-name" title="Nhấp để sửa tên">__VALUE_NAME__</span>
        <button class="btn-inline-delete delete-value" title="Xóa giá trị này">&times;</button>
    </li>
</template>

<script>
    // Pass data to JavaScript
    let attributesData = <?php echo json_encode($attributes, JSON_NUMERIC_CHECK); ?>;
    const csrfToken = '<?php echo $csrf_token; ?>';
</script>

<?php require_once 'includes/footer.php'; ?>