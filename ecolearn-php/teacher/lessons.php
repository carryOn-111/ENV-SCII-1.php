<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('teacher');

$user = getCurrentUser();
$lessons = getLessonsByTeacher($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLearn Platform - Manage Lessons</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <div class="nav-item dashboard" onclick="window.location.href='index.php'">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            
            <div class="nav-item lessons active">
                <i class="fas fa-book-open"></i>
                <span>Lessons</span>
            </div>
            
            <div class="nav-item activities" onclick="window.location.href='activities.php'">
                <i class="fas fa-puzzle-piece"></i>
                <span>Activities</span>
            </div>
            
            <div class="nav-item analytics" onclick="window.location.href='analytics.php'">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </div>
        </div>
        
        <div class="footer-nav-section">
            <div class="nav-item profile">
                <i class="fas fa-user-circle"></i>
                <span>Profile</span>
            </div>
            <div class="nav-item settings">
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
            <i class="fas fa-book-open"></i>
            <h1>Lesson Management</h1>
            <div class="header-actions">
                <button class="action-button" onclick="showCreateLessonModal()">
                    <i class="fas fa-plus"></i> Create New Lesson
                </button>
            </div>
        </header>
        
        <div id="content">
            <div class="lessons-container">
                <?php if (empty($lessons)): ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open fa-4x"></i>
                        <h2>No Lessons Created Yet</h2>
                        <p>Start creating engaging environmental science lessons for your students.</p>
                        <button class="action-button" onclick="showCreateLessonModal()">
                            <i class="fas fa-plus"></i> Create Your First Lesson
                        </button>
                    </div>
                <?php else: ?>
                    <div class="lessons-grid">
                        <?php foreach ($lessons as $lesson): ?>
                            <div class="lesson-card">
                                <div class="lesson-header">
                                    <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                    <div class="lesson-status">
                                        <span class="status-badge status-<?php echo $lesson['status']; ?>">
                                            <?php echo ucfirst($lesson['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="lesson-content">
                                    <p><?php echo htmlspecialchars(substr($lesson['content'], 0, 150)) . '...'; ?></p>
                                    <div class="lesson-meta">
                                        <span><i class="fas fa-calendar"></i> Created: <?php echo formatDate($lesson['created_at']); ?></span>
                                        <span><i class="fas fa-edit"></i> Updated: <?php echo formatDate($lesson['updated_at']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="lesson-actions">
                                    <button class="action-small-btn edit-btn" onclick="editLesson(<?php echo $lesson['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    
                                    <?php if ($lesson['status'] === 'draft'): ?>
                                        <button class="action-small-btn publish-btn" onclick="publishLesson(<?php echo $lesson['id']; ?>)">
                                            <i class="fas fa-upload"></i> Publish
                                        </button>
                                    <?php else: ?>
                                        <button class="action-small-btn view-btn" onclick="previewLesson(<?php echo $lesson['id']; ?>)">
                                            <i class="fas fa-eye"></i> Preview
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="action-small-btn archive-btn" onclick="archiveLesson(<?php echo $lesson['id']; ?>)">
                                        <i class="fas fa-archive"></i> Archive
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
            
            <form id="newLessonForm">
                <div style="margin-bottom: 15px;">
                    <label for="lessonTitle" style="display: block; font-weight: 600; margin-bottom: 5px;">Lesson Title:</label>
                    <input type="text" id="lessonTitle" name="title" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="lessonContent" style="display: block; font-weight: 600; margin-bottom: 5px;">Lesson Content:</label>
                    <textarea id="lessonContent" name="content" rows="8" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" placeholder="Enter the main content for this lesson..."></textarea>
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

                <button type="submit" class="action-button" style="width: 100%;">
                    <i class="fas fa-save"></i> Create Lesson
                </button>
            </form>
        </div>
    </div>

    <script>
        function showCreateLessonModal() {
            document.getElementById('createLessonModal').style.display = 'flex';
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function editLesson(lessonId) {
            alert('Edit lesson feature coming soon! Lesson ID: ' + lessonId);
        }
        
        function publishLesson(lessonId) {
            if (confirm('Are you sure you want to publish this lesson? Students will be able to access it.')) {
                fetch('../api/lessons.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=publish&lesson_id=' + lessonId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Lesson published successfully!');
                        window.location.reload();
                    } else {
                        alert('Error publishing lesson: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error publishing lesson');
                });
            }
        }
        
        function previewLesson(lessonId) {
            window.open('../student/lesson-viewer.php?id=' + lessonId, '_blank');
        }
        
        function archiveLesson(lessonId) {
            if (confirm('Are you sure you want to archive this lesson?')) {
                alert('Archive lesson feature coming soon! Lesson ID: ' + lessonId);
            }
        }
        
        // Handle form submission
        document.getElementById('newLessonForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create');
            
            fetch('../api/lessons.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Lesson created successfully!');
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
    </script>
    
    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .lessons-container {
            padding: 20px 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            color: #bdc3c7;
            margin-bottom: 30px;
        }
        
        .empty-state h2 {
            margin: 0 0 15px 0;
            color: #95a5a6;
        }
        
        .empty-state p {
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .lessons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
        }
        
        .lesson-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .lesson-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .lesson-header {
            background: linear-gradient(135deg, var(--primary-color), #219a52);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .lesson-header h3 {
            margin: 0;
            font-size: 1.3rem;
            flex: 1;
        }
        
        .lesson-status {
            margin-left: 15px;
        }
        
        .lesson-content {
            padding: 20px;
        }
        
        .lesson-content p {
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .lesson-meta {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-size: 14px;
            color: #95a5a6;
        }
        
        .lesson-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .lesson-actions {
            padding: 20px;
            border-top: 1px solid #ecf0f1;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
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
        
        @media (max-width: 768px) {
            .lessons-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</body>
</html>