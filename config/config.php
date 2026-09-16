<?php
/**
 * Cau hinh ung dung + ket noi CSDL.
 *
 * ============================================================
 *  CONG TAC AN TOAN / SECURE MODE
 * ------------------------------------------------------------
 *  false  => Che do LO HONG (vulnerable). Dung de demo tan cong,
 *            chup hinh minh chung 5 lo hong OWASP.
 *  true   => Che do DA VA (secure). Cac lo hong da duoc khac phuc.
 *
 *  Chi can doi 1 dong nay de so sanh "truoc / sau".
 * ============================================================
 */
define('SECURE', false);

// ----- Thong tin ket noi MySQL (sua cho phu hop moi truong cua ban) -----
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP/MariaDB mac dinh de trong.
                                // Neu dung MySQL 8 co dat mat khau root => dien vao day.
define('DB_NAME', 'owasp_shop');
define('DB_PORT', 3307);   // Mac dinh cua du an. XAMPP thuong dung 3306 -> sua cho khop
                           // cong MySQL/MariaDB dang chay tren may ban.

// ----- Cau hinh hien thi loi -----
if (SECURE) {
    // Che do an toan: KHONG lo loi ra ngoai (A02:2025 - Security Misconfiguration)
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    // Che do lo hong: bat het loi ra man hinh
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// ----- Ket noi mysqli -----
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if (!$conn) {
    die('Khong ket noi duoc CSDL: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// ----- Khoi tao session -----
if (session_status() === PHP_SESSION_NONE) {
    if (SECURE) {
        // Cookie session an toan hon (A07:2025)
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            // 'secure' => true,   // bat khi chay HTTPS
        ]);
    }
    session_start();
}
