// Enhanced Data Management for PHP Backend Integration
class DataManager {
    constructor() {
        this.apiEndpoint = '/api'; // Will be configured for PHP backend
        this.isOnline = navigator.onLine;
        this.syncQueue = [];
        
        // Listen for online/offline events
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
    }

    // Lesson Data Management
    async saveLessonData(lessonData) {
        const dataToSave = {
            ...lessonData,
            updated_at: new Date().toISOString(),
            sync_status: this.isOnline ? 'synced' : 'pending'
        };

        // Save locally first
        localStorage.setItem(`lesson_${lessonData.id}`, JSON.stringify(dataToSave));

        // Attempt to sync with PHP backend
        if (this.isOnline) {
            try {
                await this.syncLessonToBackend(dataToSave);
            } catch (error) {
                console.warn('Failed to sync lesson to backend:', error);
                this.addToSyncQueue('lesson', dataToSave);
            }
        } else {
            this.addToSyncQueue('lesson', dataToSave);
        }

        return dataToSave;
    }

    async loadLessonData(lessonId) {
        // Try to load from backend first if online
        if (this.isOnline) {
            try {
                const response = await fetch(`${this.apiEndpoint}/lessons/${lessonId}`);
                if (response.ok) {
                    const data = await response.json();
                    // Update local storage with fresh data
                    localStorage.setItem(`lesson_${lessonId}`, JSON.stringify(data));
                    return data;
                }
            } catch (error) {
                console.warn('Failed to load lesson from backend:', error);
            }
        }

        // Fallback to local storage
        const localData = localStorage.getItem(`lesson_${lessonId}`);
        return localData ? JSON.parse(localData) : null;
    }

    // Activity Data Management
    async saveActivityData(activityData) {
        const dataToSave = {
            ...activityData,
            updated_at: new Date().toISOString(),
            sync_status: this.isOnline ? 'synced' : 'pending'
        };

        // Save locally first
        localStorage.setItem(`activity_${activityData.id}`, JSON.stringify(dataToSave));

        // Attempt to sync with PHP backend
        if (this.isOnline) {
            try {
                await this.syncActivityToBackend(dataToSave);
            } catch (error) {
                console.warn('Failed to sync activity to backend:', error);
                this.addToSyncQueue('activity', dataToSave);
            }
        } else {
            this.addToSyncQueue('activity', dataToSave);
        }

        return dataToSave;
    }

    async loadActivityData(activityId) {
        // Try to load from backend first if online
        if (this.isOnline) {
            try {
                const response = await fetch(`${this.apiEndpoint}/activities/${activityId}`);
                if (response.ok) {
                    const data = await response.json();
                    // Update local storage with fresh data
                    localStorage.setItem(`activity_${activityId}`, JSON.stringify(data));
                    return data;
                }
            } catch (error) {
                console.warn('Failed to load activity from backend:', error);
            }
        }

        // Fallback to local storage
        const localData = localStorage.getItem(`activity_${activityId}`);
        return localData ? JSON.parse(localData) : null;
    }

    // Student Dashboard Integration
    async getContentForStudentDashboard() {
        const content = {
            lessons: [],
            activities: []
        };

        // Get all published lessons
        const publishedLessons = lessonsData.filter(lesson => lesson.status === 'published');
        for (const lesson of publishedLessons) {
            const lessonData = await this.loadLessonData(lesson.id);
            if (lessonData) {
                content.lessons.push({
                    id: lesson.id,
                    title: lesson.title,
                    slides: lessonData.slides || [],
                    views: lesson.views,
                    type: 'lesson'
                });
            }
        }

        // Get all open activities
        const openActivities = activitiesData.filter(activity => activity.status === 'open');
        for (const activity of openActivities) {
            const activityData = await this.loadActivityData(activity.id);
            if (activityData) {
                content.activities.push({
                    id: activity.id,
                    title: activity.title,
                    type: activity.type,
                    dueDate: activity.dueDate,
                    totalPoints: activityData.total_points || 0,
                    questions: activityData.questions || []
                });
            }
        }

        return content;
    }

