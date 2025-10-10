// Student Dashboard Integration for Dynamic Content
class StudentIntegration {
    constructor() {
        this.studentLibrary = [];
        this.studentProgress = {};
        this.init();
    }

    async init() {
        await this.loadStudentLibrary();
        this.setupEventListeners();
    }

    // Load content for student library from teacher-created lessons and activities
    async loadStudentLibrary() {
        try {
            // Get published lessons
            const publishedLessons = lessonsData.filter(lesson => lesson.status === 'published');
            
            // Get open activities
            const openActivities = activitiesData.filter(activity => activity.status === 'open');

            this.studentLibrary = [];

            // Process lessons
            for (const lesson of publishedLessons) {
                const lessonData = await dataManager.loadLessonData(lesson.id);
                if (lessonData && lessonData.slides) {
                    this.studentLibrary.push({
                        id: lesson.id,
                        title: lesson.title,
                        type: 'lesson',
                        category: 'Environmental Science',
                        difficulty: this.calculateDifficulty(lessonData.slides),
                        duration: this.estimateDuration(lessonData.slides),
                        slides: lessonData.slides,
                        views: lesson.views || 0,
                        status: 'available',
                        createdBy: 'Teacher',
                        lastUpdated: lessonData.updated_at || new Date().toISOString()
                    });
                }
            }

            // Process activities
            for (const activity of openActivities) {
                const activityData = await dataManager.loadActivityData(activity.id);
                if (activityData && activityData.questions) {
                    this.studentLibrary.push({
                        id: activity.id,
                        title: activity.title,
                        type: 'activity',
                        activityType: activity.type,
                        category: 'Environmental Science',
                        difficulty: this.calculateActivityDifficulty(activityData.questions),
                        duration: this.estimateActivityDuration(activityData.questions),
                        questions: activityData.questions,
                        totalPoints: activityData.total_points || 0,
                        dueDate: activity.dueDate,
                        status: 'available',
                        createdBy: 'Teacher',
                        lastUpdated: activityData.updated_at || new Date().toISOString()
                    });
                }
            }

            // Update student dashboard if it exists
            this.updateStudentDashboard();
            
            console.log(`Loaded ${this.studentLibrary.length} items for student library`);
        } catch (error) {
            console.error('Error loading student library:', error);
        }
    }

    // Calculate lesson difficulty based on content complexity
    calculateDifficulty(slides) {
        if (!slides || slides.length === 0) return 'Beginner';
        
        let complexityScore = 0;
        
        slides.forEach(slide => {
            // Count words in content
            const wordCount = slide.content.split(' ').length;
            if (wordCount > 100) complexityScore += 2;
            else if (wordCount > 50) complexityScore += 1;
            
            // Check for media content
            if (slide.mediaUrl) complexityScore += 1;
            
            // Check slide type
            if (slide.type === 'activity' || slide.type === 'video') complexityScore += 1;
        });
        
        const avgComplexity = complexityScore / slides.length;
        
        if (avgComplexity >= 3) return 'Advanced';
        if (avgComplexity >= 2) return 'Intermediate';
        return 'Beginner';
    }

    // Calculate activity difficulty based on question types and complexity
    calculateActivityDifficulty(questions) {
        if (!questions || questions.length === 0) return 'Beginner';
        
        let complexityScore = 0;
        
        questions.forEach(question => {
            switch (question.type) {
                case 'multiple-choice':
                case 'true-false':
                    complexityScore += 1;
                    break;
                case 'short-answer':
                    complexityScore += 2;
                    break;
                case 'essay':
                case 'simulation':
                    complexityScore += 3;
                    break;
            }
            
            // Higher point values indicate more complex questions
            if (question.points > 20) complexityScore += 1;
        });
        
        const avgComplexity = complexityScore / questions.length;
        
        if (avgComplexity >= 2.5) return 'Advanced';
        if (avgComplexity >= 1.5) return 'Intermediate';
        return 'Beginner';
    }

