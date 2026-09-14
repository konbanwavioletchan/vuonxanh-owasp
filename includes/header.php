<?php
require_once __DIR__ . '/functions.php';
$me = current_user($conn);
?><!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Vườn Xanh - cửa hàng cây cảnh, cây phong thủy, sen đá, bonsai và cây trồng trong nhà. Cây khỏe, giao nhanh, hướng dẫn chăm sóc tận tình.">
    <script>try{var t=localStorage.getItem('theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}</script>
    <title>Vườn Xanh — Cửa hàng cây cảnh</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php">Vườn Xanh</a>
    <nav>
        <a href="index.php">Trang chủ</a>
        <a href="shop.php">Cửa hàng</a>
        <a href="gioi-thieu.php">Giới thiệu</a>
        <a href="index.php#categories">Danh mục</a>
        <a href="index.php#blog">Cẩm nang</a>
        <a href="index.php#reviews">Đánh giá</a>
        <a href="index.php#contact">Liên hệ</a>
        <a href="search.php">Tìm kiếm</a>
        <?php if ($me): ?>
            <a href="profile.php?id=<?= (int)$me['id'] ?>">Tài khoản</a>
            <?php if ($me['role'] === 'admin'): ?>
                <a href="admin.php" class="admin-link">Quản trị</a>
            <?php endif; ?>
            <span class="coins"><?= (int)$me['balance'] ?> coin</span>
            <span class="hi">Chào, <?= out($me['username']) ?></span>
            <button id="theme-toggle" class="theme-btn" type="button" aria-label="Đổi giao diện sáng/tối" title="Sáng / Tối">🌙</button>
            <a href="logout.php">Đăng xuất</a>
        <?php else: ?>
            <a href="login.php">Đăng nhập</a>
            <a class="btn btn-nav" href="shop.php">Mua cây ngay</a>
            <button id="theme-toggle" class="theme-btn" type="button" aria-label="Đổi giao diện sáng/tối" title="Sáng / Tối">🌙</button>
        <?php endif; ?>
    </nav>
</header>

<div class="mode-banner <?= SECURE ? 'secure' : 'vuln' ?>">
    Chế độ: <strong><?= SECURE ? 'AN TOÀN (đã vá lỗ hổng)' : 'DỄ BỊ TẤN CÔNG (còn lỗ hổng OWASP)' ?></strong>
    &mdash; đổi trong <code>config/config.php</code>
</div>

<main class="container">
<?php foreach (get_flashes() as $f): ?>
    <div class="flash"><?= out($f) ?></div>
<?php endforeach; ?>
