<?php
require_once '../config/session.php';
require_once '../includes/auth.php';
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
    <link rel="stylesheet" href="../css/pages/lessons.css">
</head>
<body data-user-role="student" data-default-section="dashboard">
    <?php if (isGuest()): ?>
    <div class="guest-banner">
        <i class="fas fa-info-circle"></i>
        You're browsing as a guest. <a href="../register.php" style="color: #333; text-decoration: underline;">Create an account</a> to save your progress and unlock all features.
    </div>
    <?php endif; ?>

    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="studentNav">
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

    <!-- Main Content Area -->
    <div class="main-content dashboard-container">
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
            <!-- Loading message while dynamic content loads -->
            <div class="loading-message">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading dashboard...</p>
            </div>
        </div>
        
        <!-- Error container for connection issues -->
        <div id="error-container" style="display: none;"></div>
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

    <!-- Load dashboard loader script -->
    <script src="../js/components/dashboard-loader.js"></script>
    
    <!-- Fallback functions for backward compatibility -->
    <script>
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
        
        // Fallback loadStudentContent for navigation compatibility
        function loadStudentContent(section) {
            // This will be overridden by the dynamic version when loaded
            switch(section) {
                case 'catalogue':
                    window.location.href = 'catalogue.php';
                    break;
                case 'lessons':
                    window.location.href = 'my-lessons.php';
                    break;
                case 'activities':
                    window.location.href = 'my-activities.php';
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
                    window.location.href = 'profile.php';
                    <?php endif; ?>
                    break;
                case 'settings':
                    window.location.href = 'settings.php';
                    break;
                case 'dashboard':
                    window.location.reload();
                    break;
                default:
                    console.log('Loading:', section);
            }
        }
        
        function setActiveNavItem(section) {
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            const navItem = document.querySelector(`.nav-item.${section}`);
            if (navItem && !navItem.classList.contains('guest-disabled')) {
                navItem.classList.add('active');
            }
        }
    </script>
    
    <style>
        .loading-message {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
        }
        
        .loading-message i {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .error-message {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px;
            border-left: 4px solid #e74c3c;
        }
        
        .error-message h2 {
            color: #e74c3c;
            margin-bottom: 15px;
        }
        
        .error-message p {
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 300px;
        }
        
        .notification-info {
            border-left: 4px solid var(--primary-color);
        }
        
        .notification-success {
            border-left: 4px solid #27ae60;
        }
        
        .notification-error {
            border-left: 4px solid #e74c3c;
        }
        
        .notification button {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #7f8c8d;
        }
    </style>
</body>
</html>