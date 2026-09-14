-- =====================================================================
--  OWASP Demo Shop - Database schema + seed data
--  MySQL / MariaDB
--  Chay:  mysql -u root -p < db/schema.sql
-- =====================================================================

DROP DATABASE IF EXISTS owasp_shop;
CREATE DATABASE owasp_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE owasp_shop;

-- ---------- Nguoi dung ----------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    -- Cot mat khau luu 2 dang de minh hoa:
    --   password_md5   : dang KHONG an toan (che do vulnerable)
    --   password_hash  : dang AN toan bcrypt (che do secure)
    password_md5  VARCHAR(32)  DEFAULT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',
    balance       INT NOT NULL DEFAULT 100,          -- vi tien / he thong tien te
    bio           TEXT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------- Bai viet ----------
CREATE TABLE posts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    title      VARCHAR(200) NOT NULL,
    body       TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------- Binh luan ----------
CREATE TABLE comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    body       TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------- Item ban hang ----------
CREATE TABLE items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    description TEXT,
    price       INT NOT NULL DEFAULT 0,             -- gia bang coin
    stock       INT NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------- Giao dich (lich su mua hang) ----------
CREATE TABLE transactions (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    item_id    INT NOT NULL,
    amount     INT NOT NULL,                        -- so coin da tra
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);

-- ---------- Log dang nhap (dung cho chong brute-force o che do secure) ----------
CREATE TABLE login_attempts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50) NOT NULL,
    ip         VARCHAR(45) NOT NULL,
    success    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================================
--  Du lieu mau
--  Mat khau mac dinh:
--    admin / admin123
--    alice / password1
--    bob   / qwerty
--  (md5 va bcrypt cua cung mot mat khau duoc nap san)
-- =====================================================================

-- md5('admin123')   = 0192023a7bbd73250516f069df18b500
-- md5('password1')  = 7c6a180b36896a0a8c02787eeafb0e4c
-- md5('qwerty')     = d8578edf8458ce06fbc5bb76a58c5ca4
INSERT INTO users (username, password_md5, password_hash, role, balance, bio) VALUES
('admin', '0192023a7bbd73250516f069df18b500', '$2b$10$gvEAWaLZjgbDQp7.7FLmFOrryrwj1vGUhCvg4YyfFRthZmxCJNKT2', 'admin', 100000, 'Quản trị viên hệ thống'),
('alice', '7c6a180b36896a0a8c02787eeafb0e4c', '$2b$10$8RmNEoAByXn0I74r4bV94uNR/sjg50kMidXKCQQ3TvdlraIoqpuS2', 'user', 500, 'Xin chào, mình là Alice'),
('bob',   'd8578edf8458ce06fbc5bb76a58c5ca4', '$2b$10$v7pAbK/UVD.QakzmUo9q4ORGE/wzmf/Vfp5bFAxgJ4eiV1I10nPYe', 'user', 250, 'Bob ở đây');

INSERT INTO posts (user_id, title, body) VALUES
(2, '5 loại cây cảnh để bàn hợp phong thủy', 'Cây để bàn vừa làm đẹp góc làm việc vừa mang ý nghĩa phong thủy. Top gợi ý gồm kim tiền, kim ngân, ngọc bích, lưỡi hổ mini và trầu bà. Đặt cây nơi có ánh sáng gián tiếp, tưới khi đất se mặt để cây luôn xanh tốt.'),
(3, 'Bí quyết chăm sen đá không bị úng nước', 'Sen đá chết phần lớn do tưới quá nhiều. Hãy dùng đất tơi thoát nước tốt, chậu có lỗ, và chỉ tưới khi đất khô hoàn toàn. Đặt cây nơi nhiều nắng sáng, tránh để nước đọng trên lá qua đêm.'),
(2, 'Cây lọc không khí tốt nhất cho phòng ngủ', 'Lưỡi hổ, trầu bà và lô hội là những cây lọc khí lý tưởng cho phòng ngủ vì nhả oxy cả ban đêm. Chúng dễ chăm, chịu bóng và giúp không khí trong lành hơn cho giấc ngủ sâu.');

INSERT INTO comments (post_id, user_id, body) VALUES
(1, 3, 'Bài viết hữu ích quá, mình vừa mua cây kim tiền về bàn làm việc!'),
(1, 2, 'Cảm ơn mọi người, chúc cả nhà chăm cây thật xanh nhé.');

INSERT INTO items (name, description, price, stock) VALUES
('Bonsai Tùng La Hán',   'Dáng thế nghệ thuật, cây lâu năm, tôn không gian sang trọng.', 890, 6),
('Lan Hồ Điệp Trắng',    'Chậu lan sang trọng làm quà tặng, hoa bền tới 2 tháng.',      350, 15),
('Cây Kim Tiền',         'Cây phong thủy hút tài lộc, chịu bóng tốt, rất dễ chăm.',      250, 20),
('Cây Kim Ngân Bím',     'Thân bím độc đáo, biểu tượng tài lộc, xanh tốt trong nhà.',    180, 18),
('Trầu Bà Leo Cột',      'Cây lọc không khí, leo cột xanh mướt, hợp phòng khách.',       120, 35),
('Lưỡi Hổ Thái',         'Lọc khí ban đêm, khỏe, hợp phòng ngủ và văn phòng.',           95, 40),
('Xương Rồng Bát Tiên',  'Cây dễ sống, ra hoa quanh năm, hợp ban công nhiều nắng.',      65, 50),
('Sen Đá Nâu Mix',       'Chậu sen đá mini để bàn, ưa nắng nhẹ, tưới ít nước.',          45, 80);
