// Dashboard Loader - Handles dynamic content loading for both teacher and student dashboards
class DashboardLoader {
    constructor() {
        this.currentSection = 'dashboard';
        this.userRole = document.body.dataset.userRole || 'student';
        this.init();
    }

    init() {
        // Load initial content based on default section
        const defaultSection = document.body.dataset.defaultSection || 'dashboard';
        this.loadContent(defaultSection);
        
        // Set up navigation handlers
        this.setupNavigation();
    }

    setupNavigation() {
        // Handle navigation clicks
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const section = item.classList[1]; // Get second class name
                if (section && !item.classList.contains('guest-disabled')) {
                    this.loadContent(section);
                }
            });
        });
    }

    loadContent(section) {
        this.currentSection = section;
        this.setActiveNavItem(section);
        
        const contentDiv = document.getElementById('content');
        const errorDiv = document.getElementById('error-container');
        
        // Hide error container
        errorDiv.style.display = 'none';
        
        // Show loading state
        contentDiv.innerHTML = `
            <div class="loading-message">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading ${section}...</p>
            </div>
        `;

        // Route to appropriate content loader
        switch(section) {
            case 'dashboard':
                this.loadDashboard();
                break;
            case 'lessons':
                this.loadLessons();
                break;
            case 'activities':
                this.loadActivities();
                break;
            case 'analytics':
                this.loadAnalytics();
                break;
            case 'catalogue':
            case 'library':
                this.loadLibrary();
                break;
            case 'profile':
                this.showProfile();
                break;
            case 'settings':
                this.showSettings();
                break;
            default:
                this.loadDashboard();
        }
    }

    loadDashboard() {
        const contentDiv = document.getElementById('content');
        
        if (this.userRole === 'teacher') {
            this.loadTeacherDashboard();
        } else {
            this.loadStudentDashboard();
        }
    }

    loadTeacherDashboard() {
        fetch('../api/dashboard.php?action=teacher_stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.renderTeacherDashboard(data.stats);
                } else {
                    this.showError('Failed to load dashboard data: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Dashboard error:', error);
                this.showError('Unable to connect to server. Please check your connection.');
            });
    }

    loadStudentDashboard() {
        fetch('../api/dashboard.php?action=student_stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.renderStudentDashboard(data.stats);
                } else {
                    this.showError('Failed to load dashboard data: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Dashboard error:', error);
                this.showError('Unable to connect to server. Please check your connection.');
            });
    }

    loadLessons() {
        fetch('../api/lessons.php?action=list')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.renderLessons(data.lessons);
                } else {
                    this.showError('Failed to load lessons: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Lessons error:', error);
                this.showError('Unable to load lessons. Please try again.');
            });
    }

    loadActivities() {
        fetch('../api/activities.php?action=get_all')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.renderActivities(data.activities);
                } else {
                    this.showError('Failed to load activities: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Activities error:', error);
                this.showError('Unable to load activities. Please try again.');
            });
    }

    loadAnalytics() {
        if (this.userRole !== 'teacher') {
            this.showError('Analytics are only available for teachers.');
            return;
        }

        fetch('../api/analytics.php?action=overview')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.renderAnalytics(data.overview);
                } else {
                    this.showError('Failed to load analytics: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Analytics error:', error);
                this.showError('Unable to load analytics. Please try again.');
            });
    }

    loadLibrary() {
        // For students, show published lessons and activities
        Promise.all([
            fetch('../api/lessons.php?action=list'),
            fetch('../api/activities.php?action=get_all')
        ])
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(([lessonsData, activitiesData]) => {
            const lessons = lessonsData.success ? lessonsData.lessons : [];
            const activities = activitiesData.success ? activitiesData.activities : [];
            this.renderLibrary(lessons, activities);
        })
        .catch(error => {
            console.error('Library error:', error);
            this.showError('Unable to load library content. Please try again.');
        });
    }

    renderTeacherDashboard(stats) {
        const contentDiv = document.getElementById('content');
        contentDiv.innerHTML = `
            <div class="dashboard-overview">
                <h2>Teacher Dashboard</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">${stats.lessons?.total_lessons || 0}</div>
                        <div class="stat-label">Total Lessons</div>
                        <div class="stat-detail">${stats.lessons?.published_lessons || 0} published</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">${stats.activities?.total_activities || 0}</div>
                        <div class="stat-label">Total Activities</div>
                        <div class="stat-detail">${stats.activities?.published_activities || 0} published</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">${stats.engagement?.active_students || 0}</div>
                        <div class="stat-label">Active Students</div>
                        <div class="stat-detail">Last 30 days</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">${Math.round(stats.activities?.avg_score || 0)}%</div>
                        <div class="stat-label">Average Score</div>
                        <div class="stat-detail">All activities</div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <button onclick="showCreateLessonModal()" class="action-button">
                            <i class="fas fa-plus"></i> Create Lesson
                        </button>
                        <button onclick="loadContent('lessons')" class="action-button">
                            <i class="fas fa-book"></i> Manage Lessons
                        </button>
                        <button onclick="loadContent('activities')" class="action-button">
                            <i class="fas fa-tasks"></i> Manage Activities
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    renderStudentDashboard(stats) {
        const contentDiv = document.getElementById('content');
        contentDiv.innerHTML = `
            <div class="dashboard-overview">
                <h2>Student Dashboard</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">${stats.overall_avg_score || 0}%</div>
                        <div class="stat-label">Overall Average</div>
                        <div class="stat-detail">All activities</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">${stats.pending_assignments || 0}</div>
                        <div class="stat-label">Pending Tasks</div>
                        <div class="stat-detail">Due soon</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">${stats.lessons_progress?.completed || 0}/${stats.lessons_progress?.total || 0}</div>
                        <div class="stat-label">Lessons Progress</div>
                        <div class="stat-detail">Completed</div>
                    </div>
                    <div class="stat-card ${stats.overdue_activities > 0 ? 'stat-warning' : ''}">
                        <div class="stat-number">${stats.overdue_activities || 0}</div>
                        <div class="stat-label">Overdue</div>
                        <div class="stat-detail">Activities</div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <button onclick="showQRScanner()" class="action-button">
                            <i class="fas fa-qrcode"></i> Scan QR Code
                        </button>
                        <button onclick="loadContent('catalogue')" class="action-button">
                            <i class="fas fa-book-open"></i> Browse Library
                        </button>
                        <button onclick="loadContent('activities')" class="action-button">
                            <i class="fas fa-tasks"></i> My Activities
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    renderLessons(lessons) {
        const contentDiv = document.getElementById('content');
        const isTeacher = this.userRole === 'teacher';
        
        let html = `
            <div class="lessons-section">
                <div class="section-header">
                    <h2>Lessons</h2>
                    ${isTeacher ? '<button onclick="showCreateLessonModal()" class="action-button"><i class="fas fa-plus"></i> Create Lesson</button>' : ''}
                </div>
        `;

        if (lessons.length === 0) {
            html += `
                <div class="empty-state">
                    <i class="fas fa-book-open fa-3x"></i>
                    <h3>No lessons found</h3>
                    <p>${isTeacher ? 'Create your first lesson to get started.' : 'No lessons are available yet.'}</p>
                </div>
            `;
        } else {
            html += '<div class="lessons-grid">';
            lessons.forEach(lesson => {
                html += `
                    <div class="lesson-card">
                        <div class="lesson-header">
                            <h3>${lesson.title}</h3>
                            <span class="lesson-status status-${lesson.status}">${lesson.status}</span>
                        </div>
                        <div class="lesson-content">
                            <p>${lesson.content.substring(0, 100)}...</p>
                            ${lesson.teacher_name ? `<small>by ${lesson.teacher_name}</small>` : ''}
                        </div>
                        <div class="lesson-actions">
                            ${isTeacher ? 
                                `<button onclick="editLesson(${lesson.id})" class="btn btn-secondary">Edit</button>
                                 <button onclick="generateQRCode(${lesson.id}, 'lesson', '${lesson.title}')" class="btn btn-primary">QR Code</button>` :
                                `<button onclick="viewLesson(${lesson.id})" class="btn btn-primary">View Lesson</button>`
                            }
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        }
        
        html += '</div>';
        contentDiv.innerHTML = html;
    }

    renderActivities(activities) {
        const contentDiv = document.getElementById('content');
        const isTeacher = this.userRole === 'teacher';
        
        let html = `
            <div class="activities-section">
                <div class="section-header">
                    <h2>Activities</h2>
                    ${isTeacher ? '<button onclick="createActivity()" class="action-button"><i class="fas fa-plus"></i> Create Activity</button>' : ''}
                </div>
        `;

        if (activities.length === 0) {
            html += `
                <div class="empty-state">
                    <i class="fas fa-tasks fa-3x"></i>
                    <h3>No activities found</h3>
                    <p>${isTeacher ? 'Create your first activity to get started.' : 'No activities are available yet.'}</p>
                </div>
            `;
        } else {
            html += '<div class="activities-grid">';
            activities.forEach(activity => {
                html += `
                    <div class="activity-card">
                        <div class="activity-header">
                            <h3>${activity.title}</h3>
                            <span class="activity-type">${activity.type}</span>
                        </div>
                        <div class="activity-content">
                            <p>${activity.description || 'No description available'}</p>
                            ${activity.teacher_name ? `<small>by ${activity.teacher_name}</small>` : ''}
                            ${activity.due_date ? `<div class="due-date">Due: ${new Date(activity.due_date).toLocaleDateString()}</div>` : ''}
                        </div>
                        <div class="activity-actions">
                            ${isTeacher ? 
                                `<button onclick="editActivity(${activity.id})" class="btn btn-secondary">Edit</button>
                                 <button onclick="generateQRCode(${activity.id}, 'activity', '${activity.title}')" class="btn btn-primary">QR Code</button>` :
                                `<button onclick="startActivity(${activity.id})" class="btn btn-primary">Start Activity</button>`
                            }
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        }
        
        html += '</div>';
        contentDiv.innerHTML = html;
    }

    renderAnalytics(overview) {
        const contentDiv = document.getElementById('content');
        contentDiv.innerHTML = `
            <div class="analytics-section">
                <h2>Analytics Overview</h2>
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <h3>Content Overview</h3>
                        <div class="analytics-stats">
                            <div class="stat-item">
                                <span class="stat-number">${overview.total_lessons}</span>
                                <span class="stat-label">Total Lessons</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">${overview.total_activities}</span>
                                <span class="stat-label">Total Activities</span>
                            </div>
                        </div>
                    </div>
                    <div class="analytics-card">
                        <h3>Student Engagement</h3>
                        <div class="analytics-stats">
                            <div class="stat-item">
                                <span class="stat-number">${overview.total_students}</span>
                                <span class="stat-label">Active Students</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">${overview.average_score}%</span>
                                <span class="stat-label">Average Score</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    renderLibrary(lessons, activities) {
        const contentDiv = document.getElementById('content');
        contentDiv.innerHTML = `
            <div class="library-section">
                <h2>Learning Library</h2>
                
                <div class="library-tabs">
                    <button class="tab-button active" onclick="showLibraryTab('lessons')">Lessons</button>
                    <button class="tab-button" onclick="showLibraryTab('activities')">Activities</button>
                </div>
                
                <div id="lessons-tab" class="tab-content active">
                    <div class="lessons-grid">
                        ${lessons.map(lesson => `
                            <div class="lesson-card">
                                <h3>${lesson.title}</h3>
                                <p>${lesson.content.substring(0, 100)}...</p>
                                <small>by ${lesson.teacher_name}</small>
                                <button onclick="viewLesson(${lesson.id})" class="btn btn-primary">View Lesson</button>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <div id="activities-tab" class="tab-content">
                    <div class="activities-grid">
                        ${activities.map(activity => `
                            <div class="activity-card">
                                <h3>${activity.title}</h3>
                                <p>${activity.description || 'No description'}</p>
                                <small>by ${activity.teacher_name}</small>
                                <button onclick="startActivity(${activity.id})" class="btn btn-primary">Start Activity</button>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    }

    showProfile() {
        const contentDiv = document.getElementById('content');
        contentDiv.innerHTML = `
            <div class="coming-soon">
                <i class="fas fa-user-circle fa-3x"></i>
                <h2>Profile Management</h2>
                <p>Profile management features are coming soon!</p>
                <p>You'll be able to update your personal information, change your password, and manage your account settings.</p>
            </div>
        `;
    }

    showSettings() {
        const contentDiv = document.getElementById('content');
        contentDiv.innerHTML = `
            <div class="coming-soon">
                <i class="fas fa-cog fa-3x"></i>
                <h2>Settings</h2>
                <p>Settings panel is coming soon!</p>
                <p>You'll be able to customize your learning experience, notification preferences, and system settings.</p>
            </div>
        `;
    }

    showError(message) {
        const contentDiv = document.getElementById('content');
        contentDiv.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle fa-3x"></i>
                <h2>Connection Error</h2>
                <p>${message}</p>
                <button onclick="location.reload()" class="action-button">
                    <i class="fas fa-refresh"></i> Retry
                </button>
            </div>
        `;
    }

    setActiveNavItem(section) {
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });
        
        const navItem = document.querySelector(`.nav-item.${section}`);
        if (navItem && !navItem.classList.contains('guest-disabled')) {
            navItem.classList.add('active');
        }
    }
}

// Global functions for backward compatibility
window.showLibraryTab = function(tab) {
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    document.querySelector(`[onclick="showLibraryTab('${tab}')"]`).classList.add('active');
    document.getElementById(`${tab}-tab`).classList.add('active');
};

// Initialize dashboard loader when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardLoader = new DashboardLoader();
});

// Override the loadContent function for navigation
window.loadContent = function(section) {
    if (window.dashboardLoader) {
        window.dashboardLoader.loadContent(section);
    }
};

window.loadStudentContent = function(section) {
    if (window.dashboardLoader) {
        window.dashboardLoader.loadContent(section);
    }
};