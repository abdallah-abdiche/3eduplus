<?php
require_once 'config.php';

echo "Starting database schema update for payment system...\n\n";

// 1. Update paiements table - add missing columns
$paiements_updates = [
    "utilisateur_id" => "ALTER TABLE paiements ADD COLUMN utilisateur_id INT(11) DEFAULT NULL AFTER paiement_id",
    "montant_total" => "ALTER TABLE paiements ADD COLUMN montant_total DECIMAL(10,2) DEFAULT NULL AFTER montant",
    "methode_paiement" => "ALTER TABLE paiements ADD COLUMN methode_paiement VARCHAR(100) DEFAULT NULL AFTER statut",
    "reference_transaction" => "ALTER TABLE paiements ADD COLUMN reference_transaction VARCHAR(100) DEFAULT NULL AFTER methode_paiement"
];

foreach ($paiements_updates as $column => $sql) {
    $check = $conn->query("SHOW COLUMNS FROM paiements LIKE '$column'");
    if ($check->num_rows == 0) {
        if ($conn->query($sql) === TRUE) {
            echo "✓ Added column '$column' to paiements table.\n";
        } else {
            echo "✗ Error adding '$column': " . $conn->error . "\n";
        }
    } else {
        echo "- Column '$column' already exists in paiements.\n";
    }
}

// 2. Make sure inscriptions table has formation_id
$check = $conn->query("SHOW COLUMNS FROM inscriptions LIKE 'formation_id'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE inscriptions ADD COLUMN formation_id INT(11) DEFAULT NULL AFTER user_id";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Added column 'formation_id' to inscriptions table.\n";
        
        // Add foreign key
        $fkSql = "ALTER TABLE inscriptions ADD CONSTRAINT fk_inscription_formation FOREIGN KEY (formation_id) REFERENCES formations(formation_id)";
        if ($conn->query($fkSql) === TRUE) {
            echo "✓ Added foreign key constraint for formation_id.\n";
        } else {
            echo "✗ Error adding foreign key: " . $conn->error . "\n";
        }
    } else {
        echo "✗ Error adding 'formation_id': " . $conn->error . "\n";
    }
} else {
    echo "- Column 'formation_id' already exists in inscriptions.\n";
}

// 3. Verify paiement_formations table exists
$check = $conn->query("SHOW TABLES LIKE 'paiement_formations'");
if ($check->num_rows == 0) {
    $sql = "CREATE TABLE paiement_formations (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        paiement_id INT(11) NOT NULL,
        formation_id INT(11) NOT NULL,
        prix_paye DECIMAL(10,2) DEFAULT NULL,
        KEY idx_paiement_id (paiement_id),
        KEY idx_formation_id (formation_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Created table 'paiement_formations'.\n";
    } else {
        echo "✗ Error creating 'paiement_formations': " . $conn->error . "\n";
    }
} else {
    echo "- Table 'paiement_formations' already exists.\n";
}

// 4. Verify recus table exists with correct structure
$check = $conn->query("SHOW TABLES LIKE 'recus'");
if ($check->num_rows == 0) {
    $sql = "CREATE TABLE recus (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        paiement_id INT(11) NOT NULL,
        utilisateur_id INT(11) NOT NULL,
        numero_recu VARCHAR(50) NOT NULL UNIQUE,
        formations_achetees TEXT DEFAULT NULL,
        montant_total DECIMAL(10,2) DEFAULT NULL,
        date_emission TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        contenu_html LONGTEXT DEFAULT NULL,
        KEY idx_paiement_id (paiement_id),
        KEY idx_utilisateur_id (utilisateur_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Created table 'recus'.\n";
    } else {
        echo "✗ Error creating 'recus': " . $conn->error . "\n";
    }
} else {
    echo "- Table 'recus' already exists.\n";
}

echo "\n✓ Database schema update complete!\n";
echo "\nYou can now test the payment flow:\n";
echo "1. Add a course to cart\n";
echo "2. Go to cart.php\n";
echo "3. Proceed to checkout\n";
echo "4. Complete payment\n";
echo "5. Check inscription.php to see your purchased courses\n";

$conn->close();
?>
