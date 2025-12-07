<?php
session_start();
require_once '../config.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        
        header("Location: users.php");
        exit();
    } else {
        echo "Error deleting user: " . $conn->error;
        header("Location: users.php");
        exit();
    }

    $stmt->close();
} else {
    echo "No user ID provided.";
}




?>