<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? ($_SESSION['user_name'] ?? 'Utilisateur') : '';
$user_id = $is_logged_in ? $_SESSION['user_id'] : 0;

// Get cart count from database
$cart_count = 0;
if ($is_logged_in) {
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

// Get statistics from database
$stats = [];

// Total students (users with Apprenant role)
$students_query = "SELECT COUNT(*) as total FROM utilisateurs u 
                   LEFT JOIN roles r ON u.role_id = r.role_id 
                   WHERE r.nom_role = 'Apprenant' OR r.nom_role IS NULL";
$result = $conn->query($students_query);
$stats['students'] = $result->fetch_assoc()['total'] ?? 0;

// Total formations
$formations_query = "SELECT COUNT(*) as total FROM formations";
$result = $conn->query($formations_query);
$stats['formations'] = $result->fetch_assoc()['total'] ?? 0;

// Total instructors (users with Pédagogique role)
$instructors_query = "SELECT COUNT(*) as total FROM utilisateurs u 
                      LEFT JOIN roles r ON u.role_id = r.role_id 
                      WHERE r.nom_role = 'Pédagogique'";
$result = $conn->query($instructors_query);
$stats['instructors'] = $result->fetch_assoc()['total'] ?? 0;

// Get category counts
$categories_query = "SELECT categorie, COUNT(*) as count FROM formations 
                     WHERE categorie IS NOT NULL 
                     GROUP BY categorie 
                     ORDER BY count DESC 
                     LIMIT 4";
$categories_result = $conn->query($categories_query);
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

// Get popular formations (top 3)
$popular_query = "SELECT f.*, 
                  (SELECT COUNT(*) FROM inscriptions WHERE formation_id = f.formation_id) as enrollments
                  FROM formations f 
                  ORDER BY enrollments DESC, f.date_creation DESC 
                  LIMIT 3";
$popular_result = $conn->query($popular_query);
$popular_formations = $popular_result->fetch_all(MYSQLI_ASSOC);

// Get testimonials from database
$testimonials_query = "SELECT t.*, u.Nom_Complet FROM temoignages t 
                       JOIN utilisateurs u ON t.user_id = u.user_id 
                       ORDER BY t.date DESC LIMIT 3";
$testimonials_result = $conn->query($testimonials_query);
$testimonials = $testimonials_result ? $testimonials_result->fetch_all(MYSQLI_ASSOC) : [];

// Handle add to cart via AJAX
if (isset($_POST['add_to_cart']) && $is_logged_in) {
    $formation_id = (int)$_POST['formation_id'];
    
    // Get price of the formation
    $price_query = "SELECT prix FROM formations WHERE formation_id = ?";
    $price_stmt = $conn->prepare($price_query);
    $price_stmt->bind_param("i", $formation_id);
    $price_stmt->execute();
    $price_result = $price_stmt->get_result();
    $price = $price_result->fetch_assoc()['prix'] ?? 0;
    $price_stmt->close();
    
    // Insert into panier
    $insert_sql = "INSERT IGNORE INTO panier (utilisateur_id, formation_id, prix_unitaire) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iid", $user_id, $formation_id, $price);
    $stmt->execute();
    $stmt->close();
    
    // Get new count
    $count_sql = "SELECT COUNT(*) as count FROM panier WHERE utilisateur_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $new_count = $count_stmt->get_result()->fetch_assoc()['count'];
    $count_stmt->close();
    
    echo json_encode(['success' => true, 'cart_count' => $new_count]);
    exit();
}

// Category icons mapping
$category_icons = [
    'Développement Web' => 'fa-code',
    'Design & UX' => 'fa-palette',
    'Design' => 'fa-palette',
    'Marketing Digital' => 'fa-chart-line',
    'Marketing' => 'fa-chart-line',
    'Data Science' => 'fa-database',
    'Cybersécurité' => 'fa-shield-alt',
    'Applications Mobile' => 'fa-mobile-alt',
    'default' => 'fa-book'
];

