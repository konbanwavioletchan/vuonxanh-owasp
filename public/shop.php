<?php
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';

$items = mysqli_query($conn, "SELECT * FROM items ORDER BY price DESC");
$me = current_user($conn);
?>
<div class="row-between">
    <div>
        <h1 style="margin:0">Cửa hàng cây cảnh</h1>
        <p class="hint" style="margin:2px 0 0">Cây khỏe, đã thuần dưỡng &mdash; nhãn màu cho biết nhóm cây.</p>
    </div>
    <?php if ($me): ?><p class="hint" style="margin:0">Ví của bạn: <strong class="coin-txt"><?= (int)$me['balance'] ?> coin</strong></p><?php endif; ?>
</div>

<div class="grid" style="margin-top:16px">
<?php while ($it = mysqli_fetch_assoc($items)):
    $v = plant_visual($it['name']); ?>
    <div class="card item" style="--rar:<?= $v['color'] ?>">
        <div class="item-fig">
            <span class="rar-badge"><?= $v['tag'] ?></span>
            <?php if ($img = plant_image($it['name'])): ?>
                <img class="item-photo" src="<?= $img ?>" alt="<?= out($it['name']) ?>" loading="lazy">
            <?php else: ?>
                <?= $v['svg'] ?>
            <?php endif; ?>
        </div>
        <div class="item-body">
            <h3><?= out($it['name']) ?></h3>
            <p class="idesc"><?= out($it['description']) ?></p>
            <p class="stock">Còn <strong><?= (int)$it['stock'] ?></strong> chậu</p>
            <div class="item-foot">
                <span class="price"><?= (int)$it['price'] ?></span>
                <?php if (is_logged_in()): ?>
                    <form method="post" action="buy.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="item_id" value="<?= (int)$it['id'] ?>">
                        <button type="submit">Mua</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-sm" href="login.php">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endwhile; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
