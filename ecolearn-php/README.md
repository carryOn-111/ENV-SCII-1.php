EcoLearn PHP Platform - Database Setup Guide
Overview
This is a PHP-based learning management system converted from the original HTML version. It includes teacher and student dashboards with full backend functionality.

Database Requirements
Required Tables
1. Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'student', 'admin') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);
2. Lessons Table
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    teacher_id INT NOT NULL,
    background_type ENUM('simple', 'image') DEFAULT 'simple',
    background_image VARCHAR(255) DEFAULT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);
3. Lesson Slides Table
CREATE TABLE lesson_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT NOT NULL,
    slide_number INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    media_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);
4. Activities Table
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    teacher_id INT NOT NULL,
    activity_type ENUM('Quiz', 'Project', 'Simulation') NOT NULL,
    due_date DATE DEFAULT NULL,
    total_points INT DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);
5. Activity Questions Table
CREATE TABLE activity_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    question_number INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'short_answer', 'essay') NOT NULL,
    points INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
);
6. Question Options Table (for multiple choice)
CREATE TABLE question_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text VARCHAR(500) NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    option_order INT NOT NULL,
    FOREIGN KEY (question_id) REFERENCES activity_questions(id) ON DELETE CASCADE
);
7. Student Submissions Table
CREATE TABLE student_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    activity_id INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_score DECIMAL(5,2) DEFAULT 0,
    status ENUM('in_progress', 'submitted', 'graded') DEFAULT 'in_progress',
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (student_id, activity_id)
);
8. Student Answers Table
CREATE TABLE student_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_text TEXT,
    selected_option_id INT DEFAULT NULL,
    points_earned DECIMAL(5,2) DEFAULT 0,
    FOREIGN KEY (submission_id) REFERENCES student_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES activity_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (selected_option_id) REFERENCES question_options(id) ON DELETE SET NULL
);
9. Sessions Table (for login management)
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
Database Configuration
1. Create database configuration file: config/database.php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecolearn_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');
2. Update the following values:
DB_HOST: Your database server (usually ‘localhost’)
DB_NAME: Your database name (create a database called ‘ecolearn_db’)
DB_USER: Your MySQL username
DB_PASS: Your MySQL password
Installation Steps
Create Database:

CREATE DATABASE ecolearn_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
Run all the table creation SQL commands above in your MySQL client

Update database configuration in config/database.php

Create initial admin user (run this SQL after tables are created):

INSERT INTO users (username, email, password, role, full_name) 
VALUES ('admin', 'admin@ecolearn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator');
Default password: ‘password’ (change after first login)

File Structure
ecolearn-php/
├── config/
│   ├── database.php
│   └── session.php
├── includes/
│   ├── auth.php
│   ├── functions.php
│   └── header.php
├── css/ (copied from original)
├── js/ (copied from original)
├── assets/
├── teacher/
│   ├── dashboard.php
│   ├── lessons.php
│   ├── activities.php
│   └── analytics.php
├── student/
│   ├── dashboard.php
│   ├── catalogue.php
│   ├── lessons.php
│   └── activities.php
├── api/
│   ├── login.php
│   ├── logout.php
│   ├── lessons.php
│   └── activities.php
├── index.php (landing page)
├── login.php
└── register.php
Features Implemented
Teacher Dashboard:
Create and manage lessons with multiple slides
Create activities (Quiz, Project, Simulation)
View student submissions and grades
Analytics and progress tracking
QR code generation for easy access
Student Dashboard:
Browse lesson catalogue (all published lessons)
Access assigned activities
Submit answers and view grades
Track personal progress
QR code scanning for quick access
Security Features:
Password hashing with PHP’s password_hash()
Session management
SQL injection prevention with prepared statements
Role-based access control
CSRF protection
Next Steps After Database Setup:
Test login functionality with the admin account
Create teacher and student accounts
Test lesson creation and publishing
Verify student can see published lessons in catalogue
Test activity creation and student submissions
Troubleshooting:
Ensure PHP has PDO MySQL extension enabled
Check file permissions for upload directories
Verify database connection settings
Check PHP error logs for detailed error messages

----------
EcoLearn PHP Platform - Database Setup Guide Overview This is a PHP-based learning management system converted from the original HTML version. It includes teacher and student dashboards with full backend functionality.

Database Requirements Required Tables

Users Table CREATE TABLE users ( id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE NOT NULL, email VARCHAR(100) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, role ENUM(‘teacher’, ‘student’, ‘admin’) NOT NULL, full_name VARCHAR(100) NOT NULL, profile_image VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, address TEXT DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, is_active BOOLEAN DEFAULT TRUE );
Lessons Table CREATE TABLE lessons ( id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, content TEXT NOT NULL, teacher_id INT NOT NULL, background_type ENUM(‘simple’, ‘image’) DEFAULT ‘simple’, background_image VARCHAR(255) DEFAULT NULL, status ENUM(‘draft’, ‘published’, ‘archived’) DEFAULT ‘draft’, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE );
Lesson Slides Table CREATE TABLE lesson_slides ( id INT AUTO_INCREMENT PRIMARY KEY, lesson_id INT NOT NULL, slide_number INT NOT NULL, title VARCHAR(200) NOT NULL, content TEXT NOT NULL, media_url VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE );
Activities Table CREATE TABLE activities ( id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, description TEXT, teacher_id INT NOT NULL, activity_type ENUM(‘Quiz’, ‘Project’, ‘Simulation’) NOT NULL, due_date DATE DEFAULT NULL, total_points INT DEFAULT 0, status ENUM(‘draft’, ‘published’, ‘archived’) DEFAULT ‘draft’, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE );
Activity Questions Table CREATE TABLE activity_questions ( id INT AUTO_INCREMENT PRIMARY KEY, activity_id INT NOT NULL, question_number INT NOT NULL, question_text TEXT NOT NULL, question_type ENUM(‘multiple_choice’, ‘short_answer’, ‘essay’) NOT NULL, points INT DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE );
Question Options Table (for multiple choice) CREATE TABLE question_options ( id INT AUTO_INCREMENT PRIMARY KEY, question_id INT NOT NULL, option_text VARCHAR(500) NOT NULL, is_correct BOOLEAN DEFAULT FALSE, option_order INT NOT NULL, FOREIGN KEY (question_id) REFERENCES activity_questions(id) ON DELETE CASCADE );
Student Submissions Table CREATE TABLE student_submissions ( id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, activity_id INT NOT NULL, submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, total_score DECIMAL(5,2) DEFAULT 0, status ENUM(‘in_progress’, ‘submitted’, ‘graded’) DEFAULT ‘in_progress’, FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE, UNIQUE KEY unique_submission (student_id, activity_id) );
Student Answers Table CREATE TABLE student_answers ( id INT AUTO_INCREMENT PRIMARY KEY, submission_id INT NOT NULL, question_id INT NOT NULL, answer_text TEXT, selected_option_id INT DEFAULT NULL, points_earned DECIMAL(5,2) DEFAULT 0, FOREIGN KEY (submission_id) REFERENCES student_submissions(id) ON DELETE CASCADE, FOREIGN KEY (question_id) REFERENCES activity_questions(id) ON DELETE CASCADE, FOREIGN KEY (selected_option_id) REFERENCES question_options(id) ON DELETE SET NULL );
Sessions Table (for login management) CREATE TABLE user_sessions ( id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, session_token VARCHAR(255) NOT NULL, expires_at TIMESTAMP NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ); Database Configuration
Create database configuration file: config/database.php