<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Fetch all formations from database
$formations = [];
$query = "SELECT formation_id, titre, categorie, prix, niveau, duree, date_creation, formationImageUrl FROM Formations ORDER BY date_creation DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $formations[] = $row;
    }
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? ($_SESSION['user_name'] ?? 'Utilisateur') : '';

// Get cart count from session
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Handle add to cart via AJAX
if (isset($_POST['add_to_cart'])) {
    $formation_id = (int)$_POST['formation_id'];
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (!in_array($formation_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $formation_id;
    }
    
    echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
    exit();
}

?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3edu+</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="./LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <!--  Nav -->
    <header class="header-nav">
        <div class="logocontainer">
            <a href="#"><img src="./LogoEdu.png" width="150" height="100" alt="3edu+ Logo"></a>
        </div>

        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="index.php" class="active">Accueil</a></li>
                <li><a href="formation.php">Formations</a></li>
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="about.php">À propos</a></li>
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
            <a href="cart.php" class="cart-icon" title="Panier">
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
                        <a href="<?php echo getDashboardUrl(); ?>">Mon Tableau de bord</a>
                        <a href="logout.php">Se deconnecter</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="signup.php" class="signup-btn">Connexion  </a>
            <?php endif; ?>
        </div>


    </header>

    
    <article class="article-lwla">
        <h1 class="h1_article-lwla">
            Développez vos compétences avec
            <img src="./LogoEdu.png" alt="3edu+ Logo" width="270" height="215">
        </h1>
        <p>
            Accédez à plus de 100 formations professionnelles dans les domaines du digital, du design et de la
            technologie.
            <br>Formez-vous à votre rythme avec nos experts.
        </p>

        <div>
            <a href="#" class="btnArticle">Découvrir nos formations <i class="fas fa-arrow-right"></i></a>
            <a href="#" class="btnArticle">En savoir plus</a>
        </div>

        <div class="stats">
            <p>10,000+ <br> Étudiants</p>
            <p>100+ <br> Formations</p>
            <p>50+ <br> Formateurs</p>
        </div>
    </article>

    
    <section>
        <h2>Explorez nos catégories</h2>
        <p>
            Choisissez parmi nos différents domaines de formation et développez les compétences dont vous avez besoin
            pour réussir.
        </p>

        <div class="categories-grid">
            <div class="category-card">
                <i class="fa-solid fa-code" style="color:#0066ff;"></i>
                <h3>Développement Web</h3>
                <p>45 formations</p>
            </div>

            <div class="category-card">
                <i class="fa-solid fa-palette" style="color:#b800ff;"></i>
                <h3>Design & UI UX</h3>
                <p>32 formations</p>
            </div>

            <div class="category-card">
                <i class="fa-solid fa-chart-line" style="color:#00b300;"></i>
                <h3>Marketing Digital</h3>
                <p>28 formations</p>
            </div>

            <div class="category-card">
                <i class="fa-solid fa-database" style="color:#ff5500;"></i>
                <h3>Data Science</h3>
                <p>24 formations</p>
            </div>
        </div>
    </section>

    <!-- Why Choose Section -->
    <section class="why-choose-section">
        <h2>Pourquoi choisir 3edu+ ?</h2>

        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-chalkboard-teacher"></i>
                <h4>Formateurs Experts</h4>
                <p>Apprenez auprès de professionnels expérimentés</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-award"></i>
                <h4>Certifications</h4>
                <p>Obtenez des certifications reconnues</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-laptop-house"></i>
                <h4>Flexibilité</h4>
                <p>Formations en ligne et en présentiel</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-headset"></i>
                <h4>Support Premium</h4>
                <p>Accompagnement personnalisé</p>
            </div>
        </div>
    </section>

    <section class="courses-section">
        <div class="courses-header">
            <div>
                <h2>Formations populaires</h2>
                <p>Les formations les plus suivies ce mois-ci</p>
            </div>
            <a href="#">Voir tout <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="courses-grid">
            <div class="course-card">
                <div class="course-image">
                    <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=400&h=250&fit=crop"
                        alt="Développement Web Full Stack">
                    <div class="course-overlay"><span class="course-badge">Populaire</span></div>
                </div>
                <div class="course-card-header">
                    <div class="course-rating"><span class="stars">★★★★★</span><span>4.8</span></div>
                    <h3 class="course-title">Développement Web Full Stack</h3>
                    <p class="course-instructor">Jean Dupont</p>
                    <div class="course-stats"><span>1234 étudiants</span><span>12 semaines</span></div>
                </div>
                <div class="course-card-footer">
                    <span class="course-price">599€</span>
                    <button class="course-btn">Ajouter au panier</button>
                </div>
            </div>

            <div class="course-card">
                <div class="course-image">
                    <img src="https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=400&h=250&fit=crop"
                        alt="Data Science & IA">
                    <div class="course-overlay"><span class="course-badge">Nouveau</span></div>
                </div>
                <div class="course-card-header">
                    <div class="course-rating"><span class="stars">★★★★★</span><span>4.9</span></div>
                    <h3 class="course-title">Data Science & IA</h3>
                    <p class="course-instructor">Sophie Martin</p>
                    <div class="course-stats"><span>856 étudiants</span><span>16 semaines</span></div>
                </div>
                <div class="course-card-footer">
                    <span class="course-price">799€</span>
                    <button class="course-btn">Ajouter au panier</button>
                </div>
            </div>

            <div class="course-card">
                <div class="course-image">
                    <img src="https://images.unsplash.com/photo-1558655146-d09347e92766?w=400&h=250&fit=crop"
                        alt="UX/UI Design Master Class">
                    <div class="course-overlay"><span class="course-badge">Populaire</span></div>
                </div>
                <div class="course-card-header">
                    <div class="course-rating"><span class="stars">★★★★★</span><span>4.8</span></div>
                    <h3 class="course-title">UX/UI Design Master Class</h3>
                    <p class="course-instructor">Marie Claire</p>
                    <div class="course-stats"><span>892 étudiants</span><span>10 semaines</span></div>
                </div>
                <div class="course-card-footer">
                    <span class="course-price">499€</span>
                    <button class="course-btn">Ajouter au panier</button>
                </div>
            </div>
        </div>

  
        <section class="testimonials-section">
            <h2>Ce que disent nos étudiants</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <div class="stars">★★★★★</div><span class="rating-number">5.0</span>
                    </div>
                    <p class="testimonial-text">"Excellente formation qui m'a permis de changer de carrière."</p>
                    <div class="testimonial-author">Marie Dubois</div>
                    <div class="testimonial-role">Développeuse Web</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <div class="stars">★★★★★</div><span class="rating-number">4.9</span>
                    </div>
                    <p class="testimonial-text">"Une expérience d'apprentissage exceptionnelle."</p>
                    <div class="testimonial-author">Pierre Martin</div>
                    <div class="testimonial-role">UX Designer</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <div class="stars">★★★★★</div><span class="rating-number">5.0</span>
                    </div>
                    <p class="testimonial-text">"Les cours sont très bien structurés et pratiques."</p>
                    <div class="testimonial-author">Sophie Laurent</div>
                    <div class="testimonial-role">Data Analyst</div>
                </div>
            </div>
        </section>

       
        <section class="cta-section">
            <h2>Prêt à commencer votre formation ?</h2>
            <p>Rejoignez des milliers d'étudiants qui ont déjà transformé leur carrière avec 3edu+</p>
            <div class="cta-buttons">
                <button class="cta-btn">Parcourir les formations</button>
                <button class="cta-btn">Voir les événements</button>
            </div>
        </section>
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
                    <li><a href="formation.php">Nos formations</a></li>
                    <li><a href="evenements.php">Événements</a></li>
                    <li><a href="about.php">À propos</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Blog</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Catégories</h3>
                <ul class="footer-links">
                    <li><a href="#">Développement Web</a></li>
                    <li><a href="#">Design & UX</a></li>
                    <li><a href="#">Marketing Digital</a></li>
                    <li><a href="#">Data Science</a></li>
                    <li><a href="#">Cybersécurité</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Contact</h3>
                <div class="contact-info">
                    <div class="contact-item"><i class="fas fa-map-marker-alt"></i> <span>123 Avenue de la
                            Formation<br>75001 Paris, France</span></div>
                    <div class="contact-item"><i class="fas fa-phone"></i> <span>+33 1 23 45 67 89</span></div>
                    <div class="contact-item"><i class="fas fa-envelope"></i> <span>contact@3eduplus.fr</span></div>
                </div>

                <div class="demo-access">
                    <h4>Accès démo</h4>
                    <div class="demo-buttons">
                        <button class="demo-btn admin">Admin</button>
                        <button class="demo-btn marketing">Marketing</button>
                    </div>
                </div>
            </div>

            <div class="footer-section">
                <h3>Suivez-nous</h3>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>

                <div class="newsletter">
                    <h4>Newsletter</h4>
                    <p>Recevez nos dernières actualités</p>
                    <div class="newsletter-form">
                        <input type="email" placeholder="Votre email" class="newsletter-input">
                        <button class="newsletter-btn">S'abonner</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; 2025 3edu+ Centre de Formation. Tous droits réservés.</p>
                <div class="footer-bottom-links">
                    <a href="#">Mentions légales</a>
                    <a href="#">Politique de confidentialité</a>
                    <a href="#">CGV</a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>