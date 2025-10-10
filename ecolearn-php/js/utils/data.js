// Data Models and Sample Data

// Teacher Profile Data
let userProfileData = {
    name: "New Teacher",
    role: "Teacher (Unregistered)",
    email: "email@example.com",
    phone: "N/A",
    address: "N/A",
    joined: "Today",
    profilePicUrl: ""
};

// Student Profile Data
let studentProfileData = {
    name: "New Student",
    role: "Student",
    email: "student@example.com",
    phone: "N/A",
    address: "N/A",
    joined: "Today",
    profilePicUrl: ""
};

// Lessons Data
let lessonsData = [
    { id: 1, title: 'Water Cycle & Hydrology', grade: 'Grade 8', activities: 3, status: 'published', views: 150, classes: 3, lastEdit: null },
    { id: 2, title: 'Global Climate Change Basics', grade: 'Grade 9', activities: 1, status: 'draft', views: 5, classes: 1, lastEdit: '2 days ago' },
    { id: 3, title: 'Biodiversity & Ecosystems (v1)', grade: 'Grade 7', activities: 2, status: 'archived', views: 200, classes: 5, lastEdit: null }
];

// Activities Data
let activitiesData = [
    { id: 101, title: 'Water Cycle Simulation Lab', type: 'Simulation', status: 'open', submissions: 28, graded: 25, dueDate: '2025-10-15', relatedLesson: 'Water Cycle & Hydrology' },
    { id: 102, title: 'Local Pollution Photo Essay', type: 'Project', status: 'draft', submissions: 0, graded: 0, dueDate: '2025-11-01', relatedLesson: 'Pollution Sources' },
    { id: 103, title: 'Climate Change Impact Quiz', type: 'Quiz', status: 'closed', submissions: 30, graded: 30, dueDate: '2025-10-01', relatedLesson: 'Global Climate Change Basics' }
];

// Analytics Data
const analyticsData = {
    overallTrend: [75, 78, 80, 85, 82, 88], 
    overallTrendLabels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
    activityAvgScore: 88.2,
    engagement: { totalUsers: 250, lessonEngagers: 250, activityParticipants: 155, signedIn: 200, anonymous: 50 },
    dailyEngagement: [15, 20, 18, 25, 30, 10, 5], 
    dailyEngagementLabels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    weeklyAvgScore: [75, 78, 80, 85, 82, 88], 
    weeklyAvgScoreLabels: ['Wk 1', 'Wk 2', 'Wk 3', 'Wk 4', 'Wk 5', 'Wk 6'],
    monthlyAvgScore: [72, 80, 85, 88, 90], 
    monthlyAvgScoreLabels: ['May', 'Jun', 'Jul', 'Aug', 'Sep'],
    studentRecords: [
        { id: 'S1001', name: 'A. Johnson', type: 'Signed-In', totalScore: 92, lastActivity: 'Water Cycle Quiz', activityCount: 7, scores: [{name: 'Quiz 1', score: 95}, {name: 'Essay', score: 88}] },
        { id: 'S1002', name: 'M. Chen', type: 'Signed-In', totalScore: 88, lastActivity: 'Pollution Essay', activityCount: 5, scores: [{name: 'Quiz 1', score: 85}, {name: 'Essay', score: 90}] },
        { id: 'A2045', name: 'Anonymous User A', type: 'Anonymous', totalScore: 95, lastActivity: 'Climate Change Quiz', activityCount: 1, scores: [{name: 'CC Quiz', score: 95}] },
        { id: 'S1003', name: 'L. Patel', type: 'Signed-In', totalScore: 79, lastActivity: 'Biodiversity Lab', activityCount: 8, scores: [{name: 'Quiz 1', score: 75}, {name: 'Lab Report', score: 83}] },
        { id: 'A9182', name: 'Anonymous User B', type: 'Anonymous', totalScore: 81, lastActivity: 'Water Cycle Quiz', activityCount: 1, scores: [{name: 'WC Quiz', score: 81}] },
    ]
};