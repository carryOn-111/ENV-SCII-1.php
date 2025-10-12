<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a student
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$user = getCurrentUser();
if ($user['role'] !== 'student' && $user['role'] !== 'guest') {
    header('Location: ../index.php');
    exit();
}

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();
$functions = new EcoLearnFunctions();

// Get student's lessons (lessons they've viewed or are enrolled in)
$lessons = [];
if (strpos($user['id'], 'guest_') !== 0) {
    // For registered students, get their lesson progress
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT l.*, u.full_name as teacher_name,
                   lv.viewed_at,
                   sp.completion_percentage
            FROM lessons l
            JOIN users u ON l.teacher_id = u.id
            LEFT JOIN lesson_views lv ON l.id = lv.lesson_id AND lv.student_id = ?
            LEFT JOIN student_progress sp ON l.id = sp.lesson_id AND sp.student_id = ?
            WHERE l.status = 'published' AND (lv.lesson_id IS NOT NULL OR sp.lesson_id IS NOT NULL)
            ORDER BY lv.viewed_at DESC, l.created_at DESC
        ");
        $stmt->execute([$user['id'], $user['id']]);
        $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $lessons = [];
    }
} else {
    // For guest users, show all published lessons
    $lessons = $functions->getPublishedLessons();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Lessons - EcoLearn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/guest.css">
    <link rel="stylesheet" href="../css/pages/lessons.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Include sidebar -->
        <nav class="sidebar">
            <div class="logo">
                <i class="fas fa-leaf fa-2x"></i>
                <h2>EcoLearn</h2>
            </div>
            <div class="nav-section">
                <div class="nav-item dashboard" onclick="window.location.href='index.php'">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="nav-item library" onclick="window.location.href='catalogue.php'">
                    <i class="fas fa-layer-group"></i>
                    <span>Library</span>
                </div>
                <div class="nav-item student-lessons active">
                    <i class="fas fa-book-reader"></i>
                    <span>My Lessons</span>
                </div>
                <div class="nav-item student-activities" onclick="window.location.href='my-activities.php'">
                    <i class="fas fa-tasks"></i>
                    <span>My Activities</span>
                </div>
                <div class="nav-item student-progress <?php echo isGuest() ? 'guest-disabled' : ''; ?>" onclick="window.location.href='index.php'">
                    <i class="fas fa-chart-bar"></i>
                    <span>My Progress</span>
                </div>
            </div>
            <div class="footer-nav-section">
                <div class="nav-item profile <?php echo isGuest() ? 'guest-disabled' : ''; ?>" onclick="window.location.href='profile.php'">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </div>
                <div class="nav-item settings" onclick="window.location.href='settings.php'">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </div>
                <div class="nav-item logout" onclick="window.location.href='../logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span><?php echo isGuest() ? 'Exit' : 'Logout'; ?></span>
                </div>
            </div>
        </nav>

        <main class="main-content">
            <header class="content-header">
                <h1>My Lessons</h1>
                <p>Track your learning progress</p>
            </header>

            <div class="lessons-grid">
                <?php if (empty($lessons)): ?>
                    <div class="empty-state">
                        <h3>No lessons found</h3>
                        <p>You haven't started any lessons yet. <a href="catalogue.php">Browse the library</a> to get started.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($lessons as $lesson): ?>
                        <div class="lesson-card">
                            <div class="lesson-header">
                                <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                <span class="lesson-teacher">by <?php echo htmlspecialchars($lesson['teacher_name']); ?></span>
                            </div>
                            
                            <div class="lesson-content">
                                <p><?php echo htmlspecialchars(substr($lesson['content'], 0, 150)) . '...'; ?></p>
                            </div>
                            
                            <div class="lesson-progress">
                                <?php 
                                $progress = $lesson['completion_percentage'] ?? 0;
                                if (isset($lesson['viewed_at'])) {
                                    $progress = max($progress, 25); // At least 25% if viewed
                                }
                                ?>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                </div>
                                <span class="progress-text"><?php echo $progress; ?>% complete</span>
                            </div>
                            
                            <div class="lesson-actions">
                                <a href="lesson-viewer.php?id=<?php echo $lesson['id']; ?>" class="btn btn-primary">
                                    <?php echo isset($lesson['viewed_at']) ? 'Continue' : 'Start'; ?> Lesson
                                </a>
                                <?php if (isset($lesson['viewed_at'])): ?>
                                    <span class="last-viewed">
                                        Last viewed: <?php echo date('M j, Y', strtotime($lesson['viewed_at'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>