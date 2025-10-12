<?php
require_once 'config/session.php';
require_once 'includes/auth.php';

$error_message = '';
$success_message = '';

// If user is already logged in, redirect
if (isLoggedIn()) {
    $base_url = '/lms-project/ecolearn-php/';
    if ($_SESSION['user_role'] === 'teacher') {
        header('Location: ' . $base_url . 'teacher/dashboard.php');
    } else if ($_SESSION['user_role'] === 'student') {
        header('Location: ' . $base_url . 'student/dashboard.php');
    } else if ($_SESSION['user_role'] === 'guest') {
        header('Location: ' . $base_url . 'student/dashboard.php');
    }
    exit();
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    
    if (!empty($name) && !empty($email) && !empty($password) && !empty($user_type)) {
        // Generate username from email
        $username = explode('@', $email)[0];
        
        $result = $auth->register($username, $email, $password, $user_type, $name);
        if ($result === 'success') {
            // Auto-login the user after successful registration
            $login_result = $auth->login($email, $password);
            if ($login_result === true) {
                // Redirect will happen in the login method
                exit();
            } else {
                $success_message = 'Account created successfully! You can now log in.';
            }
        } else {
            $error_message = $result;
        }
    } else {
        $error_message = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Account - EcoLearn</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="auth-card text-center">
  <div class="mb-3">
    <i class="fas fa-user-plus fa-3x" style="color: #00ff88;"></i>
  </div>
  <h3 class="auth-title mb-3">Create Your Account</h3>
  <p class="text-muted mb-4">Join our learning community</p>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
      <?php echo htmlspecialchars($error_message); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($success_message)): ?>
    <div class="alert alert-success" role="alert">
      <?php echo htmlspecialchars($success_message); ?>
      <br><a href="login.php" class="alert-link">Click here to login</a>
    </div>
  <?php endif; ?>

  <form action="register.php" method="POST">
    <div class="mb-3 text-start">
      <label class="form-label">Full Name</label>
      <input type="text" class="form-control" name="name" placeholder="Enter your name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
    </div>
    <div class="mb-3 text-start">
      <label class="form-label">Email</label>
      <input type="email" class="form-control" name="email" placeholder="Enter your email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    </div>
    <div class="mb-3 text-start">
      <label class="form-label">Password</label>
      <input type="password" class="form-control" name="password" placeholder="Create a password" required>
    </div>
    <div class="mb-3 text-start">
      <label class="form-label">I am a...</label>
      <select class="form-select" name="user_type" required>
        <option value="">Select your role</option>
        <option value="student" <?php echo (($_POST['user_type'] ?? '') === 'student') ? 'selected' : ''; ?>>
          <i class="fas fa-graduation-cap"></i> Student - I want to learn and take courses
        </option>
        <option value="teacher" <?php echo (($_POST['user_type'] ?? '') === 'teacher') ? 'selected' : ''; ?>>
          <i class="fas fa-chalkboard-teacher"></i> Teacher - I want to create and share lessons
        </option>
      </select>
      <small class="form-text text-muted">
        Choose your role to get the right dashboard and features for your needs.
      </small>
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2">
      <i class="fas fa-user-plus"></i> Create Account & Login
    </button>
  </form>

  <div class="mt-4">
    <p class="text-muted">Already have an account? 
      <a href="login.php" class="text-link">Login here</a>
    </p>
    <p class="text-muted">Just want to explore? 
      <a href="guest-login.php" class="text-link">Try as guest</a>
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>