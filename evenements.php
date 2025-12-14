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
    <title>Événements</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="evenements.css">
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
                <li><a href="index.php" >Accueil</a></li>
                <li><a href="formation.php">Formations</a></li>
                <li><a href="evenements.php" class="active">Événements</a></li>
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
        <h6>Événements & Webinaires</h6>
        <h1>Rejoignez nos événements</h1>
        <p class="hero-subtitle">
          Participez à nos webinaires, ateliers et conférences pour enrichir vos compétences
          <br>et rencontrer notre communauté.
        </p>
        <div>
          <a href="#" class="btnArticle">Voir tous les événements <i class="fas fa-arrow-right"></i></a>
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
                  <div class="stat-number">6</div>
                  <div class="stat-label">Événements à venir</div>
              </div>
              <div class="stat-card">
                  <div class="stat-number">12</div>
                  <div class="stat-label">Cette semaine</div>
              </div>
              <div class="stat-card">
                  <div class="stat-number">850+</div>
                  <div class="stat-label">Participants inscrits</div>
              </div>
              <div class="stat-card">
                  <div class="stat-number">45</div>
                  <div class="stat-label">Événements passés</div>
              </div>
          </div>
      </div>
  </section>
  
  <section class="events-section">
      <div class="container">
          <div class="events-grid" id="eventsGrid">
              <!--1 -->
              <div class="event-card" data-type="online">
                  <div class="event-tag online">En ligne</div>
                  <div class="event-price free">Gratuit</div>
                  <div class="event-category">Développement Web</div>
                  <h3 class="event-title">Webinaire: Introduction au développement web moderne</h3>
                  <p class="event-instructor">Par Jean Dupont</p>
                  <p class="event-description">Découvrez les fondamentaux du développement web moderne avec React et TypeScript.</p>
                  <div class="event-details">
                      <div class="event-detail">
                          <i class="fas fa-calendar"></i>
                          <span>25 Janvier 2025</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-clock"></i>
                          <span>12:00 - 20:00</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-globe"></i>
                          <span>En ligne</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-users"></i>
                          <span>156/200 inscrits</span>
                      </div>
                  </div>
                  <button class="btn-register">
                      S'inscrire
                      <i class="fas fa-arrow-right"></i>
                  </button>
              </div>
           
              <div class="event-card" data-type="online">
                <div class="event-tag online">En ligne</div>
                <div class="event-price free">Gratuit</div>
                <div class="event-category">Développement Web</div>
                <h3 class="event-title">Webinaire: Introduction au développement web moderne</h3>
                <p class="event-instructor">Par Jean Dupont</p>
                <p class="event-description">Découvrez les fondamentaux du développement web moderne avec React et TypeScript.</p>
                <div class="event-details">
                    <div class="event-detail">
                        <i class="fas fa-calendar"></i>
                        <span>25 Janvier 2025</span>
                    </div>
                    <div class="event-detail">
                        <i class="fas fa-clock"></i>
                        <span>12:00 - 20:00</span>
                    </div>
                    <div class="event-detail">
                        <i class="fas fa-globe"></i>
                        <span>En ligne</span>
                    </div>
                    <div class="event-detail">
                        <i class="fas fa-users"></i>
                        <span>156/200 inscrits</span>
                    </div>
                </div>
                <button class="btn-register">
                    S'inscrire
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            
              <div class="event-card" data-type="online">
                <div class="event-tag online">En ligne</div>
                <div class="event-price paid">40£</div>
                <div class="event-category">Développement Web</div>
                <h3 class="event-title">Webinaire: Introduction au développement web moderne</h3>
                <p class="event-instructor">Par Jean Dupont</p>
                <p class="event-description">Découvrez les fondamentaux du développement web moderne avec React et TypeScript.</p>
                <div class="event-details">
                    <div class="event-detail">
                        <i class="fas fa-calendar"></i>
                        <span>25 Janvier 2025</span>
                    </div>
                    <div class="event-detail">
                        <i class="fas fa-clock"></i>
                        <span>12:00 - 20:00</span>
                    </div>
                    <div class="event-detail">
                        <i class="fas fa-globe"></i>
                        <span>En ligne</span>
                    </div>
                    <div class="event-detail">
                        <i class="fas fa-users"></i>
                        <span>156/200 inscrits</span>
                    </div>
                </div>
                <button class="btn-register">
                    S'inscrire
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            
              <div class="event-card" data-type="in-person">
                  <div class="event-tag in-person">Présentiel</div>
                  <div class="event-price paid">99€</div>
                  <div class="event-category">Data Science</div>
                  <h3 class="event-title">Workshop: Data Science avec Python</h3>
                  <p class="event-instructor">Par Sophie Bernard</p>
                  <p class="event-description">Journée complète dédiée à l'analyse de données avec Python et ses librairies.</p>
                  <div class="event-details">
                      <div class="event-detail">
                          <i class="fas fa-calendar"></i>
                          <span>5 Février 2025</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-clock"></i>
                          <span>09:00 - 17:00</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-map-marker-alt"></i>
                          <span>Lyon - Centre Jedu+</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-users"></i>
                          <span>4/15 inscrits</span>
                      </div>
                  </div>
                  <button class="btn-register">
                      S'inscrire
                      <i class="fas fa-arrow-right"></i>
                  </button>
              </div>
            
              <div class="event-card" data-type="online">
                  <div class="event-tag online">En ligne</div>
                  <div class="event-price free">Gratuit</div>
                  <div class="event-category">Marketing Digital</div>
                  <h3 class="event-title">Masterclass: SEO et référencement naturel</h3>
                  <p class="event-instructor">Par Anne Dubois</p>
                  <p class="event-description">Optimisez votre présence en ligne avec les meilleures pratiques SEO.</p>
                  <div class="event-details">
                      <div class="event-detail">
                          <i class="fas fa-calendar"></i>
                          <span>8 Février 2025</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-clock"></i>
                          <span>15:00 - 18:00</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-globe"></i>
                          <span>En ligne</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-users"></i>
                          <span>89/150 inscrits</span>
                      </div>
                  </div>
                  <button class="btn-register">
                      S'inscrire
                      <i class="fas fa-arrow-right"></i>
                  </button>
              </div>
              
              <div class="event-card" data-type="online">
                  <div class="event-tag online">En ligne</div>
                  <div class="event-price paid">99€</div>
                  <div class="event-category">Développement Web</div>
                  <h3 class="event-title">Session: Portfolio pour développeurs</h3>
                  <p class="event-instructor">Par Luc Moreau</p>
                  <p class="event-description">Créez un portfolio qui vous démarque et attire les recruteurs.</p>
                  <div class="event-details">
                      <div class="event-detail">
                          <i class="fas fa-calendar"></i>
                          <span>12 Février 2025</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-clock"></i>
                          <span>19:00 - 21:00</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-globe"></i>
                          <span>En ligne</span>
                      </div>
                      <div class="event-detail">
                          <i class="fas fa-users"></i>
                          <span>67/100 inscrits</span>
                      </div>
                  </div>
                  <button class="btn-register">
                      S'inscrire
                      <i class="fas fa-arrow-right"></i>
                  </button>
              </div>
          </div>
      </div>
  </section>

  <section class="cta-section">
      <div class="container">
          <h2 class="cta-title">Organisez votre événement avec nous</h2>
          <p class="cta-description">Vous êtes un expert dans votre domaine ? Proposez un webinaire ou un atelier pour partager vos connaissances 
          <button class="btn-cta">Proposer un événement</button>
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
                  <div class="contact-item">
                      <i class="fas fa-map-marker-alt"></i>
                      <span>123 Avenue de la Formation<br>75001 Paris, France</span>
                  </div>
                  <div class="contact-item">
                      <i class="fas fa-phone"></i>
                      <span>+33 1 23 45 67 89</span>
                  </div>
                  <div class="contact-item">
                      <i class="fas fa-envelope"></i>
                      <span>contact@3eduplus.fr</span>
                  </div>
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
