// Dynamic Lesson Editor Component
class LessonEditor {
    constructor() {
        this.currentLessonId = null;
        this.currentSlideIndex = 0;
        this.lessonData = {
            id: null,
            title: '',
            slides: [],
            bgType: 'simple',
            status: 'draft'
        };
        this.isModified = false;
    }

    // Initialize lesson editor with lesson data
    initEditor(lessonId, lessonTitle) {
        this.currentLessonId = lessonId;
        
        // Find existing lesson or create new one
        const existingLesson = lessonsData.find(l => l.id === lessonId);
        if (existingLesson) {
            this.lessonData = {
                id: lessonId,
                title: existingLesson.title,
                slides: existingLesson.slides || this.createDefaultSlides(),
                bgType: existingLesson.bgType || 'simple',
                status: existingLesson.status || 'draft'
            };
        } else {
            this.lessonData = {
                id: lessonId,
                title: lessonTitle,
                slides: this.createDefaultSlides(),
                bgType: 'simple',
                status: 'draft'
            };
        }

        this.currentSlideIndex = 0;
        this.renderEditor();
        this.bindEvents();
    }

    // Create default slides for new lessons
    createDefaultSlides() {
        return [
            {
                id: 1,
                title: 'Introduction',
                content: 'Welcome to this lesson! Add your introduction content here.',
                mediaUrl: '',
                type: 'content'
            },
            {
                id: 2,
                title: 'Main Content',
                content: 'Add your main lesson content here. You can include text, images, and multimedia.',
                mediaUrl: '',
                type: 'content'
            },
            {
                id: 3,
                title: 'Summary',
                content: 'Summarize the key points of this lesson.',
                mediaUrl: '',
                type: 'content'
            }
        ];
    }

    // Render the complete editor interface
    renderEditor() {
        this.renderSlideList();
        this.renderCurrentSlide();
        this.updateEditorTitle();
    }

    // Update editor title
    updateEditorTitle() {
        const titleElement = document.getElementById('editorTitle');
        if (titleElement) {
            titleElement.textContent = `Editor: ${this.lessonData.title} (ID: ${this.currentLessonId})`;
        }
    }

