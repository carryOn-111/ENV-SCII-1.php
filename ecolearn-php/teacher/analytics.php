<?php
require_once '../config/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('teacher');

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLearn Platform - Analytics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pages/analytics.css">
</head>
<body data-user-role="teacher" data-default-section="analytics">
    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="teacherNav">
        <div class="logo">
            <i class="fas fa-leaf fa-2x"></i>
            <h2>EcoLearn</h2>
        </div>

        <div class="nav-section">
            <div class="nav-item dashboard" onclick="loadContent('dashboard')">
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

            <div class="nav-item analytics active">
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
            <i class="fas fa-chart-line"></i>
            <h1>Analytics Dashboard</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($user['full_name']); ?>
            </div>
        </header>

        <div id="content">
            <!-- Loading message while dynamic content loads -->
            <div class="loading-message">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading analytics...</p>
            </div>
        </div>

        <!-- Error container for connection issues -->
        <div id="error-container" style="display: none;"></div>
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
                    window.location.reload();
                    break;
                case 'dashboard':
                    window.location.href = 'index.php';
                    break;
                default:
                    console.log('Loading:', section);
            }
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
