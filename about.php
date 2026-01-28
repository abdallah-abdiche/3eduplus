<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? ($_SESSION['user_name'] ?? 'Utilisateur') : '';

// Fetch cart items only if logged in
$cart_items = [];
$total = 0;

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    
    $cart_query = "SELECT p.formation_id, p.prix_unitaire, f.titre, f.prix as prix_courant
                   FROM panier p
                   JOIN formations f ON p.formation_id = f.formation_id
                   WHERE p.utilisateur_id = ?
                   ORDER BY p.date_ajout DESC";
    $cart_stmt = $conn->prepare($cart_query);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    
    while ($item = $cart_result->fetch_assoc()) {
        $cart_items[] = $item;
        $total += $item['prix_unitaire'];
    }
    $cart_stmt->close();
}

// Handle remove from cart
if (isset($_POST['remove_item'])) {
    $item_id = (int)$_POST['item_id'];
    $delete_stmt = $conn->prepare("DELETE FROM panier WHERE id = ? AND utilisateur_id = ?");
    $delete_stmt->bind_param("ii", $item_id, $user['id']);
    $delete_stmt->execute();
    $delete_stmt->close();
    header('Location: cart.php');
    exit();
}

// Handle checkout
if (isset($_POST['checkout'])) {
    if (count($cart_items) > 0) {
        $_SESSION['checkout_total'] = $total;
        $_SESSION['checkout_items'] = $cart_items;
        header('Location: payment.php');
        exit();
    }
}

// Fetch team members
$team_result = $conn->query("SELECT * FROM equipe ORDER BY ordre ASC");
$team_members = $team_result->fetch_all(MYSQLI_ASSOC);

// Fetch FAQs
$faqs_result = $conn->query("SELECT * FROM faqs ORDER BY ordre ASC");
$faqs_list = $faqs_result->fetch_all(MYSQLI_ASSOC);

// Fetch Blog posts
$blog_result = $conn->query("SELECT * FROM blog_posts ORDER BY date_publication DESC LIMIT 3");
$blog_posts_list = $blog_result->fetch_all(MYSQLI_ASSOC);

