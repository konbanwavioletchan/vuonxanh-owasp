# 5 lo hong OWASP Top 10:2025 — Khai thac & Khac phuc

Tai lieu mo ta 5 lo hong duoc cai dat co chu dich trong website, cach **khai thac**
(o che do `SECURE = false`) va cach **khac phuc** (o che do `SECURE = true`).
Moi lo hong deu co san ca hai nhanh code trong cung mot file — chi doi hang so
`SECURE` trong [config/config.php](config/config.php) de so sanh truoc/sau.

> Che do mac dinh la `false` (co lo hong) de tien demo. Sau khi bao cao xong, dat
> `SECURE = true` de chay ban da va.

5 lo hong duoc chon deu thuoc **OWASP Top 10:2025** (4/5 nam trong nhom Top 5 moi nhat):

| # | OWASP 2025 | Lo hong cu the | File |
|---|-----------|----------------|------|
| 1 | **A01:2025 – Broken Access Control** | Thieu kiem tra quyen admin + IDOR ho so | `admin.php`, `profile.php` |
| 2 | **A02:2025 – Security Misconfiguration** | Lo loi/CSDL, khong chan liet ke thu muc | `config.php`, VirtualHost |
| 3 | **A04:2025 – Cryptographic Failures** | Bam mat khau bang MD5 (khong salt) | `functions.php` |
| 4 | **A05:2025 – Injection** | SQL Injection + Stored XSS | `login.php`, `search.php`, `post.php` |
| 5 | **A07:2025 – Authentication Failures** | Khong chan brute-force, mat khau yeu, session fixation | `login.php` |

> Danh sach OWASP Top 10:2025 day du: A01 Broken Access Control · A02 Security
> Misconfiguration · A03 Software Supply Chain Failures · A04 Cryptographic Failures ·
> A05 Injection · A06 Insecure Design · A07 Authentication Failures · A08 Software and
> Data Integrity Failures · A09 Logging & Alerting Failures · A10 Mishandling of
> Exceptional Conditions.

---

## 1. A01:2025 — Broken Access Control (Kiem soat truy cap hong)

### Vi tri
- `public/admin.php` — trang quan tri.
- `public/profile.php` — chinh sua ho so.

### Nguyen nhan (che do vulnerable)
- `admin.php` **khong goi** `require_admin()`. Trang khong hien link cho user thuong,
  nhung do la "an toan bang che giau" (security by obscurity) — van vao thang bang URL.
- `profile.php` tin tuong truong `id` gui tu client => **IDOR** (Insecure Direct Object
  Reference): sua duoc ho so nguoi khac, tham chi **mass-assignment** truong `balance`.

### Cach khai thac
1. Dang nhap bang tai khoan thuong `alice / password1`.
2. Truy cap thang `http://localhost/owasp-shop/public/admin.php` → **vao duoc** trang quan tri
   du khong phai admin; co the tu nang quyen minh len `admin` hoac cong coin.
3. IDOR: mo `profile.php?id=1` (ho so admin) → van thay form sua. Gui POST voi
   `id=1&bio=hacked&balance=999999` (vi du bang DevTools/Burp) → sua duoc so du nguoi khac.

### Khac phuc (che do secure)
- `admin.php` goi `require_admin($conn)` — **kiem tra vai tro o phia server** cho MOI request.
- `profile.php` bo qua `id` client gui len, **chi cho sua ho so cua chinh minh**
  (`$target = $_SESSION['user_id']`) va **khong** cho sua `balance` qua form nguoi dung.
- Nguyen tac: kiem tra phan quyen o server, "deny by default", khong bao gio tin ID tu client.

---

## 2. A02:2025 — Security Misconfiguration (Cau hinh sai an toan)

### Vi tri
`config/config.php` (hien thi loi), cach cau hinh web server (liet ke thu muc,
lo file nhay cam).

### Nguyen nhan (che do vulnerable)
- Bat `display_errors = 1`, `error_reporting(E_ALL)` → **lo thong tin** duong dan,
  cau lenh SQL, ten cot (`mysqli_error()` in ra o `search.php`) — ho tro ke tan cong.
- Neu DocumentRoot tro vao thu muc goc (khong phai `public/`), attacker co the tai
  `config/config.php`, `db/schema.sql`... Neu Apache bat `Options Indexes` → **liet ke thu muc**.

### Cach khai thac
1. Gui payload SQL sai o `search.php?q='` → man hinh in **loi SQL day du** (ten bang, cot).
2. Truy cap `http://localhost/owasp-shop/db/schema.sql` (neu web root sai) → tai duoc CSDL.

### Khac phuc (che do secure)
- Tat lo loi ra ngoai: `display_errors = 0`, `error_reporting(0)`; ghi log noi bo thay vi in ra.
- **DocumentRoot chi tro vao `public/`** (xem VirtualHost trong README) — cac thu muc
  `config`, `db`, `includes` nam **ngoai** web root, khong the truy cap qua URL.
- Tat liet ke thu muc: `Options -Indexes`.
- Go du lieu/tai khoan mac dinh, doi mat khau CSDL (khong dung `root` khong mat khau).

---

## 3. A04:2025 — Cryptographic Failures (Loi mat ma hoc)

### Vi tri
`includes/functions.php` — ham `hash_password()` / `verify_password()`;
cot `users.password_md5` trong CSDL.

### Nguyen nhan (che do vulnerable)
Mat khau duoc bam bang **MD5 khong salt**: `md5($password)`.
MD5 rat nhanh, co san rainbow table, khong salt → de crack hang loat neu lo CSDL.

