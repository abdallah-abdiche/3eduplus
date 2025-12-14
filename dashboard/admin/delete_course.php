<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin']);

if (isset($_GET['id'])) {
    $course_id = $conn->real_escape_string($_GET['id']);
    
    // First, verify the course exists
    $check_sql = "SELECT formationImageUrl, video_url FROM formations WHERE formation_id = '$course_id'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Optional: Delete associated files (image/video) if they are local uploads
        // This is good practice to keep the server clean
        $imgUrl = $row['formationImageUrl'];
        $videoUrl = $row['video_url'];
        
        if ($imgUrl && strpos($imgUrl, 'uploads/') !== false && file_exists("../../" . $imgUrl)) {
            unlink("../../" . $imgUrl);
        }
        
        if ($videoUrl && strpos($videoUrl, 'uploads/') !== false && file_exists("../../" . $videoUrl)) {
            unlink("../../" . $videoUrl);
        }
        
        // Delete from database
        $sql = "DELETE FROM formations WHERE formation_id = '$course_id'";
        if ($conn->query($sql) === TRUE) {
            header("Location: courses.php?msg=deleted");
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    } else {
         header("Location: courses.php?msg=notfound");
         exit();
    }
} else {
    header("Location: courses.php");
    exit();
}
$conn->close();
?>
