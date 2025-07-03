<?php
$page_title = 'Quản lý Sản phẩm';
require_once 'includes/header.php';

// ===== XỬ LÝ HÀNH ĐỘNG XÓA (POST REQUEST) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['message'] = 'Lỗi xác thực CSRF!';
        $_SESSION['message_type'] = 'danger';
        header('Location: products.php');
        exit;
    }

    if ($id > 0) {
        $pdo->beginTransaction();
        try {
            $stmt_img = $pdo->prepare("SELECT url_hinh_anh FROM hinh_anh_san_pham WHERE san_pham_id = ?");
            $stmt_img->execute([$id]);
            $images = $stmt_img->fetchAll(PDO::FETCH_COLUMN);
            foreach ($images as $img_file) {
                if ($img_file) {
                    @unlink(ROOT_PATH . '/uploads/products/' . $img_file);
                    @unlink(ROOT_PATH . '/uploads/products/thumbs/' . $img_file);
                }
            }
            $pdo->prepare("DELETE FROM san_pham WHERE id = ?")->execute([$id]);
            $pdo->commit();
            $_SESSION['message'] = 'Xóa sản phẩm và các dữ liệu liên quan thành công!';
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['message'] = 'Lỗi khi xóa sản phẩm: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: products.php');
        exit;
    }
}

// ===== LẤY DỮ LIỆU CHO BỘ LỌC =====
$all_categories = $pdo->query("SELECT id, ten_danh_muc FROM danh_muc ORDER BY ten_danh_muc")->fetchAll();
$all_brands = $pdo->query("SELECT id, ten_thuong_hieu FROM thuong_hieu ORDER BY ten_thuong_hieu")->fetchAll();
$category_brands_map_raw = $pdo->query("SELECT danh_muc_id, GROUP_CONCAT(thuong_hieu_id) as brand_ids FROM danhmuc_thuonghieu GROUP BY danh_muc_id")->fetchAll(PDO::FETCH_KEY_PAIR);
$category_brands_map = [];
foreach ($category_brands_map_raw as $key => $value) {
    $category_brands_map[$key] = array_map('intval', explode(',', $value));
}

// ===== [UPGRADED] XÂY DỰNG CÂU TRUY VẤN TÌM KIẾM =====
$sql = "SELECT
            sp.id, sp.ten_san_pham, sp.gia_goc, sp.is_active, sp.loai_san_pham,
            dm.ten_danh_muc, th.ten_thuong_hieu,
            (SELECT ha.url_hinh_anh FROM hinh_anh_san_pham ha WHERE ha.san_pham_id = sp.id AND ha.la_anh_dai_dien = 1 LIMIT 1) as url_hinh_anh,
            (SELECT MIN(pv.gia) FROM product_variants pv WHERE pv.san_pham_id = sp.id AND pv.gia > 0) as min_variant_price,
            (SELECT MAX(pv.gia) FROM product_variants pv WHERE pv.san_pham_id = sp.id) as max_variant_price
        FROM san_pham sp
        LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id
        LEFT JOIN thuong_hieu th ON sp.thuong_hieu_id = th.id
        WHERE 1=1";
$params = [];

// Lấy giá trị bộ lọc từ URL
$filter_name = trim($_GET['product_name'] ?? '');
$filter_category = (int)($_GET['category'] ?? 0);
$filter_brand = (int)($_GET['brand'] ?? 0);
$filter_identifier = trim($_GET['identifier'] ?? ''); // [NEW] Get identifier filter

if (!empty($filter_name)) {
    $sql .= " AND sp.ten_san_pham LIKE ?";
    $params[] = '%' . $filter_name . '%';
}
if ($filter_category > 0) {
    $sql .= " AND sp.danh_muc_id = ?";
    $params[] = $filter_category;
}
if ($filter_brand > 0) {
    $sql .= " AND sp.thuong_hieu_id = ?";
    $params[] = $filter_brand;
}
// [NEW] Logic to search by unique identifier
if (!empty($filter_identifier)) {
    $sql .= " AND (
                sp.ma_dinh_danh_duy_nhat = ? 
                OR EXISTS (
                    SELECT 1 FROM product_variants pv 
                    WHERE pv.san_pham_id = sp.id 
                    AND (pv.ma_bien_the = ? OR pv.unique_identifiers LIKE ?)
                )
            )";
    $params[] = $filter_identifier;
    $params[] = $filter_identifier;
    $params[] = '%' . $filter_identifier . '%';
}