    // Render slide list in sidebar
    renderSlideList() {
        const slideList = document.getElementById('slideList');
        if (!slideList) return;

        let html = '';
        this.lessonData.slides.forEach((slide, index) => {
            const activeClass = index === this.currentSlideIndex ? 'active' : '';
            html += `
                <div class="slide-item ${activeClass}" onclick="lessonEditor.selectSlide(${index})">
                    <span>Slide ${index + 1}: ${slide.title}</span>
                    <div class="slide-actions">
                        <button class="slide-action-btn" onclick="event.stopPropagation(); lessonEditor.editSlideTitle(${index})" title="Edit Title">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="slide-action-btn delete" onclick="event.stopPropagation(); lessonEditor.deleteSlide(${index})" title="Delete Slide">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        slideList.innerHTML = html;
    }

    // Render current slide content in main panel
    renderCurrentSlide() {
        const currentSlide = this.lessonData.slides[this.currentSlideIndex];
        if (!currentSlide) return;

        const mainPanel = document.querySelector('.editor-main-panel');
        if (!mainPanel) return;

        mainPanel.innerHTML = `
            <div class="slide-editor-header">
                <h4>Editing Slide ${this.currentSlideIndex + 1}: ${currentSlide.title}</h4>
                <div class="slide-navigation">
                    <button class="nav-btn" onclick="lessonEditor.previousSlide()" ${this.currentSlideIndex === 0 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <span class="slide-counter">${this.currentSlideIndex + 1} of ${this.lessonData.slides.length}</span>
                    <button class="nav-btn" onclick="lessonEditor.nextSlide()" ${this.currentSlideIndex === this.lessonData.slides.length - 1 ? 'disabled' : ''}>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="slide-content-editor">
                <div class="form-group">
                    <label for="slideContent">Slide Content:</label>
                    <textarea id="slideContent" rows="12" placeholder="Enter your lesson content here. You can include text, bullet points, or any educational material." onchange="lessonEditor.updateSlideContent()">${currentSlide.content}</textarea>
                </div>

                <div class="form-group">
                    <label for="slideMediaUrl">Media/Image URL:</label>
                    <input type="url" id="slideMediaUrl" placeholder="Paste image or media URL here..." value="${currentSlide.mediaUrl}" onchange="lessonEditor.updateSlideMedia()">
                </div>

                <div class="form-group">
                    <label for="slideType">Slide Type:</label>
                    <select id="slideType" onchange="lessonEditor.updateSlideType()">
                        <option value="content" ${currentSlide.type === 'content' ? 'selected' : ''}>Content Slide</option>
                        <option value="image" ${currentSlide.type === 'image' ? 'selected' : ''}>Image Focus</option>
                        <option value="video" ${currentSlide.type === 'video' ? 'selected' : ''}>Video Embed</option>
                        <option value="activity" ${currentSlide.type === 'activity' ? 'selected' : ''}>Interactive Activity</option>
                    </select>
                </div>

                ${currentSlide.mediaUrl ? `
                    <div class="media-preview">
                        <h5>Media Preview:</h5>
                        <img src="${currentSlide.mediaUrl}" alt="Slide media" style="max-width: 300px; max-height: 200px; border-radius: 4px;" onerror="this.style.display='none'">
                    </div>
                ` : ''}
            </div>
        `;
    }

    // Navigation methods
    selectSlide(index) {
        if (index >= 0 && index < this.lessonData.slides.length) {
            this.currentSlideIndex = index;
            this.renderEditor();
        }
    }

    previousSlide() {
        if (this.currentSlideIndex > 0) {
            this.currentSlideIndex--;
            this.renderEditor();
        }
    }

    nextSlide() {
        if (this.currentSlideIndex < this.lessonData.slides.length - 1) {
            this.currentSlideIndex++;
            this.renderEditor();
        }
    }

    // Content update methods
    updateSlideContent() {
        const content = document.getElementById('slideContent').value;
        this.lessonData.slides[this.currentSlideIndex].content = content;
        this.markAsModified();
    }

    updateSlideMedia() {
        const mediaUrl = document.getElementById('slideMediaUrl').value;
        this.lessonData.slides[this.currentSlideIndex].mediaUrl = mediaUrl;
        this.markAsModified();
        this.renderCurrentSlide(); // Re-render to show preview
    }

    updateSlideType() {
        const type = document.getElementById('slideType').value;
        this.lessonData.slides[this.currentSlideIndex].type = type;
        this.markAsModified();
    }

    // Slide management methods
    addNewSlide() {
        const newSlide = {
            id: Date.now(),
            title: `New Slide ${this.lessonData.slides.length + 1}`,
            content: 'Add your content here...',
            mediaUrl: '',
            type: 'content'
        };

        this.lessonData.slides.push(newSlide);
        this.currentSlideIndex = this.lessonData.slides.length - 1;
        this.markAsModified();
        this.renderEditor();
    }

    editSlideTitle(index) {
        const currentTitle = this.lessonData.slides[index].title;
        const newTitle = prompt('Enter new slide title:', currentTitle);
        
        if (newTitle && newTitle.trim()) {
            this.lessonData.slides[index].title = newTitle.trim();
            this.markAsModified();
            this.renderEditor();
        }
    }

    deleteSlide(index) {
        if (this.lessonData.slides.length <= 1) {
            customAlert('Cannot delete the last slide. A lesson must have at least one slide.');
            return;
        }

        if (confirm(`Are you sure you want to delete "${this.lessonData.slides[index].title}"?`)) {
            this.lessonData.slides.splice(index, 1);
            
            // Adjust current slide index if necessary
            if (this.currentSlideIndex >= this.lessonData.slides.length) {
                this.currentSlideIndex = this.lessonData.slides.length - 1;
            }
            
            this.markAsModified();
            this.renderEditor();
        }
    }

    // Save functionality
    saveDraft() {
        this.saveToDataModel();
        customAlert(`Draft saved successfully! Lesson "${this.lessonData.title}" has ${this.lessonData.slides.length} slides.`);
        this.isModified = false;
    }

    publishLesson() {
        if (this.validateLesson()) {
            this.lessonData.status = 'published';
            this.saveToDataModel();
            customAlert(`Lesson "${this.lessonData.title}" has been published successfully!`);
            this.isModified = false;
            
            // Refresh lessons view if visible
            if (typeof renderLessons === 'function') {
                renderLessons();
            }
        }
    }

    validateLesson() {
        if (!this.lessonData.title.trim()) {
            customAlert('Please provide a lesson title before publishing.');
            return false;
        }

        if (this.lessonData.slides.length === 0) {
            customAlert('Please add at least one slide before publishing.');
            return false;
        }

        // Check if any slide has empty content
        const emptySlides = this.lessonData.slides.filter(slide => !slide.content.trim());
        if (emptySlides.length > 0) {
            if (!confirm(`${emptySlides.length} slide(s) have empty content. Publish anyway?`)) {
                return false;
            }
        }

        return true;
    }

    saveToDataModel() {
        // Update or add lesson to global lessonsData
        const existingIndex = lessonsData.findIndex(l => l.id === this.currentLessonId);
        
        const lessonRecord = {
            id: this.currentLessonId,
            title: this.lessonData.title,
            grade: 'N/A', // Can be enhanced later
            activities: 0, // Can be linked to activities later
            status: this.lessonData.status,
            views: existingIndex >= 0 ? lessonsData[existingIndex].views : 0,
            classes: existingIndex >= 0 ? lessonsData[existingIndex].classes : 0,
            lastEdit: 'just now',
            bgType: this.lessonData.bgType,
            slides: this.lessonData.slides // Store slide data
        };

        if (existingIndex >= 0) {
            lessonsData[existingIndex] = lessonRecord;
        } else {
            lessonsData.push(lessonRecord);
        }

        // Prepare data for PHP backend (future integration)
        this.preparePHPData();
    }

    preparePHPData() {
        // Structure data for future PHP backend integration
        const phpData = {
            lesson_id: this.currentLessonId,
            title: this.lessonData.title,
            status: this.lessonData.status,
            background_type: this.lessonData.bgType,
            slides: this.lessonData.slides.map(slide => ({
                slide_id: slide.id,
                title: slide.title,
                content: slide.content,
                media_url: slide.mediaUrl,
                slide_type: slide.type,
                order_index: this.lessonData.slides.indexOf(slide)
            })),
            updated_at: new Date().toISOString()
        };

        // Store in localStorage for now (will be replaced with PHP API calls)
        localStorage.setItem(`lesson_${this.currentLessonId}`, JSON.stringify(phpData));
        
        console.log('Lesson data prepared for PHP backend:', phpData);
    }

    markAsModified() {
        this.isModified = true;
        // Visual indicator that content has been modified
        const saveBtn = document.querySelector('.editor-content-area .action-small-btn.view-btn');
        if (saveBtn && !saveBtn.textContent.includes('*')) {
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Draft *';
        }
    }

    // Bind event handlers
    bindEvents() {
        // Add new slide button
        const addSlideBtn = document.querySelector('.editor-sidebar .action-small-btn.edit-btn');
        if (addSlideBtn) {
            addSlideBtn.onclick = () => this.addNewSlide();
        }

        // Save draft button
        const saveDraftBtn = document.querySelector('.modal-header-content .action-small-btn.view-btn');
        if (saveDraftBtn) {
            saveDraftBtn.onclick = () => this.saveDraft();
        }

        // Publish button
        const publishBtn = document.querySelector('.config-button-group .action-small-btn.publish-btn');
        if (publishBtn) {
            publishBtn.onclick = () => this.publishLesson();
        }

        // Auto-save on content change (debounced)
        this.setupAutoSave();
    }

    setupAutoSave() {
        let autoSaveTimeout;
        const autoSave = () => {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                if (this.isModified) {
                    this.saveToDataModel();
                    console.log('Auto-saved lesson data');
                }
            }, 2000); // Auto-save after 2 seconds of inactivity
        };

        // Listen for content changes
        document.addEventListener('input', (e) => {
            if (e.target.id === 'slideContent' || e.target.id === 'slideMediaUrl') {
                autoSave();
            }
        });
    }

    // Export lesson data for student dashboard integration
    exportForStudentDashboard() {
        return {
            id: this.currentLessonId,
            title: this.lessonData.title,
            slides: this.lessonData.slides,
            status: this.lessonData.status,
            type: 'lesson'
        };
    }
}

// Global lesson editor instance
const lessonEditor = new LessonEditor();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LessonEditor;
}   