<?php
require_once 'config.php';

echo "<h2>Updating Certifications Table Schema...</h2>";

// Add formation_id column
$check = $conn->query("SHOW COLUMNS FROM certifications LIKE 'formation_id'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE certifications ADD COLUMN formation_id INT(11) DEFAULT NULL AFTER user_id";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Added column 'formation_id' to certifications table.<br>";
    } else {
        echo "✗ Error adding 'formation_id': " . $conn->error . "<br>";
    }
} else {
    echo "- Column 'formation_id' already exists.<br>";
}

// Add nom_complet column
$check = $conn->query("SHOW COLUMNS FROM certifications LIKE 'nom_complet'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE certifications ADD COLUMN nom_complet VARCHAR(200) DEFAULT NULL AFTER type_certificat";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Added column 'nom_complet' to certifications table.<br>";
    } else {
        echo "✗ Error adding 'nom_complet': " . $conn->error . "<br>";
    }
} else {
    echo "- Column 'nom_complet' already exists.<br>";
}

// Add titre_formation column
$check = $conn->query("SHOW COLUMNS FROM certifications LIKE 'titre_formation'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE certifications ADD COLUMN titre_formation VARCHAR(300) DEFAULT NULL AFTER nom_complet";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Added column 'titre_formation' to certifications table.<br>";
    } else {
        echo "✗ Error adding 'titre_formation': " . $conn->error . "<br>";
    }
} else {
    echo "- Column 'titre_formation' already exists.<br>";
}

// Add numero_certificat column
$check = $conn->query("SHOW COLUMNS FROM certifications LIKE 'numero_certificat'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE certifications ADD COLUMN numero_certificat VARCHAR(100) UNIQUE DEFAULT NULL AFTER titre_formation";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Added column 'numero_certificat' to certifications table.<br>";
    } else {
        echo "✗ Error adding 'numero_certificat': " . $conn->error . "<br>";
    }
} else {
    echo "- Column 'numero_certificat' already exists.<br>";
}

// Make certificat_id auto increment if not already
$check = $conn->query("SHOW COLUMNS FROM certifications WHERE Field = 'certificat_id' AND Extra LIKE '%auto_increment%'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE certifications MODIFY certificat_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Set certificat_id as AUTO_INCREMENT PRIMARY KEY.<br>";
    } else {
        echo "✗ Error modifying certificat_id: " . $conn->error . "<br>";
    }
} else {
    echo "- Column 'certificat_id' is already AUTO_INCREMENT.<br>";
}

echo "<br><h3>✓ Database schema update complete!</h3>";
echo "<p><a href='formation.php'>Go to Formations</a></p>";

$conn->close();
?>
