<?php
require_once '../config/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('teacher');

$user = getCurrentUser();
$functions = new EcoLearnFunctions();
$lessons = $functions->getTeacherLessons($user['id']);
$activities = $functions->getTeacherActivities($user['id']);

// Get some basic statistics
$total_lessons = count($lessons);
$published_lessons = count(array_filter($lessons, function($l) { return $l['status'] === 'published'; }));
$total_activities = count($activities);
$published_activities = count(array_filter($activities, function($a) { return $a['status'] === 'published'; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLearn Platform - Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body data-user-role="teacher" data-default-section="dashboard">
    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="teacherNav">
        <div class="logo">
            <i class="fas fa-leaf fa-2x"></i>
            <h2>EcoLearn</h2>
        </div>
        
        <div class="nav-section">
            <div class="nav-item dashboard active" onclick="loadContent('dashboard')">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            
            <div class="nav-item lessons" onclick="loadContent('lessons')">
                <i class="fas fa-book-open"></i>
                <span>Lessons</span>
            </div>
            
            <div class="nav-item activities" onclick="loadContent('activities')">
                <i class="fas fa-puzzle-piece"></i>
                <span>Activities</span>
            </div>
            
            <div class="nav-item analytics" onclick="loadContent('analytics')">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </div>
        </div>
        
        <div class="footer-nav-section">
            <div class="nav-item profile" onclick="window.location.href='profile.php'">
                <i class="fas fa-user-circle"></i>
                <span>Profile</span>
            </div>
            <div class="nav-item settings" onclick="window.location.href='settings.php'">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </div>
            
            <div class="nav-item logout" onclick="window.location.href='../logout.php'">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-content dashboard-container">
        <header class="header">
            <i class="fas fa-chalkboard-teacher"></i>
            <h1>Teacher Dashboard - Environmental Science Learning Platform</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($user['full_name']); ?>
            </div>
        </header>
        
        <div id="content">
            <!-- Loading message while dynamic content loads -->
            <div class="loading-message">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading dashboard...</p>
            </div>
            
            <!-- Fallback static content for lessons view -->
            <div id="lessonGrid" style="display: none;"></div>
        </div>
        
        <!-- Error container for connection issues -->
        <div id="error-container" style="display: none;"></div>
    </div>

    <!-- Create Lesson Modal -->
    <div id="createLessonModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-header-content">
                <h3 style="color: var(--primary-color);">Create New Lesson</h3>
                <button onclick="hideModal('createLessonModal')" class="modal-close-btn">&times;</button>
            </div>
            
            <form id="newLessonForm" method="POST" action="../api/lessons.php">
                <input type="hidden" name="action" value="create">
                <div style="margin-bottom: 15px;">
                    <label for="lessonTitle" style="display: block; font-weight: 600; margin-bottom: 5px;">Lesson Title:</label>
                    <input type="text" id="lessonTitle" name="title" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="lessonContent" style="display: block; font-weight: 600; margin-bottom: 5px;">Lesson Content:</label>
                    <textarea id="lessonContent" name="content" rows="5" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <strong style="display: block; margin-bottom: 10px;">Background Type:</strong>
                    <div style="display: flex; gap: 15px;">
                        <label style="cursor: pointer; display: flex; align-items: center;">
                            <input type="radio" name="bgType" value="simple" checked>
                            <span style="margin-left: 5px;">Simple Theme</span>
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center;">
                            <input type="radio" name="bgType" value="image">
                            <span style="margin-left: 5px;">Image Background</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="action-button" style="width: 100%;">
                    <i class="fas fa-save"></i> Create Lesson
                </button>
            </form>
        </div>
    </div>

    <!-- Lesson Editor Modal -->
    <div id="lessonEditorModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog" style="max-width: 90%; width: 1200px;">
            <div class="modal-header-content">
                <h3 id="editorTitle">Lesson Editor</h3>
                <button onclick="hideModal('lessonEditorModal')" class="modal-close-btn">&times;</button>
            </div>
            <div style="padding: 20px;">
                <p>Lesson editor interface will be implemented here.</p>
            </div>
        </div>
    </div>

    <!-- Lesson Viewer Modal -->
    <div id="lessonViewerModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog" style="max-width: 90%; width: 1000px;">
            <div class="modal-header-content">
                <h3 id="slideshowTitle">Lesson Viewer</h3>
                <button onclick="hideModal('lessonViewerModal')" class="modal-close-btn">&times;</button>
            </div>
            <div style="padding: 20px;">
                <p>Lesson slideshow viewer will be implemented here.</p>
            </div>
        </div>
    </div>

    <!-- Load dashboard loader script -->
    <script src="../js/components/dashboard-loader.js"></script>
    
    <!-- Fallback functions for backward compatibility -->
    <script>
        function loadContent(section) {
            // Remove active class from all nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item
            document.querySelector(`.nav-item.${section}`).classList.add('active');
            
            // Load content based on section
            switch(section) {
                case 'lessons':
                    window.location.href = 'lessons.php';
                    break;
                case 'activities':
                    window.location.href = 'activities.php';
                    break;
                case 'analytics':
                    window.location.href = 'analytics.php';
                    break;
                case 'profile':
                    window.location.href = 'profile.php';
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
        
        function showCreateLessonModal() {
            document.getElementById('createLessonModal').style.display = 'flex';
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }
        
        function customAlert(message) {
            console.log('Alert:', message);
            if (typeof showNotification === 'function') {
                showNotification(message);
            } else {
                alert(message);
            }
        }
        
        function generateQRCode(lessonId, type, title) {
            customAlert(`Generating QR code for ${type}: ${title} (ID: ${lessonId})`);
        }
        
        // Handle form submissions
        document.addEventListener('DOMContentLoaded', function() {
            const lessonForm = document.getElementById('newLessonForm');
            if (lessonForm) {
                lessonForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    fetch('../api/lessons.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Lesson created successfully! You can now publish it to generate QR code and access code.');
                            hideModal('createLessonModal');
                            window.location.reload();
                        } else {
                            alert('Error creating lesson: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error creating lesson');
                    });
                });
            }
        });
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