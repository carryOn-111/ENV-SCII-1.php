// Dynamic Student Dashboard - Connected to PHP Backend
async function loadStudentContent(section) {
    const content = document.getElementById('content');
    let html = '';
    
    setActiveNavItem(section);

    switch(section) {
        case 'dashboard':
            html = await renderStudentDashboard();
            break;

        case 'catalogue':
            html = await renderStudentCatalogue();
            break;

        case 'progress':
            html = await renderStudentProgress();
            break;

        case 'profile':
            html = renderStudentProfile();
            break;

        default:
            html = `<p class="welcome-text">Select a section from the navigation menu to begin your learning journey.</p>`;
    }
    
    content.innerHTML = html;
    
    // Load charts if needed
    if (section === 'progress') {
        setTimeout(() => drawStudentPerformanceChart(), 100);
    }
}

async function renderStudentDashboard() {
    try {
        const stats = await dynamicData.getStudentDashboardStats();
        const deadlines = await dynamicData.getStudentDeadlines();
        
        const deadlinesHtml = deadlines.map(deadline => {
            const formatted = dynamicData.formatDeadline(deadline);
            const icon = dynamicData.getActivityIcon(deadline.type);
            
            return `
                <div class="recent-activity-item">
                    <div class="activity-icon activity"><i class="${icon}"></i></div>
                    <div class="activity-details">
                        <strong>${deadline.title}</strong>
                        <span>${formatted.formatted_due_date}</span>
                    </div>
                </div>
            `;
        }).join('');

        return `
            <h2>Welcome Back, Student! 🧑‍🎓</h2>
            <p class="subtitle">Quickly access lessons, check deadlines, or scan a code to start a new activity.</p>
            
            <div class="action-card qr-focus-card">
                <h3>QR Code Quick Access</h3>
                <button class="action-button primary" onclick="showModal('qrScannerModal')">
                    <i class="fas fa-qrcode"></i> **Scan QR Code with Camera**
                </button>
                <button class="action-button alt-button alt-button-top" onclick="customAlert('Opening code entry field...')">
                    <i class="fas fa-keyboard"></i> Enter Resource Code Manually
                </button>
            </div>
            
            <div class="dashboard-grid">
                <div class="stat-card">
                    <i class="fas fa-percent" style="color: #e67e22;"></i>
                    <div class="value">${stats.overall_avg_score}%</div>
                    <div class="label">Overall Avg Score</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-hourglass-start" style="color: var(--status-draft);"></i>
                    <div class="value">${stats.pending_assignments}</div>
                    <div class="label">Pending Assignments</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-book-open" style="color: var(--primary-color);"></i>
                    <div class="value">${stats.lessons_progress.completed}/${stats.lessons_progress.total}</div>
                    <div class="label">Lessons Progress</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-times" style="color: var(--status-archived);"></i>
                    <div class="value">${stats.overdue_activities}</div>
                    <div class="label">Overdue Activities</div>
                </div>
            </div>
            
            <div class="action-card dashboard-top-margin">
                <h3>Upcoming Deadlines</h3>
                <div class="activity-log">
                    ${deadlinesHtml || '<p style="text-align: center; color: #7f8c8d;">No upcoming deadlines</p>'}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading dashboard:', error);
        return `
            <div class="error-message">
                <h2>Unable to load dashboard</h2>
                <p>Please check your connection and try again.</p>
                <button class="action-button primary" onclick="loadStudentContent('dashboard')">
                    <i class="fas fa-refresh"></i> Retry
                </button>
            </div>
        `;
    }
}

async function renderStudentCatalogue() {
    try {
        const trendingLessons = await dynamicData.getTrendingLessons();
        
        const lessonsHtml = trendingLessons.map(lesson => {
            const statusClass = lesson.student_count > 100 ? 'trending' : 'recommended';
            const statusText = lesson.student_count > 100 ? 'Popular' : 'Recommended';
            const rating = lesson.avg_score ? (lesson.avg_score / 20).toFixed(1) : '4.5';
            
            return `
                <div class="lesson-card status-${statusClass}">
                    <div class="lesson-header-status">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <h4>${lesson.title}</h4>
                    <p class="topic-detail">Grade Level: ${lesson.grade_level || 'All'} | Activities: ${lesson.activity_count || 0}</p>
                    <div class="lesson-metrics">
                        <span><i class="fas fa-star"></i> Rating: ${rating}/5.0</span>
                        <span><i class="fas fa-user-graduate"></i> ${lesson.student_count || 0} Students</span>
                    </div>
                    <button class="action-small-btn view-btn" onclick="enrollInLesson(${lesson.id})">
                        <i class="fas fa-arrow-right"></i> Enroll Now
                    </button>
                </div>
            `;
        }).join('');

        return `
            <div class="lessons-container">
                <div class="catalogue-header">
                    <div>
                        <h2>Course Catalogue & Library 📚</h2>
                        <p class="subtitle">Explore all available environmental science lessons and recommended activities.</p>
                    </div>
                    <div class="catalogue-search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search lessons, activities..." onchange="searchLessons(this.value)">
                    </div>
                </div>
                
                <div class="catalogue-row-section">
                    <h3>🔥 Trending Lessons</h3>
                    <div class="catalogue-slider">
                        <div class="scroll-arrow left" onclick="scrollCatalogue('trendingSlider', 'left')"><i class="fas fa-chevron-left"></i></div>
                        <div class="slider-container" id="trendingSlider">
                            ${lessonsHtml}
                        </div>
                        <div class="scroll-arrow right" onclick="scrollCatalogue('trendingSlider', 'right')"><i class="fas fa-chevron-right"></i></div>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading catalogue:', error);
        return `
            <div class="error-message">
                <h2>Unable to load catalogue</h2>
                <p>Please check your connection and try again.</p>
            </div>
        `;
    }
}

