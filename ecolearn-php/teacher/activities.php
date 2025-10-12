<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('teacher');

$user = getCurrentUser();
$activities = getTeacherActivities($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLearn Platform - Manage Activities</title>
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

            <div class="nav-item lessons" onclick="window.location.href='lessons.php'">
                <i class="fas fa-book-open"></i>
                <span>Lessons</span>
            </div>

            <div class="nav-item activities active">
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
            <i class="fas fa-puzzle-piece"></i>
            <h1>Activity Management</h1>
            <div class="header-actions">
                <button class="action-button" onclick="createNewActivity()">
                    <i class="fas fa-plus"></i> Create New Activity
                </button>
            </div>
        </header>

        <div id="content">
            <div class="activities-container">
                <?php if (empty($activities)): ?>
                    <div class="empty-state">
                        <i class="fas fa-puzzle-piece fa-4x"></i>
                        <h2>No Activities Created Yet</h2>
                        <p>Start creating engaging environmental science activities for your students.</p>
                        <button class="action-button" onclick="createNewActivity()">
                            <i class="fas fa-plus"></i> Create Your First Activity
                        </button>
                    </div>
                <?php else: ?>
                    <div class="activities-grid">
                        <?php foreach ($activities as $activity): ?>
                            <div class="activity-card">
                                <div class="activity-header">
                                    <h3><?php echo htmlspecialchars($activity['title']); ?></h3>
                                    <div class="activity-status">
                                        <span class="status-badge status-<?php echo $activity['status']; ?>">
                                            <?php echo ucfirst($activity['status']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="activity-content">
                                    <p><?php echo htmlspecialchars(substr($activity['description'] ?? '', 0, 150)) . '...'; ?></p>
                                    <div class="activity-meta">
                                        <span><i class="fas fa-tag"></i> Type: <?php echo $activity['type']; ?></span>
                                        <span><i class="fas fa-calendar"></i> Created: <?php echo formatDate($activity['created_at']); ?></span>
                                        <?php if ($activity['due_date']): ?>
                                            <span><i class="fas fa-clock"></i> Due: <?php echo formatDate($activity['due_date']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="activity-actions">
                                    <button class="action-small-btn edit-btn" onclick="editActivity(<?php echo $activity['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <?php if ($activity['status'] === 'draft'): ?>
                                        <button class="action-small-btn publish-btn" onclick="publishActivity(<?php echo $activity['id']; ?>)">
                                            <i class="fas fa-upload"></i> Publish
                                        </button>
                                    <?php else: ?>
                                        <button class="action-small-btn view-btn" onclick="previewActivity(<?php echo $activity['id']; ?>)">
                                            <i class="fas fa-eye"></i> Preview
                                        </button>
                                    <?php endif; ?>

                                    <button class="action-small-btn archive-btn" onclick="archiveActivity(<?php echo $activity['id']; ?>)">
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

    <script>
        function createNewActivity() {
            window.location.href = 'activity-editor.php';
        }

        function editActivity(activityId) {
            window.location.href = 'activity-editor.php?id=' + activityId;
        }

        function publishActivity(activityId) {
            if (confirm('Are you sure you want to publish this activity? Students will be able to access it.')) {
                fetch('../api/activities.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=publish&id=' + activityId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Activity published successfully!');
                        window.location.reload();
                    } else {
                        alert('Error publishing activity: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error publishing activity');
                });
            }
        }

        function previewActivity(activityId) {
            alert('Preview activity feature coming soon! Activity ID: ' + activityId);
        }

        function archiveActivity(activityId) {
            if (confirm('Are you sure you want to archive this activity?')) {
                alert('Archive activity feature coming soon! Activity ID: ' + activityId);
            }
        }
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

        .activities-container {
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

        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
        }

        .activity-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .activity-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .activity-header {
            background: linear-gradient(135deg, var(--secondary-color), #8e44ad);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .activity-header h3 {
            margin: 0;
            font-size: 1.3rem;
            flex: 1;
        }

        .activity-status {
            margin-left: 15px;
        }

        .activity-content {
            padding: 20px;
        }

        .activity-content p {
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .activity-meta {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-size: 14px;
            color: #95a5a6;
        }

        .activity-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .activity-actions {
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
            .activities-grid {
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
