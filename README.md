# OWASP Demo Shop

Website demo (PHP + MySQL) mô phỏng **5 lỗ hổng trong OWASP Top 10:2025**, kèm giải pháp
khắc phục. Dự án phục vụ **mục đích học tập** trong môi trường thử nghiệm (localhost / máy ảo).
**Không** triển khai bản có lỗ hổng ra Internet công khai.



## Chức năng chính (đáp ứng yêu cầu đề bài)

| Yêu cầu | Trang / File |
|---|---|
| Đăng nhập | `public/login.php` |
| Định danh & xác thực người dùng | `login.php`, `register.php`, session |
| Phân quyền người dùng (user / admin) | `require_admin()`, `public/admin.php` |
| Đăng bài | `public/create_post.php`, `index.php` |
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

Nhờ vậy bạn dễ dàng chụp minh chứng **trước / sau** cho từng lỗ hổng. Banner màu trên đầu
trang cho biết đang ở chế độ nào (đỏ = có lỗ hổng, xanh = an toàn).

## Cài đặt nhanh (Windows + XAMPP)

1. Cài [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP).
2. Chép thư mục dự án vào `C:\xampp\htdocs\owasp-shop`.
3. Bật **Apache** và **MySQL** trong XAMPP Control Panel.
4. Nạp CSDL:

   ```bash
   C:\xampp\mysql\bin\mysql -u root < db\schema.sql
   ```

   (hoặc mở phpMyAdmin → Import → chọn `db/schema.sql`).
5. Truy cập: `http://localhost/owasp-shop/public/index.php`

Tài khoản mẫu:

| Tài khoản | Mật khẩu | Vai trò |
|---|---|---|
| `admin` | `admin123` | admin |
| `alice` | `password1` | user |
| `bob` | `qwerty` | user |

## Triển khai trên máy chủ Linux / Ubuntu (LAMP)

```bash
# 1. Cài LAMP
sudo apt update
sudo apt install -y apache2 mysql-server php php-mysqli libapache2-mod-php

# 2. Chép mã nguồn vào web root
sudo cp -r owasp-shop /var/www/html/
sudo chown -R www-data:www-data /var/www/html/owasp-shop

# 3. Nạp CSDL
sudo mysql < /var/www/html/owasp-shop/db/schema.sql

# 4. (Khuyến nghị) trỏ DocumentRoot / VirtualHost vào thư mục public/
#    để KHÔNG lộ các file config, db, includes ra ngoài web.
sudo nano /etc/apache2/sites-available/owasp-shop.conf
```

Ví dụ VirtualHost (chỉ `public/` được phục vụ — giảm A05 Security Misconfiguration):

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
- **Cách 2 (IIS):** cài PHP qua [Web Platform Installer], bật CGI/FastCGI, trỏ site vào
  thư mục `public/`, cài MySQL riêng và nạp `db/schema.sql`.

## Cấu trúc thư mục

```text
owasp-shop/
├─ config/config.php        # kết nối CSDL + công tắc SECURE
├─ db/schema.sql            # tạo CSDL + dữ liệu mẫu
├─ includes/
│  ├─ functions.php         # auth, phân quyền, CSRF, escaping...
│  ├─ header.php / footer.php
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
