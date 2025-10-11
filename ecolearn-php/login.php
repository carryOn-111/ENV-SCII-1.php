<?php
require_once 'config/session.php';
require_once 'includes/auth.php';

$error_message = '';

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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $result = $auth->login($email, $password);
        if ($result !== true) {
            $error_message = $result;
        }
    } else {
        $error_message = 'Please fill in all fields.';
    }
}

// Check for timeout message
if (isset($_GET['timeout'])) {
    $error_message = 'Your session has expired. Please log in again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>EcoLearn Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="auth-card text-center">
  <div class="mb-3">
    <i class="fas fa-leaf fa-3x" style="color: #00ff88;"></i>
  </div>
  <h3 class="auth-title mb-3">Welcome Back!</h3>
  <p class="text-muted mb-4">Login to your EcoLearn account</p>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
      <?php echo htmlspecialchars($error_message); ?>
    </div>
  <?php endif; ?>

  <form action="login.php" method="POST">
    <div class="mb-3 text-start">
      <label class="form-label">Email</label>
      <input type="email" class="form-control" name="email" placeholder="Enter your email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    </div>
    <div class="mb-3 text-start">
      <label class="form-label">Password</label>
      <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Login</button>
  </form>

  <!-- Extra Options -->
  <div class="d-grid gap-2">
    <a href="register.php" class="btn btn-outline-primary w-100">Create Account</a>
    <a href="guest-login.php" class="btn btn-outline-success w-100">
      <i class="fas fa-user-friends"></i> Try as Guest
    </a>
    <a href="index.php" class="btn btn-outline-secondary w-100">Back to Home</a>
  </div>

  <div class="mt-4">
    <small class="text-muted">
      <i class="fas fa-info-circle"></i> 
      New to EcoLearn? Try our guest access to explore lessons and activities without creating an account.
    </small>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>