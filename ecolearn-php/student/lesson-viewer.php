<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('student');

$user = getCurrentUser();
$functions = new EcoLearnFunctions();

$lesson_id = $_GET['id'] ?? null;
if (!$lesson_id) {
    header('Location: lessons.php');
    exit();
}

$lesson = $functions->getLessonById($lesson_id);
if (!$lesson || $lesson['status'] !== 'published') {
    header('Location: lessons.php');
    exit();
}

$slides = $functions->getLessonSlides($lesson_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLearn - <?php echo htmlspecialchars($lesson['title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="lesson-viewer-body">
    <!-- Lesson Header -->
    <header class="lesson-header">
        <div class="lesson-nav">
            <button class="nav-btn" onclick="window.location.href='my-lessons.php'">
                <i class="fas fa-arrow-left"></i> Back to Lessons
            </button>
            <div class="lesson-info">
                <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
                <p>By: <?php echo htmlspecialchars($lesson['teacher_name']); ?></p>
            </div>
            <div class="lesson-controls">
                <button class="nav-btn" onclick="toggleFullscreen()">
                    <i class="fas fa-expand"></i>
                </button>
                <button class="nav-btn" onclick="showNotes()">
                    <i class="fas fa-sticky-note"></i>
                </button>
            </div>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill"></div>
        </div>
    </header>
    
    <!-- Lesson Content -->
    <main class="lesson-content">
        <?php if (empty($slides)): ?>
            <!-- Single content lesson -->
            <div class="lesson-slide active" data-slide="0">
                <div class="slide-content">
                    <div class="content-area">
                        <?php echo nl2br(htmlspecialchars($lesson['content'])); ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Multi-slide lesson -->
            <?php foreach ($slides as $index => $slide): ?>
                <div class="lesson-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>">
                    <div class="slide-content">
                        <h2><?php echo htmlspecialchars($slide['title']); ?></h2>
                        <div class="content-area">
                            <?php if ($slide['media_url']): ?>
                                <div class="media-container">
                                    <img src="<?php echo htmlspecialchars($slide['media_url']); ?>" alt="Lesson media" class="lesson-media">
                                </div>
                            <?php endif; ?>
                            <div class="text-content">
                                <?php echo nl2br(htmlspecialchars($slide['content'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
    
    <!-- Navigation Controls -->
    <div class="lesson-navigation">
        <button class="nav-control" id="prevBtn" onclick="previousSlide()" disabled>
            <i class="fas fa-chevron-left"></i> Previous
        </button>
        
        <div class="slide-indicator">
            <span id="currentSlide">1</span> / <span id="totalSlides"><?php echo max(1, count($slides)); ?></span>
        </div>
        
        <button class="nav-control" id="nextBtn" onclick="nextSlide()">
            Next <i class="fas fa-chevron-right"></i>
        </button>
        
        <button class="complete-btn" id="completeBtn" onclick="completeLesson()" style="display: none;">
            <i class="fas fa-check"></i> Complete Lesson
        </button>
    </div>
    
    <!-- Notes Modal -->
    <div id="notesModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-header-content">
                <h3>My Notes</h3>
                <button onclick="hideModal('notesModal')" class="modal-close-btn">&times;</button>
            </div>
            
            <div class="modal-body">
                <textarea id="notesTextarea" rows="10" placeholder="Take notes about this lesson..."></textarea>
            </div>
            
            <div class="modal-footer">
                <button class="action-small-btn" onclick="hideModal('notesModal')">Close</button>
                <button class="action-button" onclick="saveNotes()">Save Notes</button>
            </div>
        </div>
    </div>
    
    <!-- Completion Modal -->
    <div id="completionModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-header-content">
                <h3>Lesson Complete!</h3>
            </div>
            
            <div class="modal-body">
                <div class="completion-content">
                    <i class="fas fa-check-circle fa-3x"></i>
                    <h4>Congratulations!</h4>
                    <p>You have successfully completed "<?php echo htmlspecialchars($lesson['title']); ?>"</p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="action-button" onclick="window.location.href='my-lessons.php'">
                    <i class="fas fa-list"></i> Back to Lessons
                </button>
                <button class="action-small-btn" onclick="window.location.href='index.php'">
                    <i class="fas fa-home"></i> Dashboard
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentSlideIndex = 0;
        const totalSlides = <?php echo max(1, count($slides)); ?>;
        
        function updateSlideDisplay() {
            // Hide all slides
            document.querySelectorAll('.lesson-slide').forEach(slide => {
                slide.classList.remove('active');
            });
            
            // Show current slide
            const currentSlide = document.querySelector(`[data-slide="${currentSlideIndex}"]`);
            if (currentSlide) {
                currentSlide.classList.add('active');
            }
            
            // Update indicators
            document.getElementById('currentSlide').textContent = currentSlideIndex + 1;
            
            // Update navigation buttons
            document.getElementById('prevBtn').disabled = currentSlideIndex === 0;
            
            if (currentSlideIndex === totalSlides - 1) {
                document.getElementById('nextBtn').style.display = 'none';
                document.getElementById('completeBtn').style.display = 'inline-block';
            } else {
                document.getElementById('nextBtn').style.display = 'inline-block';
                document.getElementById('completeBtn').style.display = 'none';
            }
            
            // Update progress bar
            const progress = ((currentSlideIndex + 1) / totalSlides) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
        }
        
        function nextSlide() {
            if (currentSlideIndex < totalSlides - 1) {
                currentSlideIndex++;
                updateSlideDisplay();
            }
        }
        
        function previousSlide() {
            if (currentSlideIndex > 0) {
                currentSlideIndex--;
                updateSlideDisplay();
            }
        }
        
        function completeLesson() {
            // Record lesson completion
            fetch('../api/lessons.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=complete&lesson_id=<?php echo $lesson_id; ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('completionModal').style.display = 'flex';
                } else {
                    alert('Error recording completion');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show completion modal anyway
                document.getElementById('completionModal').style.display = 'flex';
            });
        }
        
        function showNotes() {
            // Load existing notes
            const savedNotes = localStorage.getItem('lesson_notes_<?php echo $lesson_id; ?>');
            if (savedNotes) {
                document.getElementById('notesTextarea').value = savedNotes;
            }
            document.getElementById('notesModal').style.display = 'flex';
        }
        
        function saveNotes() {
            const notes = document.getElementById('notesTextarea').value;
            localStorage.setItem('lesson_notes_<?php echo $lesson_id; ?>', notes);
            alert('Notes saved!');
            hideModal('notesModal');
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' || e.key === ' ') {
                e.preventDefault();
                nextSlide();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                previousSlide();
            } else if (e.key === 'Escape') {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }
            }
        });
        
        // Initialize
        updateSlideDisplay();
    </script>
    
    <style>
        .lesson-viewer-body {
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .lesson-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .lesson-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
        }
        
        .lesson-info h1 {
            margin: 0;
            color: var(--text-color);
            font-size: 1.5rem;
        }
        
        .lesson-info p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
        }
        
        .lesson-controls {
            display: flex;
            gap: 10px;
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
        
        .lesson-content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
            min-height: calc(100vh - 200px);
        }
        
        .lesson-slide {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        .lesson-slide.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-content {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .slide-content h2 {
            margin: 0 0 30px 0;
            color: var(--text-color);
            font-size: 2rem;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 15px;
        }
        
        .content-area {
            line-height: 1.8;
            color: var(--text-color);
        }
        
        .media-container {
            margin: 20px 0;
            text-align: center;
        }
        
        .lesson-media {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .text-content {
            font-size: 1.1rem;
        }
        
        .lesson-navigation {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav-control {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        
        .nav-control:hover:not(:disabled) {
            background: #219a52;
        }
        
        .nav-control:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }
        
        .complete-btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        
        .complete-btn:hover {
            background: #219a52;
        }
        
        .slide-indicator {
            color: var(--text-color);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .completion-content {
            text-align: center;
            padding: 20px;
        }
        
        .completion-content i {
            color: #27ae60;
            margin-bottom: 20px;
        }
        
        .completion-content h4 {
            margin: 0 0 15px 0;
            color: var(--text-color);
        }
        
        .modal-footer {
            padding: 20px;
            background: #f8f9fa;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        #notesTextarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            resize: vertical;
        }
        
        @media (max-width: 768px) {
            .lesson-nav {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .lesson-content {
                padding: 20px 15px;
            }
            
            .slide-content {
                padding: 25px 20px;
            }
            
            .lesson-navigation {
                flex-direction: column;
                gap: 10px;
                padding: 15px;
            }
        }
    </style>
</body>
</html>