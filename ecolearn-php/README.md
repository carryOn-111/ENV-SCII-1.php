EcoLearn - Environmental Science Learning Platform
EcoLearn is a comprehensive PHP-based learning management system designed specifically for environmental science education. The platform features role-based access for teachers and students, QR code integration for seamless content sharing, guest access functionality, and comprehensive analytics.

🌟 Features
For Teachers
Content Creation: Create interactive lessons and activities
QR Code Generation: Automatic QR code and access code generation for easy content sharing
Publishing System: Draft and publish content with instant student access
Analytics Dashboard: Track student engagement and performance
Role-based Dashboard: Dedicated teacher interface with content management tools
For Students
Catalogue Access: Browse and access published lessons and activities
QR Code Scanning: Quick access to content via QR codes or access codes
Progress Tracking: Monitor learning progress and scores (registered users)
Guest Mode: Explore content without registration (limited features)
Interactive Activities: Complete quizzes, projects, and assessments
System Features
Guest Login: Students can access content with just their name
Role-based Registration: Clear distinction between teacher and student accounts
Responsive Design: Works on desktop, tablet, and mobile devices
Secure Authentication: Password hashing and session management
Modern UI: Clean, intuitive interface with environmental theme
📋 Requirements
PHP: 7.4 or higher
MySQL: 5.7 or higher (or MariaDB 10.2+)
Web Server: Apache or Nginx
Extensions: PDO, PDO_MySQL, JSON, OpenSSL
🚀 Installation & Backend Setup
Step 1: Download and Extract
Download the EcoLearn project files
Extract to your web server directory (e.g., /var/www/html/ecolearn-php/ or C:\xampp\htdocs\ecolearn-php\)
Step 2: Database Configuration
Create Database
CREATE DATABASE ecolearn_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
Create Database User (Optional but Recommended)
CREATE USER 'ecolearn_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON ecolearn_db.* TO 'ecolearn_user'@'localhost';
FLUSH PRIVILEGES;
Configure Database Connection
Edit config/database.php:

define('DB_HOST', 'localhost');
define('DB_NAME', 'ecolearn_db');
define('DB_USER', 'ecolearn_user');        // or 'root' for development
define('DB_PASS', 'your_secure_password'); // or '' for development
Step 3: Create Database Tables
Execute the following SQL commands in your MySQL client or phpMyAdmin:

USE ecolearn_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'student') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Lessons table
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    teacher_id INT NOT NULL,
    background_type ENUM('simple', 'image') DEFAULT 'simple',
    background_image VARCHAR(255),
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    qr_code VARCHAR(255),
    access_code VARCHAR(6),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_status (status),
    INDEX idx_access_code (access_code)
);

-- Lesson slides table
CREATE TABLE lesson_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT NOT NULL,
    slide_number INT NOT NULL,
    title VARCHAR(200),
    content TEXT,
    slide_type ENUM('text', 'image', 'video') DEFAULT 'text',
    media_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_lesson_slide (lesson_id, slide_number)
);

-- Activities table
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    teacher_id INT NOT NULL,
    type ENUM('Quiz', 'Project', 'Simulation') NOT NULL,
    due_date DATE,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    qr_code VARCHAR(255),
    access_code VARCHAR(6),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_status (status),
    INDEX idx_access_code (access_code)
);

-- Activity questions table
CREATE TABLE activity_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    question_number INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'short_answer', 'essay') NOT NULL,
    points INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    INDEX idx_activity_question (activity_id, question_number)
);

-- Question options table (for multiple choice questions)
CREATE TABLE question_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    option_order INT NOT NULL,
    FOREIGN KEY (question_id) REFERENCES activity_questions(id) ON DELETE CASCADE,
    INDEX idx_question_option (question_id, option_order)
);

-- Student submissions table
CREATE TABLE student_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL, -- Can be user ID or guest ID
    activity_id INT NOT NULL,
    submission_data JSON,
    total_score DECIMAL(5,2),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    INDEX idx_student_activity (student_id, activity_id),
    INDEX idx_activity_submissions (activity_id)
);

-- Student progress tracking table
CREATE TABLE student_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    lesson_id INT,
    activity_id INT,
    progress_type ENUM('lesson_view', 'activity_progress', 'activity_complete') NOT NULL,
    completion_percentage INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    INDEX idx_student_progress (student_id, progress_type),
    UNIQUE KEY unique_student_content (student_id, lesson_id, activity_id)
);

-- Lesson views tracking table
CREATE TABLE lesson_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    lesson_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_student_lesson (student_id, lesson_id)
);
Step 4: Web Server Configuration
Apache (.htaccess)
Create .htaccess in the root directory:

RewriteEngine On

# Redirect to HTTPS (optional, for production)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Prevent access to sensitive files
<Files ~ "^\.">
    Order allow,deny
    Deny from all
</Files>

<Files ~ "\.sql$">
    Order allow,deny
    Deny from all
</Files>
Nginx
Add to your server block:

location ~ /\. {
    deny all;
}

location ~ \.sql$ {
    deny all;
}

add_header X-Content-Type-Options nosniff;
add_header X-Frame-Options DENY;
add_header X-XSS-Protection "1; mode=block";
Step 5: File Permissions
Set appropriate permissions:

