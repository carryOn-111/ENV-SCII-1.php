// Dynamic Data Manager - Replaces static data with API calls
class DynamicDataManager {
    constructor() {
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
    }

    async getCachedData(key, fetchFunction) {
        const cached = this.cache.get(key);
        if (cached && (Date.now() - cached.timestamp) < this.cacheTimeout) {
            return cached.data;
        }

        try {
            const data = await fetchFunction();
            this.cache.set(key, {
                data: data,
                timestamp: Date.now()
            });
            return data;
        } catch (error) {
            console.error(`Failed to fetch ${key}:`, error);
            // Return cached data if available, even if expired
            return cached ? cached.data : null;
        }
    }

    // Student Data Functions
    async getStudentDashboardStats() {
        return this.getCachedData('student_stats', async () => {
            const response = await apiClient.getStudentStats();
            return response.stats;
        });
    }

    async getStudentDeadlines() {
        return this.getCachedData('student_deadlines', async () => {
            const response = await apiClient.getStudentDeadlines();
            return response.deadlines;
        });
    }

    async getStudentScores() {
        return this.getCachedData('student_scores', async () => {
            const response = await apiClient.getStudentScores();
            return response;
        });
    }

    // Teacher Data Functions
    async getTeacherStats() {
        return this.getCachedData('teacher_stats', async () => {
            const response = await apiClient.getTeacherStats();
            return response.stats;
        });
    }

    async getLessonsData() {
        return this.getCachedData('lessons_data', async () => {
            const response = await apiClient.getLessons();
            return response.lessons;
        });
    }

    async getActivitiesData() {
        return this.getCachedData('activities_data', async () => {
            const response = await apiClient.getActivities();
            return response.activities;
        });
    }

    // Shared Data Functions
    async getTrendingLessons() {
        return this.getCachedData('trending_lessons', async () => {
            const response = await apiClient.getTrendingLessons();
            return response.lessons;
        });
    }

    // Clear cache when data is updated
    clearCache(key = null) {
        if (key) {
            this.cache.delete(key);
        } else {
            this.cache.clear();
        }
    }

    // Format data for display
    formatDeadline(deadline) {
        const dueDate = new Date(deadline.due_date);
        const now = new Date();
        const diffTime = dueDate - now;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        let dueDateText;
        if (diffDays === 0) {
            dueDateText = 'Due: Today';
        } else if (diffDays === 1) {
            dueDateText = 'Due: Tomorrow';
        } else if (diffDays > 1) {
            dueDateText = `Due: ${dueDate.toLocaleDateString()}`;
        } else {
            dueDateText = 'Overdue';
        }

        return {
            ...deadline,
            formatted_due_date: dueDateText,
            is_overdue: diffDays < 0
        };
    }

    formatScore(score) {
        if (score === null || score === undefined) return 'N/A';
        return Math.round(score) + '%';
    }

    getActivityIcon(type) {
        const icons = {
            'quiz': 'fas fa-clipboard-question',
            'simulation': 'fas fa-flask',
            'project': 'fas fa-project-diagram',
            'essay': 'fas fa-pen-fancy',
            'lab': 'fas fa-microscope',
            'default': 'fas fa-tasks'
        };
        return icons[type] || icons.default;
    }

    getLessonIcon(type) {
        const icons = {
            'lesson': 'fas fa-scroll',
            'activity': 'fas fa-flask',
            'grade': 'fas fa-clipboard-question',
            'default': 'fas fa-book-open'
        };
        return icons[type] || icons.default;
    }
}

// Global instance
const dynamicData = new DynamicDataManager();