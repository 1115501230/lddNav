<?php
session_start();

// 定义正确的管理员密码
$correct_password = 'admin';

// 处理登录逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === $correct_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit();
    } else {
        $login_error = '密码错误，请重试';
    }
}

// 如果已经登录，直接跳转到仪表板
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录</title>
    <link rel="stylesheet" href="/dist/css/tabler.min.css">
</head>
<body class="d-flex flex-column">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">管理员登录</h2>
                    <?php if (isset($login_error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($login_error); ?>
                    </div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">密码</label>
                            <input type="password" name="password" class="form-control" placeholder="请输入管理员密码" required>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">登录</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="/dist/js/tabler.min.js"></script>
</body>
</html>
