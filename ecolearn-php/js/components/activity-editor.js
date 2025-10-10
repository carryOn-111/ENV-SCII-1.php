// Dynamic Activity Editor Component
class ActivityEditor {
    constructor() {
        this.currentActivityId = null;
        this.currentQuestionIndex = 0;
        this.activityData = {
            id: null,
            title: '',
            type: 'Quiz',
            dueDate: '',
            questions: [],
            status: 'draft',
            totalPoints: 0
        };
        this.isModified = false;
    }

    // Initialize activity editor with activity data
    initEditor(activityId, activityTitle) {
        this.currentActivityId = activityId;
        
        // Find existing activity or create new one
        const existingActivity = activitiesData.find(a => a.id === activityId);
        if (existingActivity) {
            this.activityData = {
                id: activityId,
                title: existingActivity.title,
                type: existingActivity.type,
                dueDate: existingActivity.dueDate,
                questions: existingActivity.questions || this.createDefaultQuestions(existingActivity.type),
                status: existingActivity.status || 'draft',
                totalPoints: existingActivity.totalPoints || 0
            };
        } else {
            this.activityData = {
                id: activityId,
                title: activityTitle,
                type: 'Quiz',
                dueDate: 'N/A',
                questions: this.createDefaultQuestions('Quiz'),
                status: 'draft',
                totalPoints: 0
            };
        }

        this.currentQuestionIndex = 0;
        this.calculateTotalPoints();
        this.renderEditor();
        this.bindEvents();
    }

    // Create default questions based on activity type
    createDefaultQuestions(type) {
        switch (type) {
            case 'Quiz':
                return [
                    {
                        id: 1,
                        type: 'multiple-choice',
                        question: 'What is the primary cause of climate change?',
                        points: 10,
                        options: ['Natural cycles', 'Human activities', 'Solar radiation', 'Ocean currents'],
                        correctAnswer: 1,
                        explanation: 'Human activities, particularly greenhouse gas emissions, are the primary driver of current climate change.'
                    },
                    {
                        id: 2,
                        type: 'short-answer',
                        question: 'Explain the water cycle in 2-3 sentences.',
                        points: 15,
                        correctAnswer: '',
                        explanation: 'Look for mention of evaporation, condensation, and precipitation.'
                    }
                ];
            case 'Project':
                return [
                    {
                        id: 1,
                        type: 'essay',
                        question: 'Research and write a 500-word essay on local environmental issues in your community.',
                        points: 50,
                        requirements: ['Minimum 500 words', 'At least 3 sources', 'Proper citations'],
                        rubric: 'Content (20pts), Research (15pts), Writing Quality (15pts)'
                    }
                ];
            case 'Simulation':
                return [
                    {
                        id: 1,
                        type: 'simulation',
                        question: 'Complete the ecosystem balance simulation and answer the following questions.',
                        points: 25,
                        simulationUrl: '',
                        tasks: ['Run simulation for 10 cycles', 'Record population changes', 'Analyze results']
                    }
                ];
            default:
                return [];
        }
    }

    // Render the complete editor interface
    renderEditor() {
        this.renderQuestionList();
        this.renderCurrentQuestion();
        this.updateEditorTitle();
    }

    // Update editor title
    updateEditorTitle() {
        const titleElement = document.getElementById('configuratorTitle');
        if (titleElement) {
            titleElement.textContent = `Configurator: ${this.activityData.title} (ID: ${this.currentActivityId}) - ${this.activityData.totalPoints} pts`;
        }
    }