    // Backend Sync Methods (for PHP integration)
    async syncLessonToBackend(lessonData) {
        const response = await fetch(`${this.apiEndpoint}/lessons`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.getAuthToken()}`
            },
            body: JSON.stringify(lessonData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
    }

    async syncActivityToBackend(activityData) {
        const response = await fetch(`${this.apiEndpoint}/activities`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.getAuthToken()}`
            },
            body: JSON.stringify(activityData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
    }

    // Sync Queue Management
    addToSyncQueue(type, data) {
        this.syncQueue.push({
            type,
            data,
            timestamp: Date.now()
        });
        
        // Save sync queue to localStorage
        localStorage.setItem('syncQueue', JSON.stringify(this.syncQueue));
    }

    async processSyncQueue() {
        if (!this.isOnline || this.syncQueue.length === 0) return;

        const queue = [...this.syncQueue];
        this.syncQueue = [];

        for (const item of queue) {
            try {
                if (item.type === 'lesson') {
                    await this.syncLessonToBackend(item.data);
                } else if (item.type === 'activity') {
                    await this.syncActivityToBackend(item.data);
                }
                console.log(`Successfully synced ${item.type} ${item.data.id}`);
            } catch (error) {
                console.error(`Failed to sync ${item.type} ${item.data.id}:`, error);
                // Re-add to queue for retry
                this.syncQueue.push(item);
            }
        }

        // Update localStorage
        localStorage.setItem('syncQueue', JSON.stringify(this.syncQueue));
    }

    // Network Event Handlers
    handleOnline() {
        this.isOnline = true;
        console.log('Connection restored. Processing sync queue...');
        this.processSyncQueue();
    }

    handleOffline() {
        this.isOnline = false;
        console.log('Connection lost. Data will be queued for sync.');
    }

    // Utility Methods
    getAuthToken() {
        // This will be implemented when PHP authentication is added
        return localStorage.getItem('authToken') || '';
    }

    // Export data for backup
    exportAllData() {
        const exportData = {
            lessons: {},
            activities: {},
            timestamp: new Date().toISOString()
        };

        // Export all lessons
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith('lesson_')) {
                const lessonId = key.replace('lesson_', '');
                exportData.lessons[lessonId] = JSON.parse(localStorage.getItem(key));
            } else if (key.startsWith('activity_')) {
                const activityId = key.replace('activity_', '');
                exportData.activities[activityId] = JSON.parse(localStorage.getItem(key));
            }
        }

        return exportData;
    }

    // Import data from backup
    importData(importData) {
        if (importData.lessons) {
            Object.keys(importData.lessons).forEach(lessonId => {
                localStorage.setItem(`lesson_${lessonId}`, JSON.stringify(importData.lessons[lessonId]));
            });
        }

        if (importData.activities) {
            Object.keys(importData.activities).forEach(activityId => {
                localStorage.setItem(`activity_${activityId}`, JSON.stringify(importData.activities[activityId]));
            });
        }

        console.log('Data imported successfully');
    }

    // Clear all local data (for testing/reset)
    clearAllData() {
        const keysToRemove = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith('lesson_') || key.startsWith('activity_') || key === 'syncQueue') {
                keysToRemove.push(key);
            }
        }

        keysToRemove.forEach(key => localStorage.removeItem(key));
        this.syncQueue = [];
        console.log('All local data cleared');
    }

    // Get sync status
    getSyncStatus() {
        return {
            isOnline: this.isOnline,
            queueLength: this.syncQueue.length,
            lastSync: localStorage.getItem('lastSyncTime') || 'Never'
        };
    }
}

// Global data manager instance
const dataManager = new DataManager();

// Load sync queue from localStorage on initialization
const savedQueue = localStorage.getItem('syncQueue');
if (savedQueue) {
    dataManager.syncQueue = JSON.parse(savedQueue);
}

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DataManager;
}