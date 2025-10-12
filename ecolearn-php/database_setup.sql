-- =====================================================
-- EcoLearn Platform - Complete Database Setup
-- =====================================================
-- Execute this SQL file to create all necessary tables
-- This file combines the original setup and all alterations
-- =====================================================

-- Create database (uncomment if needed)
-- CREATE DATABASE ecolearn_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE ecolearn_db;

-- =====================================================
-- CORE TABLES
-- =====================================================

-- Users table (teachers and students)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'student') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_username (username)
);

-- Lessons table
CREATE TABLE lessons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    background_type ENUM('simple', 'image') DEFAULT 'simple',
    background_image VARCHAR(255) DEFAULT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    qr_code VARCHAR(255) DEFAULT NULL,
    access_code VARCHAR(10) DEFAULT NULL,
    grade_level VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_status (status),
    INDEX idx_access_code (access_code),
    INDEX idx_grade_level (grade_level)
);

-- Lesson slides table
CREATE TABLE lesson_slides (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lesson_id INT NOT NULL,
    slide_number INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    slide_type ENUM('text', 'image', 'video') DEFAULT 'text',
    media_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_lesson_slide (lesson_id, slide_number)
);

-- Activities table
CREATE TABLE activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    lesson_id INT DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    type ENUM('Quiz', 'Project', 'Simulation', 'multiple_choice', 'short_answer', 'essay') NOT NULL,
    due_date DATETIME DEFAULT NULL,
    total_points INT DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    qr_code VARCHAR(255) DEFAULT NULL,
    access_code VARCHAR(10) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_lesson_id (lesson_id),
    INDEX idx_status (status),
    INDEX idx_access_code (access_code),
    INDEX idx_due_date (due_date)
);

-- Activity questions table
CREATE TABLE activity_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_id INT NOT NULL,
    question_number INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'short_answer', 'essay') NOT NULL,
    points INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    INDEX idx_activity_question (activity_id, question_number)
);

-- Question options table (for multiple choice)
CREATE TABLE question_options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    option_text VARCHAR(500) NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    option_order INT NOT NULL,
    
    FOREIGN KEY (question_id) REFERENCES activity_questions(id) ON DELETE CASCADE,
    INDEX idx_question_option (question_id, option_order)
);

-- =====================================================
-- TRACKING & PROGRESS TABLES
-- =====================================================

-- Student submissions table
CREATE TABLE student_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(50) NOT NULL, -- Can be user ID or guest ID (guest_xxxxx)
    activity_id INT NOT NULL,
    submission_data JSON NOT NULL,
    total_score DECIMAL(5,2) DEFAULT NULL,
    score INT DEFAULT NULL, -- Backward compatibility
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    graded_at TIMESTAMP NULL,
    
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    INDEX idx_student_activity (student_id, activity_id),
    INDEX idx_activity_submissions (activity_id),
    INDEX idx_submitted_at (submitted_at),
    UNIQUE KEY unique_submission (student_id, activity_id)
);

-- Student progress tracking
CREATE TABLE student_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(50) NOT NULL, -- Can be user ID or guest ID
    lesson_id INT DEFAULT NULL,
    activity_id INT DEFAULT NULL,
    progress_type ENUM('lesson_viewed', 'activity_completed', 'activity_progress') NOT NULL,
    completion_percentage INT DEFAULT 0,
    status ENUM('in_progress', 'completed', 'paused') DEFAULT 'in_progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    INDEX idx_student_progress (student_id, progress_type),
    INDEX idx_lesson_progress (lesson_id, student_id),
    INDEX idx_activity_progress (activity_id, student_id)
);

-- Lesson views tracking table
CREATE TABLE lesson_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(50) NOT NULL, -- Can be user ID or guest ID
    lesson_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_duration INT DEFAULT 0, -- in seconds
    
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_student_lesson (student_id, lesson_id),
    INDEX idx_lesson_views (lesson_id, viewed_at),
    UNIQUE KEY unique_view (student_id, lesson_id)
);

-- =====================================================
-- SAMPLE DATA FOR TESTING
-- =====================================================

-- Insert sample users for testing
-- Password for both accounts: "password123"
INSERT INTO users (username, email, password, role, full_name) VALUES 
('teacher1', 'teacher@ecolearn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Dr. Sarah Johnson'),
('student1', 'student@ecolearn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Alex Smith'),
('admin', 'admin@ecolearn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Admin Teacher');

