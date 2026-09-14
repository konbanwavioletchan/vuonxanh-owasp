# OWASP Demo Shop

Website demo (PHP + MySQL) mo phong **5 lo hong trong OWASP Top 10:2025**, kem
giai phap khac phuc. Du an phuc vu **muc dich hoc tap** trong moi truong thu nghiem
(localhost / may ao). **Khong** trien khai ban co lo hong ra Internet cong khai.

## Chuc nang chinh (dap ung yeu cau de bai)

| Yeu cau | Trang / File |
|---|---|
| Dang nhap | `public/login.php` |
| Dinh danh & xac thuc nguoi dung | `login.php`, `register.php`, session |
| Phan quyen nguoi dung (user / admin) | `require_admin()`, `public/admin.php` |
| Dang bai | `public/create_post.php`, `index.php` |
| Binh luan | `public/post.php` |
| Item ban hang | `public/shop.php` |
| He thong tien te (coin/vi) | cot `users.balance`, `public/buy.php` |
| Trien khai tren may chu | Phan "Trien khai" ben duoi |

## Cong tac SECURE / VULNERABLE

Toan bo lo hong duoc dieu khien bang **1 hang so** trong [config/config.php](config/config.php):

```php
define('SECURE', false);  // false = co lo hong (demo tan cong)
                          // true  = da va (khac phuc)
```

Nho vay ban de dang chup minh chung **truoc / sau** cho tung lo hong.
Banner mau tren dau trang cho biet dang o che do nao (do = co lo hong, xanh = an toan).

## Cai dat nhanh (Windows + XAMPP)

1. Cai [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP).
2. Chep thu muc du an vao `C:\xampp\htdocs\owasp-shop`.
3. Bat **Apache** va **MySQL** trong XAMPP Control Panel.
4. Nap CSDL:
   ```
   C:\xampp\mysql\bin\mysql -u root < db\schema.sql
   ```
   (hoac mo phpMyAdmin -> Import -> chon `db/schema.sql`).
5. Truy cap: `http://localhost/owasp-shop/public/index.php`

Tai khoan mau:

| Tai khoan | Mat khau | Vai tro |
|---|---|---|
| `admin` | `admin123` | admin |
| `alice` | `password1` | user |
| `bob`   | `qwerty`    | user |

## Trien khai tren may chu Linux / Ubuntu (LAMP)

```bash
# 1. Cai LAMP
sudo apt update
sudo apt install -y apache2 mysql-server php php-mysqli libapache2-mod-php

# 2. Chep ma nguon vao web root
sudo cp -r owasp-shop /var/www/html/
sudo chown -R www-data:www-data /var/www/html/owasp-shop

# 3. Nap CSDL
sudo mysql < /var/www/html/owasp-shop/db/schema.sql

# 4. (Khuyen nghi) tro DocumentRoot / VirtualHost vao thu muc public/
#    de KHONG lo cac file config, db, includes ra ngoai web.
sudo nano /etc/apache2/sites-available/owasp-shop.conf
```

Vi du VirtualHost (chi public/ duoc phuc vu — giam A05 Security Misconfiguration):

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

## Trien khai tren Windows Server (IIS hoac XAMPP)

- **Cach 1 (nhanh):** dung XAMPP nhu muc "Cai dat nhanh".
- **Cach 2 (IIS):** cai PHP qua [Web Platform Installer], bat CGI/FastCGI,
  tro site vao thu muc `public/`, cai MySQL rieng va nap `db/schema.sql`.

## Cau truc thu muc

```
owasp-shop/
├─ config/config.php        # ket noi CSDL + cong tac SECURE
├─ db/schema.sql            # tao CSDL + du lieu mau
├─ includes/
│  ├─ functions.php         # auth, phan quyen, CSRF, escaping...
│  ├─ header.php / footer.php
├─ public/                  # DocumentRoot (phan lo ra web)
│  ├─ index.php  login.php  register.php  logout.php
│  ├─ post.php   create_post.php
│  ├─ shop.php   buy.php
│  ├─ profile.php  admin.php  search.php
│  └─ assets/css/style.css  assets/js/main.js
├─ README.md                # tai lieu nay
└─ SECURITY.md              # chi tiet 5 lo hong + cach khai thac + cach va
```

Xem [SECURITY.md](SECURITY.md) de biet chi tiet 5 lo hong OWASP, cach khai thac
va cach khac phuc tung cai.
