<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$formations = [];
$events = [];

if (!empty($query)) {
    // Search in Formations
    $sql_formations = "SELECT * FROM Formations WHERE titre LIKE ? OR description LIKE ? OR categorie LIKE ?";
    $stmt_f = $conn->prepare($sql_formations);
    $search_term = "%" . $query . "%";
    $stmt_f->bind_param("sss", $search_term, $search_term, $search_term);
    $stmt_f->execute();
    $result_f = $stmt_f->get_result();
    while ($row = $result_f->fetch_assoc()) {
        $formations[] = $row;
    }
    $stmt_f->close();

    // Search in Events (if table exists)
    $sql_events = "SELECT * FROM evenements WHERE titre LIKE ? OR description LIKE ? OR categorie LIKE ?";
    $stmt_e = $conn->prepare($sql_events);
    if ($stmt_e) {
        $stmt_e->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt_e->execute();
        $result_e = $stmt_e->get_result();
        while ($row = $result_e->fetch_assoc()) {
            $events[] = $row;
        }
        $stmt_e->close();
    }
}

// Check auth for header
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? ($_SESSION['user_name'] ?? 'Utilisateur') : '';

// Cart count
$cart_count = 0;
if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    $count_sql = "SELECT COUNT(*) as count FROM panier WHERE utilisateur_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    if ($count_result->num_rows > 0) {
        $cart_count = $count_result->fetch_assoc()['count'];
    }
    $count_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - 3edu+</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="formation.css">
    <link rel="icon" href="./LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .search-results-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            min-height: 60vh;
        }
        .section-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .no-results {
            text-align: center;
            color: #666;
            margin-top: 50px;
            font-size: 1.2rem;
        }
        .result-type-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .badge-formation {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .badge-event {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
    </style>
</head>
<body>
    <header class="header-nav">
        <div class="logocontainer">
            <a href="index.php"><img src="./LogoEdu.png" width="150" height="100" alt="3edu+ Logo"></a>
        </div>
        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="formation.php">Formations</a></li>
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="about.php">À propos</a></li>
                <li><a href="inscription.php">Inscriptions</a></li>
            </ul>
        </nav>
        <div class="nav-actions">
            <form action="search_results.php" method="GET" class="search-container">
                <input type="text" name="q" placeholder="Rechercher..." class="search-input" value="<?php echo htmlspecialchars($query); ?>">
                <button type="submit" class="search-btn" title="Rechercher">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <div>
                <select name="lang" id="selectlang" class="select-Lang">
                    <option value="francais">FR</option>
                    <option value="arabic">AR</option>
                    <option value="english">ENG</option>
                </select>
            </div>
            <button title="Toggle dark mode" class="darkMode">
                <i class="fas fa-moon" style="color: rgba(245, 196, 0, 0.873);"></i>
            </button>
            <a href="cart.php" class="cart-icon" title="Panier">
                <img src="https://cdn-icons-png.flaticon.com/128/2838/2838895.png" width="30" height="30" alt="Panier">
                <span class="cart-count"><?php echo $cart_count; ?></span>
            </a>
            <?php if ($is_logged_in): ?>
                <div class="user-menu">
                    <button class="user-btn">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($user_name); ?>
                    </button>
                    <div class="user-dropdown">
                        <a href="<?php echo getDashboardUrl(); ?>">Mon Tableau de bord</a>
                        <a href="logout.php">Déconnexion</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="signup.php" class="signup-btn">Connexion</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="search-results-container">
        <h1>Résultats de recherche pour "<?php echo htmlspecialchars($query); ?>"</h1>
        
        <?php if (empty($formations) && empty($events)): ?>
            <div class="no-results">
                <i class="fas fa-search" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
                <p>Aucun résultat trouvé. Essayez d'autres mots-clés.</p>
            </div>
        <?php else: ?>
            
            <?php if (!empty($formations)): ?>
                <h2 class="section-title">Formations (<?php echo count($formations); ?>)</h2>
                <div class="course-grid">
                    <?php foreach ($formations as $formation): ?>
                        <div class="course-card">
                            <div class="course-image">
                                <?php if (!empty($formation['formationImageUrl'])): ?>
                                    <img src="<?php echo htmlspecialchars($formation['formationImageUrl']); ?>" alt="<?php echo htmlspecialchars($formation['titre']); ?>">
                                <?php else: ?>
                                    <div class="course-placeholder">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="course-content">
                                <span class="result-type-badge badge-formation">Formation</span>
                                <div class="course-category"><?php echo htmlspecialchars($formation['categorie']); ?></div>
                                <h3 class="course-title"><?php echo htmlspecialchars($formation['titre']); ?></h3>
                                <div class="course-footer">
                                    <span class="course-price"><?php echo number_format($formation['prix'], 2); ?> DA</span>
                                    <a href="course.php?id=<?php echo $formation['formation_id']; ?>" class="btn-details">Voir détails</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($events)): ?>
                <h2 class="section-title" style="margin-top: 40px;">Événements (<?php echo count($events); ?>)</h2>
                <div class="course-grid">
                    <?php foreach ($events as $event): ?>
                        <div class="course-card"> <!-- Using course-card style for consistency -->
                            <div class="course-content" style="padding-top: 20px;">
                                <span class="result-type-badge badge-event">Événement</span>
                                <div class="course-category"><?php echo htmlspecialchars($event['categorie']); ?></div>
                                <h3 class="course-title"><?php echo htmlspecialchars($event['titre']); ?></h3>
                                <p style="color: #666; font-size: 0.9rem; margin-bottom: 10px;">
                                    <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?>
                                </p>
                                <div class="course-footer">
                                    <span class="course-price"><?php echo $event['prix'] > 0 ? number_format($event['prix'], 2) . ' DA' : 'Gratuit'; ?></span>
                                    <a href="evenements.php#events" class="btn-details">Voir détails</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="./LogoEdu.png" alt="3edu+ Logo" width="150" height="100">
                    <p>Votre partenaire de formation professionnelle.</p>
                </div>
            </div>
            <div class="footer-section">
                <h3>Liens rapides</h3>
                <ul class="footer-links">
                    <li><a href="formation.php">Nos formations</a></li>
                    <li><a href="evenements.php">Événements</a></li>
                    <li><a href="about.php">À propos</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>contact@3eduplus.fr</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; 2025 3edu+ Centre de Formation. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html>