async function renderStudentProgress() {
    try {
        const scoresData = await dynamicData.getStudentScores();
        const recentScores = scoresData.recent_scores || [];
        const performanceTrend = scoresData.performance_trend || [];
        
        const scoresHtml = recentScores.map(score => {
            const scoreValue = dynamicData.formatScore(score.score);
            const scoreClass = score.score >= 90 ? 'score-detail-high' : 
                             score.score >= 80 ? 'score-detail-mid' : 'score-detail-low';
            
            return `
                <div class="score-detail">
                    <span><i class="fas fa-list-alt" style="margin-right: 5px; color: #7f8c8d;"></i> ${score.title}</span>
                    <span class="${scoreClass}">${scoreValue}</span>
                </div>
            `;
        }).join('');

        // Store performance data for chart
        window.studentPerformanceData = performanceTrend;

        return `
            <div class="analytics-container">
                <h2>My Progress & Scores</h2>
                <p class="subtitle">A summary of your performance across all completed activities and lessons.</p>
                
                <div class="dashboard-main-area"> 
                    <div class="chart-card progress-score-list">
                        <h3 class="progress-score-header">Latest Activity Scores</h3>
                        <div class="leaderboard-list">
                            ${scoresHtml || '<p style="text-align: center; color: #7f8c8d;">No scores available yet</p>'}
                        </div>
                        <button class="action-button alt-button dashboard-top-margin" style="width: 100%;" onclick="viewFullScoreHistory()">
                            <i class="fas fa-file-alt"></i> View Full Score History
                        </button>
                    </div>
                    
                    <div class="chart-card chart-card-flex-2">
                        <h3 class="progress-score-header">Performance Trend (Last ${performanceTrend.length} Activities)</h3>
                        <div class="chart-container" style="height: 250px;"><canvas id="studentPerformanceChart"></canvas></div>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading progress:', error);
        return `
            <div class="error-message">
                <h2>Unable to load progress data</h2>
                <p>Please check your connection and try again.</p>
            </div>
        `;
    }
}

// Helper functions
function enrollInLesson(lessonId) {
    customAlert(`Enrolling in lesson ID: ${lessonId}`);
    // TODO: Implement actual enrollment logic
}

function searchLessons(query) {
    if (query.trim()) {
        customAlert(`Searching for: ${query}`);
        // TODO: Implement search functionality
    }
}

function viewFullScoreHistory() {
    customAlert('Loading full score history...');
    // TODO: Implement full score history view
}

// Update performance chart to use dynamic data
function drawStudentPerformanceChart() {
    const canvas = document.getElementById('studentPerformanceChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const performanceData = window.studentPerformanceData || [75, 78, 80, 85, 82, 88];
    
    if (window.studentChart) {
        window.studentChart.destroy();
    }
    
    window.studentChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: performanceData.map((_, index) => `Activity ${index + 1}`),
            datasets: [{
                label: 'Score (%)',
                data: performanceData,
                borderColor: 'var(--primary-color)',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}