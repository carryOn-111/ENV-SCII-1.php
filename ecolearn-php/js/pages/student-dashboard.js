// Student Dashboard Page Functions
function loadStudentContent(section) {
  const content = document.getElementById('content');
  let html = '';
  
  setActiveNavItem(section);

  switch(section) {
    case 'dashboard':
        html = `
            <h2>Welcome Back, [Student Name]! 🧑‍🎓</h2>
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
                    <div class="value">85%</div>
                    <div class="label">Overall Avg Score</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-hourglass-start" style="color: var(--status-draft);"></i>
                    <div class="value">3</div>
                    <div class="label">Pending Assignments</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-book-open" style="color: var(--primary-color);"></i>
                    <div class="value">5/10</div>
                    <div class="label">Lessons In-Progress</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-times" style="color: var(--status-archived);"></i>
                    <div class="value">1</div>
                    <div class="label">Overdue Activities</div>
                </div>
            </div>
            
            <div class="action-card dashboard-top-margin">
                <h3>Upcoming Deadlines</h3>
                <div class="activity-log">
                    <div class="recent-activity-item">
                        <div class="activity-icon activity"><i class="fas fa-flask"></i></div>
                        <div class="activity-details">
                            <strong>Water Cycle Simulation Lab</strong>
                            <span>Due: Tomorrow, 11:59 PM</span>
                        </div>
                    </div>
                    <div class="recent-activity-item">
                        <div class="activity-icon lesson"><i class="fas fa-scroll"></i></div>
                        <div class="activity-details">
                            <strong>Lesson: Global Climate Change Basics</strong>
                            <span>Complete by: Fri, Oct 10th</span>
                        </div>
                    </div>
                    <div class="recent-activity-item">
                        <div class="activity-icon grade"><i class="fas fa-clipboard-question"></i></div>
                        <div class="activity-details">
                            <strong>Quiz 2: Biodiversity & Ecosystems</strong>
                            <span>Due: Mon, Oct 13th</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        break;

    case 'catalogue':
        html = `
            <div class="lessons-container">
                <div class="catalogue-header">
                    <div>
                        <h2>Course Catalogue & Library 📚</h2>
                        <p class="subtitle">Explore all available environmental science lessons and recommended activities.</p>
                    </div>
                    <div class="catalogue-search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search lessons, activities..." onchange="customAlert('Searching for: ' + this.value)">
                    </div>
                </div>
                
                <div class="catalogue-row-section">
                    <h3>🔥 Trending Lessons</h3>
                    <div class="catalogue-slider">
                        <div class="scroll-arrow left" onclick="scrollCatalogue('trendingSlider', 'left')"><i class="fas fa-chevron-left"></i></div>
                        <div class="slider-container" id="trendingSlider">
                            
                            <div class="lesson-card status-trending">
                                <div class="lesson-header-status">
                                    <span class="status-badge trending">Popular</span>
                                </div>
                                <h4>Introduction to Renewable Energy</h4>
                                <p class="topic-detail">Grade Level: 9-12 | Topics: 7</p>
                                <div class="lesson-metrics">
                                    <span><i class="fas fa-star"></i> Rating: 4.8/5.0</span>
                                    <span><i class="fas fa-user-graduate"></i> 550 Students</span>
                                </div>
                                <button class="action-small-btn view-btn" onclick="customAlert('Enrolling in course...')"><i class="fas fa-arrow-right"></i> Enroll Now</button>
                            </div>
                            
                            <div class="lesson-card status-recommended">
                                <div class="lesson-header-status">
                                    <span class="status-badge recommended">Recommended</span>
                                </div>
                                <h4>Forest Ecosystems and Deforestation</h4>
                                <p class="topic-detail">Grade Level: 7-9 | Topics: 5</p>
                                <div class="lesson-metrics">
                                    <span><i class="fas fa-tags"></i> Related: Biodiversity</span>
                                    <span><i class="fas fa-book-open"></i> Preview Lesson</span>
                                
                                </div>
                                <button class="action-small-btn edit-btn" onclick="customAlert('Enrolling in course...')"><i class="fas fa-arrow-right"></i> Enroll Now</button>
                            </div>
                            
                            <div class="lesson-card status-published">
                                <div class="lesson-header-status">
                                    <span class="status-badge published">Hot Topic</span>
                                </div>
                                <h4>The Science of Ocean Acidification</h4>
                                <p class="topic-detail">Grade Level: 10-12 | Topics: 6</p>
                                <div class="lesson-metrics">
                                    <span><i class="fas fa-calendar-alt"></i> Added: 3 Weeks Ago</span>
                                    <span><i class="fas fa-eye"></i> 480 Views</span>
                                </div>
                                <button class="action-small-btn view-btn" onclick="customAlert('Enrolling in course...')"><i class="fas fa-arrow-right"></i> Enroll Now</button>
                            </div>
                        </div>
                        <div class="scroll-arrow right" onclick="scrollCatalogue('trendingSlider', 'right')"><i class="fas fa-chevron-right"></i></div>
                    </div>
                </div>
            </div>
        `;
        break;

    case 'progress':
        html = `
            <div class="analytics-container">
                <h2>My Progress & Scores</h2>
                <p class="subtitle">A summary of your performance across all completed activities and lessons.</p>
                
                <div class="dashboard-main-area"> 
        
                    <div class="chart-card progress-score-list">
                        <h3 class="progress-score-header">Latest Activity Scores</h3>
                        <div class="leaderboard-list">
                            <div class="score-detail">
                                <span><i class="fas fa-list-alt" style="margin-right: 5px; color: #7f8c8d;"></i> Biodiversity Quiz 2</span>
                                <span class="score-detail-high">95%</span>
                            </div>
                            <div class="score-detail">
                                <span><i class="fas fa-list-alt" style="margin-right: 5px; color: #7f8c8d;"></i> Climate Change Quiz</span>
                                <span class="score-detail-mid">79%</span>
                            </div>
                            <div class="score-detail">
                                <span><i class="fas fa-list-alt" style="margin-right: 5px; color: #7f8c8d;"></i> Pollution Mini-Essay</span>
                                <span class="score-detail-low">88%</span>
                            </div>
                            <p class="subtitle score-footer-text">Scores dynamically fetched here.</p>
                        </div>
                        <button class="action-button alt-button dashboard-top-margin" style="width: 100%;" onclick="customAlert('Loading full score history.')">
                            <i class="fas fa-file-alt"></i> View Full Score History
                        </button>
                    </div>
                    
                    <div class="chart-card chart-card-flex-2">
                        <h3 class="progress-score-header">Performance Trend (Last 5 Quizzes)</h3>
                        <div class="chart-container" style="height: 250px;"><canvas id="studentPerformanceChart"></canvas></div>
                    </div>
                </div>
            </div>
        `;
        setTimeout(() => drawStudentPerformanceChart(), 10);
      break;

    case 'profile':
        let profilePicHtml = '';
        const nameParts = studentProfileData.name.split(' ');
        const firstWord = nameParts[0].toUpperCase();

        if (studentProfileData.profilePicUrl) {
            profilePicHtml = `<div class="student-avatar" style="width: 100px; height: 100px; font-size: 2.5rem; background-image: url('${studentProfileData.profilePicUrl}'); background-size: cover; background-position: center; border: 3px solid var(--secondary-color);"></div>`;
        } else {
            profilePicHtml = `<div class="student-avatar" style="width: 100px; height: 100px; font-size: 1.5rem; line-height: 100px; padding: 0 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; background-color: var(--secondary-color);">${firstWord}</div>`;
        }

        html = `
            <div class="profile-container chart-card">
                <div class="profile-header">
                    ${profilePicHtml}
                    <div>
                        <h2 id="userName">${studentProfileData.name}</h2>
                        <p class="role" id="userRole" style="color: var(--primary-color);">${studentProfileData.role}</p>
                        <button class="edit-btn action-small-btn edit-btn" style="background-color: var(--secondary-color);" onclick="openEditModal()">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </button>
                    </div>
                </div>

                <div class="info-section">
                    <h3 style="color: var(--secondary-color);">Contact Information</h3>
                    <div class="info-grid" id="infoGrid">
                        <div class="stat-card" style="padding: 15px;">Email: <strong>${studentProfileData.email}</strong></div>
                        <div class="stat-card" style="padding: 15px;">Phone: <strong>${studentProfileData.phone}</strong></div>
                        <div class="stat-card" style="padding: 15px;">Address: <strong>${studentProfileData.address}</strong></div>
                        <div class="stat-card" style="padding: 15px;">Joined: <strong>${studentProfileData.joined}</strong></div>
                    </div>
                </div>

                <h3 style="color: var(--secondary-color);">Learning Summary</h3>
                <div class="features dashboard-grid" style="margin-top: 15px;">
                    <div class="feature-card chart-card">
                        <h4>Overall Average Score</h4>
                        <p style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">85%</p>
                    </div>
                    <div class="feature-card chart-card">
                        <h4>Completed Lessons</h4>
                        <p style="font-size: 1.5rem; font-weight: 700;">5 / 10</p>
                    </div>
                    <div class="feature-card chart-card">
                        <h4>Assignments Due</h4>
                        <p style="font-size: 1.5rem; font-weight: 700; color: var(--status-archived);">1 Overdue</p>
                    </div>
                </div>

                <button class="logout-btn action-button delete-btn" style="width: 200px; margin-top: 25px;" onclick="handleLogout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        `;
        break;

    default:
      html = `<p class="welcome-text">Select a section from the navigation menu to begin your learning journey.</p>`;
  }
  
  content.innerHTML = html;
}