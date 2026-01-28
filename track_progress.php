<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit();
}

$user_id = $_SESSION['user_id'];
$video_id = isset($_POST['video_id']) ? (int)$_POST['video_id'] : 0;
$type = isset($_POST['type']) ? $_POST['type'] : '';

if ($video_id > 0 && ($type === 'course' || $type === 'event')) {
    $stmt = $conn->prepare("INSERT INTO user_video_progress (user_id, video_id, video_type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE watched_at = NOW()");
    $stmt->bind_param("iis", $user_id, $video_id, $type);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
}
?>