### Cach khai thac
1. Lay hash tu CSDL (vi du qua lo hong SQL Injection o muc 4, hoac `SELECT password_md5 FROM users`).
2. Tra nguoc bang rainbow table / Google:
   - `0192023a7bbd73250516f069df18b500` → `admin123`
   - `7c6a180b36896a0a8c02787eeafb0e4c` → `password1`
3. Dang nhap bang mat khau vua crack.

### Khac phuc (che do secure)
- Dung `password_hash($password, PASSWORD_BCRYPT)` (bcrypt, co salt, co work factor).
- Kiem tra bang `password_verify()`; luu vao cot `password_hash`.
- Bcrypt cham co chu dich + salt ngau nhien → khong dung rainbow table, chong crack hang loat.
- Nen bo sung: `password_needs_rehash()` de nang cap dan work factor theo thoi gian.

---

## 4. A05:2025 — Injection (SQL Injection & Cross-Site Scripting)

### 4a. SQL Injection
**Vi tri:** `public/login.php` (o Ten dang nhap), `public/search.php` (tham so `q`).

**Nguyen nhan (vulnerable):** noi chuoi truc tiep vao cau lenh SQL. O `login.php`,
ca username va mat khau (md5) deu ghep thang vao cau lenh:
```php
$sql = "SELECT * FROM users WHERE username = '$username' AND password_md5 = '$md5'";
```

**Cach khai thac:**
- **Bypass dang nhap:** o o Ten dang nhap nhap `admin' -- ` (co dau cach cuoi),
  mat khau bo trong → cau lenh thanh
  `... WHERE username = 'admin' -- ' AND password_md5 = '...'`, phan kiem tra mat khau
  bi comment → **dang nhap thang vao admin ma khong can mat khau**.
  Hoac `' OR '1'='1' LIMIT 1 -- ` → dang nhap thanh user dau tien.
- **Trich xuat du lieu (UNION) qua tim kiem:**
  ```
  search.php?q=x' UNION SELECT id,username,password_md5,role,created_at,username FROM users -- -
  ```
  → hash mat khau moi user hien ra o phan tieu de ket qua tim kiem.

**Khac phuc (secure):** dung **prepared statement** (tham so hoa):
```php
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, 's', $username);
```
Du lieu nguoi dung khong bao gio duoc ghep vao cau lenh → khong the doi cau truc SQL.

### 4b. Stored XSS
**Vi tri:** `public/post.php` — noi dung binh luan; ham `out()` trong `functions.php`.

**Nguyen nhan (vulnerable):** khi **xuat** ra HTML, `out()` tra nguyen van khong escape:
```php
<span><?= out($c['body']) ?></span>   // out() = tra thang o che do vulnerable
```

**Cach khai thac:** dang nhap, vao 1 bai viet, gui binh luan:
```html
<script>alert(document.cookie)</script>
```
→ moi nguoi xem bai viet se chay doan script (danh cap cookie/session, deface...).

**Khac phuc (secure):** escape output bang `htmlspecialchars(..., ENT_QUOTES)`:
```php
function out($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
```
Ky tu `< > " '` bi ma hoa → trinh duyet hien thi dang van ban, khong chay script.
Bo sung: header `Content-Security-Policy`, cookie `HttpOnly` (da bat o secure).

---

## 5. A07:2025 — Authentication Failures (Loi xac thuc & dinh danh)

### Vi tri
`public/login.php`, `public/register.php`, cau hinh session.

### Nguyen nhan (che do vulnerable)
- **Khong gioi han so lan dang nhap sai** → brute-force / credential stuffing thoai mai.
- **Khong co chinh sach mat khau** (chap nhan mat khau ngan nhu `123`).
- **Session fixation:** khong goi `session_regenerate_id()` sau khi dang nhap; cookie thieu `HttpOnly`.

### Cach khai thac
- Dung cong cu (Hydra/Burp Intruder) thu hang nghin mat khau vao `login.php` — khong bi chan.
- Vi mat khau nguoi dung yeu (`qwerty`, `password1`) → do thanh cong cao.

### Khac phuc (che do secure)
- **Chan brute-force:** ham `too_many_attempts()` khoa 15 phut sau 5 lan sai
  (bang bang `login_attempts`); nen them CAPTCHA / do tre tang dan.
- **Chinh sach mat khau:** yeu cau toi thieu 8 ky tu khi dang ky.
- **Session:** goi `session_regenerate_id(true)` sau dang nhap (chong fixation);
  cookie `HttpOnly` + `SameSite=Lax` (bat trong `config.php`); bat `Secure` khi chay HTTPS.

---

## Phu luc — Lo hong bo sung da xu ly o che do secure

Ngoai 5 lo hong chinh, che do secure con minh hoa them (thuoc OWASP Top 10:2025):

- **CSRF (lien quan A01:2025 – Broken Access Control):** o secure, moi form co token CSRF
  (`csrf_field()` / `csrf_check()`); o vulnerable khong co → ke tan cong co the lam nan nhan
  gui request ngoai y muon. Da chan bang token dong bo + `SameSite`.
- **Business logic (A06:2025 – Insecure Design) o `buy.php`:** o vulnerable, mua hang khong
  kiem tra so du/ton kho → so du am, race condition. O secure dung transaction +
  `UPDATE ... WHERE balance >= price` (nguyen tu).

## Checklist demo (goi y noi dung bao cao)

Voi moi lo hong, chup 2 anh:
1. **Truoc (SECURE=false):** thuc hien khai thac thanh cong (payload + ket qua).
2. **Sau (SECURE=true):** lap lai chinh thao tac do → bi chan / khong con hieu luc.
