<?php
require_once 'config/database.php';

class EcoLearnFunctions {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // User Authentication
    public function authenticateUser($email, $password) {
        $query = "SELECT id, username, email, password, role, full_name FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
    
    public function createUser($username, $email, $password, $role, $full_name) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, email, password, role, full_name) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$username, $email, $hashed_password, $role, $full_name]);
    }
    
    public function getUserById($user_id) {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getUserByEmail($email) {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Lesson Management
    public function createLesson($title, $content, $teacher_id, $background_type = 'simple', $background_image = null) {
        $qr_code = $this->generateQRCode("lesson_" . uniqid());
        $query = "INSERT INTO lessons (title, content, teacher_id, background_type, background_image, qr_code, access_code) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $access_code = strtoupper(substr(md5(uniqid()), 0, 6));
        $stmt->execute([$title, $content, $teacher_id, $background_type, $background_image, $qr_code, $access_code]);
        return $this->conn->lastInsertId();
    }
    
    public function getTeacherLessons($teacher_id) {
        $query = "SELECT * FROM lessons WHERE teacher_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPublishedLessons() {
        $query = "SELECT l.*, u.full_name as teacher_name FROM lessons l 
                  JOIN users u ON l.teacher_id = u.id 
                  WHERE l.status = 'published' 
                  ORDER BY l.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getLessonById($lesson_id) {
        $query = "SELECT l.*, u.full_name as teacher_name FROM lessons l 
                  JOIN users u ON l.teacher_id = u.id 
                  WHERE l.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$lesson_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getLessonByAccessCode($access_code) {
        $query = "SELECT l.*, u.full_name as teacher_name FROM lessons l 
                  JOIN users u ON l.teacher_id = u.id 
                  WHERE l.access_code = ? AND l.status = 'published'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$access_code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getLessonSlides($lesson_id) {
        $query = "SELECT * FROM lesson_slides WHERE lesson_id = ? ORDER BY slide_number";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$lesson_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function publishLesson($lesson_id, $teacher_id) {
        $query = "UPDATE lessons SET status = 'published' WHERE id = ? AND teacher_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$lesson_id, $teacher_id]);
    }
    
    // Activity Management
    public function createActivity($title, $description, $teacher_id, $activity_type, $due_date = null) {
        $qr_code = $this->generateQRCode("activity_" . uniqid());
        $access_code = strtoupper(substr(md5(uniqid()), 0, 6));
        $query = "INSERT INTO activities (title, description, teacher_id, type, due_date, qr_code, access_code) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$title, $description, $teacher_id, $activity_type, $due_date, $qr_code, $access_code]);
        return $this->conn->lastInsertId();
    }
    
    public function updateActivity($activity_id, $title, $description, $type, $due_date, $teacher_id) {
        $query = "UPDATE activities SET title = ?, description = ?, type = ?, due_date = ? WHERE id = ? AND teacher_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$title, $description, $type, $due_date, $activity_id, $teacher_id]);
    }
    
    public function getTeacherActivities($teacher_id) {
        $query = "SELECT * FROM activities WHERE teacher_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPublishedActivities() {
        $query = "SELECT a.*, u.full_name as teacher_name FROM activities a 
                  JOIN users u ON a.teacher_id = u.id 
                  WHERE a.status = 'published' 
                  ORDER BY a.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getActivityById($activity_id) {
        $query = "SELECT a.*, u.full_name as teacher_name FROM activities a 
                  JOIN users u ON a.teacher_id = u.id 
                  WHERE a.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$activity_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getActivityByAccessCode($access_code) {
        $query = "SELECT a.*, u.full_name as teacher_name FROM activities a 
                  JOIN users u ON a.teacher_id = u.id 
                  WHERE a.access_code = ? AND a.status = 'published'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$access_code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function publishActivity($activity_id, $teacher_id) {
        $query = "UPDATE activities SET status = 'published' WHERE id = ? AND teacher_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$activity_id, $teacher_id]);
    }
    
    // Question Management
    public function addQuestion($activity_id, $question_number, $question_text, $question_type, $points = 1) {
        $query = "INSERT INTO activity_questions (activity_id, question_number, question_text, question_type, points) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$activity_id, $question_number, $question_text, $question_type, $points]);
        return $this->conn->lastInsertId();
    }
    
    public function updateQuestion($question_id, $question_text, $question_type, $points, $teacher_id) {
        $query = "UPDATE activity_questions aq 
                  JOIN activities a ON aq.activity_id = a.id 
                  SET aq.question_text = ?, aq.question_type = ?, aq.points = ? 
                  WHERE aq.id = ? AND a.teacher_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$question_text, $question_type, $points, $question_id, $teacher_id]);
    }
    
    public function deleteQuestion($question_id, $teacher_id) {
        $query = "DELETE aq FROM activity_questions aq 
                  JOIN activities a ON aq.activity_id = a.id 
                  WHERE aq.id = ? AND a.teacher_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$question_id, $teacher_id]);
    }
    
    public function getActivityQuestions($activity_id) {
        $query = "SELECT * FROM activity_questions WHERE activity_id = ? ORDER BY question_number";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$activity_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getNextQuestionNumber($activity_id) {
        $query = "SELECT COALESCE(MAX(question_number), 0) + 1 as next_number FROM activity_questions WHERE activity_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$activity_id]);
        return $stmt->fetchColumn();
    }
    
    public function addQuestionOption($question_id, $option_text, $is_correct, $option_order) {
        $query = "INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$question_id, $option_text, $is_correct, $option_order]);
    }
    
    public function getQuestionOptions($question_id) {
        $query = "SELECT * FROM question_options WHERE question_id = ? ORDER BY option_order";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$question_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Student Submissions
    public function getStudentSubmission($student_id, $activity_id) {
        $query = "SELECT * FROM student_submissions WHERE student_id = ? AND activity_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$student_id, $activity_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function saveStudentSubmission($student_id, $activity_id, $answers, $total_score, $time_spent = null) {
        $submission_data = json_encode([
            'answers' => $answers,
            'time_spent' => $time_spent,
            'submitted_at' => date('Y-m-d H:i:s')
        ]);
        
        $query = "INSERT INTO student_submissions (student_id, activity_id, submission_data, total_score) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$student_id, $activity_id, $submission_data, $total_score]);
        return $this->conn->lastInsertId();
    }
    
    public function calculateScore($activity_id, $answers) {
        $questions = $this->getActivityQuestions($activity_id);
        $total_points = 0;
        $earned_points = 0;
        
        foreach ($questions as $question) {
            $total_points += $question['points'];
            
            if (isset($answers[$question['id']])) {
                if ($question['question_type'] === 'multiple_choice') {
                    $selected_option_id = $answers[$question['id']];
                    $query = "SELECT is_correct FROM question_options WHERE id = ?";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([$selected_option_id]);
                    $option = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($option && $option['is_correct']) {
                        $earned_points += $question['points'];
                    }
                } else {
                    // For short answer and essay questions, award full points if answered
                    // In a real system, these would need manual grading
                    if (!empty(trim($answers[$question['id']]))) {
                        $earned_points += $question['points'];
                    }
                }
            }
        }
        
        return $total_points > 0 ? round(($earned_points / $total_points) * 100, 2) : 0;
    }
    
    public function saveActivityProgress($student_id, $activity_id, $answers) {
        // Save progress for auto-save functionality
        $progress_data = json_encode([
            'answers' => $answers,
            'saved_at' => date('Y-m-d H:i:s')
        ]);
        
        $query = "INSERT INTO student_progress (student_id, activity_id, progress_type, completion_percentage) 
                  VALUES (?, ?, 'activity_progress', 0) 
                  ON DUPLICATE KEY UPDATE completion_percentage = 0";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$student_id, $activity_id]);
    }
    
    // Guest user progress tracking
    public function getGuestProgress() {
        return [
            'lessons_viewed' => 0,
            'activities_completed' => 0,
            'average_score' => 0
        ];
    }
    
    public function getStudentProgress($student_id) {
        if (strpos($student_id, 'guest_') === 0) {
            return $this->getGuestProgress();
        }
        
        // For registered users, calculate actual progress
        $analytics = [];
        
        // Lessons viewed
        $query = "SELECT COUNT(DISTINCT lesson_id) as lessons_viewed FROM lesson_views WHERE student_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$student_id]);
        $analytics['lessons_viewed'] = $stmt->fetchColumn() ?: 0;
        
        // Activities completed
        $query = "SELECT COUNT(*) as activities_completed FROM student_submissions WHERE student_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$student_id]);
        $analytics['activities_completed'] = $stmt->fetchColumn() ?: 0;
        
        // Average score
        $query = "SELECT AVG(total_score) as average_score FROM student_submissions WHERE student_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$student_id]);
        $analytics['average_score'] = $stmt->fetchColumn() ?: 0;
        
        return $analytics;
    }
    
    // Analytics
    public function getTeacherAnalytics($teacher_id) {
        $analytics = [];
        
        // Total lessons
        $query = "SELECT COUNT(*) as total_lessons FROM lessons WHERE teacher_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$teacher_id]);
        $analytics['total_lessons'] = $stmt->fetchColumn();
        
        // Total activities
        $query = "SELECT COUNT(*) as total_activities FROM activities WHERE teacher_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$teacher_id]);
        $analytics['total_activities'] = $stmt->fetchColumn();
        
        // Total students (who have submitted activities)
        $query = "SELECT COUNT(DISTINCT s.student_id) as total_students 
                  FROM student_submissions s 
                  JOIN activities a ON s.activity_id = a.id 
                  WHERE a.teacher_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$teacher_id]);
        $analytics['total_students'] = $stmt->fetchColumn();
        
        return $analytics;
    }
    
    // Utility functions
    public function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
    
    public function generateQRCode($text) {
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($text);
    }
    
    public function formatDate($date) {
        return date('M j, Y', strtotime($date));
    }
}

// Helper functions for backward compatibility
function getCurrentUser() {
    require_once 'config/session.php';
    return getCurrentUser();
}

function getLessonsByTeacher($teacher_id) {
    $functions = new EcoLearnFunctions();
    return $functions->getTeacherLessons($teacher_id);
}

function getActivitiesByTeacher($teacher_id) {
    $functions = new EcoLearnFunctions();
    return $functions->getTeacherActivities($teacher_id);
}

function getPublishedLessons() {
    $functions = new EcoLearnFunctions();
    return $functions->getPublishedLessons();
}

function getPublishedActivities() {
    $functions = new EcoLearnFunctions();
    return $functions->getPublishedActivities();
}

function getStudentProgress($student_id) {
    $functions = new EcoLearnFunctions();
    return $functions->getStudentProgress($student_id);
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}
?>