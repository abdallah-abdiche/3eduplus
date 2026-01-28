<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

checkAuth();

$user_id = $_SESSION['user_id'];
$video_id = isset($_POST['video_id']) ? intval($_POST['video_id']) : 0;
$formation_id = isset($_POST['formation_id']) ? intval($_POST['formation_id']) : 0;

if ($user_id > 0 && $video_id > 0 && $formation_id > 0) {
    try {
        $stmt = $conn->prepare("INSERT IGNORE INTO user_progress (user_id, formation_id, video_id, status) VALUES (?, ?, ?, 'completed')");
        $stmt->bind_param("iii", $user_id, $formation_id, $video_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
?>
