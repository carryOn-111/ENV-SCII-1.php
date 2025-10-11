<?php
require_once 'config/session.php';

$error_message = '';
$success_message = '';

// If user is already logged in, redirect
if (isLoggedIn()) {
    if ($_SESSION['user_role'] === 'teacher') {
        header('Location: teacher/dashboard.php');
    } else if ($_SESSION['user_role'] === 'student') {
        header('Location: student/dashboard.php');
    } else if ($_SESSION['user_role'] === 'guest') {
        header('Location: student/dashboard.php');
    }
    exit();
}

// Handle guest login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    if (!empty($name)) {
        // Generate a unique guest ID
        $guest_id = 'guest_' . uniqid();
        
        // Set session variables for guest user
        $_SESSION['user_id'] = $guest_id;
        $_SESSION['user_role'] = 'guest';
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = '';
        $_SESSION['last_activity'] = time();
        $_SESSION['is_guest'] = true;
        
        // Redirect to student dashboard
        header('Location: student/dashboard.php');
        exit();
    } else {
        $error_message = 'Please enter your name.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Guest Access - EcoLearn</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="auth-card text-center">
  <div class="mb-3">
    <i class="fas fa-user-friends fa-3x" style="color: #00ff88;"></i>
  </div>
  <h3 class="auth-title mb-3">Guest Access</h3>
  <p class="text-muted mb-4">Enter your name to access EcoLearn as a guest</p>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
      <?php echo htmlspecialchars($error_message); ?>
    </div>
  <?php endif; ?>

  <form action="guest-login.php" method="POST">
    <div class="mb-3 text-start">
      <label class="form-label">Your Name</label>
      <input type="text" class="form-control" name="name" placeholder="Enter your name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
      <small class="form-text text-muted">No registration required - just enter your name to get started!</small>
    </div>
    <button type="submit" class="btn btn-success w-100 py-2 mb-3">
      <i class="fas fa-sign-in-alt"></i> Enter as Guest
    </button>
  </form>

  <!-- Extra Options -->
  <div class="d-grid gap-2">
    <a href="login.php" class="btn btn-outline-primary w-100">Login with Account</a>
    <a href="register.php" class="btn btn-outline-secondary w-100">Create Account</a>
    <a href="index.php" class="btn btn-outline-secondary w-100">Back to Home</a>
  </div>

  <div class="mt-4">
    <small class="text-muted">
      <i class="fas fa-info-circle"></i> 
      Guest access allows you to browse lessons and activities without creating an account. 
      Your progress won't be saved permanently.
    </small>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>