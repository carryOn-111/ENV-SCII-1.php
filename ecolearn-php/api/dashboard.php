<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user = getCurrentUser();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'student_stats':
        if ($user['role'] !== 'student') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        getStudentStats($user['id']);
        break;
        
    case 'teacher_stats':
        if ($user['role'] !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        getTeacherStats($user['id']);
        break;
        
    case 'student_deadlines':
        if ($user['role'] !== 'student') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        getStudentDeadlines($user['id']);
        break;
        
    case 'student_scores':
        if ($user['role'] !== 'student') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        getStudentScores($user['id']);
        break;
        
    case 'trending_lessons':
        getTrendingLessons();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getStudentStats($student_id) {
    global $pdo;
    
    try {
        // Get overall average score
        $stmt = $pdo->prepare("
            SELECT AVG(total_score) as avg_score, COUNT(*) as total_submissions
            FROM student_submissions
            WHERE student_id = ? AND total_score IS NOT NULL
        ");
        $stmt->execute([$student_id]);
        $score_data = $stmt->fetch();
        
        // Get pending assignments count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as pending_count 
            FROM activities a
            LEFT JOIN student_submissions ss ON a.id = ss.activity_id AND ss.student_id = ?
            WHERE a.status = 'published' 
            AND a.due_date > NOW() 
            AND ss.id IS NULL
        ");
        $stmt->execute([$student_id]);
        $pending_data = $stmt->fetch();
        
        // Get lessons progress
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT l.id) as total_lessons,
                COUNT(DISTINCT sp.lesson_id) as completed_lessons
            FROM lessons l
            LEFT JOIN student_progress sp ON l.id = sp.lesson_id AND sp.student_id = ? AND sp.status = 'completed'
            WHERE l.status = 'published'
        ");
        $stmt->execute([$student_id]);
        $lesson_data = $stmt->fetch();
        
        // Get overdue activities
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as overdue_count 
            FROM activities a
            LEFT JOIN student_submissions ss ON a.id = ss.activity_id AND ss.student_id = ?
            WHERE a.status = 'published' 
            AND a.due_date < NOW() 
            AND ss.id IS NULL
        ");
        $stmt->execute([$student_id]);
        $overdue_data = $stmt->fetch();
        
        $stats = [
            'overall_avg_score' => round($score_data['avg_score'] ?? 0),
            'pending_assignments' => $pending_data['pending_count'] ?? 0,
            'lessons_progress' => [
                'completed' => $lesson_data['completed_lessons'] ?? 0,
                'total' => $lesson_data['total_lessons'] ?? 0
            ],
            'overdue_activities' => $overdue_data['overdue_count'] ?? 0
        ];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getStudentDeadlines($student_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.title,
                a.type,
                a.due_date,
                l.title as lesson_title
            FROM activities a
            LEFT JOIN lessons l ON a.lesson_id = l.id
            LEFT JOIN student_submissions ss ON a.id = ss.activity_id AND ss.student_id = ?
            WHERE a.status = 'published' 
            AND a.due_date > NOW() 
            AND ss.id IS NULL
            ORDER BY a.due_date ASC
            LIMIT 5
        ");
        $stmt->execute([$student_id]);
        $deadlines = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'deadlines' => $deadlines]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getStudentScores($student_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT
                a.title,
                ss.total_score,
                ss.submitted_at
            FROM student_submissions ss
            JOIN activities a ON ss.activity_id = a.id
            WHERE ss.student_id = ? AND ss.total_score IS NOT NULL
            ORDER BY ss.submitted_at DESC
            LIMIT 10
        ");
        $stmt->execute([$student_id]);
        $scores = $stmt->fetchAll();
        
        // Get performance trend (last 6 scores)
        $stmt = $pdo->prepare("
            SELECT total_score
            FROM student_submissions
            WHERE student_id = ? AND total_score IS NOT NULL
            ORDER BY submitted_at DESC
            LIMIT 6
        ");
        $stmt->execute([$student_id]);
        $trend_scores = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            'success' => true, 
            'recent_scores' => $scores,
            'performance_trend' => array_reverse($trend_scores)
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getTrendingLessons() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                l.id,
                l.title,
                l.grade_level,
                COUNT(DISTINCT a.id) as activity_count,
                COUNT(DISTINCT sp.student_id) as student_count,
                AVG(ss.total_score) as avg_score,
                l.created_at
            FROM lessons l
            LEFT JOIN activities a ON l.id = a.lesson_id
            LEFT JOIN student_progress sp ON l.id = sp.lesson_id
            LEFT JOIN student_submissions ss ON a.id = ss.activity_id
            WHERE l.status = 'published'
            GROUP BY l.id
            ORDER BY student_count DESC, avg_score DESC
            LIMIT 6
        ");
        $stmt->execute();
        $lessons = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'lessons' => $lessons]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getTeacherStats($teacher_id) {
    global $pdo;
    
    try {
        // Get lesson stats
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_lessons,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_lessons,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_lessons
            FROM lessons 
            WHERE teacher_id = ?
        ");
        $stmt->execute([$teacher_id]);
        $lesson_stats = $stmt->fetch();
        
        // Get activity stats
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_activities,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_activities,
                AVG(CASE WHEN ss.total_score IS NOT NULL THEN ss.total_score END) as avg_score
            FROM activities a
            LEFT JOIN student_submissions ss ON a.id = ss.activity_id
            WHERE a.teacher_id = ?
        ");
        $stmt->execute([$teacher_id]);
        $activity_stats = $stmt->fetch();
        
        // Get student engagement
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT ss.student_id) as active_students
            FROM activities a
            JOIN student_submissions ss ON a.id = ss.activity_id
            WHERE a.teacher_id = ? AND ss.submitted_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute([$teacher_id]);
        $engagement_stats = $stmt->fetch();
        
        $stats = [
            'lessons' => $lesson_stats,
            'activities' => $activity_stats,
            'engagement' => $engagement_stats
        ];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>