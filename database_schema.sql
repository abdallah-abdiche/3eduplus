-- Database schema for 3eduplus
-- Run these queries in phpMyAdmin or MySQL CLI to set up the application

-- Create roles table
CREATE TABLE IF NOT EXISTS roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    nom_role VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT IGNORE INTO roles (nom_role, description) VALUES
('Apprenant', 'Étudiant ou apprenant'),
('Commercial', 'Responsable commercial'),
('Pédagogique', 'Formateur ou instructeur'),
('Marketing', 'Responsable marketing'),
('Admin', 'Administrateur système');

-- Alter utilisateurs table to add role_id column (if not exists)
ALTER TABLE utilisateurs ADD COLUMN role_id INT DEFAULT NULL;
ALTER TABLE utilisateurs ADD FOREIGN KEY (role_id) REFERENCES roles(role_id);

CREATE TABLE IF NOT EXISTS formations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    categorie VARCHAR(100),
    prix DECIMAL(10, 2) NOT NULL,
    duree VARCHAR(50),
    niveau ENUM('Débutant', 'Intermédiaire', 'Avancé') DEFAULT 'Débutant',
    image_url VARCHAR(255),
    instructeur_id INT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructeur_id) REFERENCES utilisateurs(id)
);

-- Create inscriptions table (Enrollments)
CREATE TABLE IF NOT EXISTS inscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    formation_id INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('En cours', 'Complétée', 'Abandonnée') DEFAULT 'En cours',
    progression INT DEFAULT 0,
    date_completion TIMESTAMP NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (formation_id) REFERENCES formations(id),
    UNIQUE KEY unique_inscription (utilisateur_id, formation_id)
);

-- Create panier table (Shopping Cart)
CREATE TABLE IF NOT EXISTS panier (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    formation_id INT NOT NULL,
    quantite INT DEFAULT 1,
    prix_unitaire DECIMAL(10, 2),
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (formation_id) REFERENCES formations(id),
    UNIQUE KEY unique_panier (utilisateur_id, formation_id)
);

-- Create paiements table (Payments)
CREATE TABLE IF NOT EXISTS paiements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    montant_total DECIMAL(10, 2) NOT NULL,
    methode_paiement VARCHAR(100),
    statut_paiement ENUM('En attente', 'Complété', 'Échoué', 'Remboursé') DEFAULT 'Complété',
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reference_transaction VARCHAR(100) UNIQUE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- Create reçus table (Receipts)
CREATE TABLE IF NOT EXISTS recus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    paiement_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    numero_recu VARCHAR(50) UNIQUE NOT NULL,
    formations_achetees TEXT,
    montant_total DECIMAL(10, 2),
    date_emission TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    contenu_html LONGTEXT,
    FOREIGN KEY (paiement_id) REFERENCES paiements(id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- Create paiement_formations junction table (Payment-Course mapping)
CREATE TABLE IF NOT EXISTS paiement_formations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    paiement_id INT NOT NULL,
    formation_id INT NOT NULL,
    prix_paye DECIMAL(10, 2),
    FOREIGN KEY (paiement_id) REFERENCES paiements(id),
    FOREIGN KEY (formation_id) REFERENCES formations(id)
);
