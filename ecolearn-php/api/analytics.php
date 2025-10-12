<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

$user = getCurrentUser();
$action = $_GET['action'] ?? '';

// Only teachers can access analytics
if ($user['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

switch ($action) {
    case 'overview':
        getAnalyticsOverview($user['id']);
        break;
    case 'student_performance':
        getStudentPerformance($user['id']);
        break;
    case 'lesson_engagement':
        getLessonEngagement($user['id']);
        break;
    case 'activity_stats':
        getActivityStats($user['id']);
        break;
    default:
        getAnalyticsOverview($user['id']);
}

function getAnalyticsOverview($teacher_id) {
    global $pdo;
    
    try {
        // Get total lessons
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM lessons WHERE teacher_id = ?");
        $stmt->execute([$teacher_id]);
        $total_lessons = $stmt->fetchColumn();
        
        // Get total activities
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM activities WHERE teacher_id = ?");
        $stmt->execute([$teacher_id]);
        $total_activities = $stmt->fetchColumn();
        
        // Get total students engaged
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT ss.student_id) as total 
            FROM student_submissions ss 
            JOIN activities a ON ss.activity_id = a.id 
            WHERE a.teacher_id = ?
        ");
        $stmt->execute([$teacher_id]);
        $total_students = $stmt->fetchColumn();
        
        // Get average score
        $stmt = $pdo->prepare("
            SELECT AVG(ss.total_score) as avg_score
            FROM student_submissions ss
            JOIN activities a ON ss.activity_id = a.id
            WHERE a.teacher_id = ? AND ss.total_score IS NOT NULL
        ");
        $stmt->execute([$teacher_id]);
        $avg_score = $stmt->fetchColumn();
        
        $overview = [
            'total_lessons' => $total_lessons ?: 0,
            'total_activities' => $total_activities ?: 0,
            'total_students' => $total_students ?: 0,
            'average_score' => round($avg_score ?: 0, 1)
        ];
        
        echo json_encode(['success' => true, 'overview' => $overview]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getStudentPerformance($teacher_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT
                u.full_name as student_name,
                COUNT(ss.id) as submissions_count,
                AVG(ss.total_score) as avg_score,
                MAX(ss.submitted_at) as last_activity
            FROM users u
            JOIN student_submissions ss ON u.id = ss.student_id
            JOIN activities a ON ss.activity_id = a.id
            WHERE a.teacher_id = ?
            GROUP BY u.id, u.full_name
            ORDER BY avg_score DESC
            LIMIT 10
        ");
        $stmt->execute([$teacher_id]);
        $performance = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'performance' => $performance]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getLessonEngagement($teacher_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                l.title,
                l.created_at,
                COUNT(DISTINCT lv.student_id) as views,
                COUNT(DISTINCT a.id) as activities_count
            FROM lessons l
            LEFT JOIN lesson_views lv ON l.id = lv.lesson_id
            LEFT JOIN activities a ON l.id = a.lesson_id
            WHERE l.teacher_id = ?
            GROUP BY l.id, l.title, l.created_at
            ORDER BY views DESC
            LIMIT 10
        ");
        $stmt->execute([$teacher_id]);
        $engagement = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'engagement' => $engagement]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getActivityStats($teacher_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT
                a.title,
                a.type,
                COUNT(ss.id) as submissions,
                AVG(ss.total_score) as avg_score,
                a.due_date
            FROM activities a
            LEFT JOIN student_submissions ss ON a.id = ss.activity_id
            WHERE a.teacher_id = ?
            GROUP BY a.id, a.title, a.type, a.due_date
            ORDER BY submissions DESC
            LIMIT 10
        ");
        $stmt->execute([$teacher_id]);
        $stats = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>