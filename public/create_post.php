<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body'] ?? '');
    if ($title !== '' && $body !== '') {
        $uid = (int)$_SESSION['user_id'];
        $stmt = mysqli_prepare($conn,
            "INSERT INTO posts (user_id, title, body) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmt, 'iss', $uid, $title, $body);
        mysqli_stmt_execute($stmt);
        flash('Đã đăng bài viết.');
        header('Location: index.php');
        exit;
    }
    flash('Vui lòng nhập tiêu đề và nội dung.');
}

include __DIR__ . '/../includes/header.php';
?>
<h1>Viết bài mới</h1>
<form method="post" class="card form">
    <?= csrf_field() ?>
    <label>Tiêu đề <input type="text" name="title" required></label>
    <label>Nội dung <textarea name="body" rows="8" required></textarea></label>
    <button type="submit">Đăng bài</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
