<?php
header('Content-Type: application/json');
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($method) {
    case 'POST':
        handlePost($action);
        break;
    case 'GET':
        handleGet($action);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function handlePost($action) {
    requireLogin();
    
    switch ($action) {
        case 'create':
            createLessonAPI();
            break;
        case 'update':
            updateLessonAPI();
            break;
        case 'publish':
            publishLessonAPI();
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handleGet($action) {
    requireLogin();
    
    switch ($action) {
        case 'view':
            viewLessonAPI();
            break;
        case 'list':
            listLessonsAPI();
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function createLessonAPI() {
    requireRole('teacher');
    
    $user = getCurrentUser();
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $background_type = sanitize($_POST['background_type'] ?? 'simple');
    $background_image = sanitize($_POST['background_image'] ?? null);
    
    if (empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Title and content are required']);
        return;
    }
    
    $lesson_id = createLesson($user['id'], $title, $content, $background_type, $background_image);
    
    if ($lesson_id) {
        echo json_encode(['success' => true, 'lesson_id' => $lesson_id, 'message' => 'Lesson created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create lesson']);
    }
}

function updateLessonAPI() {
    requireRole('teacher');
    global $pdo;
    
    $user = getCurrentUser();
    $lesson_id = (int)($_POST['lesson_id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $status = sanitize($_POST['status'] ?? '');
    
    if (!$lesson_id) {
        echo json_encode(['success' => false, 'message' => 'Lesson ID is required']);
        return;
    }
    
    try {
        // Verify ownership
        $stmt = $pdo->prepare("SELECT teacher_id FROM lessons WHERE id = ?");
        $stmt->execute([$lesson_id]);
        $lesson = $stmt->fetch();
        
        if (!$lesson || $lesson['teacher_id'] != $user['id']) {
            echo json_encode(['success' => false, 'message' => 'Lesson not found or access denied']);
            return;
        }
        
        // Update lesson
        $stmt = $pdo->prepare("UPDATE lessons SET title = ?, content = ?, status = ? WHERE id = ?");
        $success = $stmt->execute([$title, $content, $status, $lesson_id]);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Lesson updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update lesson']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function publishLessonAPI() {
    requireRole('teacher');
    
    $lesson_id = (int)($_POST['lesson_id'] ?? 0);
    
    if (!$lesson_id) {
        echo json_encode(['success' => false, 'message' => 'Lesson ID is required']);
        return;
    }
    
    $success = updateLessonStatus($lesson_id, 'published');
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Lesson published successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to publish lesson']);
    }
}

function viewLessonAPI() {
    $lesson_id = (int)($_GET['id'] ?? 0);
    $user = getCurrentUser();
    
    if (!$lesson_id) {
        echo json_encode(['success' => false, 'message' => 'Lesson ID is required']);
        return;
    }
    
    $lesson = getLessonWithSlides($lesson_id);
    
    if (!$lesson) {
        echo json_encode(['success' => false, 'message' => 'Lesson not found']);
        return;
    }
    
    // Record progress for students (not for guests to avoid database errors)
    if ($user['role'] === 'student' && strpos($user['id'], 'guest_') !== 0) {
        recordProgress($user['id'], 'lesson_viewed', $lesson_id);
    }
    
    echo json_encode(['success' => true, 'lesson' => $lesson]);
}

function listLessonsAPI() {
    $user = getCurrentUser();
    
    if ($user['role'] === 'teacher') {
        $lessons = getLessonsByTeacher($user['id']);
    } else {
        $lessons = getPublishedLessons();
    }
    
    echo json_encode(['success' => true, 'lessons' => $lessons]);
}
?>