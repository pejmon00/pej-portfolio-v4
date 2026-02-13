<?php
// Admin Configuration
// -------------------
// Change this password hash by running:
//   php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_BCRYPT);"
// Then paste the result below.
define('ADMIN_PASSWORD_HASH', '$2y$10$ifqiBlqUJ1c9Sx/kG9EmqOxN5eCtBnklahqF1KEjOuLSwpeE4BMtS');

// Base path (no trailing slash)
define('BASE_PATH', realpath(__DIR__ . '/..'));

// Data directory
define('DATA_PATH', __DIR__ . '/data');

// Templates directory
define('TEMPLATES_PATH', __DIR__ . '/templates');

// Upload directory (relative to BASE_PATH)
define('UPLOAD_DIR', '/assets/images/uploads');

// Max upload size in bytes (5 MB)
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

// Allowed image extensions
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

// Allowed MIME types
define('ALLOWED_MIMES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'image/svg+xml',
]);

// --- Session setup ---
function admin_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/admin/',
            'httponly'  => true,
            'secure'   => isset($_SERVER['HTTPS']),
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

// --- CSRF helpers ---
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify($token) {
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
