<?php
require_once 'config.php';

$result = $conn->query("DESCRIBE formations");

if ($result) {
    echo "Columns in 'formations' table:\n";
    echo str_pad("Field", 20) . str_pad("Type", 20) . "\n";
    echo str_repeat("-", 40) . "\n";
    while ($row = $result->fetch_assoc()) {
        echo str_pad($row['Field'], 20) . str_pad($row['Type'], 20) . "\n";
    }
} else {
    echo "Error describing table: " . $conn->error;
}
?>
