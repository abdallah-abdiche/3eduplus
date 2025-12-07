<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

checkAuth();
$user = getCurrentUser();

// Fetch cart items
$cart_query = "SELECT p.formation_id, p.prix_unitaire, f.titre, f.prix as prix_courant
               FROM panier p
               JOIN formations f ON p.formation_id = f.formation_id
               WHERE p.formation_id = ?
               ORDER BY p.date_ajout DESC";
$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bind_param("i", $user['id']);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_items = [];
$total = 0;

while ($item = $cart_result->fetch_assoc()) {
    $cart_items[] = $item;
    $total += $item['prix_unitaire'];
}
$cart_stmt->close();

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
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos de 3edu+ - Centre de Formation Professionnelle</title>
    <link rel="stylesheet" href="about.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="./LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="user-btn.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  
    <header class="header-nav">
        <div class="logocontainer">
            <a href="#"><img src="./LogoEdu.png" width="150" height="100" alt="3edu+ Logo"></a>
        </div>

        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="index.php " >Accueil</a></li>
                <li><a href="formation.php">Formations</a></li>
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="about.php" class="active">À propos</a></li>
            </ul>
        </nav>

        <div class="nav-actions">
            <div class="search-container">
                <input type="text" placeholder="Rechercher des formations..." class="search-input">
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
            <a href="cart.html" class="cart-icon" title="Panier">
                <img src="https://cdn-icons-png.flaticon.com/128/2838/2838895.png" width="30" height="30" alt="Panier">
                <span class="cart-count">0</span>
            </a>
            <?php if ($is_logged_in): ?>
                <div class="user-menu">
                    <button class="user-btn">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($user_name); ?>
                    </button>
                    <div class="user-dropdown">
                        <a href="dashboard/apprenant/index.php">Mon Tableau de bord</a>
                        <a href="logout.php">Déconnexion</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="login-btn">Connexion</a>
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
                <div class="team-member">
                    <div class="member-photo">
                        <img src="https://i.pinimg.com/736x/ae/bd/4b/aebd4b907d4092ec8b6ecf3b6341aa7e.jpg"
                            alt="walid">
                    </div>
                    <h3>Abdiche abdallah</h3>
                    <p class="member-role">Directeur Pédagogique</p>
                    <p class="member-bio">15 ans d'expérience dans la formation professionnelle et l'innovation
                        pédagogique</p>
                </div>

                <div class="team-member">
                    <div class="member-photo">
                        <img src="https://i.pinimg.com/1200x/ab/9c/6c/ab9c6cbd3d2b38189643d08ee6e1834b.jpg"
                            alt="Tom holland">
                    </div>
                    <h3>Abdiche abdallah</h3>
                    <p class="member-role">Responsable Formation</p>
                    <p class="member-bio">Spécialiste en développement des compétences et accompagnement personnalisé
                    </p>
                </div>

                <div class="team-member">
                    <div class="member-photo">
                        <img src="https://i.pinimg.com/736x/a4/5a/1d/a45a1d0cd90a852b9c53493364c6f8cd.jpg"
                            alt="abdou">
                    </div>
                    <h3>Abdiche abdallah</h3>
                    <p class="member-role">Expert Technique</p>
                    <p class="member-bio">Formateur certifié avec une expertise reconnue en technologies digitales</p>
                </div>
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
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Comment s'inscrire à une formation ?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>L'inscription est simple : parcourez notre catalogue de formations, sélectionnez celle
                                qui vous intéresse, et suivez le processus d'inscription en ligne. Vous recevrez une
                                confirmation par email.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Les formations sont-elles certifiantes ?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Oui, toutes nos formations délivrent une certification reconnue par l'industrie. Nos
                                certifications sont valorisées par les employeurs et peuvent booster votre carrière.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Puis-je suivre une formation à mon rythme ?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Absolument ! Nos formations en ligne vous permettent d'apprendre à votre rythme, 24h/24.
                                Vous avez accès au contenu pendant toute la durée de la formation.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Y a-t-il un support technique disponible ?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Oui, notre équipe de support est disponible du lundi au vendredi de 9h à 18h. Vous pouvez
                                nous contacter par email, chat en ligne ou téléphone.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Proposez-vous des formations en entreprise ?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Oui, nous proposons des formations sur mesure pour les entreprises. Contactez-nous pour
                                discuter de vos besoins spécifiques et obtenir un devis personnalisé.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Quels sont les modes de paiement acceptés ?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Nous acceptons les cartes bancaires, virements, chèques et paiements en plusieurs fois.
                                Les formations peuvent également être financées via votre CPF.</p>
                        </div>
                    </div>
                </div>
            </div>

          
            <div class="blog-content">
                <h2>Notre Blog</h2>
                <p class="section-subtitle">Découvrez nos derniers articles et conseils</p>

                <div class="blog-grid">
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop"
                                alt="Formation Digital">
                            <div class="blog-category">Formation</div>
                        </div>
                        <div class="blog-content-card">
                            <div class="blog-meta">
                                <span class="blog-date">15 Janvier 2025</span>
                                <span class="blog-author">Par Marie Claire</span>
                            </div>
                            <h3>Les 5 compétences digitales essentielles en 2025</h3>
                            <p>Découvrez les compétences digitales qui seront les plus demandées cette année et comment
                                les développer efficacement.</p>
                            <a href="#" class="blog-link">Lire la suite <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>

                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400&h=250&fit=crop"
                                alt="Carrière">
                            <div class="blog-category">Carrière</div>
                        </div>
                        <div class="blog-content-card">
                            <div class="blog-meta">
                                <span class="blog-date">12 Janvier 2025</span>
                                <span class="blog-author">Par Jean Dupont</span>
                            </div>
                            <h3>Comment réussir sa reconversion professionnelle</h3>
                            <p>Nos conseils pratiques pour réussir votre changement de carrière et développer de
                                nouvelles compétences.</p>
                            <a href="#" class="blog-link">Lire la suite <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>

                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=400&h=250&fit=crop"
                                alt="Technologie">
                            <div class="blog-category">Technologie</div>
                        </div>
                        <div class="blog-content-card">
                            <div class="blog-meta">
                                <span class="blog-date">10 Janvier 2025</span>
                                <span class="blog-author">Par Pierre Martin</span>
                            </div>
                            <h3>L'IA dans la formation : révolution ou évolution ?</h3>
                            <p>Exploration de l'impact de l'intelligence artificielle sur les méthodes d'apprentissage
                                modernes.</p>
                            <a href="#" class="blog-link">Lire la suite <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                </div>
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
                    <li><a href="index.html">Accueil</a></li>
                    <li><a href="#">Nos formations</a></li>
                    <li><a href="#">Événements</a></li>
                    <li><a href="about.html">À propos</a></li>
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

</body>
</html>