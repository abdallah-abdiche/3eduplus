<?php
require_once 'config.php';

echo "<h2>Updating Événements Table Schema...</h2>";

// Check and add titre column
$columns_to_add = [
    'titre' => "ALTER TABLE evenements ADD COLUMN titre VARCHAR(255) NOT NULL AFTER evenement_id",
    'categorie' => "ALTER TABLE evenements ADD COLUMN categorie VARCHAR(100) DEFAULT NULL AFTER description",
    'type_evenement' => "ALTER TABLE evenements ADD COLUMN type_evenement ENUM('online', 'in-person') DEFAULT 'online' AFTER categorie",
    'heure_debut' => "ALTER TABLE evenements ADD COLUMN heure_debut TIME DEFAULT NULL AFTER date_evenement",
    'heure_fin' => "ALTER TABLE evenements ADD COLUMN heure_fin TIME DEFAULT NULL AFTER heure_debut",
    'lieu' => "ALTER TABLE evenements ADD COLUMN lieu VARCHAR(255) DEFAULT NULL AFTER heure_fin",
    'prix' => "ALTER TABLE evenements ADD COLUMN prix DECIMAL(10,2) DEFAULT 0.00 AFTER lieu",
    'max_participants' => "ALTER TABLE evenements ADD COLUMN max_participants INT(11) DEFAULT 100 AFTER prix",
    'instructeur' => "ALTER TABLE evenements ADD COLUMN instructeur VARCHAR(200) DEFAULT NULL AFTER max_participants",
    'image_url' => "ALTER TABLE evenements ADD COLUMN image_url VARCHAR(500) DEFAULT NULL AFTER instructeur",
    'createur_id' => "ALTER TABLE evenements ADD COLUMN createur_id INT(11) DEFAULT NULL AFTER image_url",
    'date_creation' => "ALTER TABLE evenements ADD COLUMN date_creation DATETIME DEFAULT CURRENT_TIMESTAMP AFTER createur_id"
];

foreach ($columns_to_add as $column => $sql) {
    $check = $conn->query("SHOW COLUMNS FROM evenements LIKE '$column'");
    if ($check->num_rows == 0) {
        if ($conn->query($sql) === TRUE) {
            echo "✓ Added column '$column' to evenements table.<br>";
        } else {
            echo "✗ Error adding '$column': " . $conn->error . "<br>";
        }
    } else {
        echo "- Column '$column' already exists.<br>";
    }
}

// Make evenement_id auto increment if not already
$check = $conn->query("SHOW COLUMNS FROM evenements WHERE Field = 'evenement_id' AND Extra LIKE '%auto_increment%'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE evenements MODIFY evenement_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Set evenement_id as AUTO_INCREMENT PRIMARY KEY.<br>";
    } else {
        echo "✗ Error modifying evenement_id: " . $conn->error . "<br>";
    }
} else {
    echo "- Column 'evenement_id' is already AUTO_INCREMENT.<br>";
}

// Create event_inscriptions table for tracking registrations
$check = $conn->query("SHOW TABLES LIKE 'event_inscriptions'");
if ($check->num_rows == 0) {
    $sql = "CREATE TABLE event_inscriptions (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        evenement_id INT(11) NOT NULL,
        user_id INT(11) NOT NULL,
        date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
        statut ENUM('inscrit', 'annulé', 'présent') DEFAULT 'inscrit',
        UNIQUE KEY unique_inscription (evenement_id, user_id),
        KEY idx_evenement (evenement_id),
        KEY idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Created table 'event_inscriptions'.<br>";
    } else {
        echo "✗ Error creating 'event_inscriptions': " . $conn->error . "<br>";
    }
} else {
    echo "- Table 'event_inscriptions' already exists.<br>";
}

// Insert sample events if table is empty
$count = $conn->query("SELECT COUNT(*) as cnt FROM evenements")->fetch_assoc()['cnt'];
if ($count == 0) {
    $sample_events = [
        [
            'titre' => 'Webinaire: Introduction au développement web moderne',
            'description' => 'Découvrez les fondamentaux du développement web moderne avec React et TypeScript.',
            'categorie' => 'Développement Web',
            'type_evenement' => 'online',
            'date_evenement' => '2025-01-25',
            'heure_debut' => '14:00:00',
            'heure_fin' => '16:00:00',
            'lieu' => 'En ligne',
            'prix' => 0,
            'max_participants' => 200,
            'instructeur' => 'Jean Dupont'
        ],
        [
            'titre' => 'Workshop: Data Science avec Python',
            'description' => 'Journée complète dédiée à l\'analyse de données avec Python et ses librairies.',
            'categorie' => 'Data Science',
            'type_evenement' => 'in-person',
            'date_evenement' => '2025-02-05',
            'heure_debut' => '09:00:00',
            'heure_fin' => '17:00:00',
            'lieu' => 'Alger - Centre 3edu+',
            'prix' => 99,
            'max_participants' => 15,
            'instructeur' => 'Sophie Bernard'
        ],
        [
            'titre' => 'Masterclass: SEO et référencement naturel',
            'description' => 'Optimisez votre présence en ligne avec les meilleures pratiques SEO.',
            'categorie' => 'Marketing Digital',
            'type_evenement' => 'online',
            'date_evenement' => '2025-02-08',
            'heure_debut' => '15:00:00',
            'heure_fin' => '18:00:00',
            'lieu' => 'En ligne',
            'prix' => 0,
            'max_participants' => 150,
            'instructeur' => 'Anne Dubois'
        ]
    ];
    
    $stmt = $conn->prepare("INSERT INTO evenements (titre, description, categorie, type_evenement, date_evenement, heure_debut, heure_fin, lieu, prix, max_participants, instructeur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($sample_events as $event) {
        $stmt->bind_param("ssssssssdis", 
            $event['titre'], 
            $event['description'], 
            $event['categorie'], 
            $event['type_evenement'], 
            $event['date_evenement'], 
            $event['heure_debut'], 
            $event['heure_fin'], 
            $event['lieu'], 
            $event['prix'], 
            $event['max_participants'], 
            $event['instructeur']
        );
        $stmt->execute();
    }
    $stmt->close();
    echo "✓ Inserted " . count($sample_events) . " sample events.<br>";
}

echo "<br><h3>✓ Database schema update complete!</h3>";
echo "<p><a href='evenements.php'>View Events Page</a> | <a href='dashboard/pedagogique/events.php'>Manage Events</a></p>";

$conn->close();
?>
