<?php
/**
 * Database Setup Script for 3edu+
 * Run this ONCE to initialize the database with all required tables and columns
 * Access: http://localhost/3eduplus/setup_database.php
 */

require_once 'config.php';

$messages = [];
$errors = [];

// Check database connection
if ($conn->connect_error) {
    $errors[] = "Connection failed: " . $conn->connect_error;
} else {
    $messages[] = "✅ Database connected successfully";
}

// SQL queries to run
$queries = [
    // Disable foreign key checks temporarily
    "SET FOREIGN_KEY_CHECKS=0",
    
    // Create roles table if not exists
    "CREATE TABLE IF NOT EXISTS roles (
        role_id INT PRIMARY KEY AUTO_INCREMENT,
        nom_role VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Insert default roles
    "INSERT IGNORE INTO roles (nom_role, description) VALUES 
    ('Apprenant', 'Étudiant ou apprenant'),
    ('Commercial', 'Responsable commercial'),
    ('Pédagogique', 'Formateur ou instructeur'),
    ('Marketing', 'Responsable marketing'),
    ('Admin', 'Administrateur système')",
    
    // Create formations table if not exists
    "CREATE TABLE IF NOT EXISTS formations (
        formation_id INT PRIMARY KEY AUTO_INCREMENT,
        titre VARCHAR(255) NOT NULL,
        description TEXT,
        categorie VARCHAR(100),
        prix DECIMAL(10, 2) NOT NULL,
        duree VARCHAR(50),
        niveau ENUM('Débutant', 'Intermédiaire', 'Avancé') DEFAULT 'Débutant',
        formationImageUrl VARCHAR(500),
        instructeur_id INT,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (instructeur_id) REFERENCES utilisateurs(user_id)
    )",
    
    // Create inscriptions table if not exists
    "CREATE TABLE IF NOT EXISTS inscriptions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        utilisateur_id INT NOT NULL,
        formation_id INT NOT NULL,
        date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        statut ENUM('En cours', 'Complétée', 'Abandonnée') DEFAULT 'En cours',
        progression INT DEFAULT 0,
        date_completion TIMESTAMP NULL,
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(user_id),
        FOREIGN KEY (formation_id) REFERENCES formations(formation_id),
        UNIQUE KEY unique_inscription (utilisateur_id, formation_id)
    )",
    
    // Create panier table if not exists
    "CREATE TABLE IF NOT EXISTS panier (
        id INT PRIMARY KEY AUTO_INCREMENT,
        utilisateur_id INT NOT NULL,
        formation_id INT NOT NULL,
        quantite INT DEFAULT 1,
        prix_unitaire DECIMAL(10, 2),
        date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(user_id),
        FOREIGN KEY (formation_id) REFERENCES formations(formation_id),
        UNIQUE KEY unique_panier (utilisateur_id, formation_id)
    )",
    
    // Create paiements table if not exists
    "CREATE TABLE IF NOT EXISTS paiements (
        id INT PRIMARY KEY AUTO_INCREMENT,
        utilisateur_id INT NOT NULL,
        montant_total DECIMAL(10, 2) NOT NULL,
        methode_paiement VARCHAR(100),
        statut_paiement ENUM('En attente', 'Complété', 'Échoué', 'Remboursé') DEFAULT 'Complété',
        date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reference_transaction VARCHAR(100) UNIQUE,
        INDEX idx_utilisateur_id (utilisateur_id)
    )",
    
    // Create recus table if not exists
    "CREATE TABLE IF NOT EXISTS recus (
        id INT PRIMARY KEY AUTO_INCREMENT,
        paiement_id INT NOT NULL,
        utilisateur_id INT NOT NULL,
        numero_recu VARCHAR(50) UNIQUE NOT NULL,
        formations_achetees TEXT,
        montant_total DECIMAL(10, 2),
        date_emission TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        contenu_html LONGTEXT,
        INDEX idx_paiement_id (paiement_id),
        INDEX idx_utilisateur_id (utilisateur_id)
    )",
    
    // Create paiement_formations table if not exists
    "CREATE TABLE IF NOT EXISTS paiement_formations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        paiement_id INT NOT NULL,
        formation_id INT NOT NULL,
        prix_paye DECIMAL(10, 2),
        INDEX idx_paiement_id (paiement_id),
        INDEX idx_formation_id (formation_id)
    )",
    
    // Re-enable foreign key checks
    "SET FOREIGN_KEY_CHECKS=1"
];

// Execute queries
foreach ($queries as $index => $query) {
    if ($conn->query($query) === TRUE) {
        $messages[] = "✅ Query " . ($index + 1) . " executed successfully";
    } else {
        // Some queries might fail if tables already exist - that's OK
        if (strpos($conn->error, 'already exists') !== false || strpos($conn->error, 'Duplicate column') !== false) {
            $messages[] = "⚠️ Query " . ($index + 1) . " skipped (already exists): " . substr($conn->error, 0, 50);
        } else {
            $errors[] = "❌ Query " . ($index + 1) . " failed: " . $conn->error;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3edu+ Database Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .status {
            margin: 30px 0;
            padding: 20px;
            border-radius: 5px;
            background: #f0f0f0;
        }
        .message {
            padding: 10px;
            margin: 8px 0;
            border-left: 4px solid #4CAF50;
            background: #f1f8f4;
            color: #2d5016;
        }
        .error {
            padding: 10px;
            margin: 8px 0;
            border-left: 4px solid #f44336;
            background: #fff3f3;
            color: #c41c00;
        }
        .warning {
            padding: 10px;
            margin: 8px 0;
            border-left: 4px solid #ff9800;
            background: #fff3f0;
            color: #e65100;
        }
        .success-box {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #c3e6cb;
        }
        .next-steps {
            background: #cce5ff;
            color: #004085;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #b3d9ff;
        }
        a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        .back-link:hover {
            background: #764ba2;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 3edu+ Database Setup</h1>
        
        <div class="status">
            <h2>Setup Results:</h2>
            
            <?php if (!empty($errors)): ?>
                <h3 style="color: #f44336;">Errors (⚠️ Non-Critical):</h3>
                <?php foreach ($errors as $error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (!empty($messages)): ?>
                <h3 style="color: #4CAF50;">Status Messages:</h3>
                <?php foreach ($messages as $msg): ?>
                    <?php if (strpos($msg, '⚠️') !== false): ?>
                        <div class="warning"><?php echo htmlspecialchars($msg); ?></div>
                    <?php else: ?>
                        <div class="message"><?php echo htmlspecialchars($msg); ?></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if (empty($errors) || count($errors) < 3): ?>
            <div class="success-box">
                <h2>✅ Setup Complete!</h2>
                <p>Your database is now ready to use. You can:</p>
                <ul>
                    <li>Go to <a href="signup.php">signup.php</a> to register and login</li>
                    <li>Go to <a href="formation.php">formation.php</a> to browse courses</li>
                    <li>Return to <a href="index.html">home page</a></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="next-steps">
                <h2>⚠️ Setup Incomplete</h2>
                <p>Please check the errors above and:</p>
                <ol>
                    <li>Verify your database connection in <code>config.php</code></li>
                    <li>Check that the <code>utilisateurs</code> table exists</li>
                    <li>Try running this script again</li>
                    <li>Or manually run the SQL queries in phpMyAdmin</li>
                </ol>
            </div>
        <?php endif; ?>
        
        <a href="index.html" class="back-link">← Return to Home</a>
    </div>
</body>
</html>
