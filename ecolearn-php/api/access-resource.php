<?php
require_once '../config/session.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$access_code = $input['access_code'] ?? '';

if (empty($access_code)) {
    echo json_encode(['success' => false, 'message' => 'Access code is required']);
    exit();
}

$functions = new EcoLearnFunctions();

// Try to find lesson with this access code
$lesson = $functions->getLessonByAccessCode($access_code);
if ($lesson) {
    echo json_encode([
        'success' => true,
        'type' => 'lesson',
        'id' => $lesson['id'],
        'title' => $lesson['title'],
        'teacher' => $lesson['teacher_name']
    ]);
    exit();
}

// Try to find activity with this access code
$activity = $functions->getActivityByAccessCode($access_code);
if ($activity) {
    echo json_encode([
        'success' => true,
        'type' => 'activity',
        'id' => $activity['id'],
        'title' => $activity['title'],
        'teacher' => $activity['teacher_name']
    ]);
    exit();
}

// No resource found
echo json_encode(['success' => false, 'message' => 'Invalid access code']);
?>