    // Render question list in sidebar
    renderQuestionList() {
        const questionList = document.getElementById('questionList');
        if (!questionList) return;

        let html = '';
        this.activityData.questions.forEach((question, index) => {
            const activeClass = index === this.currentQuestionIndex ? 'active' : '';
            const questionTypeLabel = this.getQuestionTypeLabel(question.type);
            
            html += `
                <div class="slide-item ${activeClass}" onclick="activityEditor.selectQuestion(${index})">
                    <span>Q${index + 1}: ${questionTypeLabel} (${question.points} pts)</span>
                    <div class="slide-actions">
                        <button class="slide-action-btn" onclick="event.stopPropagation(); activityEditor.duplicateQuestion(${index})" title="Duplicate Question">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button class="slide-action-btn delete" onclick="event.stopPropagation(); activityEditor.deleteQuestion(${index})" title="Delete Question">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        questionList.innerHTML = html;
    }

    getQuestionTypeLabel(type) {
        const labels = {
            'multiple-choice': 'Multiple Choice',
            'short-answer': 'Short Answer',
            'essay': 'Essay',
            'true-false': 'True/False',
            'simulation': 'Simulation',
            'matching': 'Matching'
        };
        return labels[type] || 'Question';
    }

    // Render current question content in main panel
    renderCurrentQuestion() {
        const currentQuestion = this.activityData.questions[this.currentQuestionIndex];
        if (!currentQuestion) return;

        const mainPanel = document.querySelector('.editor-main-panel');
        if (!mainPanel) return;

        let questionSpecificHtml = '';
        
        switch (currentQuestion.type) {
            case 'multiple-choice':
                questionSpecificHtml = this.renderMultipleChoiceEditor(currentQuestion);
                break;
            case 'short-answer':
                questionSpecificHtml = this.renderShortAnswerEditor(currentQuestion);
                break;
            case 'essay':
                questionSpecificHtml = this.renderEssayEditor(currentQuestion);
                break;
            case 'true-false':
                questionSpecificHtml = this.renderTrueFalseEditor(currentQuestion);
                break;
            case 'simulation':
                questionSpecificHtml = this.renderSimulationEditor(currentQuestion);
                break;
            default:
                questionSpecificHtml = '<p>Question type not supported yet.</p>';
        }

        mainPanel.innerHTML = `
            <div class="question-editor-header">
                <h4>Editing Q${this.currentQuestionIndex + 1}: ${this.getQuestionTypeLabel(currentQuestion.type)}</h4>
                <div class="question-navigation">
                    <button class="nav-btn" onclick="activityEditor.previousQuestion()" ${this.currentQuestionIndex === 0 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <span class="slide-counter">${this.currentQuestionIndex + 1} of ${this.activityData.questions.length}</span>
                    <button class="nav-btn" onclick="activityEditor.nextQuestion()" ${this.currentQuestionIndex === this.activityData.questions.length - 1 ? 'disabled' : ''}>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="question-content-editor">
                <div class="form-group">
                    <label for="questionType">Question Type:</label>
                    <select id="questionType" onchange="activityEditor.updateQuestionType()">
                        <option value="multiple-choice" ${currentQuestion.type === 'multiple-choice' ? 'selected' : ''}>Multiple Choice</option>
                        <option value="short-answer" ${currentQuestion.type === 'short-answer' ? 'selected' : ''}>Short Answer</option>
                        <option value="essay" ${currentQuestion.type === 'essay' ? 'selected' : ''}>Essay</option>
                        <option value="true-false" ${currentQuestion.type === 'true-false' ? 'selected' : ''}>True/False</option>
                        <option value="simulation" ${currentQuestion.type === 'simulation' ? 'selected' : ''}>Simulation</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="questionText">Question Text:</label>
                    <textarea id="questionText" rows="3" placeholder="Enter your question here..." onchange="activityEditor.updateQuestionText()">${currentQuestion.question}</textarea>
                </div>

                <div class="form-group">
                    <label for="questionPoints">Point Value:</label>
                    <input type="number" id="questionPoints" min="1" max="100" value="${currentQuestion.points}" onchange="activityEditor.updateQuestionPoints()">
                </div>

                ${questionSpecificHtml}

                <div class="form-group">
                    <label for="questionExplanation">Explanation (Optional):</label>
                    <textarea id="questionExplanation" rows="2" placeholder="Provide an explanation for the correct answer..." onchange="activityEditor.updateQuestionExplanation()">${currentQuestion.explanation || ''}</textarea>
                </div>
            </div>
        `;
    }

    renderMultipleChoiceEditor(question) {
        let optionsHtml = '';
        const options = question.options || ['', '', '', ''];
        
        options.forEach((option, index) => {
            optionsHtml += `
                <div class="option-row">
                    <input type="radio" name="correctAnswer" value="${index}" ${question.correctAnswer === index ? 'checked' : ''} onchange="activityEditor.updateCorrectAnswer(${index})">
                    <input type="text" class="option-input" value="${option}" placeholder="Option ${String.fromCharCode(65 + index)}" onchange="activityEditor.updateOption(${index}, this.value)">
                    <button class="option-remove-btn" onclick="activityEditor.removeOption(${index})" ${options.length <= 2 ? 'disabled' : ''}>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        });

        return `
            <div class="form-group">
                <label>Answer Options:</label>
                <div id="optionsContainer">
                    ${optionsHtml}
                </div>
                <button class="add-option-btn" onclick="activityEditor.addOption()" ${options.length >= 6 ? 'disabled' : ''}>
                    <i class="fas fa-plus"></i> Add Option
                </button>
            </div>
        `;
    }

    renderShortAnswerEditor(question) {
        return `
            <div class="form-group">
                <label for="sampleAnswer">Sample/Expected Answer:</label>
                <textarea id="sampleAnswer" rows="3" placeholder="Provide a sample answer or key points to look for..." onchange="activityEditor.updateSampleAnswer()">${question.correctAnswer || ''}</textarea>
            </div>
        `;
    }

    renderEssayEditor(question) {
        const requirements = question.requirements || [];
        let requirementsHtml = '';
        
        requirements.forEach((req, index) => {
            requirementsHtml += `
                <div class="requirement-row">
                    <input type="text" value="${req}" placeholder="Requirement ${index + 1}" onchange="activityEditor.updateRequirement(${index}, this.value)">
                    <button onclick="activityEditor.removeRequirement(${index})"><i class="fas fa-times"></i></button>
                </div>
            `;
        });

        return `
            <div class="form-group">
                <label>Requirements:</label>
                <div id="requirementsContainer">
                    ${requirementsHtml}
                </div>
                <button onclick="activityEditor.addRequirement()">
                    <i class="fas fa-plus"></i> Add Requirement
                </button>
            </div>
            <div class="form-group">
                <label for="rubric">Grading Rubric:</label>
                <textarea id="rubric" rows="3" placeholder="Describe how this essay will be graded..." onchange="activityEditor.updateRubric()">${question.rubric || ''}</textarea>
            </div>
        `;
    }

    renderTrueFalseEditor(question) {
        return `
            <div class="form-group">
                <label>Correct Answer:</label>
                <div class="true-false-options">
                    <label>
                        <input type="radio" name="trueFalseAnswer" value="true" ${question.correctAnswer === true ? 'checked' : ''} onchange="activityEditor.updateTrueFalseAnswer(true)">
                        True
                    </label>
                    <label>
                        <input type="radio" name="trueFalseAnswer" value="false" ${question.correctAnswer === false ? 'checked' : ''} onchange="activityEditor.updateTrueFalseAnswer(false)">
                        False
                    </label>
                </div>
            </div>
        `;
    }

    renderSimulationEditor(question) {
        const tasks = question.tasks || [];
        let tasksHtml = '';
        
        tasks.forEach((task, index) => {
            tasksHtml += `
                <div class="task-row">
                    <input type="text" value="${task}" placeholder="Task ${index + 1}" onchange="activityEditor.updateTask(${index}, this.value)">
                    <button onclick="activityEditor.removeTask(${index})"><i class="fas fa-times"></i></button>
                </div>
            `;
        });

        return `
            <div class="form-group">
                <label for="simulationUrl">Simulation URL:</label>
                <input type="url" id="simulationUrl" value="${question.simulationUrl || ''}" placeholder="Link to simulation or interactive content" onchange="activityEditor.updateSimulationUrl()">
            </div>
            <div class="form-group">
                <label>Tasks to Complete:</label>
                <div id="tasksContainer">
                    ${tasksHtml}
                </div>
                <button onclick="activityEditor.addTask()">
                    <i class="fas fa-plus"></i> Add Task
                </button>
            </div>
        `;
    }

    // Navigation methods
    selectQuestion(index) {
        if (index >= 0 && index < this.activityData.questions.length) {
            this.currentQuestionIndex = index;
            this.renderEditor();
        }
    }

    previousQuestion() {
        if (this.currentQuestionIndex > 0) {
            this.currentQuestionIndex--;
            this.renderEditor();
        }
    }

    nextQuestion() {
        if (this.currentQuestionIndex < this.activityData.questions.length - 1) {
            this.currentQuestionIndex++;
            this.renderEditor();
        }
    }

    // Question management methods
    addNewQuestion() {
        const newQuestion = {
            id: Date.now(),
            type: 'multiple-choice',
            question: 'Enter your question here...',
            points: 10,
            options: ['Option A', 'Option B', 'Option C', 'Option D'],
            correctAnswer: 0,
            explanation: ''
        };

        this.activityData.questions.push(newQuestion);
        this.currentQuestionIndex = this.activityData.questions.length - 1;
        this.calculateTotalPoints();
        this.markAsModified();
        this.renderEditor();
    }

    duplicateQuestion(index) {
        const originalQuestion = this.activityData.questions[index];
        const duplicatedQuestion = {
            ...originalQuestion,
            id: Date.now(),
            question: originalQuestion.question + ' (Copy)'
        };

        this.activityData.questions.splice(index + 1, 0, duplicatedQuestion);
        this.currentQuestionIndex = index + 1;
        this.calculateTotalPoints();
        this.markAsModified();
        this.renderEditor();
    }

    deleteQuestion(index) {
        if (this.activityData.questions.length <= 1) {
            customAlert('Cannot delete the last question. An activity must have at least one question.');
            return;
        }

        if (confirm('Are you sure you want to delete this question?')) {
            this.activityData.questions.splice(index, 1);
            
            if (this.currentQuestionIndex >= this.activityData.questions.length) {
                this.currentQuestionIndex = this.activityData.questions.length - 1;
            }
            
            this.calculateTotalPoints();
            this.markAsModified();
            this.renderEditor();
        }
    }

    // Update methods for different question types
    updateQuestionType() {
        const newType = document.getElementById('questionType').value;
        const currentQuestion = this.activityData.questions[this.currentQuestionIndex];
        
        if (currentQuestion.type !== newType) {
            currentQuestion.type = newType;
            
            // Reset type-specific properties
            switch (newType) {
                case 'multiple-choice':
                    currentQuestion.options = ['Option A', 'Option B', 'Option C', 'Option D'];
                    currentQuestion.correctAnswer = 0;
                    break;
                case 'true-false':
                    currentQuestion.correctAnswer = true;
                    break;
                case 'short-answer':
                case 'essay':
                    currentQuestion.correctAnswer = '';
                    break;
            }
            
            this.markAsModified();
            this.renderCurrentQuestion();
        }
    }

    updateQuestionText() {
        const text = document.getElementById('questionText').value;
        this.activityData.questions[this.currentQuestionIndex].question = text;
        this.markAsModified();
    }

    updateQuestionPoints() {
        const points = parseInt(document.getElementById('questionPoints').value) || 0;
        this.activityData.questions[this.currentQuestionIndex].points = points;
        this.calculateTotalPoints();
        this.markAsModified();
        this.updateEditorTitle();
    }

    updateQuestionExplanation() {
        const explanation = document.getElementById('questionExplanation').value;
        this.activityData.questions[this.currentQuestionIndex].explanation = explanation;
        this.markAsModified();
    }

    // Multiple choice specific methods
    updateOption(index, value) {
        const currentQuestion = this.activityData.questions[this.currentQuestionIndex];
        if (currentQuestion.options) {
            currentQuestion.options[index] = value;
            this.markAsModified();
        }
    }

    updateCorrectAnswer(index) {
        this.activityData.questions[this.currentQuestionIndex].correctAnswer = index;
        this.markAsModified();
    }

    addOption() {
        const currentQuestion = this.activityData.questions[this.currentQuestionIndex];
        if (currentQuestion.options && currentQuestion.options.length < 6) {
            currentQuestion.options.push('New Option');
            this.markAsModified();
            this.renderCurrentQuestion();
        }
    }

    removeOption(index) {
        const currentQuestion = this.activityData.questions[this.currentQuestionIndex];
        if (currentQuestion.options && currentQuestion.options.length > 2) {
            currentQuestion.options.splice(index, 1);
            
            // Adjust correct answer if necessary
            if (currentQuestion.correctAnswer >= index) {
                currentQuestion.correctAnswer = Math.max(0, currentQuestion.correctAnswer - 1);
            }
            
            this.markAsModified();
            this.renderCurrentQuestion();
        }
    }

    // Calculate total points
    calculateTotalPoints() {
        this.activityData.totalPoints = this.activityData.questions.reduce((total, question) => {
            return total + (question.points || 0);
        }, 0);
    }

    // Save functionality
    saveDraft() {
        this.saveToDataModel();
        customAlert(`Draft saved successfully! Activity "${this.activityData.title}" has ${this.activityData.questions.length} questions (${this.activityData.totalPoints} points total).`);
        this.isModified = false;
    }

    publishActivity() {
        if (this.validateActivity()) {
            this.activityData.status = 'open';
            this.saveToDataModel();
            customAlert(`Activity "${this.activityData.title}" has been published and is now open for submissions!`);
            this.isModified = false;
            
            // Refresh activities view if visible
            if (typeof renderActivities === 'function') {
                renderActivities();
            }
        }
    }

    validateActivity() {
        if (!this.activityData.title.trim()) {
            customAlert('Please provide an activity title before publishing.');
            return false;
        }

        if (this.activityData.questions.length === 0) {
            customAlert('Please add at least one question before publishing.');
            return false;
        }

        // Check for empty questions
        const emptyQuestions = this.activityData.questions.filter(q => !q.question.trim());
        if (emptyQuestions.length > 0) {
            customAlert(`${emptyQuestions.length} question(s) have empty text. Please complete all questions before publishing.`);
            return false;
        }

        return true;
    }

    saveToDataModel() {
        // Update or add activity to global activitiesData
        const existingIndex = activitiesData.findIndex(a => a.id === this.currentActivityId);
        
        const activityRecord = {
            id: this.currentActivityId,
            title: this.activityData.title,
            type: this.activityData.type,
            status: this.activityData.status,
            submissions: existingIndex >= 0 ? activitiesData[existingIndex].submissions : 0,
            graded: existingIndex >= 0 ? activitiesData[existingIndex].graded : 0,
            dueDate: this.activityData.dueDate,
            relatedLesson: existingIndex >= 0 ? activitiesData[existingIndex].relatedLesson : 'Unlinked',
            questions: this.activityData.questions,
            totalPoints: this.activityData.totalPoints
        };

        if (existingIndex >= 0) {
            activitiesData[existingIndex] = activityRecord;
        } else {
            activitiesData.push(activityRecord);
        }

        // Prepare data for PHP backend
        this.preparePHPData();
    }

    preparePHPData() {
        // Structure data for future PHP backend integration
        const phpData = {
            activity_id: this.currentActivityId,
            title: this.activityData.title,
            type: this.activityData.type,
            status: this.activityData.status,
            due_date: this.activityData.dueDate,
            total_points: this.activityData.totalPoints,
            questions: this.activityData.questions.map(question => ({
                question_id: question.id,
                question_type: question.type,
                question_text: question.question,
                points: question.points,
                options: question.options || null,
                correct_answer: question.correctAnswer,
                explanation: question.explanation || null,
                requirements: question.requirements || null,
                rubric: question.rubric || null,
                simulation_url: question.simulationUrl || null,
                tasks: question.tasks || null,
                order_index: this.activityData.questions.indexOf(question)
            })),
            updated_at: new Date().toISOString()
        };

        // Store in localStorage for now (will be replaced with PHP API calls)
        localStorage.setItem(`activity_${this.currentActivityId}`, JSON.stringify(phpData));
        
        console.log('Activity data prepared for PHP backend:', phpData);
    }

    markAsModified() {
        this.isModified = true;
        // Visual indicator that content has been modified
        const saveBtn = document.querySelector('.modal-header-content .action-small-btn.view-btn');
        if (saveBtn && !saveBtn.textContent.includes('*')) {
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Draft *';
        }
    }

    // Bind event handlers
    bindEvents() {
        // Add new question button
        const addQuestionBtn = document.querySelector('.editor-sidebar .action-small-btn.edit-btn');
        if (addQuestionBtn) {
            addQuestionBtn.onclick = () => this.addNewQuestion();
        }

        // Save draft button
        const saveDraftBtn = document.querySelector('.modal-header-content .action-small-btn.view-btn');
        if (saveDraftBtn) {
            saveDraftBtn.onclick = () => this.saveDraft();
        }

        // Publish button
        const publishBtn = document.querySelector('.config-button-group .action-small-btn.publish-btn');
        if (publishBtn) {
            publishBtn.onclick = () => this.publishActivity();
        }

        // Auto-save setup
        this.setupAutoSave();
    }

    setupAutoSave() {
        let autoSaveTimeout;
        const autoSave = () => {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                if (this.isModified) {
                    this.saveToDataModel();
                    console.log('Auto-saved activity data');
                }
            }, 2000);
        };

        // Listen for content changes
        document.addEventListener('input', (e) => {
            if (e.target.id === 'questionText' || e.target.id === 'questionPoints' || e.target.classList.contains('option-input')) {
                autoSave();
            }
        });
    }

    // Export activity data for student dashboard integration
    exportForStudentDashboard() {
        return {
            id: this.currentActivityId,
            title: this.activityData.title,
            type: this.activityData.type,
            questions: this.activityData.questions,
            totalPoints: this.activityData.totalPoints,
            dueDate: this.activityData.dueDate,
            status: this.activityData.status
        };
    }
}

// Global activity editor instance
const activityEditor = new ActivityEditor();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ActivityEditor;
}