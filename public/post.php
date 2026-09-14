<?php
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

// Them binh luan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    csrf_check();
    $body = trim($_POST['body'] ?? '');
    if ($body !== '') {
        $uid = (int)$_SESSION['user_id'];
        // Luu nguyen van (khong loc). Chong XSS thuc hien luc XUAT bang out().
        $stmt = mysqli_prepare($conn,
            "INSERT INTO comments (post_id, user_id, body) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmt, 'iis', $id, $uid, $body);
        mysqli_stmt_execute($stmt);
    }
    header('Location: post.php?id=' . $id);
    exit;
}

$pr = mysqli_prepare($conn,
    "SELECT p.*, u.username FROM posts p JOIN users u ON u.id=p.user_id WHERE p.id=?");
mysqli_stmt_bind_param($pr, 'i', $id);
mysqli_stmt_execute($pr);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($pr));

if (!$post) { include __DIR__.'/../includes/header.php'; echo '<p>Không tìm thấy bài viết.</p>'; include __DIR__.'/../includes/footer.php'; exit; }

$cr = mysqli_prepare($conn,
    "SELECT c.*, u.username FROM comments c JOIN users u ON u.id=c.user_id
     WHERE c.post_id=? ORDER BY c.created_at ASC");
mysqli_stmt_bind_param($cr, 'i', $id);
mysqli_stmt_execute($cr);
$comments = mysqli_stmt_get_result($cr);

include __DIR__ . '/../includes/header.php';
?>
<article class="card">
    <h1><?= out($post['title']) ?></h1>
    <p class="meta">✍️ <?= out($post['username']) ?> &middot; <?= out($post['created_at']) ?></p>
    <div class="post-body"><?= nl2br(out($post['body'])) ?></div>
</article>

<section class="comments">
    <h2>Bình luận</h2>
    <?php while ($c = mysqli_fetch_assoc($comments)): ?>
        <div class="comment">
            <strong><?= out($c['username']) ?></strong>
            <!-- LO HONG XSS (A05:2025) o che do vulnerable: out() khong escape -->
            <span><?= out($c['body']) ?></span>
        </div>
    <?php endwhile; ?>

    <?php if (is_logged_in()): ?>
        <form method="post" class="form card">
            <?= csrf_field() ?>
            <label>Viết bình luận
                <textarea name="body" rows="3" required></textarea>
            </label>
            <button type="submit">Gửi</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Đăng nhập</a> để bình luận.</p>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
