# 5 lỗ hổng OWASP Top 10:2025 — Khai thác & Khắc phục

Tài liệu mô tả 5 lỗ hổng được cài đặt có chủ đích trong website, cách **khai thác**
(ở chế độ `SECURE = false`) và cách **khắc phục** (ở chế độ `SECURE = true`).
Mỗi lỗ hổng đều có sẵn cả hai nhánh code trong cùng một file — chỉ đổi hằng số
`SECURE` trong [config/config.php](config/config.php) để so sánh trước/sau.

> Chế độ mặc định là `false` (có lỗ hổng) để tiện demo. Sau khi báo cáo xong, đặt
> `SECURE = true` để chạy bản đã vá.

5 lỗ hổng được chọn đều thuộc **OWASP Top 10:2025** (4/5 nằm trong nhóm Top 5 mới nhất):

| # | OWASP 2025 | Lỗ hổng cụ thể | File |
|---|-----------|----------------|------|
| 1 | **A01:2025 – Broken Access Control** | Thiếu kiểm tra quyền admin + IDOR hồ sơ | `admin.php`, `profile.php` |
| 2 | **A02:2025 – Security Misconfiguration** | Lộ lỗi/CSDL, không chặn liệt kê thư mục | `config.php`, VirtualHost |
| 3 | **A04:2025 – Cryptographic Failures** | Băm mật khẩu bằng MD5 (không salt) | `functions.php` |
| 4 | **A05:2025 – Injection** | SQL Injection + Stored XSS | `login.php`, `search.php`, `post.php` |
| 5 | **A07:2025 – Authentication Failures** | Không chặn brute-force, mật khẩu yếu, session fixation | `login.php` |

> Danh sách OWASP Top 10:2025 đầy đủ: A01 Broken Access Control · A02 Security
> Misconfiguration · A03 Software Supply Chain Failures · A04 Cryptographic Failures ·
> A05 Injection · A06 Insecure Design · A07 Authentication Failures · A08 Software and
> Data Integrity Failures · A09 Logging & Alerting Failures · A10 Mishandling of
> Exceptional Conditions.

---

## 1. A01:2025 — Broken Access Control (Kiểm soát truy cập hỏng)

### Vị trí
- `public/admin.php` — trang quản trị.
- `public/profile.php` — chỉnh sửa hồ sơ.

### Nguyên nhân (chế độ vulnerable)
- `admin.php` **không gọi** `require_admin()`. Trang không hiện link cho user thường,
  nhưng đó là "an toàn bằng che giấu" (security by obscurity) — vẫn vào thẳng bằng URL.
- `profile.php` tin tưởng trường `id` gửi từ client => **IDOR** (Insecure Direct Object
  Reference): sửa được hồ sơ người khác, thậm chí **mass-assignment** trường `balance`.

### Cách khai thác
1. Đăng nhập bằng tài khoản thường `alice / password1`.
2. Truy cập thẳng `http://localhost/owasp-shop/public/admin.php` → **vào được** trang quản trị
   dù không phải admin; có thể tự nâng quyền mình lên `admin` hoặc cộng coin.
3. IDOR: mở `profile.php?id=1` (hồ sơ admin) → vẫn thấy form sửa. Gửi POST với
   `id=1&bio=hacked&balance=999999` (ví dụ bằng DevTools/Burp) → sửa được số dư người khác.

### Khắc phục (chế độ secure)
- `admin.php` gọi `require_admin($conn)` — **kiểm tra vai trò ở phía server** cho MỌI request.
- `profile.php` bỏ qua `id` client gửi lên, **chỉ cho sửa hồ sơ của chính mình**
  (`$target = $_SESSION['user_id']`) và **không** cho sửa `balance` qua form người dùng.
- Nguyên tắc: kiểm tra phân quyền ở server, "deny by default", không bao giờ tin ID từ client.

---

## 2. A02:2025 — Security Misconfiguration (Cấu hình sai an toàn)

