<?php
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';

$q = $_GET['q'] ?? '';
$results = null;

if ($q !== '') {
    if (SECURE) {
        // ----- SECURE: prepared statement, chong SQL Injection (A05:2025) -----
        $like = '%' . $q . '%';
        $stmt = mysqli_prepare($conn,
            "SELECT p.*, u.username FROM posts p JOIN users u ON u.id=p.user_id
             WHERE p.title LIKE ? OR p.body LIKE ? ORDER BY p.created_at DESC");
        mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
        mysqli_stmt_execute($stmt);
        $results = mysqli_stmt_get_result($stmt);
    } else {
        // ----- VULNERABLE: noi chuoi => SQL Injection (A05:2025) -----
        // Thu khai thac (UNION) tren URL:
        //   search.php?q=x' UNION SELECT id,username,password_md5,role,created_at,username FROM users -- -
        $sql = "SELECT p.*, u.username FROM posts p JOIN users u ON u.id=p.user_id
                WHERE p.title LIKE '%$q%' OR p.body LIKE '%$q%'
                ORDER BY p.created_at DESC";
        $results = mysqli_query($conn, $sql);
        if (!$results) echo '<pre class="error">Lỗi SQL: ' . mysqli_error($conn) . '</pre>';
    }
}
?>
<h1>Tìm kiếm bài viết</h1>
<form method="get" class="form">
    <input type="text" name="q" value="<?= out($q) ?>" placeholder="Từ khóa...">
    <button type="submit">Tìm</button>
</form>

<?php if ($results): ?>
    <p class="hint">Kết quả cho: <strong><?= out($q) ?></strong></p>
    <?php while ($r = mysqli_fetch_assoc($results)): ?>
        <article class="card link">
            <h3><a href="post.php?id=<?= (int)$r['id'] ?>"><?= out($r['title']) ?></a></h3>
            <p class="meta">✍️ <?= out($r['username']) ?></p>
        </article>
    <?php endwhile; ?>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
