// Dynamic Teacher Dashboard - Connected to PHP Backend
let lessonsData = []; // Will be populated from API
let activitiesData = []; // Will be populated from API

async function initializeTeacherDashboard() {
    try {
        // Load initial data
        await loadLessonsData();
        await loadActivitiesData();
        
        // Render lessons by default
        renderLessons('all');
    } catch (error) {
        console.error('Error initializing dashboard:', error);
        showErrorMessage('Failed to load dashboard data');
    }
}

async function loadLessonsData() {
    try {
        lessonsData = await dynamicData.getLessonsData();
    } catch (error) {
        console.error('Error loading lessons:', error);
        lessonsData = [];
    }
}

async function loadActivitiesData() {
    try {
        activitiesData = await dynamicData.getActivitiesData();
    } catch (error) {
        console.error('Error loading activities:', error);
        activitiesData = [];
    }
}

function renderLessons(filterStatus = 'all') {
    const lessonGrid = document.getElementById('lessonGrid');
    if (!lessonGrid) return; 

    const filteredLessons = lessonsData.filter(lesson => {
        if (filterStatus === 'all') return true;
        return lesson.status === filterStatus;
    });

    let lessonHtml = '';

    if (filteredLessons.length === 0) {
        lessonHtml = '<p style="text-align: center; padding: 50px; color: #7f8c8d;">No lessons found matching the current filter criteria.</p>';
    } else {
        filteredLessons.forEach(lesson => {
            let actions = '';
            let statusBadgeClass = '';
            let statusText = '';
            
            if (lesson.status === 'published') {
                statusBadgeClass = 'published';
                statusText = 'Published';
                actions = `
                    <button class="action-small-btn edit-btn" onclick="editLesson(${lesson.id}, '${lesson.title}')"><i class="fas fa-edit"></i> Edit</button>
                    <button class="action-small-btn view-btn" onclick="viewLesson(${lesson.id}, '${lesson.title}')"><i class="fas fa-book-open"></i> Preview</button>
                    <button class="action-small-btn qr-btn" onclick="generateQRCode(${lesson.id}, 'lesson', '${lesson.title}')"><i class="fas fa-qrcode"></i> Get QR</button>
                    <button class="action-small-btn delete-btn" onclick="deleteLesson(${lesson.id}, '${lesson.title}')"><i class="fas fa-trash-alt"></i> Delete</button>
                `;
            } else if (lesson.status === 'draft') {
                statusBadgeClass = 'draft';
                statusText = 'Draft';
                actions = `
                    <button class="action-small-btn edit-btn" onclick="editLesson(${lesson.id}, '${lesson.title}')"><i class="fas fa-edit"></i> Continue Editing</button>
                    <button class="action-small-btn publish-btn" onclick="publishLesson(${lesson.id}, '${lesson.title}')"><i class="fas fa-upload"></i> Publish</button>
                    <button class="action-small-btn delete-btn" onclick="deleteLesson(${lesson.id}, '${lesson.title}')"><i class="fas fa-trash-alt"></i> Delete</button>
                `;
            } else if (lesson.status === 'archived') {
                statusBadgeClass = 'archived';
                statusText = 'Archived';
                actions = `
                    <button class="action-small-btn archive-btn" onclick="restoreLesson(${lesson.id}, '${lesson.title}')"><i class="fas fa-sync-alt"></i> Restore</button>
                    <button class="action-small-btn delete-btn" onclick="deleteLesson(${lesson.id}, '${lesson.title}')"><i class="fas fa-trash-alt"></i> Delete</button>
                `;
            }

            // Calculate activity count and views from related data
            const activityCount = activitiesData.filter(a => a.lesson_id === lesson.id).length;
            const views = lesson.views || 0;
            const classes = lesson.classes || 0;

            lessonHtml += `
                <div class="lesson-card status-${lesson.status}">
                    <div class="lesson-header-status">
                        <span class="status-badge ${statusBadgeClass}">${statusText}</span>
                        <i class="fas fa-ellipsis-v action-icon" onclick="customAlert('Options for ${lesson.title} lesson.')"></i>
                    </div>
                    <h4>${lesson.title}</h4>
                    <p class="topic-detail">Grade Level: ${lesson.grade_level || 'N/A'} | Activity Count: ${activityCount}</p>
                    <div class="lesson-metrics">
                        <span><i class="fas fa-eye"></i> ${views} Views</span>
                        <span><i class="fas fa-users"></i> ${classes} Classes</span>
                    </div>
                    ${actions}
                </div>
            `;
        });
    }

    lessonHtml += `
        <div class="lesson-card status-new">
            <div class="lesson-placeholder" onclick="initLessonCreation()">
                <i class="fas fa-layer-group"></i>
                <h4>Start from Template</h4>
                <p>Use a structured template to speed up creation.</p>
            </div>
        </div>
    `;

    lessonGrid.innerHTML = lessonHtml;
}

