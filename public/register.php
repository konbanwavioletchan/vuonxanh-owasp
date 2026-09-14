<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        flash('Vui lòng nhập đầy đủ thông tin.');
    } elseif (SECURE && strlen($password) < 8) {
        // A07:2025: chinh sach mat khau toi thieu (chi bat o che do secure)
        flash('Mật khẩu phải có ít nhất 8 ký tự.');
    } else {
        // Kiem tra trung ten (dung prepared statement o ca 2 che do cho phan nay)
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            flash('Tên đăng nhập đã tồn tại.');
        } else {
            $hash_md5  = SECURE ? null : md5($password);
            $hash_bcry = SECURE ? password_hash($password, PASSWORD_BCRYPT) : null;
            $ins = mysqli_prepare($conn,
                "INSERT INTO users (username, password_md5, password_hash) VALUES (?,?,?)");
            mysqli_stmt_bind_param($ins, 'sss', $username, $hash_md5, $hash_bcry);
            mysqli_stmt_execute($ins);
            flash('Đăng ký thành công! Mời đăng nhập.');
            header('Location: login.php');
            exit;
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h1>Đăng ký</h1>
<form method="post" class="card form">
    <?= csrf_field() ?>
    <label>Tên đăng nhập
        <input type="text" name="username" required>
    </label>
    <label>Mật khẩu
        <input type="password" name="password" required>
    </label>
    <button type="submit">Tạo tài khoản</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
