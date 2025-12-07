
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="formation.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="./LogoEdu.png" type="image/png">
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
                <li><a href="formation.php" class="active">Formations</a></li>
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="about.html">À propos</a></li>
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
            <a href="login.html" class="login-btn">Connexion</a>
        </div>


    </header>

    <section class="p-Decouvrez-Formation">
        <h1>Nos Formations</h1>
        <p >Découvrez notre catalogue de formations professionnelles</p>
    </section>


    <div class="content-wrapper">
       
        <aside class="sidebar">
            <div class="filter-section">
                <h3><i class="fas fa-filter"></i> Filtres</h3>

                <div class="filter-group">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher une formation" id="searchInput">
                    </div>
                </div>

               
                <div class="filter-group">
                    <h4>Catégorie</h4>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="category" value="all" checked>
                            <span class="checkmark"></span>
                            Toutes
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="category" value="web-dev">
                            <span class="checkmark"></span>
                            Développement Web
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="category" value="design">
                            <span class="checkmark"></span>
                            Design & UX
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="category" value="marketing">
                            <span class="checkmark"></span>
                            Marketing Digital
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="category" value="data">
                            <span class="checkmark"></span>
                            Data Science
                        </label>
                    </div>
                </div>

                <!-- Level Filter -->
                <div class="filter-group">
                    <h4>Niveau</h4>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="level" value="all" checked>
                            <span class="checkmark"></span>
                            Tous niveaux
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="level" value="beginner">
                            <span class="checkmark"></span>
                            Débutant
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="level" value="intermediate">
                            <span class="checkmark"></span>
                            Intermédiaire
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="level" value="advanced">
                            <span class="checkmark"></span>
                            Avancé
                        </label>
                    </div>
                </div>

              
                <div class="filter-group">
                    <h4>Prix</h4>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="price" value="under-300">
                            <span class="checkmark"></span>
                            Moins de 300€
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="price" value="300-500">
                            <span class="checkmark"></span>
                            300€-500€
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="price" value="over-500">
                            <span class="checkmark"></span>
                            Plus de 500€
                        </label>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main>
            <section class="main-content">
                <div class="content-header">
                    <span class="course-count">6 formations disponibles</span>
                    <div class="sort-dropdown">
                        <select id="sortSelect">
                            <option value="popular">Plus populaire</option>
                            <option value="price-low">Prix croissant</option>
                            <option value="price-high">Prix décroissant</option>
                            <option value="rating">Mieux notés</option>
                        </select>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>

                <!-- Course Grid -->
                <div class="course-grid" id="courseGrid">
                    <!-- Course Cards -->
                    



                    




                    <?php
include 'config.php';


$sql = "SELECT * FROM formations";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $formation_id = $row['formation_id'];
        $titre = $row['titre'];  
        $categorie = $row['categorie'];
        $prix = $row['prix'];
        $niveau = $row['niveau'];
        $duree = $row['duree'];
        $date_creation = $row['date_creation'];
        $url = $row['url'];

        echo '
        <div class="course-card" data-category="'.$categorie.'" data-level="'.$niveau.'" data-price="'.$prix.'">
            <div class="course-image">
                <div class="image-placeholder"><img src="'.$url.'" alt="'.$titre.'"></div>
            </div>
            <div class="course-content">
                <div class="course-category">'.$categorie.'</div>
                <h3 class="course-title">'.$titre.'</h3>
                <p class="course-description">Description du cours ici...</p>
                <div class="course-details">
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span>'.$duree.'</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-book"></i>
                        <span>Leçons à définir</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-users"></i>
                        <span>Participants à définir</span>
                    </div>
                </div>
                <div class="course-rating">
                    <i class="fas fa-star"></i>
                    <span>4.8</span>
                </div>
                <div class="course-footer">
                    <div class="course-price">'.$prix.'€</div>
                    <div class="course-level">'.$niveau.'</div>
                </div>
                <button class="add-to-cart-btn">
                    <i class="fas fa-shopping-cart"></i>
                    Ajouter
                </button>
            </div>
        </div>';
    }
} else {
    echo "Erreur lors de la récupération des formations : " . mysqli_error($conn);
}

?>   
<br>
<br>


                 <div class="pagination">
                        <button class="pagination-btn prev-btn">
                            <i class="fas fa-chevron-left"></i>
                            Précédent
                        </button>
                        <div class="pagination-numbers">
                            <button class="pagination-number">1</button>
                            <button class="pagination-number active">2</button>
                            <button class="pagination-number">3</button>
                        </div>
                        <button class="pagination-btn next-btn">
                            Suivant
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>


    </div>
    </main>
<br>

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
                    <li><a href="#">Nos formations</a></li>
                    <li><a href="#">Événements</a></li>
                    <li><a href="#">À propos</a></li>
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
<script src="formation.js"> </script>
</body>

</html>