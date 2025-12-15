<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

$events = [];
$query = "SELECT e.*, 
          (SELECT COUNT(*) FROM event_inscriptions WHERE evenement_id = e.evenement_id) as participants_inscrits
          FROM evenements e 
          WHERE e.date_evenement >= CURDATE()
          ORDER BY e.date_evenement ASC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

$stats_query = "SELECT 
    (SELECT COUNT(*) FROM evenements WHERE date_evenement >= CURDATE()) as upcoming,
    (SELECT COUNT(*) FROM evenements WHERE date_evenement >= CURDATE() AND date_evenement <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)) as this_week,
    (SELECT COUNT(*) FROM event_inscriptions) as total_participants,
    (SELECT COUNT(*) FROM evenements WHERE date_evenement < CURDATE()) as past_events";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? ($_SESSION['user_name'] ?? 'Utilisateur') : '';
$user_id = $is_logged_in ? $_SESSION['user_id'] : 0;

$cart_count = 0;
if ($is_logged_in) {
    $count_sql = "SELECT COUNT(*) as count FROM panier WHERE utilisateur_id = " . $user_id;
    $count_res = $conn->query($count_sql);
    if ($count_res) {
        $cart_count = $count_res->fetch_assoc()['count'];
    }
}

if (isset($_POST['register_event']) && $is_logged_in) {
    $event_id = (int)$_POST['event_id'];
    
    $check = $conn->prepare("SELECT id FROM event_inscriptions WHERE evenement_id = ? AND user_id = ?");
    $check->bind_param("ii", $event_id, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows == 0) {
        $register = $conn->prepare("INSERT INTO event_inscriptions (evenement_id, user_id) VALUES (?, ?)");
        $register->bind_param("ii", $event_id, $user_id);
        $register->execute();
        $register->close();
    }
    $check->close();
    
    header("Location: evenements.php?registered=1");
    exit();
}

