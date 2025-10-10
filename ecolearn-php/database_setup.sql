-- EcoLearn Platform Database Setup
-- Execute this SQL file to create all necessary tables

-- Create database (uncomment if needed)
-- CREATE DATABASE ecolearn_db;
-- USE ecolearn_db;

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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Lesson slides table
CREATE TABLE lesson_slides (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lesson_id INT NOT NULL,
    slide_number INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    media_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

-- Activities table
CREATE TABLE activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    type ENUM('Quiz', 'Project', 'Simulation') NOT NULL,
    due_date DATE DEFAULT NULL,
    total_points INT DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
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
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
);

-- Question options table (for multiple choice)
CREATE TABLE question_options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    option_text VARCHAR(500) NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    option_order INT NOT NULL,
    FOREIGN KEY (question_id) REFERENCES activity_questions(id) ON DELETE CASCADE
);

-- Student submissions table
CREATE TABLE student_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    activity_id INT NOT NULL,
    submission_data JSON NOT NULL,
    score INT DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    graded_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (student_id, activity_id)
);

-- Student progress tracking
CREATE TABLE student_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    lesson_id INT DEFAULT NULL,
    activity_id INT DEFAULT NULL,
    progress_type ENUM('lesson_viewed', 'activity_completed') NOT NULL,
    completion_percentage INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
);

-- Insert sample users for testing
-- Password for both accounts: "password123"
INSERT INTO users (username, email, password, role, full_name) VALUES 
('teacher1', 'teacher@ecolearn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Dr. Sarah Johnson'),
('student1', 'student@ecolearn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Alex Smith');

-- Insert sample lesson
INSERT INTO lessons (teacher_id, title, content, status) VALUES 
(1, 'Introduction to Water Cycle', 'This lesson covers the basic concepts of the water cycle, including evaporation, condensation, precipitation, and collection. Students will learn about the continuous movement of water on, above, and below the surface of the Earth.', 'published');

-- Insert sample activity
INSERT INTO activities (teacher_id, title, type, status) VALUES 
(1, 'Water Cycle Quiz', 'Quiz', 'published');

-- Insert sample question for the activity
INSERT INTO activity_questions (activity_id, question_number, question_text, question_type, points) VALUES 
(1, 1, 'What is the process by which water changes from liquid to gas?', 'multiple_choice', 10);

-- Insert sample options for the question
INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES 
(1, 'Evaporation', TRUE, 1),
(1, 'Condensation', FALSE, 2),
(1, 'Precipitation', FALSE, 3),
(1, 'Collection', FALSE, 4);