# Make sure web server can read files
chmod -R 644 /path/to/ecolearn-php/
chmod -R 755 /path/to/ecolearn-php/

# Make directories executable
find /path/to/ecolearn-php/ -type d -exec chmod 755 {} \;
Step 6: Create Initial Admin User (Optional)
Insert a test teacher account:

INSERT INTO users (username, email, password, role, full_name) 
VALUES ('admin', 'admin@ecolearn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Admin Teacher');
-- Password is 'password' (change this!)
🎯 How It Works - System Architecture
User Flow Diagram
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Landing Page  │    │   Registration  │    │  Guest Login    │
│                 │───▶│                 │    │                 │
│ • Features      │    │ • Role Selection│    │ • Name Only     │
│ • Login Options │    │ • Auto-redirect │    │ • Temp Access   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     Login       │    │ Teacher Dashboard│    │Student Dashboard│
│                 │───▶│                 │    │                 │
│ • Credentials   │    │ • Create Content│    │ • Browse Content│
│ • Role Check    │    │ • QR Codes      │    │ • QR Scanner    │
└─────────────────┘    │ • Analytics     │    │ • Progress      │
                       └─────────────────┘    └─────────────────┘
Content Sharing Flow
Teacher Creates Content
         │
         ▼
System Generates:
• 6-digit Access Code (ABC123)
• QR Code URL
• Catalogue Entry
         │
         ▼
Teacher Publishes Content
         │
         ▼
Students Access Via:
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│   QR Code Scan  │  │  Access Code    │  │   Catalogue     │
│                 │  │                 │  │                 │
│ • Camera/App    │  │ • Manual Entry  │  │ • Browse & Click│
│ • Instant Access│  │ • 6-digit Code  │  │ • Search/Filter │
└─────────────────┘  └─────────────────┘  └─────────────────┘
         │                    │                    │
         └────────────────────┼────────────────────┘
                              ▼
                    Content Viewer
                  (Lesson/Activity)
Database Relationships
users (Teachers/Students)
  │
  ├── lessons (1:many)
  │     ├── lesson_slides (1:many)
  │     └── lesson_views (1:many) ──┐
  │                                 │
  └── activities (1:many)           │
        ├── activity_questions      │
        │     └── question_options  │
        ├── student_submissions ────┤
        └── student_progress ───────┘
                                    │
                              student_id
                        (registered or guest)
Security Features
Password Hashing: Uses PHP’s password_hash() with bcrypt
Session Management: Secure session handling with timeout
SQL Injection Prevention: Prepared statements throughout
XSS Protection: Input sanitization and output escaping
CSRF Protection: Session-based validation (can be enhanced)
Guest Isolation: Guest users have limited database interaction
🔧 Configuration Options
Session Configuration (config/session.php)
SESSION_TIMEOUT: Default 24 hours
Cookie Security: HTTP-only, secure options
Guest Handling: Special session management for guest users
Database Configuration (config/database.php)
Connection Settings: Host, database, credentials
Character Set: UTF-8 support for international content
Error Handling: PDO exception mode
📱 Usage Instructions
For Teachers
Register as a teacher or login
Create Lessons: Add title, content, choose background
Create Activities: Add questions, set due dates
Publish Content: Generate QR codes and access codes
Share Access: Provide QR codes or access codes to students
Monitor Progress: View analytics and student submissions
For Students
Register as a student, login, or use guest access
Browse Catalogue: Explore available content
Use QR Codes: Scan codes for instant access
Enter Access Codes: Use 6-digit codes from teachers
Complete Activities: Submit assignments and track progress
For Administrators
Database Management: Use phpMyAdmin or MySQL client
User Management: Direct database queries for user operations
Content Moderation: Monitor published content
Analytics: Query database for detailed statistics
🔍 Troubleshooting
Common Issues
Database Connection Errors

Check config/database.php settings
Verify MySQL service is running
Confirm database and user exist
Permission Errors

Ensure web server can read PHP files
Check file ownership and permissions
Verify database user privileges
Session Issues

Check PHP session configuration
Verify session directory permissions
Clear browser cookies/cache
QR Code Not Displaying

Check internet connection (uses external QR API)
Verify QR code URL generation in functions.php
Consider implementing local QR generation
Development vs Production
Development Setup

Use localhost database host
Enable error reporting in PHP
Use simple passwords for testing
Disable HTTPS redirects
Production Setup

Use secure database credentials
Enable HTTPS and security headers
Implement proper backup strategy
Monitor error logs regularly
Consider CDN for static assets
🚀 Future Enhancements
Real-time Notifications: WebSocket integration
Mobile App: React Native or Flutter companion
Advanced Analytics: Detailed learning analytics
Content Import/Export: SCORM compatibility
Video Integration: Embedded video lessons
Offline Support: Progressive Web App features
Multi-language Support: Internationalization
Advanced QR Features: Custom QR designs, bulk generation
📄 License
This project is open source and available under the MIT License.

🤝 Contributing
Fork the repository
Create a feature branch
Make your changes
Test thoroughly
Submit a pull request
📧 Support
For technical support or questions:

Check the troubleshooting section
Review the code comments
Create an issue in the repository
Contact the development team
EcoLearn - Empowering Environmental Education Through Technology 🌱