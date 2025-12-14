<?php
require_once 'config.php';

$sql = "ALTER TABLE formations ADD COLUMN description TEXT AFTER titre";

if ($conn->query($sql) === TRUE) {
    echo "✅ Successfully added 'description' column to 'formations' table.";
} else {
    echo "❌ Error adding column: " . $conn->error;
}
?>
