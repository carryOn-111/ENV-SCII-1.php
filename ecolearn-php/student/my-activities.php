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

// Get student's activities
$activities = [];
$submissions = [];

if (strpos($user['id'], 'guest_') !== 0) {
    // For registered students, get their activities and submissions
    try {
        // Get all published activities
        $stmt = $pdo->prepare("
            SELECT a.*, u.full_name as teacher_name,
                   ss.id as submission_id, ss.total_score, ss.submitted_at
            FROM activities a
            JOIN users u ON a.teacher_id = u.id
            LEFT JOIN student_submissions ss ON a.id = ss.activity_id AND ss.student_id = ?
            WHERE a.status = 'published'
            ORDER BY a.due_date ASC, a.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $activities = [];
    }
} else {
    // For guest users, show all published activities
    $activities = $functions->getPublishedActivities();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Activities - EcoLearn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/guest.css">
    <link rel="stylesheet" href="../css/pages/lessons.css">
    <style>
        .activity-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .activity-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .activity-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .activity-meta {
            color: #666;
            font-size: 0.9em;
        }
        .activity-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-overdue {
            background: #f8d7da;
            color: #721c24;
        }
        .activity-score {
            font-size: 1.1em;
            font-weight: bold;
            color: #28a745;
        }
    </style>
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
                <div class="nav-item student-lessons" onclick="window.location.href='my-lessons.php'">
                    <i class="fas fa-book-reader"></i>
                    <span>My Lessons</span>
                </div>
                <div class="nav-item student-activities active">
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
                <h1>My Activities</h1>
                <p>Track your assignments and quizzes</p>
            </header>

            <div class="activities-list">
                <?php if (empty($activities)): ?>
                    <div class="empty-state">
                        <h3>No activities found</h3>
                        <p>No activities have been assigned yet. Check back later or <a href="catalogue.php">browse lessons</a> for related activities.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <?php
                        $is_submitted = !empty($activity['submission_id']);
                        $is_overdue = !$is_submitted && $activity['due_date'] && strtotime($activity['due_date']) < time();
                        $status = $is_submitted ? 'completed' : ($is_overdue ? 'overdue' : 'pending');
                        ?>
                        <div class="activity-card">
                            <div class="activity-header">
                                <div>
                                    <div class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></div>
                                    <div class="activity-meta">
                                        Type: <?php echo ucfirst($activity['type']); ?> | 
                                        Teacher: <?php echo htmlspecialchars($activity['teacher_name']); ?>
                                        <?php if ($activity['due_date']): ?>
                                            | Due: <?php echo date('M j, Y g:i A', strtotime($activity['due_date'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <span class="activity-status status-<?php echo $status; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="activity-content">
                                <p><?php echo htmlspecialchars($activity['description'] ?? 'No description available.'); ?></p>
                            </div>
                            
                            <?php if ($is_submitted): ?>
                                <div class="activity-result">
                                    <div class="activity-score">Score: <?php echo $activity['total_score']; ?>%</div>
                                    <div class="activity-meta">
                                        Submitted: <?php echo date('M j, Y g:i A', strtotime($activity['submitted_at'])); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="activity-actions">
                                    <a href="activity-viewer.php?id=<?php echo $activity['id']; ?>" class="btn btn-primary">
                                        Start Activity
                                    </a>
                                    <?php if ($is_overdue): ?>
                                        <span class="overdue-notice">This activity is overdue</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>