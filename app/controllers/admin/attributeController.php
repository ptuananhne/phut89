<?php
// FILE: /app/controllers/admin/AttributeController.php
// MÔ TẢ: Controller quản lý Thuộc tính Biến thể.
//        ĐÃ BỔ SUNG LOGIC BẢO MẬT CSRF.

namespace Admin;

class AttributeController extends AdminBaseController
{
    public function index(): void
    {
        $attributes = $this->pdo->query("
            SELECT tt.id, tt.ten_thuoc_tinh, GROUP_CONCAT(gtt.id, '::', gtt.gia_tri SEPARATOR '||') as `values`
            FROM thuoc_tinh_bien_the tt
            LEFT JOIN gia_tri_thuoc_tinh_bien_the gtt ON tt.id = gtt.thuoc_tinh_id
            GROUP BY tt.id ORDER BY tt.ten_thuoc_tinh
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'page_title' => 'Quản lý Thuộc tính Biến thể',
            'attributes_json' => json_encode($attributes, JSON_NUMERIC_CHECK),
        ];
        $this->render('pages/attributes', $data);
    }

    // [SỬA LỖI] Tất cả các hàm xử lý AJAX dưới đây cần xác thực CSRF
    private function ajaxWrapper(callable $callback)
    {
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(false, 'Lỗi xác thực CSRF!');
            return;
        }
        try {
            $callback();
        } catch (\Exception $e) {
            $this->jsonResponse(false, $e->getMessage());
        }
    }

    public function add()
    {
        $this->ajaxWrapper(function () {
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) throw new \Exception("Tên thuộc tính không được để trống.");
            $this->pdo->prepare("INSERT INTO thuoc_tinh_bien_the (ten_thuoc_tinh) VALUES (?)")->execute([$name]);
            $this->jsonResponse(true, 'Thêm thành công', ['id' => $this->pdo->lastInsertId(), 'name' => $name]);
        });
    }
    public function update()
    {
        $this->ajaxWrapper(function () {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            if (empty($name) || $id === 0) throw new \Exception("Dữ liệu không hợp lệ.");
            $this->pdo->prepare("UPDATE thuoc_tinh_bien_the SET ten_thuoc_tinh = ? WHERE id = ?")->execute([$name, $id]);
            $this->jsonResponse(true, 'Cập nhật thành công.');
        });
    }
    public function delete()
    {
        $this->ajaxWrapper(function () {
            $id = (int)($_POST['id'] ?? 0);
            if ($id === 0) throw new \Exception("ID không hợp lệ.");
            $this->pdo->prepare("DELETE FROM thuoc_tinh_bien_the WHERE id = ?")->execute([$id]);
            $this->jsonResponse(true, 'Xóa thành công.');
        });
    }
    public function addValue()
    {
        $this->ajaxWrapper(function () {
            $attr_id = (int)($_POST['attribute_id'] ?? 0);
            $value = trim($_POST['value'] ?? '');
            if (empty($value) || $attr_id === 0) throw new \Exception("Dữ liệu không hợp lệ.");
            $this->pdo->prepare("INSERT INTO gia_tri_thuoc_tinh_bien_the (thuoc_tinh_id, gia_tri) VALUES (?, ?)")->execute([$attr_id, $value]);
            $this->jsonResponse(true, 'Thêm giá trị thành công', ['id' => $this->pdo->lastInsertId(), 'value' => $value]);
        });
    }
    public function updateValue()
    {
        $this->ajaxWrapper(function () {
            $id = (int)($_POST['id'] ?? 0);
            $value = trim($_POST['value'] ?? '');
            if (empty($value) || $id === 0) throw new \Exception("Dữ liệu không hợp lệ.");
            $this->pdo->prepare("UPDATE gia_tri_thuoc_tinh_bien_the SET gia_tri = ? WHERE id = ?")->execute([$value, $id]);
            $this->jsonResponse(true, 'Cập nhật giá trị thành công.');
        });
    }
    public function deleteValue()
    {
        $this->ajaxWrapper(function () {
            $id = (int)($_POST['id'] ?? 0);
            if ($id === 0) throw new \Exception("ID không hợp lệ.");
            $this->pdo->prepare("DELETE FROM gia_tri_thuoc_tinh_bien_the WHERE id = ?")->execute([$id]);
            $this->jsonResponse(true, 'Xóa giá trị thành công.');
        });
    }
}
