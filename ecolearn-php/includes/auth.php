<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/functions.php';

class Auth {
    private $functions;
    
    public function __construct() {
        $this->functions = new EcoLearnFunctions();
    }
    
    public function login($email, $password) {
        $user = $this->functions->authenticateUser($email, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['last_activity'] = time();
            
            // Redirect based on role
            if ($user['role'] === 'teacher') {
                header('Location: teacher/dashboard.php');
            } else if ($user['role'] === 'student') {
                header('Location: student/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            return "Invalid email or password.";
        }
    }
    
    public function register($username, $email, $password, $role, $full_name) {
        // Check if user already exists
        $existing_user = $this->functions->getUserByEmail($email);
        if ($existing_user) {
            return "User with this email already exists.";
        }
        
        // Create new user
        if ($this->functions->createUser($username, $email, $password, $role, $full_name)) {
            return "success";
        } else {
            return "Registration failed. Please try again.";
        }
    }
    
    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit();
    }
}
?>