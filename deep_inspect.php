<?php
require_once 'config.php';

echo "=== TABLE: paiement_formations ===\n";
$result = $conn->query("DESCRIBE paiement_formations");
if ($result) {
    echo str_pad("Field", 20) . str_pad("Type", 20) . "\n";
    echo str_repeat("-", 40) . "\n";
    while ($row = $result->fetch_assoc()) {
        echo str_pad($row['Field'], 20) . str_pad($row['Type'], 20) . "\n";
    }
}

echo "\n=== TABLE: inscriptions (First 5 Rows) ===\n";
$result = $conn->query("SELECT * FROM inscriptions LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
}

echo "\n=== TABLE: formations (First 5 Rows - IDs only) ===\n";
$result = $conn->query("SELECT formation_id, titre FROM formations LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['formation_id'] . ": " . $row['titre'] . "\n";
    }
}
?>