### Vị trí
`config/config.php` (hiển thị lỗi), cách cấu hình web server (liệt kê thư mục,
lộ file nhạy cảm).

### Nguyên nhân (chế độ vulnerable)
- Bật `display_errors = 1`, `error_reporting(E_ALL)` → **lộ thông tin** đường dẫn,
  câu lệnh SQL, tên cột (`mysqli_error()` in ra ở `search.php`) — hỗ trợ kẻ tấn công.
- Nếu DocumentRoot trỏ vào thư mục gốc (không phải `public/`), attacker có thể tải
  `config/config.php`, `db/schema.sql`... Nếu Apache bật `Options Indexes` → **liệt kê thư mục**.

### Cách khai thác
1. Gửi payload SQL sai ở `search.php?q='` → màn hình in **lỗi SQL đầy đủ** (tên bảng, cột).
2. Truy cập `http://localhost/owasp-shop/db/schema.sql` (nếu web root sai) → tải được CSDL.

### Khắc phục (chế độ secure)
- Tắt lộ lỗi ra ngoài: `display_errors = 0`, `error_reporting(0)`; ghi log nội bộ thay vì in ra.
- **DocumentRoot chỉ trỏ vào `public/`** (xem VirtualHost trong README) — các thư mục
  `config`, `db`, `includes` nằm **ngoài** web root, không thể truy cập qua URL.
- Tắt liệt kê thư mục: `Options -Indexes`.
- Gỡ dữ liệu/tài khoản mặc định, đổi mật khẩu CSDL (không dùng `root` không mật khẩu).

---

## 3. A04:2025 — Cryptographic Failures (Lỗi mật mã học)

### Vị trí
`includes/functions.php` — hàm `hash_password()` / `verify_password()`;
cột `users.password_md5` trong CSDL.

### Nguyên nhân (chế độ vulnerable)
Mật khẩu được băm bằng **MD5 không salt**: `md5($password)`.
MD5 rất nhanh, có sẵn rainbow table, không salt → dễ crack hàng loạt nếu lộ CSDL.

### Cách khai thác
1. Lấy hash từ CSDL (ví dụ qua lỗ hổng SQL Injection ở mục 4, hoặc `SELECT password_md5 FROM users`).
2. Tra ngược bằng rainbow table / Google:
   - `0192023a7bbd73250516f069df18b500` → `admin123`
   - `7c6a180b36896a0a8c02787eeafb0e4c` → `password1`
3. Đăng nhập bằng mật khẩu vừa crack.

### Khắc phục (chế độ secure)
- Dùng `password_hash($password, PASSWORD_BCRYPT)` (bcrypt, có salt, có work factor).
- Kiểm tra bằng `password_verify()`; lưu vào cột `password_hash`.
- Bcrypt chậm có chủ đích + salt ngẫu nhiên → không dùng rainbow table, chống crack hàng loạt.
- Nên bổ sung: `password_needs_rehash()` để nâng cấp dần work factor theo thời gian.

---

## 4. A05:2025 — Injection (SQL Injection & Cross-Site Scripting)

### 4a. SQL Injection
**Vị trí:** `public/login.php` (ô Tên đăng nhập), `public/search.php` (tham số `q`).

**Nguyên nhân (vulnerable):** nối chuỗi trực tiếp vào câu lệnh SQL. Ở `login.php`,
cả username và mật khẩu (md5) đều ghép thẳng vào câu lệnh:
```php
$sql = "SELECT * FROM users WHERE username = '$username' AND password_md5 = '$md5'";
```

**Cách khai thác:**
- **Bypass đăng nhập:** ở ô Tên đăng nhập nhập `admin' -- ` (có dấu cách cuối),
  mật khẩu bỏ trống → câu lệnh thành
  `... WHERE username = 'admin' -- ' AND password_md5 = '...'`, phần kiểm tra mật khẩu
  bị comment → **đăng nhập thẳng vào admin mà không cần mật khẩu**.
  Hoặc `' OR '1'='1' LIMIT 1 -- ` → đăng nhập thành user đầu tiên.
