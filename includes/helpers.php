<?php
// includes/helpers.php — Utility Functions

require_once __DIR__ . '/../config/config.php';

/**
 * Sanitize input: trim, strip tags, htmlspecialchars
 */
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token and store in session
 */
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output CSRF hidden input field
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Validate CSRF token from POST
 */
function verify_csrf(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    // Check $_POST first (standard form)
    $token = $_POST['csrf_token'] ?? '';
    
    // Check JSON body if $_POST is empty
    if (!$token) {
        $json = json_decode(file_get_contents('php://input'), true);
        $token = $json['csrf'] ?? $json['csrf_token'] ?? '';
    }
    
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Format currency in INR
 */
function format_currency(int $amount): string {
    return '₹' . number_format($amount);
}

/**
 * Format currency compact (e.g. ₹7K, ₹12K)
 */
function format_currency_compact(int $amount): string {
    if ($amount >= 1000) {
        return '₹' . number_format($amount / 1000, 0) . 'K';
    }
    return '₹' . $amount;
}

/**
 * Generate a URL-friendly slug from a string
 */
function make_slug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Ensure slug is unique in pg_listings table
 */
function unique_slug(PDO $pdo, string $base_slug, int $exclude_id = 0): string {
    $slug = $base_slug;
    $counter = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM pg_listings WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $exclude_id]);
        if (!$stmt->fetch()) break;
        $slug = $base_slug . '-' . $counter++;
    }
    return $slug;
}

/**
 * Generate a 6-digit OTP
 */
function generate_otp(): string {
    return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Paginate results: returns array with offset, total_pages, current_page
 */
function paginate(int $total, int $per_page = 12, string $param = 'page'): array {
    $current = max(1, (int)($_GET[$param] ?? 1));
    $total_pages = max(1, ceil($total / $per_page));
    $current = min($current, $total_pages);
    $offset = ($current - 1) * $per_page;
    return compact('current', 'total_pages', 'offset', 'per_page', 'total');
}

/**
 * Generate pagination HTML
 */
function pagination_html(array $p, string $base_url): string {
    if ($p['total_pages'] <= 1) return '';
    $html = '<div class="pagination">';
    $sep = str_contains($base_url, '?') ? '&' : '?';

    if ($p['current'] > 1) {
        $html .= '<a href="' . $base_url . $sep . 'page=' . ($p['current'] - 1) . '" class="page-btn">‹ Prev</a>';
    }
    for ($i = max(1, $p['current'] - 2); $i <= min($p['total_pages'], $p['current'] + 2); $i++) {
        $active = $i === $p['current'] ? ' active' : '';
        $html .= '<a href="' . $base_url . $sep . 'page=' . $i . '" class="page-btn' . $active . '">' . $i . '</a>';
    }
    if ($p['current'] < $p['total_pages']) {
        $html .= '<a href="' . $base_url . $sep . 'page=' . ($p['current'] + 1) . '" class="page-btn">Next ›</a>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * Calculate distance from MUJ using Haversine formula (in km)
 */
function distance_from_muj(float $lat, float $lng): float {
    $earth_radius = 6371;
    $d_lat = deg2rad($lat - MUJ_LAT);
    $d_lng = deg2rad($lng - MUJ_LNG);
    $a = sin($d_lat/2)**2 + cos(deg2rad(MUJ_LAT)) * cos(deg2rad($lat)) * sin($d_lng/2)**2;
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return round($earth_radius * $c, 2);
}

/**
 * Format distance display
 */
function format_distance(float $km): string {
    if ($km < 1) return (int)($km * 1000) . 'm from MUJ';
    return number_format($km, 2) . ' km from MUJ';
}

/**
 * Create an in-app notification
 */
function create_notification(PDO $pdo, int $user_id, string $type, string $title, string $body, string $link = ''): void {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, body, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $type, $title, $body, $link]);
}

/**
 * Get unread notification count for a user
 */
function unread_notification_count(PDO $pdo, int $user_id): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Flash message helpers
 */
function flash_set(string $key, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function flash_has(string $key): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['flash'][$key]);
}

/**
 * Redirect helper
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Mask phone number for guests
 */
function mask_phone(string $phone): string {
    if (strlen($phone) <= 4) return $phone;
    return substr($phone, 0, -7) . 'XXXXXXX';
}

/**
 * Time ago format
 */
function time_ago(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('d M Y', $time);
}

/**
 * Badge HTML helper
 */
function status_badge(string $status): string {
    $map = [
        'pending'   => 'badge-warning',
        'confirmed' => 'badge-success',
        'rejected'  => 'badge-danger',
        'cancelled' => 'badge-secondary',
        'completed' => 'badge-info',
        'approved'  => 'badge-success',
        'draft'     => 'badge-secondary',
        'inactive'  => 'badge-secondary',
        'open'      => 'badge-danger',
        'in_review' => 'badge-warning',
        'resolved'  => 'badge-success',
        'success'   => 'badge-success',
        'failed'    => 'badge-danger',
        'refunded'  => 'badge-info',
    ];
    $class = $map[$status] ?? 'badge-secondary';
    return '<span class="badge ' . $class . '">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
}

/**
 * Gender badge
 */
function gender_badge(string $gender): string {
    $map = ['male' => 'badge-blue', 'female' => 'badge-pink', 'any' => 'badge-green'];
    $labels = ['male' => '♂ Boys', 'female' => '♀ Girls', 'any' => '⚥ Co-ed'];
    $class = $map[$gender] ?? 'badge-secondary';
    $label = $labels[$gender] ?? ucfirst($gender);
    return '<span class="badge ' . $class . '">' . $label . '</span>';
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 80): string {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '…';
}

/**
 * Get the cover image path for a PG or return placeholder
 */
function get_pg_cover(PDO $pdo, int $pg_id): string {
    $stmt = $pdo->prepare("SELECT file_path FROM pg_images WHERE pg_id = ? AND is_cover = 1 LIMIT 1");
    $stmt->execute([$pg_id]);
    $row = $stmt->fetch();
    if ($row) return BASE_URL . '/' . ltrim($row['file_path'], '/');
    // Try any image
    $stmt = $pdo->prepare("SELECT file_path FROM pg_images WHERE pg_id = ? ORDER BY sort_order LIMIT 1");
    $stmt->execute([$pg_id]);
    $row = $stmt->fetch();
    if ($row) return BASE_URL . '/' . ltrim($row['file_path'], '/');
    return BASE_URL . '/assets/images/pg-placeholder.jpg';
}

/**
 * Is the current page active (for navbar highlight)?
 */
function is_active_page(string $page): string {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $page ? 'active' : '';
}

// Auth helpers (is_logged_in, current_user_id, current_role, require_auth, redirect_if_logged_in)
// are defined in includes/auth_check.php to avoid redeclaration conflicts.

// removed handle_multiple_uploads to avoid redeclaration conflict with upload_handler.php


/**
 * Format large numbers for display
 */
function number_compact(int $n): string {
    if ($n >= 1000000) return round($n / 1000000, 1) . 'M';
    if ($n >= 1000)    return round($n / 1000, 0) . 'K';
    return (string)$n;
}

// get_db() is defined in includes/db.php as a static singleton.
// Call get_db() directly anywhere db.php has been included.

/**
 * Format a link: returns external URLs as-is, or prepends BASE_URL for internal paths.
 */
function format_link(?string $link): string {
    if (!$link || $link === '#') return '#';
    if (preg_match('/^(https?:\/\/|ftp:\/\/|\/\/)/i', $link)) {
        return $link;
    }
    return BASE_URL . (str_starts_with($link, '/') ? '' : '/') . $link;
}

