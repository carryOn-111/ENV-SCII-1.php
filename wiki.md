Project Summary
The LMS Project is an educational platform designed to facilitate online learning for students and teachers. It provides functionalities such as lesson management, activity tracking, and analytics for educators, enhancing the teaching and learning experience. The project aims to create a comprehensive environment where students can engage with their lessons and activities while allowing teachers to monitor progress and performance.

Project Module Description
The LMS Project consists of several modules:

Dashboard: Displays an overview of lessons and activities for both teachers and students.
Lessons: Allows teachers to create, update, and manage lessons while students can track their progress.
Activities: Facilitates the creation and management of assignments and quizzes, along with submission tracking.
Analytics: Provides insights into student performance and engagement metrics for teachers.
Directory Tree
uploads/lms-project/
├── ecolearn-php/
│   ├── api/
│   │   ├── activities.php
│   │   ├── analytics.php
│   │   ├── dashboard.php
│   │   ├── lessons.php
│   ├── includes/
│   │   ├── auth.php
│   ├── student/
│   │   ├── my-activities.php
│   │   ├── my-lessons.php
File Description Inventory
api/activities.php: Handles API requests related to activities, including creation, updates, and submissions.
api/analytics.php: Provides analytics data for teachers, including performance metrics and engagement statistics.
api/dashboard.php: Manages the dashboard data for users based on their roles.
api/lessons.php: Manages lesson-related API requests, allowing teachers to create and update lessons.
includes/auth.php: Contains authentication functions for user sessions and role management.
student/my-activities.php: Displays a list of activities assigned to the student, along with submission statuses.
student/my-lessons.php: Shows lessons that the student is enrolled in or has viewed.
Technology Stack
PHP: Server-side scripting language for backend development.
MySQL: Database management system for storing user data, lessons, and activities.
HTML/CSS: Frontend technologies for creating user interfaces.
JavaScript: Client-side scripting for interactive elements.
Usage
To set up and run the LMS Project:

Install dependencies using Composer or any package manager as required.
Set up the MySQL database and configure database connection settings in the config/database.php file.
Run the application by accessing the appropriate entry point in your web server setup.