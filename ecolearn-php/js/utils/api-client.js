// API Client for connecting to PHP backend
class APIClient {
    constructor() {
        this.baseURL = '../api/';
    }

    async request(endpoint, options = {}) {
        const url = this.baseURL + endpoint;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'API request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Student Dashboard APIs
    async getStudentStats() {
        return this.request('dashboard.php?action=student_stats');
    }

    async getStudentDeadlines() {
        return this.request('dashboard.php?action=student_deadlines');
    }

    async getStudentScores() {
        return this.request('dashboard.php?action=student_scores');
    }

    // Teacher Dashboard APIs
    async getTeacherStats() {
        return this.request('dashboard.php?action=teacher_stats');
    }

    async getLessons() {
        return this.request('lessons.php?action=list');
    }

    async getActivities() {
        return this.request('activities.php?action=get_all');
    }

    // Shared APIs
    async getTrendingLessons() {
        return this.request('dashboard.php?action=trending_lessons');
    }

    // Lesson Management
    async createLesson(lessonData) {
        const formData = new FormData();
        Object.keys(lessonData).forEach(key => {
            formData.append(key, lessonData[key]);
        });

        return this.request('lessons.php', {
            method: 'POST',
            body: formData,
            headers: {} // Remove Content-Type to let browser set it for FormData
        });
    }

    async updateLesson(lessonId, lessonData) {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('lesson_id', lessonId);
        Object.keys(lessonData).forEach(key => {
            formData.append(key, lessonData[key]);
        });

        return this.request('lessons.php', {
            method: 'POST',
            body: formData,
            headers: {}
        });
    }

    async publishLesson(lessonId) {
        const formData = new FormData();
        formData.append('action', 'publish');
        formData.append('lesson_id', lessonId);

        return this.request('lessons.php', {
            method: 'POST',
            body: formData,
            headers: {}
        });
    }

    // Activity Management
    async createActivity(activityData) {
        const formData = new FormData();
        formData.append('action', 'create');
        Object.keys(activityData).forEach(key => {
            formData.append(key, activityData[key]);
        });

        return this.request('activities.php', {
            method: 'POST',
            body: formData,
            headers: {}
        });
    }

    async updateActivity(activityId, activityData) {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('id', activityId);
        Object.keys(activityData).forEach(key => {
            formData.append(key, activityData[key]);
        });

        return this.request('activities.php', {
            method: 'POST',
            body: formData,
            headers: {}
        });
    }

    async publishActivity(activityId) {
        const formData = new FormData();
        formData.append('action', 'publish');
        formData.append('id', activityId);

        return this.request('activities.php', {
            method: 'POST',
            body: formData,
            headers: {}
        });
    }
}

// Global API client instance
const apiClient = new APIClient();