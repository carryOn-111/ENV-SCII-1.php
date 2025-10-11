-- Alter tables to match the code

-- Add missing columns to lessons
ALTER TABLE lessons ADD COLUMN qr_code VARCHAR(255) DEFAULT NULL;
ALTER TABLE lessons ADD COLUMN access_code VARCHAR(10) DEFAULT NULL;

-- Add missing columns to activities
ALTER TABLE activities ADD COLUMN description TEXT DEFAULT NULL;
ALTER TABLE activities ADD COLUMN qr_code VARCHAR(255) DEFAULT NULL;
ALTER TABLE activities ADD COLUMN access_code VARCHAR(10) DEFAULT NULL;

-- Change score to total_score in student_submissions
ALTER TABLE student_submissions CHANGE score total_score INT DEFAULT NULL;

-- Update ENUM in student_progress to include 'activity_progress'
ALTER TABLE student_progress MODIFY COLUMN progress_type ENUM('lesson_viewed', 'activity_completed', 'activity_progress') NOT NULL;

-- Add missing table lesson_views
CREATE TABLE lesson_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    lesson_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_view (student_id, lesson_id)
);
