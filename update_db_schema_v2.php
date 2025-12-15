<?php
require_once 'config.php';

// SQL to add formation_id column if it doesn't exist
$sql = "SHOW COLUMNS FROM inscriptions LIKE 'formation_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $alterSql = "ALTER TABLE inscriptions ADD COLUMN formation_id INT(11) DEFAULT NULL AFTER user_id";
    if ($conn->query($alterSql) === TRUE) {
        echo "Table inscriptions updated successfully. formation_id column added.\n";
        
        // Add foreign key constraint
        $fkSql = "ALTER TABLE inscriptions ADD CONSTRAINT fk_inscription_formation FOREIGN KEY (formation_id) REFERENCES formations(formation_id)";
        if ($conn->query($fkSql) === TRUE) {
             echo "Foreign key constraint added successfully.\n";
        } else {
             echo "Error adding foreign key: " . $conn->error . "\n";
        }
    } else {
        echo "Error updating table: " . $conn->error . "\n";
    }
} else {
    echo "Column formation_id already exists.\n";
}

$conn->close();
?>