$category_colors = [
    'Développement Web' => '#0066ff',
    'Design & UX' => '#b800ff',
    'Design' => '#b800ff',
    'Marketing Digital' => '#00b300',
    'Marketing' => '#00b300',
    'Data Science' => '#ff5500',
    'Cybersécurité' => '#dc3545',
    'Applications Mobile' => '#17a2b8',
    'default' => '#6c757d'
];
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3edu+ - Centre de Formation Professionnelle</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="./LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <!-- Nav -->
    <header class="header-nav">
        <div class="logocontainer">
            <a href="index.php"><img src="./LogoEdu.png" width="150" height="100" alt="3edu+ Logo"></a>
        </div>

        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="index.php" class="active">Accueil</a></li>
                <li><a href="formation.php">Formations</a></li>
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="about.php">À propos</a></li>
                <li><a href="inscription.php">Inscriptions</a></li>
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
                        <a href="logout.php">Se déconnecter</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="signup.php" class="signup-btn">Connexion</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px auto; max-width: 1200px; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center; font-weight: bold;">
            <?php 
                echo htmlspecialchars($_SESSION['error_message']); 
                unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>


    
    <article class="article-lwla">
        <h1 class="h1_article-lwla">
            Développez vos compétences avec
            <img src="./LogoEdu.png" alt="3edu+ Logo" width="270" height="215">
        </h1>
        <p>
            Accédez à plus de <?php echo $stats['formations']; ?> formations professionnelles dans les domaines du digital, du design et de la
            technologie.
            <br>Formez-vous à votre rythme avec nos experts.
        </p>

        <div>
            <a href="formation.php" class="btnArticle">Découvrir nos formations <i class="fas fa-arrow-right"></i></a>
            <a href="about.php" class="btnArticle">En savoir plus</a>
        </div>

        <div class="stats">
            <p><?php echo number_format($stats['students']); ?>+ <br> Étudiants</p>
            <p><?php echo $stats['formations']; ?>+ <br> Formations</p>
            <p><?php echo $stats['instructors']; ?>+ <br> Formateurs</p>
        </div>
    </article>

    
    <section>
        <h2>Explorez nos catégories</h2>
        <p>
            Choisissez parmi nos différents domaines de formation et développez les compétences dont vous avez besoin
            pour réussir.
        </p>

        <div class="categories-grid">
            <?php if (count($categories) > 0): ?>
                <?php foreach ($categories as $cat): 
                    $icon = $category_icons[$cat['categorie']] ?? $category_icons['default'];
                    $color = $category_colors[$cat['categorie']] ?? $category_colors['default'];
                ?>
                    <div class="category-card">
                        <i class="fa-solid <?php echo $icon; ?>" style="color:<?php echo $color; ?>;"></i>
                        <h3><?php echo htmlspecialchars($cat['categorie']); ?></h3>
                        <p><?php echo $cat['count']; ?> formations</p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="category-card">
                    <i class="fa-solid fa-code" style="color:#0066ff;"></i>
                    <h3>Développement Web</h3>
                    <p>Bientôt disponible</p>
                </div>
                <div class="category-card">
                    <i class="fa-solid fa-palette" style="color:#b800ff;"></i>
                    <h3>Design & UI UX</h3>
                    <p>Bientôt disponible</p>
                </div>
                <div class="category-card">
                    <i class="fa-solid fa-chart-line" style="color:#00b300;"></i>
                    <h3>Marketing Digital</h3>
                    <p>Bientôt disponible</p>
                </div>
                <div class="category-card">
                    <i class="fa-solid fa-database" style="color:#ff5500;"></i>
                    <h3>Data Science</h3>
                    <p>Bientôt disponible</p>
                </div>
            <?php endif; ?>
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
            <a href="formation.php">Voir tout <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="courses-grid">
            <?php if (count($popular_formations) > 0): ?>
                <?php foreach ($popular_formations as $index => $formation): ?>
                    <div class="course-card">
                        <div class="course-image">
                            <?php if (!empty($formation['formationImageUrl'])): ?>
                                <img src="<?php echo htmlspecialchars($formation['formationImageUrl']); ?>" alt="<?php echo htmlspecialchars($formation['titre']); ?>">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=400&h=250&fit=crop" alt="Formation">
                            <?php endif; ?>
                            <div class="course-overlay">
                                <span class="course-badge"><?php echo $index == 0 ? 'Populaire' : ($index == 1 ? 'Nouveau' : 'Recommandé'); ?></span>
                            </div>
                        </div>
                        <div class="course-card-header">
                            <div class="course-rating"><span class="stars">★★★★★</span><span>4.8</span></div>
                            <h3 class="course-title"><?php echo htmlspecialchars(substr($formation['titre'], 0, 50)); ?></h3>
                            <p class="course-instructor"><?php echo htmlspecialchars($formation['categorie'] ?? 'Formation'); ?></p>
                            <div class="course-stats">
                                <span><?php echo $formation['enrollments']; ?> étudiants</span>
                                <span><?php echo $formation['duree'] ?? '8'; ?> semaines</span>
                            </div>
                        </div>
                        <div class="course-card-footer">
                            <span class="course-price"><?php echo number_format($formation['prix'], 0); ?>€</span>
                            <button class="course-btn" onclick="addToCart(<?php echo $formation['formation_id']; ?>)">Ajouter au panier</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <p style="color: #666;">Aucune formation disponible pour le moment.</p>
                    <a href="formation.php" class="btnArticle" style="margin-top: 20px;">Voir toutes les formations</a>
                </div>
            <?php endif; ?>
        </div>

  
        <section class="testimonials-section">
            <h2>Ce que disent nos étudiants</h2>
            <div class="testimonials-grid">
                <?php if (count($testimonials) > 0): ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="testimonial-card">
                            <div class="testimonial-rating">
                                <div class="stars"><?php echo str_repeat('★', $testimonial['note'] ?? 5); ?></div>
                                <span class="rating-number"><?php echo number_format($testimonial['note'] ?? 5, 1); ?></span>
                            </div>
                            <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['contenu']); ?>"</p>
                            <div class="testimonial-author"><?php echo htmlspecialchars($testimonial['Nom_Complet']); ?></div>
                            <div class="testimonial-role">Étudiant</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
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
                <?php endif; ?>
            </div>
        </section>

       
        <section class="cta-section">
            <h2>Prêt à commencer votre formation ?</h2>
            <p>Rejoignez des milliers d'étudiants qui ont déjà transformé leur carrière avec 3edu+</p>
            <div class="cta-buttons">
                <a href="formation.php" class="cta-btn">Parcourir les formations</a>
                <a href="evenements.php" class="cta-btn">Voir les événements</a>
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
                </ul>
            </div>

            <div class="footer-section">
                <h3>Contact</h3>
                <div class="contact-info">
                    <div class="contact-item"><i class="fas fa-envelope"></i> <span>contact@3eduplus.fr</span></div>
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
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; 2025 3edu+ Centre de Formation. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        // Add to cart functionality
        function addToCart(formationId) {
            <?php if (!$is_logged_in): ?>
                alert('Veuillez vous connecter pour ajouter une formation au panier.');
                window.location.href = 'signup.php';
                return;
            <?php endif; ?>

            const formData = new FormData();
            formData.append('add_to_cart', '1');
            formData.append('formation_id', formationId);

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.cart-count').textContent = data.cart_count;
                    alert('Formation ajoutée au panier!');
                }
            })
            .catch(error => console.error('Error:', error));
        }

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