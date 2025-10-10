// Teacher Dashboard Page Functions
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
                    <button class="action-small-btn publish-btn" onclick="customAlert('Attempting to publish ${lesson.title}.')"><i class="fas fa-upload"></i> Publish</button>
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

            lessonHtml += `
                <div class="lesson-card status-${lesson.status}">
                    <div class="lesson-header-status">
                        <span class="status-badge ${statusBadgeClass}">${statusText}</span>
                        <i class="fas fa-ellipsis-v action-icon" onclick="customAlert('Options for ${lesson.title} lesson.')"></i>
                    </div>
                    <h4>${lesson.title}</h4>
                    <p class="topic-detail">Grade Level: ${lesson.grade} | Activity Count: ${lesson.activities}</p>
                    <div class="lesson-metrics">
                        <span><i class="fas fa-eye"></i> ${lesson.views} Views</span>
                        <span><i class="fas fa-users"></i> ${lesson.classes} Classes</span>
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

function filterLessons() {
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        renderLessons(statusFilter.value);
        customAlert(`Filtering lessons by status: ${statusFilter.value}`);
    }
}

function initLessonCreation() {
    showModal('createLessonModal');
    
    const form = document.getElementById('newLessonForm');
    form.onsubmit = function(event) {
        event.preventDefault();

        let nextId = lessonsData.length > 0 ? Math.max(...lessonsData.map(l => l.id)) + 1 : 1;
        const title = document.getElementById('lessonTitle').value;
        const bgType = document.querySelector('input[name="bgType"]:checked').value;
        
        const newLesson = {
            id: nextId,
            title: title,
            grade: 'N/A', 
            activities: 0,
            status: 'draft', 
            views: 0,
            classes: 0,
            lastEdit: 'just now',
            bgType: bgType
        };

        lessonsData.push(newLesson);

        hideModal('createLessonModal');
        editLesson(nextId, title);
        
        renderLessons(document.getElementById('statusFilter')?.value || 'all');
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

function deleteLesson(lessonId, title) {
    customAlert(`Deleting lesson: ${title} (ID: ${lessonId})`);
}

function restoreLesson(lessonId, title) {
    customAlert(`Restoring lesson: ${title} (ID: ${lessonId})`);
}