<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: shop.php'); exit; }
csrf_check();

$uid     = (int)$_SESSION['user_id'];
$item_id = (int)($_POST['item_id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM items WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $item_id);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$me = current_user($conn);

if (!$item) {
    flash('Vật phẩm không tồn tại.');
    header('Location: shop.php'); exit;
}

if (SECURE) {
    // ----- SECURE: kiem tra day du + giao dich nguyen tu (A01:2025 / A06:2025) -----
    if ($item['stock'] <= 0) { flash('Hết hàng.'); header('Location: shop.php'); exit; }
    if ($me['balance'] < $item['price']) { flash('Bạn không đủ coin.'); header('Location: shop.php'); exit; }

    mysqli_begin_transaction($conn);
    try {
        // Tru coin CO dieu kien du so du (chong tieu am / race condition)
        $u = mysqli_prepare($conn,
            "UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?");
        mysqli_stmt_bind_param($u, 'iii', $item['price'], $uid, $item['price']);
        mysqli_stmt_execute($u);
        if (mysqli_stmt_affected_rows($u) !== 1) throw new Exception('Số dư không đủ');

        $s = mysqli_prepare($conn,
            "UPDATE items SET stock = stock - 1 WHERE id = ? AND stock > 0");
        mysqli_stmt_bind_param($s, 'i', $item_id);
        mysqli_stmt_execute($s);
        if (mysqli_stmt_affected_rows($s) !== 1) throw new Exception('Hết hàng');

        $t = mysqli_prepare($conn,
            "INSERT INTO transactions (user_id, item_id, amount) VALUES (?,?,?)");
        mysqli_stmt_bind_param($t, 'iii', $uid, $item_id, $item['price']);
        mysqli_stmt_execute($t);

        mysqli_commit($conn);
        flash('Mua thành công: ' . $item['name']);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        flash('Mua thất bại: ' . $e->getMessage());
    }
} else {
    // ----- VULNERABLE: khong kiem tra so du, khong nguyen tu -----
    // A01:2025 Broken Access Control / A06:2025 Insecure Design:
    //  - so du co the am
    //  - client co the sua gia? khong, nhung khong kiem tra ton kho / so du
    $price = (int)$item['price'];
    mysqli_query($conn, "UPDATE users SET balance = balance - $price WHERE id = $uid");
    mysqli_query($conn, "UPDATE items SET stock = stock - 1 WHERE id = $item_id");
    mysqli_query($conn, "INSERT INTO transactions (user_id, item_id, amount)
                         VALUES ($uid, $item_id, $price)");
    flash('Mua thành công: ' . $item['name']);
}

header('Location: shop.php');
exit;
