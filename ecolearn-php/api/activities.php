<?php
require_once '../config/session.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$functions = new EcoLearnFunctions();
$user = getCurrentUser();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        if ($user['role'] !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $title = $_POST['title'] ?? '';
        $type = $_POST['type'] ?? '';
        $due_date = $_POST['due_date'] ?? null;
        $description = $_POST['description'] ?? '';
        
        if (empty($title) || empty($type)) {
            echo json_encode(['success' => false, 'message' => 'Title and type are required']);
            exit();
        }
        
        try {
            $activity_id = $functions->createActivity($title, $description, $user['id'], $type, $due_date);
            echo json_encode(['success' => true, 'activity_id' => $activity_id]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error creating activity']);
        }
        break;
        
    case 'update':
        if ($user['role'] !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $activity_id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $type = $_POST['type'] ?? '';
        $due_date = $_POST['due_date'] ?? null;
        $description = $_POST['description'] ?? '';
        
        if (empty($activity_id) || empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Activity ID and title are required']);
            exit();
        }
        
        try {
            $result = $functions->updateActivity($activity_id, $title, $description, $type, $due_date, $user['id']);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update activity']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error updating activity']);
        }
        break;
        
    case 'publish':
        if ($user['role'] !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $activity_id = $_POST['id'] ?? '';
        
        if (empty($activity_id)) {
            echo json_encode(['success' => false, 'message' => 'Activity ID required']);
            exit();
        }
        
        try {
            $result = $functions->publishActivity($activity_id, $user['id']);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to publish activity']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error publishing activity']);
        }
        break;
        
    case 'save_question':
        if ($user['role'] !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $activity_id = $_POST['activity_id'] ?? '';
        $question_id = $_POST['question_id'] ?? '';
        $question_text = $_POST['question_text'] ?? '';
        $question_type = $_POST['question_type'] ?? '';
        $points = $_POST['points'] ?? 1;
        
        if (empty($activity_id) || empty($question_text) || empty($question_type)) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing']);
            exit();
        }
        
        try {
            if (empty($question_id)) {
                // Create new question
                $question_number = $functions->getNextQuestionNumber($activity_id);
                $new_question_id = $functions->addQuestion($activity_id, $question_number, $question_text, $question_type, $points);
                
                // Add options for multiple choice questions
                if ($question_type === 'multiple_choice' && isset($_POST['options'])) {
                    $options = json_decode($_POST['options'], true);
                    foreach ($options as $index => $option) {
                        $functions->addQuestionOption($new_question_id, $option['text'], $option['is_correct'], $index + 1);
                    }
                }
                
                echo json_encode(['success' => true, 'question_id' => $new_question_id]);
            } else {
                // Update existing question
                $result = $functions->updateQuestion($question_id, $question_text, $question_type, $points, $user['id']);
                echo json_encode(['success' => $result]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error saving question']);
        }
        break;
        
    case 'delete_question':
        if ($user['role'] !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $question_id = $_POST['question_id'] ?? '';
        
        if (empty($question_id)) {
            echo json_encode(['success' => false, 'message' => 'Question ID required']);
            exit();
        }
        
        try {
            $result = $functions->deleteQuestion($question_id, $user['id']);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error deleting question']);
        }
        break;
        
    case 'submit':
        if ($user['role'] !== 'student') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $activity_id = $_POST['activity_id'] ?? '';
        $time_spent = $_POST['time_spent'] ?? '';
        
        if (empty($activity_id)) {
            echo json_encode(['success' => false, 'message' => 'Activity ID required']);
            exit();
        }
        
        // Check if already submitted
        $existing = $functions->getStudentSubmission($user['id'], $activity_id);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Activity already submitted']);
            exit();
        }
        
        try {
            // Collect answers
            $answers = [];
            $questions = $functions->getActivityQuestions($activity_id);
            
            foreach ($questions as $question) {
                $answer_key = 'question_' . $question['id'];
                if (isset($_POST[$answer_key])) {
                    $answers[$question['id']] = $_POST[$answer_key];
                }
            }
            
            // Calculate score
            $total_score = $functions->calculateScore($activity_id, $answers);
            
            // Save submission
            $submission_id = $functions->saveStudentSubmission($user['id'], $activity_id, $answers, $total_score, $time_spent);
            
            echo json_encode(['success' => true, 'submission_id' => $submission_id, 'score' => $total_score]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error submitting activity']);
        }
        break;
        
    case 'auto_save':
        if ($user['role'] !== 'student') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $activity_id = $_POST['activity_id'] ?? '';
        
        if (empty($activity_id)) {
            echo json_encode(['success' => false, 'message' => 'Activity ID required']);
            exit();
        }
        
        try {
            // Save progress (implement auto-save logic)
            $answers = [];
            $questions = $functions->getActivityQuestions($activity_id);
            
            foreach ($questions as $question) {
                $answer_key = 'question_' . $question['id'];
                if (isset($_POST[$answer_key])) {
                    $answers[$question['id']] = $_POST[$answer_key];
                }
            }
            
            $functions->saveActivityProgress($user['id'], $activity_id, $answers);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Auto-save failed']);
        }
        break;
        
    case 'get_all':
        try {
            if ($user['role'] === 'teacher') {
                $activities = $functions->getTeacherActivities($user['id']);
            } else {
                $activities = $functions->getPublishedActivities();
            }
            echo json_encode(['success' => true, 'activities' => $activities]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error loading activities']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>