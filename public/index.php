<?php
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';

// Cay noi bat
$feat = mysqli_query($conn, "SELECT * FROM items ORDER BY price DESC LIMIT 4");
// Bai viet cam nang
$posts = mysqli_query($conn,
    "SELECT p.*, u.username FROM posts p JOIN users u ON u.id=p.user_id
     ORDER BY p.created_at DESC LIMIT 3");
?>
<!-- ===================== HERO ===================== -->
<section class="hero hero-plant" id="about">
    <div class="hero-text">
        <span class="hero-eyebrow">🌿 Vườn Xanh · Cây cảnh cho mọi không gian</span>
        <h1>Mang mảng xanh vào<br>không gian sống của bạn</h1>
        <p>Cây cảnh khỏe mạnh đã thuần dưỡng, từ cây để bàn, cây phong thủy đến bonsai nghệ thuật.
           Giao nhanh, bảo hành cây và hướng dẫn chăm sóc tận tình.</p>
        <div class="cta">
            <a class="btn btn-light" href="shop.php">Xem cửa hàng</a>
            <a class="btn btn-ghost" href="#categories">Khám phá danh mục</a>
        </div>
        <div class="hero-trust">
            <span>⭐ 4.9/5 từ 2.400+ khách</span>
            <span>🚚 Giao trong 24h nội thành</span>
            <span>🛡️ Bảo hành cây 7 ngày</span>
        </div>
    </div>
    <div class="hero-art">
        <img class="hero-photo" src="assets/img/hero-plant.jpg" alt="Cây monstera lá xẻ xanh mướt trong nhà" loading="eager">
    </div>
</section>

<!-- ===================== THỐNG KÊ ===================== -->
<section class="stats">
    <div class="stat"><span class="stat-num" data-count="2400" data-suffix="+">0</span><span class="stat-label">Khách hàng hài lòng</span></div>
    <div class="stat"><span class="stat-num" data-count="180" data-suffix="+">0</span><span class="stat-label">Giống cây các loại</span></div>
    <div class="stat"><span class="stat-num" data-count="8">0</span><span class="stat-label">Năm kinh nghiệm</span></div>
    <div class="stat"><span class="stat-num" data-count="98" data-suffix="%">0</span><span class="stat-label">Tỉ lệ cây sống khỏe</span></div>
</section>

<!-- ===================== DANH MỤC ===================== -->
<section id="categories" class="block-sec">
    <div class="sec-head-c">
        <h2>Danh mục cây</h2>
        <p class="hint">Chọn nhóm cây phù hợp với không gian và nhu cầu của bạn.</p>
    </div>
    <div class="cat-grid">
        <a class="cat-tile" href="shop.php"><span class="cat-ico">🪴</span><h3>Cây để bàn</h3><p>Nhỏ gọn, xanh mát cho bàn làm việc.</p></a>
        <a class="cat-tile" href="shop.php"><span class="cat-ico">🎍</span><h3>Cây phong thủy</h3><p>Hút tài lộc, hợp mệnh gia chủ.</p></a>
        <a class="cat-tile" href="shop.php"><span class="cat-ico">🌵</span><h3>Sen đá &amp; xương rồng</h3><p>Dễ sống, tưới ít, ưa nắng.</p></a>
        <a class="cat-tile" href="shop.php"><span class="cat-ico">🌳</span><h3>Bonsai nghệ thuật</h3><p>Dáng thế độc đáo, cây lâu năm.</p></a>
        <a class="cat-tile" href="shop.php"><span class="cat-ico">🌸</span><h3>Cây hoa</h3><p>Rực rỡ, làm quà tặng ý nghĩa.</p></a>
    </div>
</section>

<!-- ===================== CÂY NỔI BẬT ===================== -->
<section class="block-sec">
    <div class="row-between">
        <h2 style="margin:0">Cây bán chạy</h2>
        <a class="btn btn-sm btn-outline" href="shop.php">Xem tất cả →</a>
    </div>
    <div class="grid" style="margin-top:16px">
    <?php while ($it = mysqli_fetch_assoc($feat)):
        $v = plant_visual($it['name']); ?>
        <div class="card item" style="--rar:<?= $v['color'] ?>">
            <div class="item-fig"><span class="rar-badge"><?= $v['tag'] ?></span>
                <?php if ($img = plant_image($it['name'])): ?>
                    <img class="item-photo" src="<?= $img ?>" alt="<?= out($it['name']) ?>" loading="lazy">
                <?php else: ?><?= $v['svg'] ?><?php endif; ?>
            </div>
            <div class="item-body">
                <h3><?= out($it['name']) ?></h3>
                <p class="idesc"><?= out($it['description']) ?></p>
                <div class="item-foot">
                    <span class="price"><?= (int)$it['price'] ?></span>
                    <a class="btn btn-sm" href="shop.php">Xem</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</section>

<!-- ===================== VÌ SAO CHỌN ===================== -->
<section class="block-sec why">
    <div class="sec-head-c">
        <h2>Vì sao chọn Vườn Xanh?</h2>
        <p class="hint">Chúng tôi đồng hành từ lúc bạn chọn cây đến khi cây xanh tốt trong nhà.</p>
    </div>
    <div class="why-grid">
        <div class="why-item"><span class="why-ico">🌱</span><h3>Cây khỏe, đã thuần</h3><p>Cây được dưỡng tại vườn, thích nghi tốt trước khi đến tay bạn.</p></div>
        <div class="why-item"><span class="why-ico">🛡️</span><h3>Bảo hành 7 ngày</h3><p>Đổi cây mới nếu cây gặp vấn đề trong tuần đầu.</p></div>
        <div class="why-item"><span class="why-ico">🚚</span><h3>Giao nhanh, đóng gói kỹ</h3><p>Giao nội thành trong 24h, chậu được cố định chắc chắn.</p></div>
        <div class="why-item"><span class="why-ico">📖</span><h3>Hướng dẫn chăm sóc</h3><p>Kèm cẩm nang riêng cho từng loại cây, hỗ trợ trọn đời.</p></div>
    </div>
</section>

<!-- ===================== ĐÁNH GIÁ ===================== -->
<section id="reviews" class="block-sec">
    <div class="sec-head-c">
        <h2>Khách hàng nói gì</h2>
        <p class="hint">Hàng nghìn không gian đã xanh hơn cùng Vườn Xanh.</p>
    </div>
    <div class="review-grid">
        <figure class="review"><div class="stars">★★★★★</div>
            <blockquote>Cây kim tiền về rất xanh và chắc, đóng gói cẩn thận. Có kèm hướng dẫn tưới nên mình chăm dễ lắm.</blockquote>
            <figcaption><span class="avatar">TH</span><span><strong>Thu Hà</strong><small>Nhân viên văn phòng</small></span></figcaption>
        </figure>
        <figure class="review"><div class="stars">★★★★★</div>
            <blockquote>Bonsai tùng dáng đẹp đúng như hình, shop tư vấn tận tình. Sẽ ủng hộ tiếp cho ban công nhà mình.</blockquote>
            <figcaption><span class="avatar">MK</span><span><strong>Minh Khang</strong><small>Kiến trúc sư</small></span></figcaption>
        </figure>
        <figure class="review"><div class="stars">★★★★★</div>
            <blockquote>Mua sen đá làm quà, bạn mình thích mê. Giao nhanh, giá hợp lý, cây nào cây nấy mập mạp.</blockquote>
            <figcaption><span class="avatar">LP</span><span><strong>Lan Phương</strong><small>Sinh viên</small></span></figcaption>
        </figure>
    </div>
</section>

<!-- ===================== CẨM NANG / BLOG ===================== -->
<section id="blog" class="block-sec">
    <div class="row-between">
        <h2 style="margin:0">Cẩm nang chăm cây</h2>
        <?php if (is_logged_in()): ?><a class="btn btn-sm btn-outline" href="create_post.php">+ Viết bài</a><?php endif; ?>
    </div>
    <div class="blog-grid">
    <?php while ($p = mysqli_fetch_assoc($posts)): ?>
        <a class="blog-card" href="post.php?id=<?= (int)$p['id'] ?>">
            <div class="blog-thumb"><img src="<?= blog_image($p['title']) ?>" alt="<?= out($p['title']) ?>" loading="lazy"></div>
            <div class="blog-body">
                <h3><?= out($p['title']) ?></h3>
                <p class="meta">✍️ <?= out($p['username']) ?> · <?= out(mb_substr($p['created_at'],0,10)) ?></p>
                <p class="blog-ex"><?= out(mb_substr($p['body'], 0, 110)) ?>…</p>
                <span class="read-more">Đọc tiếp →</span>
            </div>
        </a>
    <?php endwhile; ?>
    </div>
</section>

<!-- ===================== FAQ ===================== -->
<section id="faq" class="block-sec faq">
    <div class="sec-head-c"><h2>Câu hỏi thường gặp</h2></div>
    <details><summary>Cây có được bảo hành không?</summary><p>Có. Mọi cây được bảo hành 7 ngày kể từ khi nhận. Nếu cây gặp vấn đề do vận chuyển hoặc dưỡng, chúng tôi đổi cây mới.</p></details>
    <details><summary>Tôi không biết chăm cây thì sao?</summary><p>Mỗi đơn hàng đều kèm cẩm nang chăm sóc riêng cho loại cây bạn mua, và bạn có thể nhắn tin nhờ tư vấn bất cứ lúc nào.</p></details>
    <details><summary>Giao hàng mất bao lâu?</summary><p>Nội thành TP.Hà Nội giao trong 24h. Các tỉnh khác từ 2–4 ngày, cây được đóng gói cố định chắc chắn.</p></details>
    <details><summary>Có cây hợp phòng thiếu sáng không?</summary><p>Có. Trầu bà, lưỡi hổ, kim tiền... đều chịu bóng tốt, rất hợp phòng ngủ và văn phòng ít nắng.</p></details>
    <details><summary>Có xuất hóa đơn / mua số lượng lớn không?</summary><p>Có. Vui lòng liên hệ hotline để được báo giá sỉ và xuất hóa đơn cho văn phòng, sự kiện.</p></details>
</section>

<!-- ===================== ĐĂNG KÝ NHẬN TIN ===================== -->
<section class="newsletter">
    <div class="nl-inner">
        <div>
            <h2>Nhận mẹo chăm cây hằng tuần</h2>
            <p>Đăng ký để nhận cẩm nang chăm cây và ưu đãi dành riêng cho thành viên.</p>
        </div>
        <form class="nl-form" data-demo action="#">
            <input type="email" name="email" placeholder="Email của bạn" required aria-label="Email">
            <button type="submit">Đăng ký</button>
        </form>
    </div>
</section>

<!-- ===================== LIÊN HỆ ===================== -->
<section id="contact" class="block-sec contact">
    <div class="contact-grid">
        <div>
            <h2>Liên hệ với chúng tôi</h2>
            <p class="hint">Cần tư vấn chọn cây? Nhắn cho Vườn Xanh, đội ngũ sẽ phản hồi trong ngày.</p>
            <ul class="contact-info">
                <li>📍 Ngõ 21 Yên Xá, Tân Triều, Thanh Trì, Hà Nội</li>
                <li>📞 0976 701 275</li>
                <li>✉️ huyph2005@gmail.com</li>
                <li>🕘 8:00 – 21:00 (T2 – T7)</li>
            </ul>
        </div>
        <form class="card form contact-form" data-demo action="#">
            <label>Họ và tên <input type="text" name="name" required></label>
            <label>Email <input type="email" name="email" required></label>
            <label>Nội dung <textarea name="msg" rows="4" required></textarea></label>
            <button type="submit">Gửi liên hệ</button>
        </form>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