-- Insert sample lessons
INSERT INTO lessons (teacher_id, title, content, status, qr_code, access_code, grade_level) VALUES 
(1, 'Introduction to Water Cycle', 'This lesson covers the basic concepts of the water cycle, including evaporation, condensation, precipitation, and collection. Students will learn about the continuous movement of water on, above, and below the surface of the Earth.', 'published', 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=lesson_1', 'WATER1', 'Grade 6-8'),
(1, 'Climate Change Basics', 'Understanding the fundamentals of climate change, greenhouse gases, and their impact on our environment. This lesson explores both natural and human factors affecting global climate patterns.', 'published', 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=lesson_2', 'CLIMA1', 'Grade 9-12'),
(3, 'Renewable Energy Sources', 'Explore different types of renewable energy including solar, wind, hydroelectric, and geothermal power. Learn about their benefits and challenges in our modern world.', 'published', 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=lesson_3', 'RENEW1', 'Grade 7-10');

-- Insert sample lesson slides
INSERT INTO lesson_slides (lesson_id, slide_number, title, content, slide_type) VALUES 
(1, 1, 'What is the Water Cycle?', 'The water cycle is the continuous movement of water within the Earth and atmosphere.', 'text'),
(1, 2, 'Evaporation Process', 'Water changes from liquid to gas when heated by the sun.', 'text'),
(1, 3, 'Condensation in Clouds', 'Water vapor cools and forms droplets in the atmosphere.', 'text');

-- Insert sample activities
INSERT INTO activities (teacher_id, lesson_id, title, description, type, status, qr_code, access_code, due_date) VALUES 
(1, 1, 'Water Cycle Quiz', 'Test your knowledge of the water cycle processes and terminology.', 'Quiz', 'published', 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=activity_1', 'QUIZ01', DATE_ADD(NOW(), INTERVAL 7 DAY)),
(1, 2, 'Climate Impact Assessment', 'Analyze the impact of climate change on your local environment.', 'Project', 'published', 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=activity_2', 'PROJ01', DATE_ADD(NOW(), INTERVAL 14 DAY)),
(3, 3, 'Energy Source Comparison', 'Compare different renewable energy sources and their efficiency.', 'Quiz', 'published', 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=activity_3', 'ENRG01', DATE_ADD(NOW(), INTERVAL 10 DAY));

-- Insert sample questions for activities
INSERT INTO activity_questions (activity_id, question_number, question_text, question_type, points) VALUES 
(1, 1, 'What is the process by which water changes from liquid to gas?', 'multiple_choice', 10),
(1, 2, 'Which of the following is NOT a part of the water cycle?', 'multiple_choice', 10),
(1, 3, 'Describe how the water cycle affects weather patterns.', 'short_answer', 15),
(2, 1, 'What are the main greenhouse gases contributing to climate change?', 'multiple_choice', 10),
(2, 2, 'Explain how human activities contribute to global warming.', 'essay', 20),
(3, 1, 'Which renewable energy source is most efficient in coastal areas?', 'multiple_choice', 10),
(3, 2, 'What are the main advantages of solar energy?', 'short_answer', 15);

-- Insert sample options for multiple choice questions
INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES 
-- Question 1 options
(1, 'Evaporation', TRUE, 1),
(1, 'Condensation', FALSE, 2),
(1, 'Precipitation', FALSE, 3),
(1, 'Collection', FALSE, 4),
-- Question 2 options
(2, 'Evaporation', FALSE, 1),
(2, 'Photosynthesis', TRUE, 2),
(2, 'Condensation', FALSE, 3),
(2, 'Precipitation', FALSE, 4),
-- Question 4 options (Climate change)
(4, 'Carbon dioxide and methane', TRUE, 1),
(4, 'Oxygen and nitrogen', FALSE, 2),
(4, 'Hydrogen and helium', FALSE, 3),
(4, 'Argon and neon', FALSE, 4),
-- Question 6 options (Renewable energy)
(6, 'Wind energy', TRUE, 1),
(6, 'Solar energy', FALSE, 2),
(6, 'Geothermal energy', FALSE, 3),
(6, 'Hydroelectric energy', FALSE, 4);

-- =====================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- =====================================================

-- Additional indexes for better query performance
CREATE INDEX idx_lessons_created_at ON lessons(created_at DESC);
CREATE INDEX idx_activities_created_at ON activities(created_at DESC);
CREATE INDEX idx_submissions_score ON student_submissions(total_score DESC);
CREATE INDEX idx_progress_updated ON student_progress(updated_at DESC);

-- =====================================================
-- VIEWS FOR COMMON QUERIES
-- =====================================================

-- View for published content with teacher information
CREATE VIEW published_lessons_view AS
SELECT 
    l.id,
    l.title,
    l.content,
    l.grade_level,
    l.access_code,
    l.qr_code,
    l.created_at,
    u.full_name as teacher_name,
    u.email as teacher_email,
    COUNT(DISTINCT lv.student_id) as view_count,
    COUNT(DISTINCT a.id) as activity_count
FROM lessons l
JOIN users u ON l.teacher_id = u.id
LEFT JOIN lesson_views lv ON l.id = lv.lesson_id
LEFT JOIN activities a ON l.id = a.lesson_id AND a.status = 'published'
WHERE l.status = 'published'
GROUP BY l.id, l.title, l.content, l.grade_level, l.access_code, l.qr_code, l.created_at, u.full_name, u.email;

-- View for activity statistics
CREATE VIEW activity_stats_view AS
SELECT 
    a.id,
    a.title,
    a.type,
    a.due_date,
    u.full_name as teacher_name,
    COUNT(DISTINCT ss.student_id) as submission_count,
    AVG(ss.total_score) as average_score,
    MAX(ss.total_score) as highest_score,
    MIN(ss.total_score) as lowest_score
FROM activities a
JOIN users u ON a.teacher_id = u.id
LEFT JOIN student_submissions ss ON a.id = ss.activity_id
WHERE a.status = 'published'
GROUP BY a.id, a.title, a.type, a.due_date, u.full_name;

-- =====================================================
-- SETUP COMPLETE
-- =====================================================

-- Display setup completion message
SELECT 'EcoLearn Database Setup Complete!' as message,
       'Default login credentials:' as note1,
       'Teacher: teacher@ecolearn.com / password123' as teacher_login,
       'Student: student@ecolearn.com / password123' as student_login,
       'Admin: admin@ecolearn.com / password123' as admin_login,
       'Remember to change default passwords in production!' as security_note;