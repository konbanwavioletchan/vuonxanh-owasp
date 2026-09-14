<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

/*
 * A01:2025 - BROKEN ACCESS CONTROL
 *  SECURE   : goi require_admin() => chi admin vao duoc.
 *  VULNERABLE: KHONG kiem tra vai tro => moi user dang nhap deu mo duoc
 *              /admin.php va toan quyen (chinh role, cong coin...).
 *  Ngoai ra trang khong co lien ket hien thi voi user thuong
 *  (security by obscurity) - van truy cap truc tiep bang URL duoc.
 */
if (SECURE) {
    require_admin($conn);
}

// Xu ly hanh dong quan tri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'set_role') {
        $uid  = (int)$_POST['uid'];
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
        $stmt = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $role, $uid);
        mysqli_stmt_execute($stmt);
        flash('Đã đổi vai trò người dùng #' . $uid);
    } elseif ($action === 'set_balance') {
        $uid = (int)$_POST['uid'];
        $bal = (int)$_POST['balance'];
        $stmt = mysqli_prepare($conn, "UPDATE users SET balance = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $bal, $uid);
        mysqli_stmt_execute($stmt);
        flash('Đã cập nhật số dư người dùng #' . $uid);
    } elseif ($action === 'add_item') {
        $name  = trim($_POST['name'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $price = (int)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $stmt = mysqli_prepare($conn,
            "INSERT INTO items (name, description, price, stock) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'ssii', $name, $desc, $price, $stock);
        mysqli_stmt_execute($stmt);
        flash('Đã thêm vật phẩm.');
    }
    header('Location: admin.php');
    exit;
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id");
include __DIR__ . '/../includes/header.php';
?>
<h1>Trang quản trị</h1>

<h2>Người dùng</h2>
<div class="table-scroll">
<table class="table">
    <tr><th>ID</th><th>Tên</th><th>Vai trò</th><th>Số dư</th><th>Thao tác</th></tr>
    <?php while ($u = mysqli_fetch_assoc($users)): ?>
    <tr>
        <td><?= (int)$u['id'] ?></td>
        <td><?= out($u['username']) ?></td>
        <td><span class="badge <?= $u['role']==='admin'?'badge-admin':'badge-user' ?>"><?= out($u['role']) ?></span></td>
        <td><?= (int)$u['balance'] ?></td>
        <td>
            <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="set_role">
                <input type="hidden" name="uid" value="<?= (int)$u['id'] ?>">
                <select name="role">
                    <option value="user"  <?= $u['role']==='user'?'selected':'' ?>>user</option>
                    <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>admin</option>
                </select>
                <button type="submit">Đổi quyền</button>
            </form>
            <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="set_balance">
                <input type="hidden" name="uid" value="<?= (int)$u['id'] ?>">
                <input type="number" name="balance" value="<?= (int)$u['balance'] ?>" style="width:90px">
                <button type="submit">Sửa coin</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
</div>

<h2>Thêm vật phẩm</h2>
<form method="post" class="card form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add_item">
    <label>Tên <input name="name" required></label>
    <label>Mô tả <input name="description"></label>
    <label>Giá <input type="number" name="price" value="0"></label>
    <label>Tồn kho <input type="number" name="stock" value="0"></label>
    <button type="submit">Thêm</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
