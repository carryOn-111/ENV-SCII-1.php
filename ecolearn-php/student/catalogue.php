<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('student');

$user = getCurrentUser();
$available_lessons = getPublishedLessons();
$available_activities = getPublishedActivities();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLearn Platform - Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/guest.css">
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
            
            <div class="nav-item library active">
                <i class="fas fa-layer-group"></i>
                <span>Library</span>
            </div>
            
            <div class="nav-item student-lessons" onclick="window.location.href='my-lessons.php'">
                <i class="fas fa-book-reader"></i>
                <span>My Lessons</span>
            </div>

            <div class="nav-item student-activities" onclick="window.location.href='my-activities.php'">
                <i class="fas fa-tasks"></i>
                <span>My Activities</span>
            </div>
            
            <div class="nav-item student-progress">
                <i class="fas fa-chart-bar"></i>
                <span>My Progress</span>
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
    <div class="main-content">
        <header class="header">
            <i class="fas fa-layer-group"></i>
            <h1>Learning Library</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($user['full_name']); ?>
            </div>
        </header>
        
        <div id="content">
            <!-- Filter and Search Section -->
            <div class="library-controls">
                <div class="search-section">
                    <input type="text" id="searchInput" placeholder="Search lessons and activities..." class="search-input">
                    <button class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="filter-section">
                    <select id="typeFilter" class="filter-select">
                        <option value="all">All Content</option>
                        <option value="lessons">Lessons Only</option>
                        <option value="activities">Activities Only</option>
                    </select>
                    
                    <select id="sortFilter" class="filter-select">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="title">By Title</option>
                    </select>
                </div>
            </div>
            
            <!-- Content Sections -->
            <div class="library-content">
                <!-- Lessons Section -->
                <div class="content-section" id="lessonsSection">
                    <div class="section-header">
                        <h2><i class="fas fa-book-open"></i> Available Lessons</h2>
                        <span class="content-count"><?php echo count($available_lessons); ?> lessons</span>
                    </div>
                    
                    <div class="content-grid">
                        <?php if (empty($available_lessons)): ?>
                            <div class="no-content-message">
                                <i class="fas fa-book-open fa-3x"></i>
                                <h3>No Lessons Available</h3>
                                <p>Check back later for new lessons from your teachers!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($available_lessons as $lesson): ?>
                                <div class="content-card lesson-card" data-type="lesson" data-title="<?php echo strtolower($lesson['title']); ?>">
                                    <div class="card-header">
                                        <div class="card-icon">
                                            <i class="fas fa-book-open"></i>
                                        </div>
                                        <div class="card-type">Lesson</div>
                                    </div>
                                    
                                    <div class="card-content">
                                        <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                        <p class="card-description">
                                            <?php echo htmlspecialchars(substr($lesson['content'], 0, 100)) . '...'; ?>
                                        </p>
                                        <div class="card-meta">
                                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($lesson['teacher_name']); ?></span>
                                            <span><i class="fas fa-calendar"></i> <?php echo formatDate($lesson['created_at']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-actions">
                                        <button class="action-btn primary" onclick="startLesson(<?php echo $lesson['id']; ?>)">
                                            <i class="fas fa-play"></i> Start Lesson
                                        </button>
                                        <button class="action-btn secondary" onclick="previewLesson(<?php echo $lesson['id']; ?>)">
                                            <i class="fas fa-eye"></i> Preview
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Activities Section -->
                <div class="content-section" id="activitiesSection">
                    <div class="section-header">
                        <h2><i class="fas fa-tasks"></i> Available Activities</h2>
                        <span class="content-count"><?php echo count($available_activities); ?> activities</span>
                    </div>
                    
                    <div class="content-grid">
                        <?php if (empty($available_activities)): ?>
                            <div class="no-content-message">
                                <i class="fas fa-tasks fa-3x"></i>
                                <h3>No Activities Available</h3>
                                <p>Check back later for new activities from your teachers!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($available_activities as $activity): ?>
                                <div class="content-card activity-card" data-type="activity" data-title="<?php echo strtolower($activity['title']); ?>">
                                    <div class="card-header">
                                        <div class="card-icon activity-icon">
                                            <?php
                                            $icon = 'fas fa-tasks';
                                            switch($activity['type']) {
                                                case 'Quiz': $icon = 'fas fa-question-circle'; break;
                                                case 'Project': $icon = 'fas fa-project-diagram'; break;
                                                case 'Simulation': $icon = 'fas fa-flask'; break;
                                            }
                                            ?>
                                            <i class="<?php echo $icon; ?>"></i>
                                        </div>
                                        <div class="card-type"><?php echo $activity['type']; ?></div>
                                    </div>
                                    
                                    <div class="card-content">
                                        <h3><?php echo htmlspecialchars($activity['title']); ?></h3>
                                        <div class="card-meta">
                                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($activity['teacher_name']); ?></span>
                                            <span><i class="fas fa-calendar"></i> <?php echo formatDate($activity['created_at']); ?></span>
                                            <?php if ($activity['due_date']): ?>
                                                <span class="due-date">
                                                    <i class="fas fa-clock"></i> Due: <?php echo formatDate($activity['due_date']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card-actions">
                                        <button class="action-btn primary" onclick="startActivity(<?php echo $activity['id']; ?>)">
                                            <i class="fas fa-play-circle"></i> Start Activity
                                        </button>
                                        <button class="action-btn secondary" onclick="viewActivityDetails(<?php echo $activity['id']; ?>)">
                                            <i class="fas fa-info-circle"></i> Details
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

    <script>
        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('input', filterContent);
        document.getElementById('typeFilter').addEventListener('change', filterContent);
        document.getElementById('sortFilter').addEventListener('change', sortContent);
        
        function filterContent() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const typeFilter = document.getElementById('typeFilter').value;
            const cards = document.querySelectorAll('.content-card');
            
            cards.forEach(card => {
                const title = card.dataset.title;
                const type = card.dataset.type;
                
                const matchesSearch = title.includes(searchTerm);
                const matchesType = typeFilter === 'all' || 
                                   (typeFilter === 'lessons' && type === 'lesson') ||
                                   (typeFilter === 'activities' && type === 'activity');
                
                if (matchesSearch && matchesType) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show/hide sections based on filter
            const lessonsSection = document.getElementById('lessonsSection');
            const activitiesSection = document.getElementById('activitiesSection');
            
            if (typeFilter === 'lessons') {
                lessonsSection.style.display = 'block';
                activitiesSection.style.display = 'none';
            } else if (typeFilter === 'activities') {
                lessonsSection.style.display = 'none';
                activitiesSection.style.display = 'block';
            } else {
                lessonsSection.style.display = 'block';
                activitiesSection.style.display = 'block';
            }
        }
        
        function sortContent() {
            const sortBy = document.getElementById('sortFilter').value;
            const sections = document.querySelectorAll('.content-grid');
            
            sections.forEach(section => {
                const cards = Array.from(section.querySelectorAll('.content-card'));
                
                cards.sort((a, b) => {
                    if (sortBy === 'title') {
                        return a.dataset.title.localeCompare(b.dataset.title);
                    }
                    // For date sorting, we'd need to add data attributes with timestamps
                    return 0;
                });
                
                cards.forEach(card => section.appendChild(card));
            });
        }
        
        function startLesson(lessonId) {
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
        
        function previewLesson(lessonId) {
            alert('Lesson preview feature coming soon!');
        }
        
        function startActivity(activityId) {
            window.location.href = 'activity-viewer.php?id=' + activityId;
        }
        
        function viewActivityDetails(activityId) {
            alert('Activity details feature coming soon!');
        }
    </script>
    
    <style>
        .library-controls {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
        
        .search-section {
            display: flex;
            flex: 1;
            max-width: 400px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 8px 0 0 8px;
            font-size: 16px;
        }
        
        .search-btn {
            padding: 12px 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
        }
        
        .filter-section {
            display: flex;
            gap: 15px;
        }
        
        .filter-select {
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 16px;
            background: white;
        }
        
        .content-section {
            margin-bottom: 40px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .section-header h2 {
            color: var(--text-color);
            margin: 0;
        }
        
        .content-count {
            background: var(--primary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), #219a52);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .activity-card .card-header {
            background: linear-gradient(135deg, var(--secondary-color, #e74c3c), #c0392b);
        }
        
        .card-icon {
            font-size: 24px;
        }
        
        .card-type {
            font-size: 14px;
            font-weight: 600;
            opacity: 0.9;
        }
        
        .card-content {
            padding: 20px;
        }
        
        .card-content h3 {
            margin: 0 0 10px 0;
            color: var(--text-color);
            font-size: 1.3rem;
        }
        
        .card-description {
            color: #7f8c8d;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .card-meta {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-size: 14px;
            color: #95a5a6;
        }
        
        .card-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .due-date {
            color: #e74c3c !important;
            font-weight: 600;
        }
        
        .card-actions {
            padding: 20px;
            border-top: 1px solid #ecf0f1;
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .action-btn.primary {
            background: var(--primary-color);
            color: white;
        }
        
        .action-btn.primary:hover {
            background: #219a52;
        }
        
        .action-btn.secondary {
            background: #ecf0f1;
            color: var(--text-color);
        }
        
        .action-btn.secondary:hover {
            background: #d5dbdb;
        }
        
        .no-content-message {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .no-content-message i {
            color: #bdc3c7;
            margin-bottom: 20px;
        }
        
        .no-content-message h3 {
            margin: 0 0 10px 0;
            color: #95a5a6;
        }
        
        .user-info {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .library-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-section {
                max-width: none;
            }
            
            .filter-section {
                justify-content: space-between;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>