async function filterLessons() {
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        renderLessons(statusFilter.value);
        customAlert(`Filtering lessons by status: ${statusFilter.value}`);
    }
}

async function initLessonCreation() {
    showModal('createLessonModal');
    
    const form = document.getElementById('newLessonForm');
    form.onsubmit = async function(event) {
        event.preventDefault();

        const title = document.getElementById('lessonTitle').value;
        const bgType = document.querySelector('input[name="bgType"]:checked').value;
        
        try {
            const response = await apiClient.createLesson({
                action: 'create',
                title: title,
                content: '', // Empty content for new lesson
                background_type: bgType
            });

            if (response.success) {
                hideModal('createLessonModal');
                customAlert(`Lesson "${title}" created successfully!`);
                
                // Reload lessons data
                await loadLessonsData();
                renderLessons(document.getElementById('statusFilter')?.value || 'all');
                
                // Open editor for the new lesson
                editLesson(response.lesson_id, title);
            } else {
                customAlert(`Error creating lesson: ${response.message}`);
            }
        } catch (error) {
            console.error('Error creating lesson:', error);
            customAlert('Failed to create lesson. Please try again.');
        }
        
        form.reset();
    };
}

function editLesson(lessonId, title) {
    document.getElementById('editorTitle').textContent = `Editor: ${title} (ID: ${lessonId})`;
    customAlert(`Opening editor for Lesson ID ${lessonId}: "${title}"`);
    showModal('lessonEditorModal');
}

function viewLesson(lessonId, title) {
    document.getElementById('slideshowTitle').textContent = `Viewing Lesson: ${title}`;
    customAlert(`Launching slideshow viewer for lesson ID: ${lessonId}.`);
    showModal('lessonViewerModal');
}

async function publishLesson(lessonId, title) {
    try {
        const response = await apiClient.publishLesson(lessonId);
        
        if (response.success) {
            customAlert(`Lesson "${title}" published successfully!`);
            
            // Reload lessons data and re-render
            await loadLessonsData();
            renderLessons(document.getElementById('statusFilter')?.value || 'all');
            
            // Clear cache to refresh data
            dynamicData.clearCache('lessons_data');
        } else {
            customAlert(`Error publishing lesson: ${response.message}`);
        }
    } catch (error) {
        console.error('Error publishing lesson:', error);
        customAlert('Failed to publish lesson. Please try again.');
    }
}

async function deleteLesson(lessonId, title) {
    if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
        try {
            // TODO: Implement delete API endpoint
            customAlert(`Deleting lesson: ${title} (ID: ${lessonId})`);
            
            // For now, just remove from local data
            lessonsData = lessonsData.filter(lesson => lesson.id !== lessonId);
            renderLessons(document.getElementById('statusFilter')?.value || 'all');
            
        } catch (error) {
            console.error('Error deleting lesson:', error);
            customAlert('Failed to delete lesson. Please try again.');
        }
    }
}

async function restoreLesson(lessonId, title) {
    try {
        // TODO: Implement restore API endpoint
        customAlert(`Restoring lesson: ${title} (ID: ${lessonId})`);
        
        // For now, just update local data
        const lesson = lessonsData.find(l => l.id === lessonId);
        if (lesson) {
            lesson.status = 'draft';
            renderLessons(document.getElementById('statusFilter')?.value || 'all');
        }
        
    } catch (error) {
        console.error('Error restoring lesson:', error);
        customAlert('Failed to restore lesson. Please try again.');
    }
}

function showErrorMessage(message) {
    const content = document.getElementById('content');
    if (content) {
        content.innerHTML = `
            <div class="error-message">
                <h2>Error</h2>
                <p>${message}</p>
                <button class="action-button primary" onclick="location.reload()">
                    <i class="fas fa-refresh"></i> Reload Page
                </button>
            </div>
        `;
    }
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initializeTeacherDashboard === 'function') {
        initializeTeacherDashboard();
    }
});