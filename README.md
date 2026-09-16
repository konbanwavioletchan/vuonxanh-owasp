# OWASP Demo Web

Website demo (PHP + MySQL) mô phỏng **5 lỗ hổng trong OWASP Top 10:2025**, kèm giải pháp
khắc phục. Dự án phục vụ **mục đích học tập** trong môi trường thử nghiệm (localhost / máy ảo).
**Không** triển khai bản có lỗ hổng ra Internet công khai.



## Chức năng chính

| Yêu cầu | Trang / File |
|---|---|
| Đăng nhập | `public/login.php` |
| Định danh & xác thực người dùng | `public/login.php`, `public/register.php`, session |
| Phân quyền người dùng (user / admin) | `require_admin()`, `public/admin.php` |
| Đăng bài | `public/create_post.php`, `public/index.php` |
| Bình luận | `public/post.php` |
| Vật phẩm bán hàng | `public/shop.php` |
| Hệ thống tiền tệ (coin / ví) | cột `users.balance`, `public/buy.php` |
| Triển khai trên máy chủ | Phần "Triển khai" bên dưới |

## Công tắc SECURE / VULNERABLE

Toàn bộ lỗ hổng được điều khiển bằng **một hằng số** trong
[config/config.php](config/config.php):

```php
define('SECURE', false);  // false = có lỗ hổng (demo tấn công)
                          // true  = đã vá  (khắc phục)
```


## Cấu hình kết nối CSDL

Trước khi chạy, mở [config/config.php](config/config.php) và sửa cho khớp máy của bạn:

```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');     // XAMPP/MariaDB mặc định để trống
define('DB_NAME', 'owasp_shop');
define('DB_PORT', 3307);   // giá trị mặc định của dự án; XAMPP thường dùng 3306 -> đổi cho khớp
```

> `DB_PORT` phải trùng cổng của MySQL/MariaDB đang chạy, nếu không web sẽ báo không kết
> nối được CSDL. Xem cổng thực tế trong XAMPP Control Panel → **Config** → `my.ini` → `port=`.

## Cài đặt nhanh (Windows + XAMPP)

> Clone / giải nén mã nguồn vào `htdocs` của XAMPP, đổi tên thư mục thành `owasp-shop`.

1. Cài [XAMPP](https://www.apachefriends.org/), bật **Apache** + **MySQL** trong Control Panel.
2. Sửa thông tin kết nối trong `config/config.php` (xem mục **Cấu hình kết nối CSDL** ở trên).
3. Nạp CSDL: phpMyAdmin → **Import** → chọn `db/schema.sql`.
4. Truy cập `http://localhost/owasp-shop/public/index.php`.

Tài khoản mẫu:

| Tài khoản | Mật khẩu | Vai trò |
|---|---|---|
| `admin` | `admin123` | admin |
| `alice` | `password1` | user |
| `bob` | `qwerty` | user |

## Triển khai trên máy chủ Linux / Ubuntu (LAMP)

```bash
# 1. Cài LAMP (gói php-mysql cung cấp cả mysqli lẫn pdo_mysql)
sudo apt update
sudo apt install -y apache2 mysql-server php php-mysql libapache2-mod-php

# 2. Chép mã nguồn vào web root
sudo cp -r owasp-shop /var/www/html/
sudo chown -R www-data:www-data /var/www/html/owasp-shop

# 3. Sửa config/config.php cho khớp MySQL trên máy chủ
#    (DB_USER/DB_PASS, và DB_PORT thường là 3306 trên Ubuntu)
sudo nano /var/www/html/owasp-shop/config/config.php

# 4. Nạp CSDL
sudo mysql < /var/www/html/owasp-shop/db/schema.sql

# 5. (Khuyến nghị) trỏ DocumentRoot / VirtualHost vào thư mục public/
#    để KHÔNG lộ các file config, db, includes ra ngoài web.
sudo nano /etc/apache2/sites-available/owasp-shop.conf
```

Ví dụ VirtualHost (chỉ `public/` được phục vụ — giảm A02:2025 Security Misconfiguration):

```apache
<VirtualHost *:80>
    ServerName owasp-shop.local
    DocumentRoot /var/www/html/owasp-shop/public
    <Directory /var/www/html/owasp-shop/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>
</VirtualHost>
```

```bash
sudo a2ensite owasp-shop.conf
sudo systemctl reload apache2
```

## Triển khai trên Windows Server (IIS hoặc XAMPP)

- **Cách 1 (nhanh):** dùng XAMPP như mục "Cài đặt nhanh".
- **Cách 2 (IIS):** tải bản PHP non-thread-safe tại [windows.php.net](https://windows.php.net/download/),
  bật **CGI/FastCGI** trong IIS và khai báo handler cho `php-cgi.exe`, trỏ site vào
  thư mục `public/`, cài MySQL riêng rồi nạp `db/schema.sql`.

## Cấu trúc thư mục

```text
owasp-shop/
├─ config/config.php        # kết nối CSDL + công tắc SECURE
├─ db/schema.sql            # tạo CSDL + dữ liệu mẫu
├─ includes/
│  ├─ functions.php         # auth, phân quyền, CSRF, escaping...
│  └─ header.php / footer.php
├─ public/                  # DocumentRoot (phần lộ ra web)
│  ├─ index.php  login.php  register.php  logout.php
│  ├─ post.php   create_post.php
│  ├─ shop.php   buy.php
│  ├─ profile.php  admin.php  search.php  gioi-thieu.php
│  └─ assets/css/style.css  assets/js/main.js  assets/img/
├─ README.md                # tài liệu này
└─ SECURITY.md              # chi tiết 5 lỗ hổng + cách khai thác + cách vá
```

Xem [SECURITY.md](SECURITY.md) để biết chi tiết 5 lỗ hổng OWASP, cách khai thác và cách
khắc phục từng cái.
