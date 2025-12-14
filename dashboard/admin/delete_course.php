<?php
session_start();

// Admin check
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
     if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
         header("Location: /3eduplus/signup.php"); 
         exit();
    }
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $username = "root";
    $password = "";
    $database = "3eduplus";
    $servername = "localhost";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM formations WHERE formation_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: courses.php?msg=deleted");
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
exit();
?>
