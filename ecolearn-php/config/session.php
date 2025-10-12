<?php
// Session Configuration - Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session security settings BEFORE starting the session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_path', '/lms-project/ecolearn-php/');
    
    // Now start the session
    session_start();
}

// Session timeout (24 hours)
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 24 * 60 * 60);
}

// Global database connection for session functions
global $pdo;
if (!isset($pdo)) {
    require_once __DIR__ . '/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
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
        
        // For registered users, fetch from database if available
        global $pdo;
        if ($pdo && !strpos($_SESSION['user_id'], 'guest_')) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    return $user;
                }
            } catch (PDOException $e) {
                // Fall back to session data if database query fails
            }
        }
        
        // Fallback to session data
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['user_name'] ?? '',
            'full_name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role']
        ];
    }
}

if (!function_exists('requireLogin')) {
    function requireLogin() {
        if (!isLoggedIn()) {
            if (strpos($_SERVER['SCRIPT_NAME'], 'api') !== false || 
                (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authentication required']);
                exit();
            } else {
                header('Location: /lms-project/ecolearn-php/login.php');
                exit();
            }
        }
    }
}

if (!function_exists('requireRole')) {
    function requireRole($role) {
        requireLogin();
        $user = getCurrentUser();
        if ($user['role'] !== $role && !($role === 'student' && $user['role'] === 'guest')) {
            if (strpos($_SERVER['SCRIPT_NAME'], 'api') !== false || 
                (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit();
            } else {
                header('Location: /lms-project/ecolearn-php/index.php');
                exit();
            }
        }
    }
}

if (!function_exists('checkSessionTimeout')) {
    function checkSessionTimeout() {
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
                session_destroy();
                header('Location: /lms-project/ecolearn-php/login.php?timeout=1');
                exit();
            }
        }
        $_SESSION['last_activity'] = time();
    }
}

// Check session timeout on every page load
if (isLoggedIn()) {
    checkSessionTimeout();
}
?>