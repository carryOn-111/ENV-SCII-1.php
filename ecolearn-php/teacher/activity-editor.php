<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('teacher');

$user = getCurrentUser();
$functions = new EcoLearnFunctions();

$activity_id = $_GET['id'] ?? null;
$activity = null;
$questions = [];

if ($activity_id) {
    $activity = $functions->getActivityById($activity_id);
    if (!$activity || $activity['teacher_id'] != $user['id']) {
        header('Location: activities.php');
        exit();
    }
    $questions = $functions->getActivityQuestions($activity_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLearn - Activity Editor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="logo">
            <i class="fas fa-leaf fa-2x"></i>
            <h2>EcoLearn</h2>
        </div>
        
        <div class="nav-section">
            <div class="nav-item dashboard" onclick="window.location.href='index.php'">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            
            <div class="nav-item lessons" onclick="window.location.href='lessons.php'">
                <i class="fas fa-book-open"></i>
                <span>Lessons</span>
            </div>
            
            <div class="nav-item activities active" onclick="window.location.href='activities.php'">
                <i class="fas fa-puzzle-piece"></i>
                <span>Activities</span>
            </div>
        </div>
        
        <div class="footer-nav-section">
            <div class="nav-item logout" onclick="window.location.href='../logout.php'">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <header class="header">
            <i class="fas fa-edit"></i>
            <h1><?php echo $activity ? 'Edit Activity' : 'Create Activity'; ?></h1>
            <div class="header-actions">
                <button class="action-small-btn view-btn" onclick="saveActivity()">
                    <i class="fas fa-save"></i> Save Draft
                </button>
                <?php if ($activity && $activity['status'] === 'draft'): ?>
                    <button class="action-small-btn publish-btn" onclick="publishActivity()">
                        <i class="fas fa-upload"></i> Publish
                    </button>
                <?php endif; ?>
                <button class="action-small-btn" onclick="window.location.href='activities.php'">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
            </div>
        </header>
        
        <div class="editor-container">
            <!-- Activity Info Panel -->
            <div class="activity-info-panel">
                <h3>Activity Information</h3>
                <form id="activityInfoForm">
                    <input type="hidden" id="activityId" value="<?php echo $activity['id'] ?? ''; ?>">
                    
                    <div class="form-group">
                        <label for="activityTitle">Title:</label>
                        <input type="text" id="activityTitle" value="<?php echo htmlspecialchars($activity['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="activityType">Type:</label>
                        <select id="activityType">
                            <option value="Quiz" <?php echo ($activity['type'] ?? '') === 'Quiz' ? 'selected' : ''; ?>>Quiz</option>
                            <option value="Project" <?php echo ($activity['type'] ?? '') === 'Project' ? 'selected' : ''; ?>>Project</option>
                            <option value="Simulation" <?php echo ($activity['type'] ?? '') === 'Simulation' ? 'selected' : ''; ?>>Simulation</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="activityDueDate">Due Date:</label>
                        <input type="date" id="activityDueDate" value="<?php echo $activity['due_date'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="activityDescription">Description:</label>
                        <textarea id="activityDescription" rows="4"><?php echo htmlspecialchars($activity['description'] ?? ''); ?></textarea>
                    </div>
                </form>
            </div>
            
            <!-- Questions Panel -->
            <div class="questions-panel">
                <div class="questions-header">
                    <h3>Questions</h3>
                    <button class="action-small-btn edit-btn" onclick="addNewQuestion()">
                        <i class="fas fa-plus"></i> Add Question
                    </button>
                </div>
                
                <div id="questionsList">
                    <?php if (empty($questions)): ?>
                        <div class="no-questions">
                            <p>No questions added yet. Click "Add Question" to get started.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="question-item" data-question-id="<?php echo $question['id']; ?>">
                                <div class="question-header">
                                    <h4>Question <?php echo $index + 1; ?></h4>
                                    <div class="question-actions">
                                        <button class="action-small-btn edit-btn" onclick="editQuestion(<?php echo $question['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-small-btn delete-btn" onclick="deleteQuestion(<?php echo $question['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="question-preview">
                                    <p><strong>Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $question['question_type'])); ?></p>
                                    <p><strong>Points:</strong> <?php echo $question['points']; ?></p>
                                    <p><strong>Question:</strong> <?php echo htmlspecialchars(substr($question['question_text'], 0, 100)) . '...'; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Editor Modal -->
    <div id="questionEditorModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog lg">
            <div class="modal-header-content">
                <h3 id="questionModalTitle">Add Question</h3>
                <button onclick="hideModal('questionEditorModal')" class="modal-close-btn">&times;</button>
            </div>
            
            <form id="questionForm">
                <input type="hidden" id="questionId" value="">
                
                <div class="form-group">
                    <label for="questionText">Question Text:</label>
                    <textarea id="questionText" rows="4" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="questionType">Question Type:</label>
                        <select id="questionType" onchange="toggleQuestionOptions()">
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="short_answer">Short Answer</option>
                            <option value="essay">Essay</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="questionPoints">Points:</label>
                        <input type="number" id="questionPoints" value="1" min="1" required>
                    </div>
                </div>
                
                <div id="optionsContainer" class="options-container">
                    <h4>Answer Options</h4>
                    <div id="optionsList">
                        <!-- Options will be added dynamically -->
                    </div>
                    <button type="button" class="action-small-btn edit-btn" onclick="addOption()">
                        <i class="fas fa-plus"></i> Add Option
                    </button>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="action-small-btn" onclick="hideModal('questionEditorModal')">Cancel</button>
                    <button type="submit" class="action-button">Save Question</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentQuestionId = null;
        let optionCounter = 0;
        
        function saveActivity() {
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', document.getElementById('activityId').value);
            formData.append('title', document.getElementById('activityTitle').value);
            formData.append('type', document.getElementById('activityType').value);
            formData.append('due_date', document.getElementById('activityDueDate').value);
            formData.append('description', document.getElementById('activityDescription').value);
            
            fetch('../api/activities.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Activity saved successfully!');
                } else {
                    alert('Error saving activity: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving activity');
            });
        }
        
        function publishActivity() {
            if (confirm('Are you sure you want to publish this activity? Students will be able to access it.')) {
                const formData = new FormData();
                formData.append('action', 'publish');
                formData.append('id', document.getElementById('activityId').value);
                
                fetch('../api/activities.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Activity published successfully!');
                        window.location.reload();
                    } else {
                        alert('Error publishing activity: ' + data.message);
                    }
                });
            }
        }
        
        function addNewQuestion() {
            currentQuestionId = null;
            document.getElementById('questionModalTitle').textContent = 'Add Question';
            document.getElementById('questionForm').reset();
            document.getElementById('questionId').value = '';
            toggleQuestionOptions();
            document.getElementById('questionEditorModal').style.display = 'flex';
        }
        
        function editQuestion(questionId) {
            currentQuestionId = questionId;
            document.getElementById('questionModalTitle').textContent = 'Edit Question';
            
            // Load question data (would fetch from API in real implementation)
            document.getElementById('questionId').value = questionId;
            document.getElementById('questionEditorModal').style.display = 'flex';
        }
        
        function deleteQuestion(questionId) {
            if (confirm('Are you sure you want to delete this question?')) {
                fetch('../api/activities.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete_question&question_id=' + questionId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Question deleted successfully!');
                        window.location.reload();
                    } else {
                        alert('Error deleting question: ' + data.message);
                    }
                });
            }
        }
        
        function toggleQuestionOptions() {
            const questionType = document.getElementById('questionType').value;
            const optionsContainer = document.getElementById('optionsContainer');
            
            if (questionType === 'multiple_choice') {
                optionsContainer.style.display = 'block';
                initializeOptions();
            } else {
                optionsContainer.style.display = 'none';
            }
        }
        
        function initializeOptions() {
            const optionsList = document.getElementById('optionsList');
            optionsList.innerHTML = '';
            optionCounter = 0;
            
            // Add default options
            addOption();
            addOption();
        }
        
        function addOption() {
            optionCounter++;
            const optionsList = document.getElementById('optionsList');
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-item';
            optionDiv.innerHTML = `
                <div class="option-row">
                    <input type="radio" name="correctAnswer" value="${optionCounter}">
                    <input type="text" placeholder="Option ${optionCounter}" class="option-text">
                    <button type="button" class="delete-option-btn" onclick="removeOption(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            optionsList.appendChild(optionDiv);
        }
        
        function removeOption(button) {
            button.closest('.option-item').remove();
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Handle question form submission
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'save_question');
            formData.append('activity_id', document.getElementById('activityId').value);
            formData.append('question_id', document.getElementById('questionId').value);
            formData.append('question_text', document.getElementById('questionText').value);
            formData.append('question_type', document.getElementById('questionType').value);
            formData.append('points', document.getElementById('questionPoints').value);
            
            // Add options for multiple choice questions
            if (document.getElementById('questionType').value === 'multiple_choice') {
                const options = [];
                const correctAnswer = document.querySelector('input[name="correctAnswer"]:checked')?.value;
                
                document.querySelectorAll('.option-text').forEach((input, index) => {
                    if (input.value.trim()) {
                        options.push({
                            text: input.value.trim(),
                            is_correct: (index + 1) == correctAnswer
                        });
                    }
                });
                
                formData.append('options', JSON.stringify(options));
            }
            
            fetch('../api/activities.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Question saved successfully!');
                    hideModal('questionEditorModal');
                    window.location.reload();
                } else {
                    alert('Error saving question: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving question');
            });
        });
        
        // Initialize
        toggleQuestionOptions();
    </script>
    
    <style>
        .editor-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .activity-info-panel, .questions-panel {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .activity-info-panel h3, .questions-panel h3 {
            margin: 0 0 20px 0;
            color: var(--text-color);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .questions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .question-item {
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
        }
        
        .question-header {
            background: #f8f9fa;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .question-header h4 {
            margin: 0;
            color: var(--text-color);
        }
        
        .question-actions {
            display: flex;
            gap: 10px;
        }
        
        .question-preview {
            padding: 15px;
        }
        
        .question-preview p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        
        .no-questions {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .options-container {
            margin-top: 20px;
        }
        
        .options-container h4 {
            margin: 0 0 15px 0;
            color: var(--text-color);
        }
        
        .option-item {
            margin-bottom: 10px;
        }
        
        .option-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .option-text {
            flex: 1;
        }
        
        .delete-option-btn {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px;
            cursor: pointer;
        }
        
        .delete-option-btn:hover {
            background: #c0392b;
        }
        
        .modal-footer {
            padding: 20px;
            background: #f8f9fa;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .delete-btn {
            background: #e74c3c;
            color: white;
        }
        
        .delete-btn:hover {
            background: #c0392b;
        }
        
        @media (max-width: 768px) {
            .editor-container {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>