- **Trích xuất dữ liệu (UNION) qua tìm kiếm:**
  ```
  search.php?q=x' UNION SELECT id,username,password_md5,role,created_at,username FROM users -- -
  ```
  → hash mật khẩu mọi user hiện ra ở phần tiêu đề kết quả tìm kiếm.

**Khắc phục (secure):** dùng **prepared statement** (tham số hóa):
```php
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, 's', $username);
```
Dữ liệu người dùng không bao giờ được ghép vào câu lệnh → không thể đổi cấu trúc SQL.

### 4b. Stored XSS
**Vị trí:** `public/post.php` — nội dung bình luận; hàm `out()` trong `functions.php`.

**Nguyên nhân (vulnerable):** khi **xuất** ra HTML, `out()` trả nguyên văn không escape:
```php
<span><?= out($c['body']) ?></span>   // out() = trả thẳng ở chế độ vulnerable
```

**Cách khai thác:** đăng nhập, vào 1 bài viết, gửi bình luận:
```html
<script>alert(document.cookie)</script>
```
→ mọi người xem bài viết sẽ chạy đoạn script (đánh cắp cookie/session, deface...).

**Khắc phục (secure):** escape output bằng `htmlspecialchars(..., ENT_QUOTES)`:
```php
function out($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
```
Ký tự `< > " '` bị mã hóa → trình duyệt hiển thị dạng văn bản, không chạy script.
Bổ sung: header `Content-Security-Policy`, cookie `HttpOnly` (đã bật ở secure).

---

## 5. A07:2025 — Authentication Failures (Lỗi xác thực & định danh)

### Vị trí
`public/login.php`, `public/register.php`, cấu hình session.

### Nguyên nhân (chế độ vulnerable)
- **Không giới hạn số lần đăng nhập sai** → brute-force / credential stuffing thoải mái.
- **Không có chính sách mật khẩu** (chấp nhận mật khẩu ngắn như `123`).
- **Session fixation:** không gọi `session_regenerate_id()` sau khi đăng nhập; cookie thiếu `HttpOnly`.

### Cách khai thác
- Dùng công cụ (Hydra/Burp Intruder) thử hàng nghìn mật khẩu vào `login.php` — không bị chặn.
- Vì mật khẩu người dùng yếu (`qwerty`, `password1`) → dò thành công cao.

### Khắc phục (chế độ secure)
- **Chặn brute-force:** hàm `too_many_attempts()` khóa 15 phút sau 5 lần sai
  (bằng bảng `login_attempts`); nên thêm CAPTCHA / độ trễ tăng dần.
- **Chính sách mật khẩu:** yêu cầu tối thiểu 8 ký tự khi đăng ký.
- **Session:** gọi `session_regenerate_id(true)` sau đăng nhập (chống fixation);
  cookie `HttpOnly` + `SameSite=Lax` (bật trong `config.php`); bật `Secure` khi chạy HTTPS.

---

## Phụ lục — Lỗ hổng bổ sung đã xử lý ở chế độ secure

Ngoài 5 lỗ hổng chính, chế độ secure còn minh họa thêm (thuộc OWASP Top 10:2025):

- **CSRF (liên quan A01:2025 – Broken Access Control):** ở secure, mọi form có token CSRF
  (`csrf_field()` / `csrf_check()`); ở vulnerable không có → kẻ tấn công có thể làm nạn nhân
  gửi request ngoài ý muốn. Đã chặn bằng token đồng bộ + `SameSite`.
- **Business logic (A06:2025 – Insecure Design) ở `buy.php`:** ở vulnerable, mua hàng không
  kiểm tra số dư/tồn kho → số dư âm, race condition. Ở secure dùng transaction +
  `UPDATE ... WHERE balance >= price` (nguyên tử).

## CONG TAC AN TOAN / SECURE MODE

1. **Trước (SECURE=false):** thực hiện khai thác thành công (payload + kết quả).
2. **Sau (SECURE=true):** lặp lại chính thao tác đó → bị chặn / không còn hiệu lực.
