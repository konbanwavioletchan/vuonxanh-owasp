<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (too_many_attempts($conn, $username)) {
        // A07:2025: chan brute-force (chi o che do secure)
        flash('Bạn đã nhập sai quá nhiều lần. Thử lại sau 15 phút.');
    } else {
        if (SECURE) {
            // ----- SECURE: prepared statement + kiem tra mat khau bang bcrypt (A05:2025 / A04:2025) -----
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            $ok   = $user && verify_password($conn, $user, $password);
        } else {
            // ----- VULNERABLE: noi chuoi truc tiep => SQL Injection (A05:2025) -----
            // Ca ten dang nhap VA mat khau deu duoc kiem tra ngay trong cau SQL,
            // nen co the bypass hoan toan bang injection o o Ten dang nhap:
            //     admin' --            (dang nhap thang thanh admin, khong can mat khau)
            //     ' OR '1'='1' LIMIT 1 --   (dang nhap thanh user dau tien)
            $md5 = md5($password);
            $sql = "SELECT * FROM users
                    WHERE username = '$username' AND password_md5 = '$md5'";
            $res  = mysqli_query($conn, $sql);
            $user = $res ? mysqli_fetch_assoc($res) : null;
            $ok   = (bool)$user;   // co row tra ve la coi nhu dang nhap thanh cong
        }

        log_attempt($conn, is_array($user) ? ($user['username'] ?? $username) : $username, $ok);

        if ($ok) {
            if (SECURE) session_regenerate_id(true);   // A07:2025: chong session fixation
            $_SESSION['user_id'] = $user['id'];
            flash('Đăng nhập thành công.');
            header('Location: index.php');
            exit;
        } else {
            flash('Sai tên đăng nhập hoặc mật khẩu.');
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h1>Đăng nhập</h1>
<form method="post" class="card form">
    <?= csrf_field() ?>
    <label>Tên đăng nhập
        <input type="text" name="username" required>
    </label>
    <label>Mật khẩu
        <input type="password" name="password" required>
    </label>
    <button type="submit">Đăng nhập</button>
</form>
<p class="hint">Tài khoản mẫu: <code>alice / password1</code>, <code>admin / admin123</code></p>
<?php include __DIR__ . '/../includes/footer.php'; ?>
