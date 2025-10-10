<?php
require_once 'config/session.php';
require_once 'includes/auth.php';

$error_message = '';
$success_message = '';

// If user is already logged in, redirect
if (isLoggedIn()) {
    if ($_SESSION['user_role'] === 'teacher') {
        header('Location: teacher/dashboard.php');
    } else if ($_SESSION['user_role'] === 'student') {
        header('Location: student/dashboard.php');
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
            $success_message = 'Account created successfully! You can now log in.';
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
  <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="auth-card text-center">
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
      <label class="form-label">User Type</label>
      <select class="form-select" name="user_type" required>
        <option value="">Select Type</option>
        <option value="student" <?php echo (($_POST['user_type'] ?? '') === 'student') ? 'selected' : ''; ?>>Student</option>
        <option value="teacher" <?php echo (($_POST['user_type'] ?? '') === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2">Sign Up</button>
  </form>

  <div class="mt-4">
    <p class="text-muted">Already have an account? 
      <a href="login.php" class="text-link">Login here</a>
    </p>
  </div>
</div>

</body>
</html>