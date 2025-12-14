<?php
require_once 'config.php';

// Try to add the column
$sql = "ALTER TABLE formations ADD COLUMN description TEXT";
echo "Executing: $sql\n";

if ($conn->query($sql) === TRUE) {
    echo "✅ Query executed successfully.\n";
} else {
    echo "❌ Query failed: " . $conn->error . "\n";
}

// Verify immediately
echo "\nVerifying schema:\n";
$result = $conn->query("DESCRIBE formations");
if ($result) {
    echo str_pad("Field", 20) . str_pad("Type", 20) . "\n";
    echo str_repeat("-", 40) . "\n";
    while ($row = $result->fetch_assoc()) {
        echo str_pad($row['Field'], 20) . str_pad($row['Type'], 20) . "\n";
    }
}
?>
