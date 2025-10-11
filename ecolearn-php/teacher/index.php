<?php
require_once '../config/session.php';
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
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
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
            <div class="nav-item profile" onclick="loadContent('profile')">
                <i class="fas fa-user-circle"></i>
                <span>Profile</span>
            </div>
            <div class="nav-item settings" onclick="loadContent('settings')">
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
    <div class="main-content">
        <header class="header">
            <i class="fas fa-chalkboard-teacher"></i>
            <h1>Teacher Dashboard - Environmental Science Learning Platform</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($user['full_name']); ?>
            </div>
        </header>
        
        <div id="content">
            <!-- Dashboard Content -->
            <div class="dashboard-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_lessons; ?></h3>
                        <p>Total Lessons</p>
                        <small><?php echo $published_lessons; ?> Published</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-puzzle-piece"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_activities; ?></h3>
                        <p>Total Activities</p>
                        <small><?php echo $published_activities; ?> Published</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>-</h3>
                        <p>Active Students</p>
                        <small>Coming Soon</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $published_lessons + $published_activities; ?></h3>
                        <p>QR Codes Generated</p>
                        <small>For easy access</small>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <button class="action-card" onclick="showCreateLessonModal()">
                        <i class="fas fa-plus-circle"></i>
                        <h3>Create New Lesson</h3>
                        <p>Design interactive environmental science lessons with QR access</p>
                    </button>
                    
                    <button class="action-card" onclick="showCreateActivityModal()">
                        <i class="fas fa-tasks"></i>
                        <h3>Create New Activity</h3>
                        <p>Build quizzes, projects, and assessments with instant access codes</p>
                    </button>
                    
                    <button class="action-card" onclick="loadContent('analytics')">
                        <i class="fas fa-chart-bar"></i>
                        <h3>View Analytics</h3>
                        <p>Track student progress and performance</p>
                    </button>
                </div>
            </div>
            
            <div class="recent-content">
                <div class="recent-section">
                    <h3>Recent Lessons <span class="section-badge"><?php echo count($lessons); ?></span></h3>
                    <div class="content-list">
                        <?php if (empty($lessons)): ?>
                            <p class="no-content">No lessons created yet. <a href="#" onclick="showCreateLessonModal()">Create your first lesson</a></p>
                        <?php else: ?>
                            <?php foreach (array_slice($lessons, 0, 5) as $lesson): ?>
                                <div class="content-item">
                                    <div class="content-info">
                                        <h4><?php echo htmlspecialchars($lesson['title']); ?></h4>
                                        <p>Created: <?php echo formatDate($lesson['created_at']); ?></p>
                                        <?php if ($lesson['status'] === 'published' && isset($lesson['access_code'])): ?>
                                            <div class="access-info">
                                                <span class="access-code">Code: <strong><?php echo $lesson['access_code']; ?></strong></span>
                                                <button class="btn-qr-small" onclick="showQRCode('<?php echo $lesson['qr_code']; ?>', '<?php echo htmlspecialchars($lesson['title']); ?>', '<?php echo $lesson['access_code']; ?>')">
                                                    <i class="fas fa-qrcode"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="content-status">
                                        <span class="status-badge status-<?php echo $lesson['status']; ?>">
                                            <?php echo ucfirst($lesson['status']); ?>
                                        </span>
                                        <?php if ($lesson['status'] === 'draft'): ?>
                                            <button class="btn-publish" onclick="publishContent('lesson', <?php echo $lesson['id']; ?>)">
                                                <i class="fas fa-upload"></i> Publish
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="recent-section">
                    <h3>Recent Activities <span class="section-badge"><?php echo count($activities); ?></span></h3>
                    <div class="content-list">
                        <?php if (empty($activities)): ?>
                            <p class="no-content">No activities created yet. <a href="#" onclick="showCreateActivityModal()">Create your first activity</a></p>
                        <?php else: ?>
                            <?php foreach (array_slice($activities, 0, 5) as $activity): ?>
                                <div class="content-item">
                                    <div class="content-info">
                                        <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                                        <p>Type: <?php echo $activity['type']; ?> | Created: <?php echo formatDate($activity['created_at']); ?></p>
                                        <?php if ($activity['status'] === 'published' && isset($activity['access_code'])): ?>
                                            <div class="access-info">
                                                <span class="access-code">Code: <strong><?php echo $activity['access_code']; ?></strong></span>
                                                <button class="btn-qr-small" onclick="showQRCode('<?php echo $activity['qr_code']; ?>', '<?php echo htmlspecialchars($activity['title']); ?>', '<?php echo $activity['access_code']; ?>')">
                                                    <i class="fas fa-qrcode"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="content-status">
                                        <span class="status-badge status-<?php echo $activity['status']; ?>">
                                            <?php echo ucfirst($activity['status']); ?>
                                        </span>
                                        <?php if ($activity['status'] === 'draft'): ?>
                                            <button class="btn-publish" onclick="publishContent('activity', <?php echo $activity['id']; ?>)">
                                                <i class="fas fa-upload"></i> Publish
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
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
                            <input type="radio" name="background_type" value="simple" checked>
                            <span style="margin-left: 5px;">Simple Theme</span>
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center;">
                            <input type="radio" name="background_type" value="image">
                            <span style="margin-left: 5px;">Image Background</span>
                        </label>
                    </div>
                </div>

                <div class="access-preview" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <h6><i class="fas fa-info-circle"></i> Access Information</h6>
                    <p style="margin: 5px 0; font-size: 14px;">After publishing, students can access this lesson by:</p>
                    <ul style="margin: 5px 0; padding-left: 20px; font-size: 14px;">
                        <li>Scanning the generated QR code</li>
                        <li>Entering the 6-digit access code</li>
                        <li>Browsing the student catalogue</li>
                    </ul>
                </div>

                <button type="submit" class="action-button" style="width: 100%;">
                    <i class="fas fa-save"></i> Create Lesson
                </button>
            </form>
        </div>
    </div>

    <!-- Create Activity Modal -->
    <div id="createActivityModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-header-content">
                <h3 style="color: var(--secondary-color);">Create New Activity</h3>
                <button onclick="hideModal('createActivityModal')" class="modal-close-btn">&times;</button>
            </div>
            
            <form id="newActivityForm" method="POST" action="../api/activities.php">
                <input type="hidden" name="action" value="create">
                <div style="margin-bottom: 15px;">
                    <label for="activityTitle" style="display: block; font-weight: 600; margin-bottom: 5px;">Activity Title:</label>
                    <input type="text" id="activityTitle" name="title" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="activityDescription" style="display: block; font-weight: 600; margin-bottom: 5px;">Description:</label>
                    <textarea id="activityDescription" name="description" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="activityType" style="display: block; font-weight: 600; margin-bottom: 5px;">Activity Type:</label>
                    <select id="activityType" name="type" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="Quiz">Quiz / Assessment</option>
                        <option value="Project">Project / Essay</option>
                        <option value="Simulation">Simulation / Lab</option>
                    </select>
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="activityDueDate" style="display: block; font-weight: 600; margin-bottom: 5px;">Due Date (Optional):</label>
                    <input type="date" id="activityDueDate" name="due_date" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div class="access-preview" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <h6><i class="fas fa-info-circle"></i> Access Information</h6>
                    <p style="margin: 5px 0; font-size: 14px;">After publishing, students can access this activity by:</p>
                    <ul style="margin: 5px 0; padding-left: 20px; font-size: 14px;">
                        <li>Scanning the generated QR code</li>
                        <li>Entering the 6-digit access code</li>
                        <li>Browsing the student catalogue</li>
                    </ul>
                </div>

                <button type="submit" class="action-button" style="width: 100%; background-color: var(--secondary-color);">
                    <i class="fas fa-tasks"></i> Create Activity
                </button>
            </form>
        </div>
    </div>

    <!-- QR Code Display Modal -->
    <div id="qrCodeModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-header-content">
                <h3><i class="fas fa-qrcode"></i> QR Code & Access Information</h3>
                <button onclick="hideModal('qrCodeModal')" class="modal-close-btn">&times;</button>
            </div>
            
            <div class="qr-display">
                <div class="qr-code-container">
                    <img id="qrCodeImage" src="" alt="QR Code" style="width: 200px; height: 200px; border: 1px solid #ddd;">
                </div>
                <div class="access-details">
                    <h4 id="contentTitle"></h4>
                    <div class="access-code-display">
                        <label>Access Code:</label>
                        <span id="accessCodeDisplay" class="code-highlight"></span>
                        <button onclick="copyAccessCode()" class="btn-copy">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <div class="instructions">
                        <h6>How students can access:</h6>
                        <ol>
                            <li>Scan this QR code with their device camera</li>
                            <li>Enter the access code in the student portal</li>
                            <li>Browse the catalogue in their dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        
        function showCreateActivityModal() {
            document.getElementById('createActivityModal').style.display = 'flex';
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function showQRCode(qrCodeUrl, title, accessCode) {
            document.getElementById('qrCodeImage').src = qrCodeUrl;
            document.getElementById('contentTitle').textContent = title;
            document.getElementById('accessCodeDisplay').textContent = accessCode;
            document.getElementById('qrCodeModal').style.display = 'flex';
        }

        function copyAccessCode() {
            const accessCode = document.getElementById('accessCodeDisplay').textContent;
            navigator.clipboard.writeText(accessCode).then(() => {
                alert('Access code copied to clipboard!');
            });
        }

        function publishContent(type, id) {
            if (confirm(`Are you sure you want to publish this ${type}? Students will be able to access it immediately.`)) {
                fetch(`../api/${type}s.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=publish&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`${type.charAt(0).toUpperCase() + type.slice(1)} published successfully!`);
                        window.location.reload();
                    } else {
                        alert(`Error publishing ${type}: ` + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(`Error publishing ${type}`);
                });
            }
        }
        
        // Handle form submissions
        document.getElementById('newLessonForm').addEventListener('submit', function(e) {
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
        
        document.getElementById('newActivityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/activities.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Activity created successfully! You can now publish it to generate QR code and access code.');
                    hideModal('createActivityModal');
                    window.location.reload();
                } else {
                    alert('Error creating activity: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating activity');
            });
        });
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
        
        .action-card:hover {
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

        .recent-section h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .section-badge {
            background: var(--primary-color);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .content-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 15px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .content-item:last-child {
            border-bottom: none;
        }

        .content-info {
            flex: 1;
        }

        .content-status {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .access-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
        }

        .access-code {
            font-size: 12px;
            color: #666;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .btn-qr-small {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-qr-small:hover {
            background: #219a52;
        }

        .btn-publish {
            background: #f39c12;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-publish:hover {
            background: #e67e22;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-draft {
            background: #f39c12;
            color: white;
        }
        
        .status-published {
            background: #27ae60;
            color: white;
        }
        
        .status-archived {
            background: #95a5a6;
            color: white;
        }
        
        .user-info {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .no-content {
            text-align: center;
            color: #7f8c8d;
            padding: 20px;
        }
        
        .no-content a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .no-content a:hover {
            text-decoration: underline;
        }

        .access-preview {
            border-left: 4px solid var(--primary-color);
        }

        .qr-display {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .qr-code-container {
            text-align: center;
        }

        .access-details {
            flex: 1;
        }

        .access-code-display {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .code-highlight {
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
            color: var(--primary-color);
            background: white;
            padding: 5px 10px;
            border-radius: 4px;
            border: 2px solid var(--primary-color);
        }

        .btn-copy {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-copy:hover {
            background: #219a52;
        }

        .instructions {
            margin-top: 20px;
        }

        .instructions ol {
            padding-left: 20px;
        }

        .instructions li {
            margin: 5px 0;
        }

        @media (max-width: 768px) {
            .qr-display {
                flex-direction: column;
                text-align: center;
            }
            
            .recent-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>