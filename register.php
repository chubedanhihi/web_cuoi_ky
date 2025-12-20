<?php
session_start();
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($full_name === '' || $username === '' || $email === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email=? OR username=?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Email hoặc Username đã tồn tại';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $full_name, $username, $email, $hash);
            $stmt->execute();
            header("Location: login.php?register=success");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="css/auth.css">
</head>

<body>
    <form class="auth-box" method="post">
        <h2>Đăng ký</h2>
        <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <input name="full_name" placeholder="Họ và tên" required>
        <input name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button>Đăng ký</button>
        <a href="login.php">Đã có tài khoản? Đăng nhập</a>
        <a href="index.php">← Về trang chủ</a>
    </form>
</body>

</html>