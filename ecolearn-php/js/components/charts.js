// Chart Component Functions
let analyticsChartInstances = {};
let trendChartInstance = null;
let studentChartInstance = null;

function initPerformanceChart() {
    const ctx = document.getElementById('performanceChart');
    if (analyticsChartInstances.dashboardChart) {
        analyticsChartInstances.dashboardChart.destroy();
    }

    if (ctx) {
        analyticsChartInstances.dashboardChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Water Cycle', 'Bio Diversity', 'Climate Change', 'Sustainability', 'Pollution'],
                datasets: [{
                    label: 'Class Average Score (%)',
                    data: [85, 72, 90, 78, 88], 
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.7)', 
                        'rgba(52, 152, 219, 0.7)', 
                        'rgba(241, 196, 15, 0.7)', 
                        'rgba(230, 126, 34, 0.7)', 
                        'rgba(155, 89, 182, 0.7)'  
                    ],
                    borderColor: [
                        'rgba(46, 204, 113, 1)',
                        'rgba(52, 152, 219, 1)',
                        'rgba(241, 196, 15, 1)',
                        'rgba(230, 126, 34, 1)',
                        'rgba(155, 89, 182, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    title: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Score (%)'
                        },
                        grid: { color: '#ecf0f1' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
}

function drawStudentPerformanceChart() {
    if(studentChartInstance) studentChartInstance.destroy();
    const ctx = document.getElementById('studentPerformanceChart');
    if (ctx) {
         studentChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Quiz 1', 'Proj 1', 'Quiz 2', 'Lab 1', 'Quiz 3'],
                datasets: [{
                    label: 'My Score (%)',
                    data: [75, 88, 79, 92, 95], 
                    borderColor: 'var(--primary-color)',
                    backgroundColor: 'rgba(46, 204, 113, 0.2)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: { display: true, text: 'Score (%)' }
                    }
                }
            }
        });
    }
}

function updateTrendChart(timeframe) {
    const ctx = document.getElementById('combinedTrendChart');
    if (!ctx) return;

    if (trendChartInstance) {
        trendChartInstance.destroy();
    }

    let data, labels, chartType, title, color, yAxisMax;

    if (timeframe === 'daily') {
        data = analyticsData.dailyEngagement;
        labels = analyticsData.dailyEngagementLabels;
        chartType = 'bar';
        title = 'Total Platform Accesses';
        color = 'rgba(52, 152, 219, 0.7)';
        yAxisMax = 35;
    } else if (timeframe === 'weekly') {
        data = analyticsData.weeklyAvgScore;
        labels = analyticsData.weeklyAvgScoreLabels;
        chartType = 'line';
        title = 'Weekly Average Score (%)';
        color = 'var(--primary-color)';
        yAxisMax = 100;
    } else {
        data = analyticsData.monthlyAvgScore;
        labels = analyticsData.monthlyAvgScoreLabels;
        chartType = 'bar';
        title = 'Monthly Average Score (%)';
        color = 'rgba(155, 89, 182, 0.7)';
        yAxisMax = 100;
    }

    trendChartInstance = new Chart(ctx, {
        type: chartType,
        data: {
            labels: labels,
            datasets: [{
                label: title,
                data: data,
                borderColor: chartType === 'line' ? color : 'transparent',
                backgroundColor: chartType === 'line' ? 'rgba(46, 204, 113, 0.2)' : color,
                fill: chartType === 'line',
                tension: chartType === 'line' ? 0.4 : 0,
                pointBackgroundColor: chartType === 'line' ? color : 'transparent',
                borderWidth: chartType === 'bar' ? 1 : 2,
                borderRadius: chartType === 'bar' ? 4 : 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: yAxisMax,
                    title: { display: true, text: (timeframe === 'daily') ? 'Accesses' : 'Score (%)' }
                }
            }
        }
    });
    analyticsChartInstances.combinedTrend = trendChartInstance;
}