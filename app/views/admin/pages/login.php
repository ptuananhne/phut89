<?php
// FILE: /app/views/admin/pages/login.php
// MÔ TẢ: View cho trang đăng nhập. File này không sử dụng layout chung.
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Trang Quản trị</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/admin-assets/css/admin_style.css?v=<?php echo time(); ?>">
</head>

<body class="login-body">
    <div class="login-container">
        <h1><?php echo SITE_NAME; ?> - Admin</h1>
        <p>Đăng nhập vào trang quản trị</p>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>
        <form action="<?php echo BASE_URL; ?>/admin/login" method="POST" novalidate>
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