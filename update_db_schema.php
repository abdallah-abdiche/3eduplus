<?php
/**
 * Database Update Script for 3edu+
 * Adds 'video_url' column to 'formations' table if it doesn't exist.
 * Access: http://localhost/3eduplus/update_db_schema.php
 */

require_once 'config.php';

$messages = [];

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Add video_url to formations table
$check_column = "SHOW COLUMNS FROM formations LIKE 'video_url'";
$result = $conn->query($check_column);

if ($result && $result->num_rows == 0) {
    $sql = "ALTER TABLE formations ADD COLUMN video_url VARCHAR(500) AFTER formationImageUrl";
    if ($conn->query($sql) === TRUE) {
        $messages[] = "✅ 'video_url' column added successfully to 'formations' table.";
    } else {
        $messages[] = "❌ Error adding column: " . $conn->error;
    }
} else {
    $messages[] = "ℹ️ 'video_url' column already exists.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Update</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f0f2f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .message { padding: 10px; margin: 5px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Database Update Status</h2>
        <?php foreach($messages as $msg) { echo "<div class='message'>$msg</div>"; } ?>
        <p><a href="dashboard/admin/courses.php">Go to Admin Dashboard</a></p>
    </div>
</body>
</html>
