<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$view_id = (int)($_GET['id'] ?? $_SESSION['user_id']);

// Cap nhat ho so
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $target = (int)($_POST['id'] ?? 0);
    $bio    = trim($_POST['bio'] ?? '');

    if (SECURE) {
        // ----- SECURE: chi cho sua HO SO CUA CHINH MINH (A01:2025) -----
        $target = (int)$_SESSION['user_id'];               // bo qua id gui len
        $stmt = mysqli_prepare($conn, "UPDATE users SET bio = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $bio, $target);
        mysqli_stmt_execute($stmt);
    } else {
        // ----- VULNERABLE: IDOR - tin tuong id tu client -----
        // Bat ky ai cung sua duoc ho so nguoi khac bang cach doi truong 'id'.
        // Con dinh mass-assignment: neu gui them 'balance' se bi ap dung.
        $bal_sql = '';
        if (isset($_POST['balance'])) {
            $bal = (int)$_POST['balance'];
            $bal_sql = ", balance = $bal";
        }
        $bio_e = mysqli_real_escape_string($conn, $bio);
        mysqli_query($conn, "UPDATE users SET bio = '$bio_e' $bal_sql WHERE id = $target");
    }
    flash('Đã cập nhật hồ sơ.');
    header('Location: profile.php?id=' . $target);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $view_id);
mysqli_stmt_execute($stmt);
$u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

include __DIR__ . '/../includes/header.php';
if (!$u) { echo '<p>Không tìm thấy người dùng.</p>'; include __DIR__.'/../includes/footer.php'; exit; }

$is_self = ((int)$_SESSION['user_id'] === (int)$u['id']);
$can_edit = SECURE ? $is_self : true;   // vulnerable: ai cung "edit" duoc
$role_class = $u['role'] === 'admin' ? 'badge-admin' : 'badge-user';
?>
<h1>Hồ sơ: <?= out($u['username']) ?></h1>
<div class="card">
    <p><strong>Vai trò:</strong> <span class="badge <?= $role_class ?>"><?= out($u['role']) ?></span></p>
    <p><strong>Số dư:</strong> <?= (int)$u['balance'] ?> coin</p>
    <p><strong>Giới thiệu:</strong> <?= nl2br(out($u['bio'])) ?></p>
</div>

<?php if ($can_edit): ?>
    <h2>Chỉnh sửa hồ sơ</h2>
    <form method="post" class="card form">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
        <label>Giới thiệu <textarea name="bio" rows="3"><?= out($u['bio']) ?></textarea></label>
        <button type="submit">Lưu</button>
    </form>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
