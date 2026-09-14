<?php
/**
 * Ham tien ich dung chung: xac thuc, phan quyen, CSRF, escaping...
 * Hanh vi thay doi theo hang so SECURE trong config/config.php
 */

require_once __DIR__ . '/../config/config.php';

/* --------------------------------------------------------------------
 *  Escaping output
 * ------------------------------------------------------------------ */

/**
 * O che do SECURE ham nay escape HTML de chong XSS (A05:2025).
 * O che do vulnerable ham TRA NGUYEN van ban => dinh XSS.
 */
function out($str) {
    if (SECURE) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
    return $str;   // LO HONG: khong escape
}

/* --------------------------------------------------------------------
 *  Session / dang nhap
 * ------------------------------------------------------------------ */
function current_user($conn) {
    if (empty($_SESSION['user_id'])) return null;
    $id = (int)$_SESSION['user_id'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
    return $res ? mysqli_fetch_assoc($res) : null;
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Kiem tra quyen admin.
 *   SECURE   : bat buoc phai la admin => neu khong thi chan (A01:2025).
 *   vulnerable: ham van ton tai nhung cac trang KHONG goi no
 *               => bat ky ai cung vao duoc (Broken Access Control).
 */
function require_admin($conn) {
    $u = current_user($conn);
    if (!$u || $u['role'] !== 'admin') {
        http_response_code(403);
        die('403 - Ban khong co quyen truy cap trang nay.');
    }
}

/* --------------------------------------------------------------------
 *  CSRF token  (chi ap dung o che do SECURE - lien quan A01:2025)
 * ------------------------------------------------------------------ */
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    if (!SECURE) return '';   // vulnerable: khong co token
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check() {
    if (!SECURE) return true; // vulnerable: khong kiem tra
    $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
    if (!$ok) {
        http_response_code(419);
        die('419 - CSRF token khong hop le.');
    }
    return true;
}

/* --------------------------------------------------------------------
 *  Mat khau
 * ------------------------------------------------------------------ */

/** Bam mat khau khi dang ky. */
function hash_password($plain) {
    // Che do SECURE dung bcrypt; vulnerable dung md5 (A04:2025).
    return SECURE ? password_hash($plain, PASSWORD_BCRYPT) : md5($plain);
}

/** Kiem tra mat khau dang nhap. */
function verify_password($conn, $user, $plain) {
    if (SECURE) {
        return $user['password_hash']
            && password_verify($plain, $user['password_hash']);
    }
    // vulnerable: so sanh md5
    return $user['password_md5'] === md5($plain);
}

/* --------------------------------------------------------------------
 *  Chong brute-force (A07:2025) - chi bat o che do SECURE
 * ------------------------------------------------------------------ */
function too_many_attempts($conn, $username) {
    if (!SECURE) return false;   // vulnerable: khong gioi han
    $u  = mysqli_real_escape_string($conn, $username);
    $ip = mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR'] ?? '');
    $sql = "SELECT COUNT(*) c FROM login_attempts
            WHERE username='$u' AND ip='$ip' AND success=0
              AND created_at > (NOW() - INTERVAL 15 MINUTE)";
    $row = mysqli_fetch_assoc(mysqli_query($conn, $sql));
    return ((int)$row['c']) >= 5;
}

function log_attempt($conn, $username, $success) {
    $u  = mysqli_real_escape_string($conn, $username);
    $ip = mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR'] ?? '');
    $s  = $success ? 1 : 0;
    mysqli_query($conn, "INSERT INTO login_attempts (username, ip, success)
                         VALUES ('$u','$ip',$s)");
}

function flash($msg) { $_SESSION['flash'][] = $msg; }
function get_flashes() {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* --------------------------------------------------------------------
 *  Trinh bay san pham cay canh (frontend): icon minh hoa + mau + nhan loai
 * ------------------------------------------------------------------ */

/** Boc noi dung SVG. */
function _svg($inner) {
    return '<svg viewBox="0 0 64 64" fill="currentColor" aria-hidden="true">' . $inner . '</svg>';
}

/** Suy ra loai cay tu ten -> ['svg' => ..., 'color' => ..., 'tag' => ...]. */
function plant_visual($name) {
    $n = mb_strtolower($name ?? '', 'UTF-8');
    $has = function ($kws) use ($n) {
        foreach ($kws as $k) if (mb_strpos($n, $k) !== false) return true;
        return false;
    };
    $pot = '<path d="M19 43h26l-2.6 15.2A4 4 0 0 1 38.4 61H25.6a4 4 0 0 1-3.9-3.4L19 43z" opacity=".5"/>';

    if ($has(['sen đá','sen da','xương rồng','xuong rong','cactus','succulent'])) {
        $s = '<rect x="28" y="12" width="8" height="34" rx="4"/>'
           . '<path d="M28 27h-3a4 4 0 0 0-4 4v3a3 3 0 0 0 6 0" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round"/>'
           . '<path d="M36 23h3a4 4 0 0 1 4 4v3a3 3 0 0 1-6 0" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round"/>';
        return ['svg' => _svg($pot.$s), 'color' => '#c1683a', 'tag' => 'Sen đá / Xương rồng'];
    }
    if ($has(['lan','hoa','hồng','hong','cúc','cuc','flower'])) {
        $s = '<path d="M31 30h2v16h-2z"/>'
           . '<path d="M22 22c0-7 5-13 10-13s10 6 10 13-5 12-10 12-10-5-10-12z"/>'
           . '<circle cx="32" cy="22" r="4" opacity=".45"/>'
           . '<path d="M33 45c6-1 10-5 12-11-7 0-11 4-12 11z" opacity=".8"/>';
        return ['svg' => _svg($pot.$s), 'color' => '#d65c8a', 'tag' => 'Cây hoa'];
    }
    if ($has(['bonsai','tùng','tung','lộc vừng','loc vung','sanh','đa','da '])) {
        $s = '<path d="M32 44V27" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>'
           . '<ellipse cx="24" cy="23" rx="10" ry="7"/><ellipse cx="41" cy="19" rx="11" ry="7"/><ellipse cx="33" cy="13" rx="9" ry="6"/>';
        return ['svg' => _svg($pot.$s), 'color' => '#1f7a4d', 'tag' => 'Bonsai'];
    }
    if ($has(['trầu bà','trau ba','trầu','leo','lá','la ','vạn niên','van nien','lưỡi hổ','luoi ho'])) {
        $s = '<path d="M14 48C14 28 30 15 49 15 49 35 33 48 14 48z"/>'
           . '<path d="M32 46c8-6 13-14 15-24" fill="none" stroke="currentColor" stroke-width="3" opacity=".35"/>';
        return ['svg' => _svg($pot.$s), 'color' => '#2e9e57', 'tag' => 'Cây lá / Trong nhà'];
    }
    // Mac dinh: cay chau la
    $s = '<path d="M32 44C31 32 25 26 15 25c1 11 7 17 17 19z" opacity=".8"/>'
       . '<path d="M32 44C33 30 40 24 50 24c-1 12-8 18-18 20z"/>'
       . '<path d="M32 44V22" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>';
    return ['svg' => _svg($pot.$s), 'color' => '#47a06a', 'tag' => 'Cây cảnh'];
}

/** Anh that cua vat pham theo ten (neu co file), nguoc lai tra null de dung icon SVG. */
function plant_image($name) {
    $n = mb_strtolower($name ?? '', 'UTF-8');
    $map = [
        'sen đá' => 'sen-da', 'sen da' => 'sen-da',
        'xương rồng' => 'xuong-rong', 'xuong rong' => 'xuong-rong',
        'hồ điệp' => 'lan-ho-diep', 'lan' => 'lan-ho-diep',
        'bonsai' => 'bonsai', 'tùng' => 'bonsai',
        'kim tiền' => 'kim-tien', 'kim tien' => 'kim-tien',
        'kim ngân' => 'kim-ngan', 'kim ngan' => 'kim-ngan',
        'trầu bà' => 'trau-ba', 'trau ba' => 'trau-ba',
        'lưỡi hổ' => 'luoi-ho', 'luoi ho' => 'luoi-ho',
    ];
    foreach ($map as $kw => $slug) {
        if (mb_strpos($n, $kw) !== false) {
            $rel = 'assets/img/plants/' . $slug . '.jpg';
            if (is_file(__DIR__ . '/../public/' . $rel)) return $rel;
        }
    }
    return null;
}

/** Anh minh hoa cho bai viet cam nang theo tu khoa tieu de. */
function blog_image($title) {
    $t = mb_strtolower($title ?? '', 'UTF-8');
    if (mb_strpos($t, 'sen đá') !== false || mb_strpos($t, 'sen da') !== false) $slug = 'sen-da';
    elseif (mb_strpos($t, 'lọc không khí') !== false || mb_strpos($t, 'phòng ngủ') !== false) $slug = 'luoi-ho';
    elseif (mb_strpos($t, 'để bàn') !== false || mb_strpos($t, 'phong thủy') !== false) $slug = 'kim-tien';
    else $slug = 'trau-ba';
    return 'assets/img/plants/' . $slug . '.jpg';
}
