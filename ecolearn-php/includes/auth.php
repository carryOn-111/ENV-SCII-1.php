<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Initialize PDO connection for auth functions
if (!isset($pdo)) {
    $database = new Database();
    $pdo = $database->getConnection();
}

// Remove duplicate function definitions - they're now in session.php

// Lesson functions
if (!function_exists('createLesson')) {
    function createLesson($teacher_id, $title, $content, $background_type = 'simple', $background_image = null) {
        global $pdo;

        $qr_code = generateQRCode("lesson_" . uniqid());
        $access_code = strtoupper(substr(md5(uniqid()), 0, 6));

        $stmt = $pdo->prepare("INSERT INTO lessons (title, content, teacher_id, background_type, background_image, qr_code, access_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $content, $teacher_id, $background_type, $background_image, $qr_code, $access_code]);
        return $pdo->lastInsertId();
    }
}

if (!function_exists('getLessonsByTeacher')) {
    function getLessonsByTeacher($teacher_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM lessons WHERE teacher_id = ? ORDER BY created_at DESC");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('getPublishedLessons')) {
    function getPublishedLessons() {
        global $pdo;
        $stmt = $pdo->prepare("SELECT l.*, u.full_name as teacher_name FROM lessons l JOIN users u ON l.teacher_id = u.id WHERE l.status = 'published' ORDER BY l.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('getLessonWithSlides')) {
    function getLessonWithSlides($lesson_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT l.*, u.full_name as teacher_name FROM lessons l JOIN users u ON l.teacher_id = u.id WHERE l.id = ?");
        $stmt->execute([$lesson_id]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lesson) {
            $stmt = $pdo->prepare("SELECT * FROM lesson_slides WHERE lesson_id = ? ORDER BY slide_number");
            $stmt->execute([$lesson_id]);
            $lesson['slides'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $lesson;
    }
}

if (!function_exists('updateLessonStatus')) {
    function updateLessonStatus($lesson_id, $status) {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE lessons SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $lesson_id]);
    }
}

if (!function_exists('recordProgress')) {
    function recordProgress($student_id, $action, $resource_id) {
        global $pdo;

        if ($action === 'lesson_viewed') {
            $stmt = $pdo->prepare("INSERT INTO lesson_views (student_id, lesson_id, viewed_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE viewed_at = NOW()");
            return $stmt->execute([$student_id, $resource_id]);
        }

        return true;
    }
}

if (!function_exists('generateQRCode')) {
    function generateQRCode($text) {
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($text);
    }
}

if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }
}

class Auth {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function register($username, $email, $password, $user_type, $name) {
        // Check if email already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return 'Email already exists.';
        }

        // Check if username already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return 'Username already exists.';
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, role, full_name) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed_password, $user_type, $name])) {
            return 'success';
        } else {
            return 'Registration failed. Please try again.';
        }
    }

    public function login($email, $password) {
        // Find user by email
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return 'Invalid email or password.';
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return 'Invalid email or password.';
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];

        // Redirect based on role
        $base_url = '/lms-project/ecolearn-php/';
        if ($user['role'] === 'teacher') {
            header('Location: ' . $base_url . 'teacher/');
        } else {
            header('Location: ' . $base_url . 'student/');
        }
        exit();

        return true; // Though it exits, for consistency
    }
}
?>