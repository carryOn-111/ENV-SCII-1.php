<?php
require_once '../config/session.php';
require_once '../includes/functions.php';

// Redirect to index.php (which handles dashboard logic)
header('Location: index.php');
exit();