$sql .= " GROUP BY sp.id ORDER BY sp.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Bộ lọc Sản phẩm</h2>
    </div>
    <div class="card-body">
        <form method="GET" action="products.php" class="filter-form">
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
                <!-- [NEW] Identifier search input -->
                <div class="form-group">
                    <label for="identifier">Tìm theo Mã định danh</label>
                    <input type="text" name="identifier" id="identifier" class="form-control" value="<?php echo e($filter_identifier); ?>" placeholder="SKU, IMEI, Serial, Biển số...">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Lọc</button><a href="products.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Danh sách Sản phẩm (<?php echo count($products); ?>)</h2>
        <a href="product_edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm mới</a>
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
                            <td><img src="<?php echo BASE_URL . '/uploads/products/thumbs/' . e($product['url_hinh_anh'] ?? 'placeholder.png'); ?>" class="thumbnail" alt="Ảnh sản phẩm" loading="lazy"></td>
                            <td><?php echo e($product['ten_san_pham']); ?></td>
                            <td>
                                <?php
                                if ($product['loai_san_pham'] === 'variable') {
                                    if ($product['min_variant_price'] && $product['max_variant_price']) {
                                        if ($product['min_variant_price'] == $product['max_variant_price']) {
                                            echo number_format($product['min_variant_price']) . ' VNĐ';
                                        } else {
                                            echo number_format($product['min_variant_price']) . ' - ' . number_format($product['max_variant_price']) . ' VNĐ';
                                        }
                                    } else {
                                        echo '<span class="text-muted">Chưa có giá</span>';
                                    }
                                } else {
                                    echo number_format($product['gia_goc']) . ' VNĐ';
                                }
                                ?>
                            </td>
                            <td><?php echo e($product['ten_danh_muc']); ?></td>
                            <td><?php echo e($product['ten_thuong_hieu']); ?></td>
                            <td><span class="status <?php echo $product['loai_san_pham'] === 'variable' ? 'status-info' : 'status-secondary'; ?>"><?php echo $product['loai_san_pham'] === 'variable' ? 'Có biến thể' : 'Đơn giản'; ?></span></td>
                            <td>
                                <button class="status-toggle-btn status <?php echo $product['is_active'] ? 'active' : 'inactive'; ?>"
                                    data-id="<?php echo $product['id']; ?>"
                                    data-type="san_pham"
                                    data-current-status="<?php echo $product['is_active']; ?>"
                                    data-csrf="<?php echo csrf_token(); ?>">
                                    <?php echo $product['is_active'] ? 'Hoạt động' : 'Ẩn'; ?>
                                </button>
                            </td>
                            <td class="actions">
                                <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary" title="Sửa"><i class="fas fa-edit"></i></a>
                                <form action="products.php" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Thao tác này sẽ xóa vĩnh viễn và không thể hoàn tác.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                    <button type="submit" class="btn btn-danger" title="Xóa"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">Không tìm thấy sản phẩm nào khớp với bộ lọc.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    const allBrandsForFilter = <?php echo json_encode($all_brands, JSON_NUMERIC_CHECK); ?>;
    const categoryBrandsMapForFilter = <?php echo json_encode($category_brands_map, JSON_NUMERIC_CHECK); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const filterCategorySelect = document.getElementById('filter_category');
        const filterBrandSelect = document.getElementById('filter_brand');

        function updateFilterBrandOptions() {
            if (!filterCategorySelect || !filterBrandSelect) return;
            const selectedCategoryId = filterCategorySelect.value;
            const currentBrandId = filterBrandSelect.dataset.currentBrand || '0';
            filterBrandSelect.innerHTML = '<option value="">Tất cả thương hiệu</option>';

            if (!selectedCategoryId) {
                allBrandsForFilter.forEach(brand => {
                    const option = new Option(brand.ten_thuong_hieu, brand.id);
                    if (String(brand.id) === String(currentBrandId)) option.selected = true;
                    filterBrandSelect.add(option);
                });
            } else if (categoryBrandsMapForFilter && categoryBrandsMapForFilter[selectedCategoryId]) {
                const brandsToShow = allBrandsForFilter.filter(brand =>
                    categoryBrandsMapForFilter[selectedCategoryId].includes(brand.id)
                );
                brandsToShow.forEach(brand => {
                    const option = new Option(brand.ten_thuong_hieu, brand.id);
                    if (String(brand.id) === String(currentBrandId)) option.selected = true;
                    filterBrandSelect.add(option);
                });
            }
        }
        if (filterCategorySelect) {
            filterCategorySelect.addEventListener('change', updateFilterBrandOptions);
            updateFilterBrandOptions();
        }
    });
</script>
<?php require_once 'includes/footer.php'; ?>