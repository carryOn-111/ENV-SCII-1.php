<?php
// Session Configuration
session_start();

// Session security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Session timeout (24 hours)
define('SESSION_TIMEOUT', 24 * 60 * 60);

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /ecolearn-php/login.php');
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        header('Location: /ecolearn-php/index.php');
        exit();
    }
}

function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            session_destroy();
            header('Location: /ecolearn-php/login.php?timeout=1');
            exit();
        }
    }
    $_SESSION['last_activity'] = time();
}

// Check session timeout on every page load
if (isLoggedIn()) {
    checkSessionTimeout();
}
?>