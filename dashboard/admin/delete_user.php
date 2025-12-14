<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin']);

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: users.php?msg=deleted");
        exit();
    } else {
        $stmt->close();
        header("Location: users.php?msg=error");
        exit();
    }
} else {
    header("Location: users.php");
    exit();
}




?>