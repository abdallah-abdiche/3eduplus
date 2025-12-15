<<<<<<< HEAD
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
=======

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `certifications` (
  `certificat_id` int(11) NOT NULL,
  `date_obtention` date DEFAULT NULL,
  `type_certificat` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `evenements` (
  `evenement_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `date_evenement` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `formations` (
  `formation_id` int(11) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `niveau` varchar(50) DEFAULT NULL,
  `duree` varchar(50) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `createur_id` int(11) DEFAULT NULL,
  `formationImageUrl` varchar(255) NOT NULL,
  `video_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `formations` (`formation_id`, `titre`, `description`, `categorie`, `prix`, `niveau`, `duree`, `date_creation`, `createur_id`, `formationImageUrl`) VALUES
(1, 'Développement Web Full Stack\n\nPar Jean Dupont\n\nMaîtrisez le développement web de A à Z avec React, Node.js et MongoD ', NULL, 'Développement Web ', 599.00, 'Débutant', '8', '2025-12-07 17:21:36', NULL, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.pTLHqGiGuIa3WBzv-J0PrQHaEI%3Fpid%3DApi&f=1&ipt=a978229b95854ed93ddb76c958cc454457ef7b927ca8ff1d2ef71f913490e142&ipo=images'),
(2, 'Design UX/UI Moderne\r\nPar Claire Martin\r\nApprenez à créer des interfaces intuitives et attrayantes grâce aux principes clés du design UX/UI', NULL, 'Design & UX ', 499.00, 'Débutant', '4', '2025-12-07 17:24:13', NULL, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fmiro.medium.com%2Fv2%2Fresize%3Afit%3A1358%2F0*wuDgpMYQORGAmoi2&f=1&nofb=1&ipt=d181ece831281d9a68af78e7d57d572a446a463fc840053a4dd3fb0d01c25a33'),
(3, 'Marketing Digital & Stratégies Growth\nPar Léa Fontaine\nBoostez votre visibilité en ligne avec les techniques modernes de SEO, SEA et campagnes sociales.', NULL, 'Marketing Digital', 499.00, 'Débutant', '5', '2025-12-07 17:25:01', NULL, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.uFCouXWjlUh4jGYbUAsn-gHaEK%3Fpid%3DApi&f=1&ipt=92298d239f50df49fac30e71b3cf67f69f8c281c4acb55f8d765da70e69a5d60&ipo=images'),
(4, 'Cybersécurité & Protection des Systèmes\r\nPar Antoine Leroy\r\nComprenez les menaces actuelles et apprenez à sécuriser vos applications et infrastructures.', NULL, 'Cybersécurité ', 1099.00, 'Intermédiaire', '9', '2025-12-07 17:25:49', NULL, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.suXaZTYF4oHdamg4njQvvwHaDe%3Fpid%3DApi&f=1&ipt=368448d1f5b48308eca4fcb933d079d17d8f2f28729edeb4aace854f95ae3d86&ipo=images'),
(5, 'Data Science & Analyse de Données\r\nPar Karim Haddad\r\nDominez Python, Pandas et les modèles prédictifs pour transformer vos données en décisions.', NULL, 'Data Science', 999.00, 'Intermédiaire', '9', '2025-12-07 17:26:33', NULL, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.oBFcCHjC3lKn-dTmafYLbAHaFr%3Fcb%3Ducfimg2%26pid%3DApi%26ucfimg%3D1&f=1&ipt=d28d91763e67bc881a2be98415f8608dc94a4de77559bb35244ce7cfd3552322&ipo=images'),
(6, 'Création d’Applications Mobile\r\nPar Sofia Morel\r\nDéveloppez des apps performantes avec Flutter et Dart, du design à la publication.', NULL, 'Applications Mobile', 799.00, 'Intermédiaire', '8', '2025-12-07 17:27:25', NULL, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.nOiqIEoh5pcWAY_uRysELAHaDt%3Fcb%3Ducfimg2%26pid%3DApi%26ucfimg%3D1&f=1&ipt=d862f729b83a4c709c3795a5daf23498d3e30e7ecb785941708414ad22c6046e&ipo=images'),
(7, 'Data science', 'super advanced', 'e.g.Developper', 5000.00, 'Intermédiaire', '10', '2025-12-14 11:58:22', NULL, 'uploads/courses/Capture d\'écran 2025-07-02 084424.png'),
(10, 'cyber ', 'test', 'web', 1000.00, 'Avancé', '20', '2025-12-14 11:59:30', NULL, 'uploads/courses/Capture d\'écran 2025-07-01 211550.png'),
(11, 'test1', 's', '32132', 222.00, 'Débutant', '22', '2025-12-14 11:59:58', NULL, ''),
(12, 'test1', 's', '32132', 222.00, 'Débutant', '22', '2025-12-14 12:00:03', NULL, ''),
(13, 'test1', 's', '32132', 222.00, 'Débutant', '22', '2025-12-14 12:00:05', NULL, ''),
(15, 'test1', 's', '32132', 222.00, 'Débutant', '22', '2025-12-14 12:00:07', NULL, ''),
(16, 'test2', 'ksjk', 'sdsml', 11.00, 'Débutant', 'mlkm', '2025-12-14 12:03:18', NULL, ''),
(17, 'test2', 'ksjk', 'sdsml', 11.00, 'Débutant', 'mlkm', '2025-12-14 12:03:22', NULL, ''),
(18, 'test2', 'ksjk', 'sdsml', 11.00, 'Débutant', 'mlkm', '2025-12-14 12:03:34', NULL, ''),
(20, 'test3', 'sdsmlm', 'ùlsùmdl', 321321.00, 'Avancé', '10', '2025-12-14 12:04:49', NULL, ''),
(21, 'Bug Bounty', 'this is the best fomation u can took', 'Web', 1000.00, 'Avancé', '10', '2025-12-14 12:08:05', NULL, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.y2mcdegZblsm4hQhUC7CmQHaE8%3Fcb%3Ducfimg2%26pid%3DApi%26ucfimg%3D1&f=1&ipt=6cbaee1eb60e442af9d7bb8179c86781a0cfa4079294a50337ee335399827794&ipo=images');



CREATE TABLE `inscriptions` (
  `inscription_id` int(11) NOT NULL,
  `statut_inscription` varchar(50) DEFAULT NULL,
  `date_inscription` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `message_text` text DEFAULT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `date_envoi` datetime DEFAULT current_timestamp(),
  `destinataire_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `paiements` (
  `paiement_id` int(11) NOT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `date_paiement` datetime DEFAULT current_timestamp(),
  `statut` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `paiements` (`paiement_id`, `montant`, `date_paiement`, `statut`, `user_id`) VALUES
(1, 1300.00, '2025-11-30 22:04:53', 'paid', NULL);



CREATE TABLE `paiement_formations` (
  `id` int(11) NOT NULL,
  `paiement_id` int(11) NOT NULL,
  `formation_id` int(11) NOT NULL,
  `prix_paye` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `panier` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `formation_id` int(11) NOT NULL,
  `quantite` int(11) DEFAULT 1,
  `prix_unitaire` decimal(10,2) DEFAULT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `panier` (`id`, `utilisateur_id`, `formation_id`, `quantite`, `prix_unitaire`, `date_ajout`) VALUES
(1, 12, 6, 1, 799.00, '2025-12-07 20:02:34'),
(2, 12, 2, 1, 499.00, '2025-12-07 20:02:48'),
(3, 12, 1, 1, 599.00, '2025-12-07 20:02:50'),
(4, 12, 5, 1, 999.00, '2025-12-07 20:02:52'),
(5, 12, 4, 1, 1099.00, '2025-12-07 20:33:34');



CREATE TABLE `recus` (
  `id` int(11) NOT NULL,
  `paiement_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `numero_recu` varchar(50) NOT NULL,
  `formations_achetees` text DEFAULT NULL,
  `montant_total` decimal(10,2) DEFAULT NULL,
  `date_emission` timestamp NOT NULL DEFAULT current_timestamp(),
  `contenu_html` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `nom_role` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `roles` (`role_id`, `nom_role`, `description`) VALUES
(1, 'Apprenant', 'Étudiant ou apprenant'),
(2, 'Commercial', 'Responsable commercial'),
(3, 'Pédagogique', 'Formateur ou instructeur'),
(4, 'Marketing', 'Responsable marketing'),
(5, 'Admin', 'Administrateur système');



CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `date_debut` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `lieu` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE `temoignages` (
  `temoignage_id` int(11) NOT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `note` int(11) DEFAULT NULL,
  `contenu` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;





CREATE TABLE `utilisateurs` (
  `user_id` int(11) NOT NULL,
  `Nom_Complet` varchar(100) NOT NULL,
  `Mot_de_passe` varchar(255) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Wilaya` varchar(50) DEFAULT NULL,
  `numero_tlf_utilisateur` varchar(20) DEFAULT NULL,
  `date_registration` datetime DEFAULT current_timestamp(),
  `image_utilisateur` varchar(255) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `utilisateurs` (`user_id`, `Nom_Complet`, `Mot_de_passe`, `Email`, `Wilaya`, `numero_tlf_utilisateur`, `date_registration`, `image_utilisateur`, `gender`, `role_id`) VALUES
(5, 'dkfmlqk', '$2y$10$3DllAT452XBA8FJ0geZl6umHcmXE5wMxo7LvAoB318.lpdlWOLm9G', 'qmfl@xn--slmflm-hyaf', 'lqdfklqmsk', '44521', '0012-02-14 00:00:00', '', 'Female', NULL),
(12, 'xwxw', '$2y$10$6qnkxPfbv61.x06xk0bbIupKuOgc1TR.fMRtM98qorkcXHrDYMYeu', 'muemail@gmail.com', 'fddf', '564564', '0000-00-00 00:00:00', '', 'Male', NULL),
(13, 'mohe', '$2y$10$oCzbqxtlB3YWeDjOluf2MOXrseeuF2uqJZtlq8EwwBBuLQJ40cAPi', 'moha@gmail.com', 'tt', '123', '0001-02-02 00:00:00', '', 'Male', 1),
(14, 'mmohe', '$2y$10$knSBQUqViETKenweQpCtL.K/dGynFSa9vmIiWaORuH49FLY9g5aiK', 'mmohe@gmail.com', 'ff', '13', '0012-12-01 00:00:00', '', 'Male', 1),
(15, 'mohe', '$2y$10$n.U5Ndhdt./VfIWYlWVGGeLWNeNUha52E5yzavM9ZD/iEwLJ0lXLq', 'mohe@gmail.com', 'd', '5465', '2025-12-04 00:00:00', '', 'Male', 3),
(16, 'mohe', '$2y$10$Zs1MczPXM4iHJ5c5m5uuSexh9oBUQMZ4xB8gcu7UDMJRjicUVoSA2', 'mohe1@gmail.com', 'lsl', '132', '2025-12-02 00:00:00', '', 'Male', 4),
(17, 'mohe', '$2y$10$joG4052qnM4..Lut6z24XO93FS0tIE.kl70gB2BzMZWT5.J7AFGcW', 'mohe2@gmail.com', 'dd', '13', '2025-12-03 00:00:00', '', 'Male', 1),
(18, 'Admin', '$2y$10$ckky7JjF.wVXTiKCalZt1.2WP/fJTEMab1boA2Vs5Tn/gtb3o1IEm', 'admin@gmail.com', 'Tipaza', '0549473088', '2005-11-21 00:00:00', '', 'Male', 5);


ALTER TABLE `certifications`
  ADD PRIMARY KEY (`certificat_id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `evenements`
  ADD PRIMARY KEY (`evenement_id`);


ALTER TABLE `formations`
  ADD PRIMARY KEY (`formation_id`),
  ADD KEY `createur_id` (`createur_id`);


ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`inscription_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_id` (`session_id`);


ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `destinataire_id` (`destinataire_id`);


ALTER TABLE `paiements`
  ADD PRIMARY KEY (`paiement_id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `paiement_formations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_paiement_id` (`paiement_id`),
  ADD KEY `idx_formation_id` (`formation_id`);


ALTER TABLE `panier`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_panier` (`utilisateur_id`,`formation_id`),
  ADD KEY `formation_id` (`formation_id`);


ALTER TABLE `recus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_recu` (`numero_recu`),
  ADD KEY `idx_paiement_id` (`paiement_id`),
  ADD KEY `idx_utilisateur_id` (`utilisateur_id`);


ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `nom_role` (`nom_role`);


ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`);


ALTER TABLE `temoignages`
  ADD PRIMARY KEY (`temoignage_id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `fk_role_id` (`role_id`);


ALTER TABLE `certifications`
  MODIFY `certificat_id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `evenements`
  MODIFY `evenement_id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `formations`
  MODIFY `formation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;


ALTER TABLE `inscriptions`
  MODIFY `inscription_id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `paiements`
  MODIFY `paiement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `paiement_formations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `panier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;


ALTER TABLE `recus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;


ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `temoignages`
  MODIFY `temoignage_id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `utilisateurs`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;


ALTER TABLE `certifications`
  ADD CONSTRAINT `certifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`user_id`);


ALTER TABLE `formations`
  ADD CONSTRAINT `formations_ibfk_1` FOREIGN KEY (`createur_id`) REFERENCES `utilisateurs` (`user_id`);


ALTER TABLE `inscriptions`
  ADD CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`user_id`),
  ADD CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`);


ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`user_id`);


ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`user_id`);


ALTER TABLE `panier`
  ADD CONSTRAINT `panier_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`user_id`),
  ADD CONSTRAINT `panier_ibfk_2` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`formation_id`);


ALTER TABLE `temoignages`
  ADD CONSTRAINT `temoignages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`user_id`);


ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE SET NULL;
COMMIT;


>>>>>>> 3e34b36 (newe version)
