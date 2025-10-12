<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('student');

$user = getCurrentUser();
$functions = new EcoLearnFunctions();

$activity_id = $_GET['id'] ?? null;
if (!$activity_id) {
    header('Location: activities.php');
    exit();
}

$activity = $functions->getActivityById($activity_id);
if (!$activity || $activity['status'] !== 'published') {
    header('Location: activities.php');
    exit();
}

$questions = $functions->getActivityQuestions($activity_id);
$existing_submission = $functions->getStudentSubmission($user['id'], $activity_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLearn - <?php echo htmlspecialchars($activity['title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="activity-viewer-body">
    <!-- Activity Header -->
    <header class="activity-header">
        <div class="activity-nav">
            <button class="nav-btn" onclick="window.location.href='my-activities.php'">
                <i class="fas fa-arrow-left"></i> Back to Activities
            </button>
            <div class="activity-info">
                <h1><?php echo htmlspecialchars($activity['title']); ?></h1>
                <div class="activity-meta">
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($activity['teacher_name']); ?></span>
                    <span><i class="fas fa-puzzle-piece"></i> <?php echo $activity['type']; ?></span>
                    <?php if ($activity['due_date']): ?>
                        <span><i class="fas fa-calendar"></i> Due: <?php echo formatDate($activity['due_date']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="activity-controls">
                <div class="timer" id="timer" style="display: none;">
                    <i class="fas fa-clock"></i>
                    <span id="timeDisplay">00:00</span>
                </div>
            </div>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill"></div>
        </div>
    </header>
    
    <?php if ($existing_submission): ?>
        <!-- Show results if already submitted -->
        <main class="activity-content">
            <div class="submission-results">
                <div class="results-header">
                    <i class="fas fa-check-circle fa-3x"></i>
                    <h2>Activity Completed</h2>
                    <p>You have already submitted this activity.</p>
                </div>
                
                <div class="score-display">
                    <div class="score-card">
                        <h3>Your Score</h3>
                        <div class="score-value">
                            <?php echo $existing_submission['total_score']; ?>%
                        </div>
                        <p>Submitted: <?php echo formatDate($existing_submission['submitted_at']); ?></p>
                    </div>
                </div>
                
                <div class="results-actions">
                    <button class="action-button" onclick="window.location.href='my-activities.php'">
                        <i class="fas fa-list"></i> Back to Activities
                    </button>
                </div>
            </div>
        </main>
    <?php else: ?>
        <!-- Activity Form -->
        <main class="activity-content">
            <form id="activityForm">
                <input type="hidden" name="activity_id" value="<?php echo $activity_id; ?>">
                
                <?php if ($activity['description']): ?>
                    <div class="activity-description">
                        <h3>Instructions</h3>
                        <p><?php echo nl2br(htmlspecialchars($activity['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="questions-container">
                    <?php if (empty($questions)): ?>
                        <div class="no-questions">
                            <p>This activity has no questions yet. Please check back later.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="question-card" data-question="<?php echo $index; ?>">
                                <div class="question-header">
                                    <h4>Question <?php echo $index + 1; ?></h4>
                                    <span class="points-badge"><?php echo $question['points']; ?> pts</span>
                                </div>
                                
                                <div class="question-content">
                                    <p class="question-text"><?php echo nl2br(htmlspecialchars($question['question_text'])); ?></p>
                                    
                                    <?php if ($question['question_type'] === 'multiple_choice'): ?>
                                        <div class="answer-options">
                                            <?php 
                                            $options = $functions->getQuestionOptions($question['id']);
                                            foreach ($options as $option): 
                                            ?>
                                                <label class="option-label">
                                                    <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo $option['id']; ?>" required>
                                                    <span class="option-text"><?php echo htmlspecialchars($option['option_text']); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($question['question_type'] === 'short_answer'): ?>
                                        <div class="answer-input">
                                            <textarea name="question_<?php echo $question['id']; ?>" rows="3" placeholder="Enter your answer..." required></textarea>
                                        </div>
                                    <?php elseif ($question['question_type'] === 'essay'): ?>
                                        <div class="answer-input">
                                            <textarea name="question_<?php echo $question['id']; ?>" rows="8" placeholder="Write your essay response..." required></textarea>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($questions)): ?>
                    <div class="submission-section">
                        <div class="submission-info">
                            <p><strong>Total Questions:</strong> <?php echo count($questions); ?></p>
                            <p><strong>Total Points:</strong> <?php echo array_sum(array_column($questions, 'points')); ?></p>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Submit Activity
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </main>
    <?php endif; ?>
    
    <!-- Confirmation Modal -->
    <div id="confirmSubmissionModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-header-content">
                <h3>Confirm Submission</h3>
            </div>
            
            <div class="modal-body">
                <p>Are you sure you want to submit this activity? You won't be able to change your answers after submission.</p>
                <div class="submission-summary" id="submissionSummary">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="action-small-btn" onclick="hideModal('confirmSubmissionModal')">Cancel</button>
                <button class="action-button" onclick="submitActivity()">
                    <i class="fas fa-check"></i> Confirm Submit
                </button>
            </div>
        </div>
    </div>

    <script>
        let startTime = new Date();
        let timerInterval;
        
        function startTimer() {
            timerInterval = setInterval(function() {
                const now = new Date();
                const elapsed = Math.floor((now - startTime) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                
                document.getElementById('timeDisplay').textContent = 
                    String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            }, 1000);
            
            document.getElementById('timer').style.display = 'flex';
        }
        
        function updateProgress() {
            const questions = document.querySelectorAll('.question-card');
            let answered = 0;
            
            questions.forEach(question => {
                const inputs = question.querySelectorAll('input[type="radio"]:checked, textarea');
                let hasAnswer = false;
                
                inputs.forEach(input => {
                    if (input.type === 'radio' && input.checked) {
                        hasAnswer = true;
                    } else if (input.tagName === 'TEXTAREA' && input.value.trim()) {
                        hasAnswer = true;
                    }
                });
                
                if (hasAnswer) {
                    answered++;
                    question.classList.add('answered');
                } else {
                    question.classList.remove('answered');
                }
            });
            
            const progress = (answered / questions.length) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
        }
        
        function showSubmissionConfirmation() {
            const questions = document.querySelectorAll('.question-card');
            let answered = 0;
            
            questions.forEach(question => {
                const inputs = question.querySelectorAll('input[type="radio"]:checked, textarea');
                inputs.forEach(input => {
                    if ((input.type === 'radio' && input.checked) || 
                        (input.tagName === 'TEXTAREA' && input.value.trim())) {
                        answered++;
                    }
                });
            });
            
            const summary = `
                <p><strong>Questions Answered:</strong> ${answered} of ${questions.length}</p>
                <p><strong>Time Spent:</strong> ${document.getElementById('timeDisplay').textContent}</p>
            `;
            
            document.getElementById('submissionSummary').innerHTML = summary;
            document.getElementById('confirmSubmissionModal').style.display = 'flex';
        }
        
        function submitActivity() {
            const formData = new FormData(document.getElementById('activityForm'));
            formData.append('action', 'submit');
            
            // Add time spent
            const timeSpent = document.getElementById('timeDisplay').textContent;
            formData.append('time_spent', timeSpent);
            
            fetch('../api/activities.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Activity submitted successfully!');
                    window.location.reload();
                } else {
                    alert('Error submitting activity: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting activity');
            })
            .finally(() => {
                hideModal('confirmSubmissionModal');
            });
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Form submission handler
        document.getElementById('activityForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            showSubmissionConfirmation();
        });
        
        // Progress tracking
        document.addEventListener('change', updateProgress);
        document.addEventListener('input', updateProgress);
        
        // Auto-save functionality (optional)
        let autoSaveInterval = setInterval(function() {
            const formData = new FormData(document.getElementById('activityForm'));
            formData.append('action', 'auto_save');
            
            fetch('../api/activities.php', {
                method: 'POST',
                body: formData
            }).catch(error => {
                console.log('Auto-save failed:', error);
            });
        }, 30000); // Auto-save every 30 seconds
        
        // Initialize
        <?php if (!$existing_submission): ?>
            startTimer();
            updateProgress();
        <?php endif; ?>
        
        // Prevent accidental page leave
        <?php if (!$existing_submission): ?>
        window.addEventListener('beforeunload', function(e) {
            e.preventDefault();
            e.returnValue = 'Are you sure you want to leave? Your progress may be lost.';
        });
        <?php endif; ?>
    </script>
    
    <style>
        .activity-viewer-body {
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .activity-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .activity-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
        }

        .nav-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-btn:hover {
            background: #219a52;
        }
        
        .activity-info h1 {
            margin: 0;
            color: var(--text-color);
            font-size: 1.5rem;
        }
        
        .activity-meta {
            margin-top: 8px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .activity-meta span {
            color: #7f8c8d;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .timer {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-color);
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .progress-bar {
            height: 4px;
            background: #ecf0f1;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--primary-color);
            transition: width 0.3s ease;
            width: 0%;
        }
        
        .activity-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .activity-description {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .activity-description h3 {
            margin: 0 0 15px 0;
            color: var(--text-color);
        }
        
        .question-card {
            background: white;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .question-card.answered {
            border-left: 4px solid var(--primary-color);
        }
        
        .question-header {
            background: #f8f9fa;
            padding: 20px 25px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .question-header h4 {
            margin: 0;
            color: var(--text-color);
        }
        
        .points-badge {
            background: var(--secondary-color);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .question-content {
            padding: 25px;
        }
        
        .question-text {
            margin: 0 0 20px 0;
            color: var(--text-color);
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .answer-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .option-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .option-label:hover {
            background: #e9ecef;
        }
        
        .option-label input[type="radio"] {
            margin: 0;
        }
        
        .option-text {
            flex: 1;
            color: var(--text-color);
        }
        
        .answer-input textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            transition: border-color 0.3s ease;
        }
        
        .answer-input textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .submission-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }
        
        .submission-info p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        
        .submit-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background: #219a52;
            transform: translateY(-2px);
        }
        
        .submission-results {
            text-align: center;
            padding: 40px;
        }
        
        .results-header i {
            color: #27ae60;
            margin-bottom: 20px;
        }
        
        .results-header h2 {
            margin: 0 0 10px 0;
            color: var(--text-color);
        }
        
        .score-display {
            margin: 40px 0;
        }
        
        .score-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            display: inline-block;
            min-width: 200px;
        }
        
        .score-card h3 {
            margin: 0 0 15px 0;
            color: var(--text-color);
        }
        
        .score-value {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .no-questions {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .modal-footer {
            padding: 20px;
            background: #f8f9fa;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .activity-nav {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .activity-meta {
                justify-content: center;
            }
            
            .submission-section {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .question-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</body>
</html>