$user_registrations = [];
if ($is_logged_in) {
    $reg_query = $conn->prepare("SELECT evenement_id FROM event_inscriptions WHERE user_id = ?");
    $reg_query->bind_param("i", $user_id);
    $reg_query->execute();
    $reg_result = $reg_query->get_result();
    while ($row = $reg_result->fetch_assoc()) {
        $user_registrations[] = $row['evenement_id'];
    }
    $reg_query->close();
}
?>
  
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements - 3edu+</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="evenements.css">
    <link rel="icon" href="./LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                <li><a href="evenements.php" class="active">Événements</a></li>
                <li><a href="about.php">À propos</a></li>
                <li><a href="inscription.php">Inscriptions</a></li>
            </ul>
        </nav>

        <div class="nav-actions">
            <div class="search-container">
                <input type="text" placeholder="Rechercher des événements..." class="search-input">
                <button class="search-btn" title="Rechercher">
                    <i class="fas fa-search"></i>
                </button>
            </div>
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

    <?php if (isset($_GET['registered'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; text-align: center; border-bottom: 1px solid #c3e6cb;">
            <i class="fas fa-check-circle"></i> Vous êtes inscrit à l'événement avec succès!
        </div>
    <?php endif; ?>
   
    <section class="about-hero">
        <div class="about-hero-content">
            <h6>Événements & Webinaires</h6>
            <h1>Rejoignez nos événements</h1>
            <p class="hero-subtitle">
                Participez à nos webinaires, ateliers et conférences pour enrichir vos compétences
                <br>et rencontrer notre communauté.
            </p>
            <div>
                <a href="#events" class="btnArticle">Voir tous les événements <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <section class="filter-stats">
        <div class="container">
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">Tout</button>
                <button class="filter-tab" data-filter="online">En ligne</button>
                <button class="filter-tab" data-filter="in-person">Présentiel</button>
            </div>
           
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['upcoming'] ?? 0; ?></div>
                    <div class="stat-label">Événements à venir</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['this_week'] ?? 0; ?></div>
                    <div class="stat-label">Cette semaine</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_participants'] ?? 0; ?>+</div>
                    <div class="stat-label">Participants inscrits</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['past_events'] ?? 0; ?></div>
                    <div class="stat-label">Événements passés</div>
                </div>
            </div>
        </div>
    </section>

    <section class="events-section" id="events">
        <div class="container">
            <div class="events-grid" id="eventsGrid">
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-card" data-type="<?php echo $event['type_evenement']; ?>">
                            <div class="event-tag <?php echo $event['type_evenement']; ?>">
                                <?php echo $event['type_evenement'] === 'online' ? 'En ligne' : 'Présentiel'; ?>
                            </div>
                            <div class="event-price <?php echo $event['prix'] > 0 ? 'paid' : 'free'; ?>">
                                <?php echo $event['prix'] > 0 ? $event['prix'] . '€' : 'Gratuit'; ?>
                            </div>
                            <div class="event-category"><?php echo htmlspecialchars($event['categorie'] ?? 'Général'); ?></div>
                            <h3 class="event-title"><?php echo htmlspecialchars($event['titre']); ?></h3>
                            <p class="event-instructor">Par <?php echo htmlspecialchars($event['instructeur'] ?? 'Instructeur'); ?></p>
                            <p class="event-description"><?php echo htmlspecialchars($event['description'] ?? ''); ?></p>
                            <div class="event-details">
                                <div class="event-detail">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('d F Y', strtotime($event['date_evenement'])); ?></span>
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo date('H:i', strtotime($event['heure_debut'])); ?> - <?php echo date('H:i', strtotime($event['heure_fin'])); ?></span>
                                </div>
                                <div class="event-detail">
                                    <i class="fas <?php echo $event['type_evenement'] === 'online' ? 'fa-globe' : 'fa-map-marker-alt'; ?>"></i>
                                    <span><?php echo htmlspecialchars($event['lieu'] ?? 'Non défini'); ?></span>
                                </div>
                                <div class="event-detail">
                                    <i class="fas fa-users"></i>
                                    <span><?php echo $event['participants_inscrits']; ?>/<?php echo $event['max_participants']; ?> inscrits</span>
                                </div>
                            </div>
                            <?php if ($is_logged_in): ?>
                                <?php if (in_array($event['evenement_id'], $user_registrations)): ?>
                                    <button class="btn-register registered" disabled>
                                        <i class="fas fa-check"></i> Inscrit
                                    </button>
                                <?php elseif ($event['participants_inscrits'] >= $event['max_participants']): ?>
                                    <button class="btn-register full" disabled>
                                        Complet
                                    </button>
                                <?php else: ?>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="event_id" value="<?php echo $event['evenement_id']; ?>">
                                        <button type="submit" name="register_event" class="btn-register">
                                            S'inscrire
                                            <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="signup.php" class="btn-register">
                                    Connectez-vous pour s'inscrire
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 60px;">
                        <i class="fas fa-calendar-times" style="font-size: 60px; color: #ccc; margin-bottom: 20px;"></i>
                        <h3>Aucun événement à venir</h3>
                        <p style="color: #666;">Revenez bientôt pour découvrir nos prochains événements!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Organisez votre événement avec nous</h2>
            <p class="cta-description">Vous êtes un expert dans votre domaine ? Proposez un webinaire ou un atelier pour partager vos connaissances.</p>
            <a href="signup.php" class="btn-cta">Proposer un événement</a>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="./LogoEdu.png" alt="3edu+ Logo" width="150" height="100">
                    <p>Votre partenaire de formation professionnelle pour développer vos compétences.</p>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterTabs = document.querySelectorAll('.filter-tab');
            const eventCards = document.querySelectorAll('.event-card');

            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    filterTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    const filter = this.dataset.filter;
                    
                    eventCards.forEach(card => {
                        if (filter === 'all' || card.dataset.type === filter) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

          
            const userBtn = document.querySelector('.user-btn');
            const userMenu = document.querySelector('.user-menu');
            if (userBtn && userMenu) {
                userBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('open');
                });
                document.addEventListener('click', () => userMenu.classList.remove('open'));
            }
        });
    </script>
</body>
</html>
