<?php
// Session Configuration - Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session security settings BEFORE starting the session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    
    // Now start the session
    session_start();
}

// Session timeout (24 hours)
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 24 * 60 * 60);
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }
}

if (!function_exists('isGuest')) {
    function isGuest() {
        return isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;
    }
}

if (!function_exists('requireLogin')) {
    function requireLogin() {
        if (!isLoggedIn()) {
            header('Location: /ecolearn-php/login.php');
            exit();
        }
    }
}

if (!function_exists('requireRole')) {
    function requireRole($role) {
        requireLogin();
        if ($_SESSION['user_role'] !== $role && !($role === 'student' && $_SESSION['user_role'] === 'guest')) {
            header('Location: /ecolearn-php/index.php');
            exit();
        }
    }
}

if (!function_exists('checkSessionTimeout')) {
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
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        if (!isLoggedIn()) {
            return null;
        }
        
        if (isGuest()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['user_id'],
                'full_name' => $_SESSION['user_name'],
                'email' => '',
                'role' => 'guest'
            ];
        }
        
        // For registered users, you might want to fetch from database
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['user_name'] ?? '',
            'full_name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role']
        ];
    }
}

// Check session timeout on every page load
if (isLoggedIn()) {
    checkSessionTimeout();
}
?>