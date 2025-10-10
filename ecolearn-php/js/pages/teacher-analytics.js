// Teacher Analytics Page Functions
function drawLeaderboard() {
    const leaderboardList = document.getElementById('leaderboardList');
    if (!leaderboardList) return;

    const sortedStudents = [...analyticsData.studentRecords].sort((a, b) => b.totalScore - a.totalScore);

    let listHtml = '';
    sortedStudents.forEach((student, index) => {
        const avatarChar = student.name.charAt(0).toUpperCase();
        const rank = index + 1;
        
        listHtml += `
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-weight: 700; color: var(--secondary-color); width: 20px;">#${rank}</span>
                    <div class="student-avatar ${student.type === 'Anonymous' ? 'anonymous' : ''}" style="width: 35px; height: 35px; font-size: 1rem;">
                        ${avatarChar}
                    </div>
                    <div style="font-weight: 600;">
                        ${student.name}
                        <span style="display: block; font-size: 0.75rem; color: #7f8c8d;">${student.type} | ID: ${student.id}</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 700; color: var(--text-color);">${student.totalScore}%</span>
                    <button onclick="viewStudentRecords('${student.id}')" style="background: var(--secondary-color); color: white; border: none; border-radius: 4px; padding: 5px 10px; font-size: 0.7rem; cursor: pointer;">
                        <i class="fas fa-file-alt"></i> View Record
                    </button>
                </div>
            </div>
        `;
    });

    leaderboardList.innerHTML = listHtml;
}

function viewStudentRecords(studentId) {
    const student = analyticsData.studentRecords.find(s => s.id === studentId);
    if (!student) {
        customAlert(`Error: Student ID ${studentId} not found.`);
        return;
    }

    document.getElementById('studentRecordName').textContent = student.name;
    document.getElementById('studentRecordId').textContent = `ID: ${student.id}`;
    document.getElementById('studentRecordType').textContent = `Type: ${student.type}`;
    document.getElementById('studentRecordScore').textContent = `Total Avg Score: ${student.totalScore}%`;

    const avatarEl = document.getElementById('studentAvatar');
    avatarEl.textContent = student.name.charAt(0).toUpperCase();
    
    avatarEl.classList.remove('anonymous');
    if (student.type === 'Anonymous') {
        avatarEl.classList.add('anonymous');
    }

    const scoreHistoryEl = document.getElementById('scoreHistory');
    let historyHtml = '';
    
    if (student.scores && student.scores.length > 0) {
        student.scores.forEach(score => {
            const scoreColor = score.score >= 80 ? 'var(--primary-color)' : (score.score >= 70 ? '#e67e22' : '#e74c3c');
            historyHtml += `
                <div class="score-detail">
                    <span><i class="fas fa-list-alt" style="margin-right: 5px; color: #7f8c8d;"></i> ${score.name}</span>
                    <span style="font-weight: 700; color: ${scoreColor};">${score.score}%</span>
                </div>
            `;
        });
    } else {
        historyHtml = '<p style="color: #7f8c8d; text-align: center; padding: 15px;">No detailed history available for this dummy user.</p>';
    }
    scoreHistoryEl.innerHTML = historyHtml;

    showModal('studentRecordsModal');
}

function drawAnalyticsDashboard() {
    drawLeaderboard();
    updateTrendChart('daily');
    
    const ctx = document.getElementById('userTypeDonutChart');
    if (analyticsChartInstances.donutChart) {
        analyticsChartInstances.donutChart.destroy();
    }
    if (ctx) {
        analyticsChartInstances.donutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Signed-In Students', 'Anonymous QR Users'],
                datasets: [{
                    data: [analyticsData.engagement.signedIn, analyticsData.engagement.anonymous], 
                    backgroundColor: ['var(--secondary-color)', '#7f8c8d'], 
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { position: 'bottom' },
                    title: { display: false }
                }
            }
        });
    }

    document.getElementById('analytic-activity-score').textContent = `${analyticsData.activityAvgScore}%`;
    document.getElementById('analytic-lesson-engagement').textContent = analyticsData.engagement.lessonEngagers;
    document.getElementById('analytic-activity-engagement').textContent = analyticsData.engagement.activityParticipants;
}