// Fetch Upcoming Events
$events_result = $conn->query("SELECT * FROM evenements ORDER BY date_evenement ASC LIMIT 2");
$upcoming_events = $events_result->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos de 3edu+ - Centre de Formation Professionnelle</title>
    <link rel="stylesheet" href="style.css">
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
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="about.php" class="active">À propos</a></li>
                <li><a href="inscription.php">Inscriptions</a></li>
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
                <span class="cart-count"><?php echo count($cart_items); ?></span>
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

    
    <section class="about-hero">
        <div class="about-hero-content">
            <h1>À propos de 3edu+</h1>
            <p class="hero-subtitle">Depuis 2015, nous accompagnons des milliers de professionnels dans leur
                développement de compétences et leur transformation digitale</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">10,000+</span>
                    <span class="stat-label">Étudiants formés</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">100+</span>
                    <span class="stat-label">Formations</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">50+</span>
                    <span class="stat-label">Formateurs experts</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">95%</span>
                    <span class="stat-label">Satisfaction</span>
                </div>
            </div>
        </div>
    </section>

   
    <section class="mission-section">
        <div class="container">
            <h2>Notre Mission</h2>
            <p class="mission-text">Rendre la formation professionnelle accessible à tous et accompagner chacun dans son
                développement de compétences pour réussir dans un monde en constante évolution.</p>
        </div>
    </section>

    <section class="values-section">
        <div class="container">
            <h2>Nos Valeurs</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <h3>Excellence</h3>
                    <p>Des formations de haute qualité dispensées par des experts reconnus dans leur domaine</p>
                </div>

                <div class="value-card">
                    <div class="value-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3>Collaboration</h3>
                    <p>Un environnement d'apprentissage collaboratif qui favorise l'échange et le partage</p>
                </div>

                <div class="value-card">
                    <div class="value-icon">
                        <i class="fa-solid fa-medal"></i>
                    </div>
                    <h3>Innovation</h3>
                    <p>Des méthodes pédagogiques innovantes et des technologies de pointe</p>
                </div>

                <div class="value-card">
                    <div class="value-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3>Progression</h3>
                    <p>Un accompagnement personnalisé pour mesurer et valoriser vos progrès</p>
                </div>
            </div>
        </div>
    </section>

   
    <section class="team-section">
        <div class="container">
            <h2>Notre Équipe</h2>
            <p class="team-subtitle">Des experts passionnés à votre service</p>

            <div class="team-grid">
                <?php foreach ($team_members as $member): ?>
                    <div class="team-member">
                        <div class="member-photo">
                            <img src="<?php echo htmlspecialchars($member['image_url']); ?>" alt="<?php echo htmlspecialchars($member['nom']); ?>">
                        </div>
                        <h3><?php echo htmlspecialchars($member['nom']); ?></h3>
                        <p class="member-role"><?php echo htmlspecialchars($member['role']); ?></p>
                        <p class="member-bio"><?php echo htmlspecialchars($member['bio']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

  
    <section class="faq-blog-section">
        <div class="container">

            <div class="faq-blog-tabs">
                <button class="tab-btn active">
                    <i class="fas fa-question-circle"></i>
                    FAQ
                </button>
                <button class="tab-btn">
                    <i class="fas fa-blog"></i>
                    Blog
                </button>
            </div>

           
            <div class="faq-content active">
                <h2>Questions Fréquentes</h2>
                <p class="section-subtitle">Trouvez les réponses à vos questions les plus courantes</p>

                <div class="faq-grid">
                    <?php foreach ($faqs_list as $faq): ?>
                        <div class="faq-item">
                            <div class="faq-question">
                                <h3><?php echo htmlspecialchars($faq['question']); ?></h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p><?php echo htmlspecialchars($faq['reponse']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

          
            <div class="blog-content">
                <h2>Notre Blog</h2>
                <p class="section-subtitle">Découvrez nos derniers articles et conseils</p>

                <div class="blog-grid">
                    <?php foreach ($blog_posts_list as $post): ?>
                        <article class="blog-card">
                            <div class="blog-image">
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['titre']); ?>">
                                <div class="blog-category"><?php echo htmlspecialchars($post['categorie']); ?></div>
                            </div>
                            <div class="blog-content-card">
                                <div class="blog-meta">
                                    <span class="blog-date"><?php echo date('d M Y', strtotime($post['date_publication'])); ?></span>
                                    <span class="blog-author">Par <?php echo htmlspecialchars($post['auteur']); ?></span>
                                </div>
                                <h3><?php echo htmlspecialchars($post['titre']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($post['contenu'], 0, 100)); ?>...</p>
                                <a href="#" class="blog-link">Lire la suite <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section (from database) -->
    <?php if (!empty($upcoming_events)): ?>
    <section class="blog-section" style="padding: 60px 20px; background: #f8fafc;">
        <h2 style="text-align: center; margin-bottom: 40px;">Prochains Événements & Actualités</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto;">
            <?php foreach ($upcoming_events as $event): ?>
            <article style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <?php if ($event['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($event['image_url']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 100%; height: 200px; background: #3b82f6; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                <?php endif; ?>
                <div style="padding: 20px;">
                    <span style="color: #3b82f6; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;"><?php echo htmlspecialchars($event['categorie']); ?></span>
                    <h3 style="margin: 10px 0;"><?php echo htmlspecialchars($event['titre']); ?></h3>
                    <p style="color: #64748b; font-size: 0.9rem;"><?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>...</p>
                    <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.85rem; color: #1e3a8a;"><i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?></span>
                        <a href="evenements.php" style="color: #1e3a8a; font-weight: 600; text-decoration: none;">Voir détails →</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="cta-section">
        <div class="container">
            <h2>Prêt à transformer votre avenir ?</h2>
            <p>Rejoignez 3edu+ dès aujourd'hui et profitez de nos formations d'excellence.</p>
            <div class="cta-buttons">
                <a href="signup.php" class="cta-btn">Créer un compte</a>
                <a href="contact.php" class="cta-btn secondary">Nous contacter</a>
            </div>
        </div>
    </section>

    <section class="contact-section">
        <div class="container">
            <h2>Une question ? Contactez-nous</h2>
            <p class="contact-subtitle">Notre équipe est là pour vous aider</p>

            <div class="contact-methods">
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email</h3>
                    <p>contact@3eduplus.fr</p>
                    <a href="mailto:contact@3eduplus.fr" class="contact-btn">Envoyer un email</a>
                </div>

                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Chat en ligne</h3>
                    <p>Disponible 9h-18h</p>
                    <button class="contact-btn">Démarrer le chat</button>
                </div>

                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Prendre RDV</h3>
                    <p>Consultation gratuite</p>
                    <button class="contact-btn">Réserver un créneau</button>
                </div>
            </div>
        </div>
    </section>

   
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="./LogoEdu.png" alt="3edu+ Logo" width="150" height="100">
                    <p>Votre partenaire de formation professionnelle pour développer vos compétences et accélérer votre
                        carrière.</p>
                </div>
            </div>

            <div class="footer-section">
                <h3>Liens rapides</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="formation.php">Nos formations</a></li>
                    <li><a href="evenements.php">Événements</a></li>
                    <li><a href="about.php">À propos</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Contact</h3>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>contact@3eduplus.fr</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+33 1 23 45 67 89</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>123 Avenue de la Formation<br>75001 Paris, France</span>
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
        // FAQ Toggle functionality
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', function() {
                const faqItem = this.parentElement;
                faqItem.classList.toggle('active');
            });
        });

        // Tab switching functionality
        document.querySelectorAll('.tab-btn').forEach((btn, index) => {
            btn.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.faq-content, .blog-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Show corresponding content
                if (index === 0) {
                    document.querySelector('.faq-content').classList.add('active');
                } else {
                    document.querySelector('.blog-content').classList.add('active');
                }
            });
        });

        // User menu toggle
        document.addEventListener('DOMContentLoaded', function() {
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