    // Estimate lesson duration
    estimateDuration(slides) {
        if (!slides) return '5 min';
        
        let totalMinutes = 0;
        
        slides.forEach(slide => {
            // Base time per slide
            totalMinutes += 2;
            
            // Additional time for content length
            const wordCount = slide.content.split(' ').length;
            totalMinutes += Math.ceil(wordCount / 200); // ~200 words per minute reading
            
            // Additional time for media
            if (slide.mediaUrl) totalMinutes += 1;
            
            // Additional time for interactive content
            if (slide.type === 'activity') totalMinutes += 3;
            if (slide.type === 'video') totalMinutes += 5;
        });
        
        return `${Math.max(5, totalMinutes)} min`;
    }

    // Estimate activity duration
    estimateActivityDuration(questions) {
        if (!questions) return '10 min';
        
        let totalMinutes = 0;
        
        questions.forEach(question => {
            switch (question.type) {
                case 'multiple-choice':
                case 'true-false':
                    totalMinutes += 1;
                    break;
                case 'short-answer':
                    totalMinutes += 3;
                    break;
                case 'essay':
                    totalMinutes += 10;
                    break;
                case 'simulation':
                    totalMinutes += 15;
                    break;
            }
        });
        
        return `${Math.max(5, totalMinutes)} min`;
    }

    // Update student dashboard with new content
    updateStudentDashboard() {
        // Update the dynamic catalogue data if it exists
        if (typeof window.updateDynamicCatalogue === 'function') {
            window.updateDynamicCatalogue(this.studentLibrary);
        }

        // Trigger refresh of student content if student dashboard is active
        if (typeof window.loadStudentContent === 'function') {
            const currentContent = document.getElementById('content');
            if (currentContent && currentContent.innerHTML.includes('library')) {
                window.loadStudentContent('catalogue');
            }
        }
    }

    // Get content for student by ID and type
    async getContentForStudent(contentId, contentType) {
        const content = this.studentLibrary.find(item => 
            item.id === contentId && item.type === contentType
        );

        if (!content) {
            throw new Error(`Content not found: ${contentType} ${contentId}`);
        }

        // Track student access
        this.trackStudentAccess(contentId, contentType);

        return content;
    }

    // Track student access for analytics
    trackStudentAccess(contentId, contentType) {
        const accessKey = `student_access_${contentType}_${contentId}`;
        const currentAccess = JSON.parse(localStorage.getItem(accessKey) || '[]');
        
        currentAccess.push({
            timestamp: new Date().toISOString(),
            studentId: this.getCurrentStudentId(),
            sessionId: this.getSessionId()
        });

        localStorage.setItem(accessKey, JSON.stringify(currentAccess));

        // Update view count in teacher data
        if (contentType === 'lesson') {
            const lesson = lessonsData.find(l => l.id === contentId);
            if (lesson) {
                lesson.views = (lesson.views || 0) + 1;
            }
        }
    }

    // Get current student ID (placeholder for authentication system)
    getCurrentStudentId() {
        return localStorage.getItem('currentStudentId') || `anonymous_${Date.now()}`;
    }

