<?php
require_once 'config.php';

$result = $conn->query("SHOW TABLES");

if ($result) {
    echo "Tables in database:\n";
    while ($row = $result->fetch_array()) {
        echo $row[0] . "\n";
    }
} else {
    echo "Error showing tables: " . $conn->error;
}
?>
