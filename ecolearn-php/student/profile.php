<?php
require_once '../config/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Allow both students and guests, but guests might not have profile
if (!isLoggedIn() || ($_SESSION['user_role'] !== 'student' && $_SESSION['user_role'] !== 'guest')) {
    header('Location: ../login.php');
    exit();
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - EcoLearn Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/components/coming-soon.css">
</head>
<body>
    <div class="coming-soon">
        <i class="fas fa-user-circle fa-3x"></i>
        <h2>Profile Management</h2>
        <p>Profile management features are coming soon!</p>
        <p>You'll be able to update your personal information, change your password, and manage your account settings.</p>
        <a href="index.php" class="action-button">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>