    // Get session ID
    getSessionId() {
        let sessionId = sessionStorage.getItem('sessionId');
        if (!sessionId) {
            sessionId = `session_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
            sessionStorage.setItem('sessionId', sessionId);
        }
        return sessionId;
    }

    // Submit student activity response
    async submitActivityResponse(activityId, responses) {
        const activity = this.studentLibrary.find(item => 
            item.id === activityId && item.type === 'activity'
        );

        if (!activity) {
            throw new Error(`Activity not found: ${activityId}`);
        }

        // Calculate score
        const score = this.calculateActivityScore(activity.questions, responses);

        // Save submission
        const submission = {
            activityId,
            studentId: this.getCurrentStudentId(),
            responses,
            score,
            maxScore: activity.totalPoints,
            submittedAt: new Date().toISOString(),
            sessionId: this.getSessionId()
        };

        // Store submission locally
        const submissionKey = `submission_${activityId}_${this.getCurrentStudentId()}`;
        localStorage.setItem(submissionKey, JSON.stringify(submission));

        // Update activity submission count in teacher data
        const teacherActivity = activitiesData.find(a => a.id === activityId);
        if (teacherActivity) {
            teacherActivity.submissions = (teacherActivity.submissions || 0) + 1;
        }

        // Add to sync queue for backend
        if (dataManager) {
            dataManager.addToSyncQueue('submission', submission);
        }

        return submission;
    }

    // Calculate activity score
    calculateActivityScore(questions, responses) {
        let totalScore = 0;

        questions.forEach((question, index) => {
            const response = responses[index];
            if (!response) return;

            switch (question.type) {
                case 'multiple-choice':
                    if (response.answer === question.correctAnswer) {
                        totalScore += question.points;
                    }
                    break;
                case 'true-false':
                    if (response.answer === question.correctAnswer) {
                        totalScore += question.points;
                    }
                    break;
                case 'short-answer':
                case 'essay':
                    // For now, give partial credit - will be manually graded
                    totalScore += Math.floor(question.points * 0.8);
                    break;
                case 'simulation':
                    // Check if all tasks were completed
                    if (response.tasksCompleted && response.tasksCompleted.length === question.tasks.length) {
                        totalScore += question.points;
                    }
                    break;
            }
        });

        return totalScore;
    }

    // Get student progress
    getStudentProgress() {
        const studentId = this.getCurrentStudentId();
        const progress = {
            lessonsCompleted: 0,
            activitiesCompleted: 0,
            totalScore: 0,
            averageScore: 0,
            recentActivity: []
        };

        // Count completed lessons (viewed)
        this.studentLibrary.filter(item => item.type === 'lesson').forEach(lesson => {
            const accessKey = `student_access_lesson_${lesson.id}`;
            const access = JSON.parse(localStorage.getItem(accessKey) || '[]');
            if (access.some(a => a.studentId === studentId)) {
                progress.lessonsCompleted++;
            }
        });

        // Count completed activities and calculate scores
        let totalActivities = 0;
        let totalPoints = 0;
        let maxPoints = 0;

        this.studentLibrary.filter(item => item.type === 'activity').forEach(activity => {
            const submissionKey = `submission_${activity.id}_${studentId}`;
            const submission = JSON.parse(localStorage.getItem(submissionKey) || 'null');
            if (submission) {
                progress.activitiesCompleted++;
                totalActivities++;
                totalPoints += submission.score;
                maxPoints += submission.maxScore;
                
                progress.recentActivity.push({
                    title: activity.title,
                    type: 'activity',
                    score: submission.score,
                    maxScore: submission.maxScore,
                    date: submission.submittedAt
                });
            }
        });

        progress.totalScore = totalPoints;
        progress.averageScore = maxPoints > 0 ? Math.round((totalPoints / maxPoints) * 100) : 0;

        // Sort recent activity by date
        progress.recentActivity.sort((a, b) => new Date(b.date) - new Date(a.date));
        progress.recentActivity = progress.recentActivity.slice(0, 10); // Keep last 10

        return progress;
    }

    // Setup event listeners for real-time updates
    setupEventListeners() {
        // Listen for teacher content updates
        document.addEventListener('teacherContentUpdated', () => {
            this.loadStudentLibrary();
        });

        // Listen for lesson/activity publishing
        document.addEventListener('contentPublished', (event) => {
            if (event.detail.type === 'lesson' || event.detail.type === 'activity') {
                this.loadStudentLibrary();
            }
        });
    }

    // Trigger content update event
    triggerContentUpdate() {
        document.dispatchEvent(new CustomEvent('teacherContentUpdated'));
    }

    // Get library data for student dashboard
    getLibraryData() {
        return this.studentLibrary;
    }

    // Filter library content
    filterLibraryContent(filters) {
        let filtered = [...this.studentLibrary];

        if (filters.type && filters.type !== 'all') {
            filtered = filtered.filter(item => item.type === filters.type);
        }

        if (filters.difficulty && filters.difficulty !== 'all') {
            filtered = filtered.filter(item => item.difficulty === filters.difficulty);
        }

        if (filters.category && filters.category !== 'all') {
            filtered = filtered.filter(item => item.category === filters.category);
        }

        if (filters.search) {
            const searchTerm = filters.search.toLowerCase();
            filtered = filtered.filter(item => 
                item.title.toLowerCase().includes(searchTerm) ||
                (item.slides && item.slides.some(slide => 
                    slide.content.toLowerCase().includes(searchTerm)
                ))
            );
        }

        return filtered;
    }
}

// Global student integration instance
const studentIntegration = new StudentIntegration();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StudentIntegration;
}