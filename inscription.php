<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

checkAuth(); // Ensure user is logged in
$user = getCurrentUser();

$formations = [];
$user_id = $user['id'];

$formations = [];
$user_id = $user['id'];

// Fetch purchased formations
$query = "
    SELECT f.*, i.date_inscription 
    FROM formations f 
    JOIN inscriptions i ON f.formation_id = i.formation_id 
    WHERE i.user_id = ? 
    ORDER BY i.date_inscription DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $formations[] = $row;
    }
}
$stmt->close();

// Fetch registered events
$events = [];
$event_query = "
    SELECT e.*, ei.date_inscription 
    FROM evenements e 
    JOIN event_inscriptions ei ON e.evenement_id = ei.evenement_id 
    WHERE ei.user_id = ? 
    ORDER BY ei.date_inscription DESC
";
$e_stmt = $conn->prepare($event_query);
$e_stmt->bind_param("i", $user_id);
$e_stmt->execute();
$e_result = $e_stmt->get_result();
if ($e_result && $e_result->num_rows > 0) {
    while ($row = $e_result->fetch_assoc()) {
        $events[] = $row;
    }
}
$e_stmt->close();

// Get cart count
$cart_count = 0;
$count_sql = "SELECT COUNT(*) as count FROM panier WHERE utilisateur_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
if ($count_result->num_rows > 0) {
    $cart_count = $count_result->fetch_assoc()['count'];
}
$count_stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Inscriptions - 3edu+</title>
    <link rel="stylesheet" href="formation.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="./LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .my-courses-header {
            background: linear-gradient(135deg, #1a6dcc 0%, #022d63 100%);
            padding: 3rem 2rem;
            color: white;
            text-align: center;
            margin-bottom: 2rem;
        }
        .my-courses-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .course-card .course-footer .course-price {
            color: #28a745;
            font-weight: bold;
        }
        .access-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            font-weight: 500;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .access-btn:hover {
            background-color: #0056b3;
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
                <li><a href="inscription.php" class="active">Inscriptions</a></li>
            </ul>
        </nav>

        <div class="nav-actions">
            <form action="search_results.php" method="GET" class="search-container">
                <input type="text" name="q" placeholder="Rechercher des formations..." class="search-input">
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
            <div class="user-menu">
                <button class="user-btn">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($user['name']); ?>
                </button>
                <div class="user-dropdown">
                    <a href="<?php echo getDashboardUrl($user['role']); ?>">Mon Tableau de bord</a>
                    <a href="logout.php">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <div class="my-courses-header">
        <h1>Mes Inscriptions</h1>
        <p>Retrouvez toutes vos formations et événements</p>
    </div>

    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <h2 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">
            <i class="fas fa-graduation-cap"></i> Mes Formations
        </h2>
        <?php if (count($formations) > 0): ?>
            <div class="course-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; margin-bottom: 50px;">
                <?php foreach ($formations as $formation): ?>
                    <div class="course-card">
                        <div class="course-image">
                            <?php if (!empty($formation['formationImageUrl'])): ?>
                                <img src="<?php echo htmlspecialchars($formation['formationImageUrl']); ?>" alt="<?php echo htmlspecialchars($formation['titre']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div style="height: 200px; background: #eee; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-book" style="font-size: 50px; color: #ccc;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="course-overlay"><span class="course-badge">Inscrit</span></div>
                        </div>
                        <div class="course-content">
                            <div class="course-category"><?php echo htmlspecialchars($formation['categorie'] ?? 'Général'); ?></div>
                            <h3 class="course-title"><?php echo htmlspecialchars($formation['titre']); ?></h3>
                            <div class="course-details" style="margin-bottom: 15px;">
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo htmlspecialchars($formation['duree'] ?? 'Non spécifié'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-layer-group"></i>
                                    <span><?php echo htmlspecialchars($formation['niveau'] ?? 'Tous niveaux'); ?></span>
                                </div>
                            </div>
                            <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px; font-size: 0.9em; color: #666;">
                                Inscrit le: <?php echo date('d/m/Y', strtotime($formation['date_inscription'])); ?>
                            </div>
                            <a href="course.php?id=<?php echo $formation['formation_id']; ?>" class="access-btn">
                                <i class="fas fa-play-circle"></i> Accéder au cours
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 50px;">
                <p style="color: #666;">Vous n'êtes inscrit à aucune formation.</p>
                <a href="formation.php" style="color: #007bff; text-decoration: none; font-weight: 500;">Découvrir les formations</a>
            </div>
        <?php endif; ?>

        <h2 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-top: 40px;">
            <i class="fas fa-calendar-alt"></i> Mes Événements
        </h2>
        <?php if (count($events) > 0): ?>
            <div class="course-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
                <?php foreach ($events as $event): ?>
                    <div class="course-card">
                        <div class="course-image">
                            <?php if (!empty($event['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['titre']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div style="height: 200px; background: #eee; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-calendar-alt" style="font-size: 50px; color: #ccc;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="course-overlay"><span class="course-badge" style="background: #28a745;">Inscrit</span></div>
                        </div>
                        <div class="course-content">
                            <div class="course-category"><?php echo htmlspecialchars($event['type'] ?? 'Événement'); ?></div>
                            <h3 class="course-title"><?php echo htmlspecialchars($event['titre']); ?></h3>
                            <div class="course-details" style="margin-bottom: 15px;">
                                <div class="detail-item">
                                    <i class="fas fa-calendar-day"></i>
                                    <span><?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($event['lieu']); ?></span>
                                </div>
                            </div>
                            <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px; font-size: 0.9em; color: #666;">
                                Inscrit le: <?php echo date('d/m/Y', strtotime($event['date_inscription'])); ?>
                            </div>
                            <a href="event_content.php?id=<?php echo $event['evenement_id']; ?>" class="access-btn" style="background-color: #28a745;">
                                <i class="fas fa-video"></i> Accéder au contenu
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <p style="color: #666;">Vous n'êtes inscrit à aucun événement.</p>
                <a href="evenements.php" style="color: #28a745; text-decoration: none; font-weight: 500;">Parcourir les événements</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer" style="margin-top: 50px;">
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
        </div>
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; 2025 3edu+ Centre de Formation.</p>
            </div>
        </div>
    </footer>

    <script>
    document.querySelector('.user-btn').addEventListener('click', function(e){
      e.stopPropagation();
      this.parentElement.classList.toggle('open');
    });
    document.addEventListener('click', () =>
      document.querySelector('.user-menu').classList.remove('open')
    );
    </script>
</body>
</html>
