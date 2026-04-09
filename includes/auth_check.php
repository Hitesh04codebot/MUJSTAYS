<?php
// includes/auth_check.php — Role-Based Access Control Middleware
// Include at the TOP of any page that requires authentication

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require authentication & optional role.
 * Pages call: require_auth('student') | require_auth('owner') | require_auth('admin') | require_auth()
 */
function require_auth(string $required_role = '', string $redirect_to = ''): void {
    require_once __DIR__ . '/../config/config.php';

    if (empty($_SESSION['user_id'])) {
        $_SESSION['flash']['error'] = 'Please log in to access this page.';
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    if (!empty($_SESSION['is_active']) && $_SESSION['is_active'] == 0) {
        session_destroy();
        header('Location: ' . BASE_URL . '/login.php?error=suspended');
        exit;
    }

    if ($required_role && ($_SESSION['role'] ?? '') !== $required_role) {
        $_SESSION['flash']['error'] = 'You do not have permission to access this page.';
        $dash = match($_SESSION['role'] ?? '') {
            'student' => BASE_URL . '/user/dashboard.php',
            'owner'   => BASE_URL . '/owner/dashboard.php',
            'admin'   => BASE_URL . '/admin/dashboard.php',
            default   => BASE_URL . '/login.php',
        };
        header('Location: ' . $dash);
        exit;
    }

    // Admins bypass email verification requirement
    if (($_SESSION['role'] ?? '') === 'admin') return;

    // Require verified email
    if (empty($_SESSION['is_verified'])) {
        if (basename($_SERVER['PHP_SELF']) !== 'verify-email.php') {
            header('Location: ' . BASE_URL . '/verify-email.php');
            exit;
        }
    }
}

/**
 * Redirect already-logged-in users away from guest pages (login/signup).
 */
function redirect_if_logged_in(): void {
    require_once __DIR__ . '/../config/config.php';
    if (!empty($_SESSION['user_id'])) {
        $dash = match($_SESSION['role'] ?? '') {
            'student' => BASE_URL . '/user/dashboard.php',
            'owner'   => BASE_URL . '/owner/dashboard.php',
            'admin'   => BASE_URL . '/admin/dashboard.php',
            default   => BASE_URL . '/',
        };
        header('Location: ' . $dash);
        exit;
    }
}

/**
 * Check if current user is logged in (non-fatal).
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Get current user role.
 */
function current_role(): string {
    return $_SESSION['role'] ?? '';
}

/**
 * Get current user ID.
 */
function current_user_id(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}
