<?php
require_once '../config/session.php';
require_once '../includes/functions.php';

// Allow both students and guests
if (!isLoggedIn() || ($_SESSION['user_role'] !== 'student' && $_SESSION['user_role'] !== 'guest')) {
    header('Location: ../login.php');
    exit();
}

$user = getCurrentUser();
$progress = getStudentProgress($user['id']);
$available_lessons = getPublishedLessons();
$available_activities = getPublishedActivities();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLearn Platform - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/guest.css">
</head>
<body>
    <?php if (isGuest()): ?>
    <div class="guest-banner">
        <i class="fas fa-info-circle"></i>
        You're browsing as a guest. <a href="../register.php" style="color: #333; text-decoration: underline;">Create an account</a> to save your progress and unlock all features.
    </div>
    <?php endif; ?>

    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="logo">
            <i class="fas fa-leaf fa-2x"></i>
            <h2>EcoLearn</h2>
        </div>
        
        <div class="nav-section">
            <div class="nav-item dashboard active" onclick="loadStudentContent('dashboard')">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            
            <div class="nav-item library" onclick="loadStudentContent('catalogue')">
                <i class="fas fa-layer-group"></i>
                <span>Library</span>
            </div>
            
            <div class="nav-item student-lessons" onclick="loadStudentContent('lessons')">
                <i class="fas fa-book-reader"></i>
                <span>My Lessons</span>
            </div>
            
            <div class="nav-item student-activities" onclick="loadStudentContent('activities')">
                <i class="fas fa-tasks"></i>
                <span>My Activities</span>
            </div>
            
            <div class="nav-item student-progress <?php echo isGuest() ? 'guest-disabled' : ''; ?>" onclick="loadStudentContent('progress')">
                <i class="fas fa-chart-bar"></i>
                <span>My Progress</span>
            </div>
        </div>
        
        <div class="footer-nav-section">
            <div class="nav-item profile <?php echo isGuest() ? 'guest-disabled' : ''; ?>" onclick="loadStudentContent('profile')">
                <i class="fas fa-user-circle"></i>
                <span>Profile</span>
            </div>
            <div class="nav-item settings" onclick="loadStudentContent('settings')">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </div>
            <div class="nav-item logout" onclick="window.location.href='../logout.php'">
                <i class="fas fa-sign-out-alt"></i>
                <span><?php echo isGuest() ? 'Exit' : 'Logout'; ?></span>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-content">
        <header class="header">
            <i class="fas fa-graduation-cap"></i>
            <h1>Student Learning Space</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($user['full_name']); ?>
                <?php if (isGuest()): ?>
                    <span class="guest-indicator">GUEST</span>
                <?php endif; ?>
            </div>
        </header>
        
        <div id="content">
            <?php if (isGuest()): ?>
            <div class="guest-limitations">
                <h5><i class="fas fa-exclamation-triangle"></i> Guest Mode Limitations</h5>
                <ul>
                    <li>Progress is not saved permanently</li>
                    <li>Limited access to advanced features</li>
                    <li>No personalized recommendations</li>
                    <li>Cannot submit assignments for grading</li>
                </ul>
                <div class="guest-upgrade-prompt">
                    <h6>Ready for the full experience?</h6>
                    <p>Create a free account to unlock all features and save your progress!</p>
                    <a href="../register.php" class="btn btn-light btn-sm">Create Account</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Student Dashboard Content -->
            <div class="dashboard-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $progress['lessons_viewed']; ?></h3>
                        <p>Lessons Viewed</p>
                        <small><?php echo count($available_lessons); ?> Available</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $progress['activities_completed']; ?></h3>
                        <p>Activities Completed</p>
                        <small><?php echo count($available_activities); ?> Available</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($progress['average_score'], 1); ?>%</h3>
                        <p>Average Score</p>
                        <small><?php echo isGuest() ? 'Guest Mode' : 'Keep it up!'; ?></small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-content">
                        <h3>-</h3>
                        <p>Achievements</p>
                        <small>Coming Soon</small>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <button class="action-card" onclick="loadStudentContent('catalogue')">
                        <i class="fas fa-layer-group"></i>
                        <h3>Browse Library</h3>
                        <p>Explore available lessons and activities</p>
                    </button>
                    
                    <button class="action-card" onclick="showQRScanner()">
                        <i class="fas fa-qrcode"></i>
                        <h3>Scan QR Code</h3>
                        <p>Access content shared by your teacher</p>
                    </button>
                    
                    <button class="action-card <?php echo isGuest() ? 'guest-disabled' : ''; ?>" onclick="loadStudentContent('progress')">
                        <i class="fas fa-chart-line"></i>
                        <h3>View Progress</h3>
                        <p>Track your learning journey</p>
                    </button>
                </div>
            </div>
            
            <div class="recent-content">
                <div class="recent-section">
                    <h3>Available Lessons</h3>
                    <div class="content-list">
                        <?php if (empty($available_lessons)): ?>
                            <p class="no-content">No lessons available yet. Check back later!</p>
                        <?php else: ?>
                            <?php foreach (array_slice($available_lessons, 0, 5) as $lesson): ?>
                                <div class="content-item">
                                    <div class="content-info">
                                        <h4><?php echo htmlspecialchars($lesson['title']); ?></h4>
                                        <p>By: <?php echo htmlspecialchars($lesson['teacher_name']); ?></p>
                                        <p>Published: <?php echo formatDate($lesson['created_at']); ?></p>
                                        <?php if (isset($lesson['access_code'])): ?>
                                            <small class="text-muted">Access Code: <strong><?php echo $lesson['access_code']; ?></strong></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="content-actions">
                                        <button class="action-small-btn view-btn" onclick="viewLesson(<?php echo $lesson['id']; ?>)">
                                            <i class="fas fa-play"></i> Start
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="recent-section">
                    <h3>Available Activities</h3>
                    <div class="content-list">
                        <?php if (empty($available_activities)): ?>
                            <p class="no-content">No activities available yet. Check back later!</p>
                        <?php else: ?>
                            <?php foreach (array_slice($available_activities, 0, 5) as $activity): ?>
                                <div class="content-item">
                                    <div class="content-info">
                                        <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                                        <p>Type: <?php echo $activity['type']; ?> | By: <?php echo htmlspecialchars($activity['teacher_name']); ?></p>
                                        <?php if ($activity['due_date']): ?>
                                            <p>Due: <?php echo formatDate($activity['due_date']); ?></p>
                                        <?php endif; ?>
                                        <?php if (isset($activity['access_code'])): ?>
                                            <small class="text-muted">Access Code: <strong><?php echo $activity['access_code']; ?></strong></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="content-actions">
                                        <button class="action-small-btn edit-btn" onclick="startActivity(<?php echo $activity['id']; ?>)">
                                            <i class="fas fa-play-circle"></i> Start
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Scanner Modal -->
    <div id="qrScannerModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-header-content">
                <h3 class="score-detail-low">Scan or Enter Resource Code</h3>
                <button onclick="hideModal('qrScannerModal')" class="modal-close-btn">&times;</button>
            </div>
            
            <p class="subtitle modal-text-center">Use your camera or enter the code provided by your teacher to access content.</p>

            <div class="camera-placeholder">
                <i class="fas fa-camera fa-3x placeholder-icon"></i>
                <p class="subtitle">QR Scanner functionality coming soon!</p>
            </div>

            <form onsubmit="event.preventDefault(); accessResource();">
                <div class="filter-group">
                    <label for="resourceCode">Or Enter 6-Digit Code:</label>
                    <input type="text" id="resourceCode" maxlength="6" required class="resource-input" placeholder="ABC123">
                </div>
                <button type="submit" class="apply-filter-btn primary">
                    <i class="fas fa-play-circle"></i> Access Resource
                </button>
            </form>
        </div>
    </div>

    <script>
        function loadStudentContent(section) {
            // Remove active class from all nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item
            const navItem = document.querySelector(`.nav-item.${section}`);
            if (navItem && !navItem.classList.contains('guest-disabled')) {
                navItem.classList.add('active');
            }
            
            // Load content based on section
            switch(section) {
                case 'catalogue':
                    window.location.href = 'catalogue.php';
                    break;
                case 'lessons':
                    window.location.href = 'lessons.php';
                    break;
                case 'activities':
                    window.location.href = 'activities.php';
                    break;
                case 'progress':
                    <?php if (isGuest()): ?>
                    alert('Progress tracking requires an account. Please register to access this feature.');
                    <?php else: ?>
                    alert('Progress tracking coming soon!');
                    <?php endif; ?>
                    break;
                case 'profile':
                    <?php if (isGuest()): ?>
                    alert('Profile management requires an account. Please register to access this feature.');
                    <?php else: ?>
                    alert('Profile management coming soon!');
                    <?php endif; ?>
                    break;
                case 'dashboard':
                    window.location.reload();
                    break;
                default:
                    console.log('Loading:', section);
            }
        }
        
        function showQRScanner() {
            document.getElementById('qrScannerModal').style.display = 'flex';
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function accessResource() {
            const code = document.getElementById('resourceCode').value.toUpperCase();
            
            // Try to access lesson or activity by code
            fetch('../api/access-resource.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ access_code: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.type === 'lesson') {
                        window.location.href = 'lesson-viewer.php?id=' + data.id;
                    } else if (data.type === 'activity') {
                        window.location.href = 'activity-viewer.php?id=' + data.id;
                    }
                } else {
                    alert('Invalid access code. Please check the code and try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error accessing resource. Please try again.');
            });
            
            hideModal('qrScannerModal');
        }
        
        function viewLesson(lessonId) {
            // Record progress and redirect to lesson viewer
            fetch('../api/lessons.php?action=view&id=' + lessonId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'lesson-viewer.php?id=' + lessonId;
                    } else {
                        alert('Error accessing lesson');
                    }
                });
        }
        
        function startActivity(activityId) {
            // Redirect to activity page
            window.location.href = 'activity-viewer.php?id=' + activityId;
        }
    </script>
    
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            border-radius: 50%;
            font-size: 24px;
        }
        
        .stat-content h3 {
            margin: 0;
            font-size: 2rem;
            color: var(--text-color);
        }
        
        .stat-content p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
            cursor: pointer;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .action-card:hover:not(.guest-disabled) {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .action-card i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .recent-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .recent-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .content-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .content-item:last-child {
            border-bottom: none;
        }
        
        .user-info {
            color: #7f8c8d;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .no-content {
            text-align: center;
            color: #7f8c8d;
            padding: 20px;
        }
        
        .camera-placeholder {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .placeholder-icon {
            color: #bdc3c7;
            margin-bottom: 15px;
        }
        
        .filter-group {
            margin: 15px 0;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .resource-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 16px;
            text-align: center;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .apply-filter-btn {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        
        .apply-filter-btn:hover {
            background: #219a52;
        }
    </style>
</body>
</html>