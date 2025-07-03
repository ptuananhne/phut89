<?php
session_start();

// Nếu đã đăng nhập, chuyển hướng ngay tới trang dashboard
if (isset($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}

// Vì trang này đứng độc lập, ta cần gọi config và db từ thư mục gốc
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Đăng nhập thành công, tạo lại session id để tăng bảo mật
                session_regenerate_id(true);
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
            }
        } catch (PDOException $e) {
            $error = 'Lỗi hệ thống, không thể truy vấn CSDL.';
            if (APP_ENV === 'development') {
                error_log('Login PDOException: ' . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Trang Quản trị</title>
    <link rel="stylesheet" href="assets/admin_style.css?v=<?php echo time(); ?>">
</head>

<body class="login-body">
    <div class="login-container">
        <h1><?php echo SITE_NAME; ?> - Admin</h1>
        <p>Đăng nhập vào trang quản trị</p>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST" novalidate>
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
        </form>
    </div>
</body>

</html>