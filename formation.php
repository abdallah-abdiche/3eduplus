<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Pagination Logic
$limit = 6; // Number of items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Fetch total count for pagination
$count_query = "SELECT COUNT(*) as total FROM Formations";
$count_result = $conn->query($count_query);
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $limit);

// Fetch limited formations from database
$formations = [];
$query = "SELECT formation_id, titre, categorie, prix, niveau, duree, date_creation, formationImageUrl FROM Formations ORDER BY date_creation DESC LIMIT ? OFFSET ?";
$stmt_list = $conn->prepare($query);
$stmt_list->bind_param("ii", $limit, $offset);
$stmt_list->execute();
$result = $stmt_list->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $formations[] = $row;
    }
}
$stmt_list->close();

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
    
    // Insert into panier (IGNORE to avoid duplicates)
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
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formations - 3edu+</title>
    <link rel="stylesheet" href="formation.css">
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
                <li><a href="formation.php" class="active">Formations</a></li>
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="about.php">À propos</a></li>
                <li><a href="inscription.php">Inscriptions</a></li>
            </ul>
        </nav>

        <div class="nav-actions">
            <form action="search_results.php" method="GET" class="search-container">
                <input type="text" name="q" placeholder="Rechercher des formations..." class="search-input" id="globalSearch">
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

    <section class="p-Decouvrez-Formation">
        <h1>Nos Formations</h1>
        <p>Découvrez notre catalogue de formations professionnelles</p>
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
                        <?php 
                        // Get unique categories from database
                        $cat_query = "SELECT DISTINCT categorie FROM Formations WHERE categorie IS NOT NULL";
                        $cat_result = $conn->query($cat_query);
                        if ($cat_result && $cat_result->num_rows > 0) {
                            while ($cat = $cat_result->fetch_assoc()) {
                                $cat_value = strtolower(str_replace(' ', '-', $cat['categorie']));
                                echo '<label class="checkbox-item">';
                                echo '<input type="checkbox" name="category" value="' . htmlspecialchars($cat_value) . '">';
                                echo '<span class="checkmark"></span>';
                                echo htmlspecialchars($cat['categorie']);
                                echo '</label>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="filter-group">
                    <h4>Niveau</h4>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="level" value="all" checked>
                            <span class="checkmark"></span>
                            Tous niveaux
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="level" value="Débutant">
                            <span class="checkmark"></span>
                            Débutant
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="level" value="Intermédiaire">
                            <span class="checkmark"></span>
                            Intermédiaire
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="level" value="Avancé">
                            <span class="checkmark"></span>
                            Avancé
                        </label>
                    </div>
                </div>

                <div class="filter-group">
                    <h4>Prix</h4>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="price" value="0-500">
                            <span class="checkmark"></span>
                            Moins de 500€
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="price" value="500-1000">
                            <span class="checkmark"></span>
                            500€-1000€
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="price" value="1000-5000">
                            <span class="checkmark"></span>
                            Plus de 1000€
                        </label>
                    </div>
                </div>
            </div>
        </aside>

        <main>
            <section class="main-content">
                <div class="content-header">
                    <span class="course-count" id="courseCount"><?php echo count($formations); ?> formations disponibles</span>
                    <div class="sort-dropdown">
                        <select id="sortSelect">
                            <option value="recent">Plus récent</option>
                            <option value="price-low">Prix croissant</option>
                            <option value="price-high">Prix décroissant</option>
                            <option value="popular">Plus populaire</option>
                        </select>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>

                <div class="course-grid" id="courseGrid">
                    <?php if (count($formations) > 0): ?>
                        <?php foreach ($formations as $formation): ?>
                            <div class="course-card" 
                                 data-category="<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $formation['categorie'] ?? ''))); ?>" 
                                 data-level="<?php echo htmlspecialchars($formation['niveau'] ?? ''); ?>" 
                                 data-price="<?php echo (float)$formation['prix']; ?>"
                                 data-title="<?php echo htmlspecialchars(strtolower($formation['titre'])); ?>"
                                 data-id="<?php echo $formation['formation_id']; ?>">
                                <div class="course-image">
                                    <div class="image-placeholder">
                                        <?php if (!empty($formation['formationImageUrl'])): ?>
                                            <img src="<?php echo htmlspecialchars($formation['formationImageUrl']); ?>" alt="<?php echo htmlspecialchars($formation['titre']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <i class="fas fa-book" style="font-size: 60px; color: #ccc;"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="course-content">
                                    <div class="course-category"><?php echo htmlspecialchars($formation['categorie'] ?? 'Non catégorisé'); ?></div>
                                    <h3 class="course-title"><?php echo htmlspecialchars($formation['titre']); ?></h3>
                                    <p class="course-instructor">Créé le <?php echo date('d/m/Y', strtotime($formation['date_creation'])); ?></p>
                                    <div class="course-details">
                                        <div class="detail-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo htmlspecialchars($formation['duree'] ?? 'Non spécifié'); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-layer-group"></i>
                                            <span><?php echo htmlspecialchars($formation['niveau'] ?? 'Tous niveaux'); ?></span>
                                        </div>
                                    </div>
                                    <div class="course-footer">
                                        <div class="course-price"><?php echo number_format((float)$formation['prix'], 2, ',', ' '); ?>€</div>
                                        <div class="course-level"><?php echo htmlspecialchars($formation['niveau'] ?? 'Tous'); ?></div>
                                    </div>
                                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $formation['formation_id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                        Ajouter
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                            <p style="color: #999; font-size: 18px;">Aucune formation disponible pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn prev-btn">
                            <i class="fas fa-chevron-left"></i>
                            Précédent
                        </a>
                    <?php endif; ?>
                    
                    <div class="pagination-numbers">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="pagination-number <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn next-btn">
                            Suivant
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

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

    <!-- Toast Notification Container -->
    <div id="cartToast" class="toast-notification">
        <div class="toast-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="toast-message">Formation ajoutée au panier !</div>
    </div>

    <script>
        // Notification Handler
        function showNotification() {
            const toast = document.getElementById('cartToast');
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

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

            fetch('formation.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.cart-count').textContent = data.cart_count;
                    showNotification();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const globalSearch = document.getElementById('globalSearch');
            const categoryCheckboxes = document.querySelectorAll('input[name="category"]');
            const levelCheckboxes = document.querySelectorAll('input[name="level"]');
            const priceCheckboxes = document.querySelectorAll('input[name="price"]');
            const sortSelect = document.getElementById('sortSelect');
            const courseGrid = document.getElementById('courseGrid');
            const courseCount = document.getElementById('courseCount');
            const courseCards = courseGrid.querySelectorAll('.course-card');

            function filterCourses() {
                let visibleCount = 0;

                courseCards.forEach(card => {
                    let show = true;

                    // Search filter
                    const searchTerm = (searchInput.value + globalSearch.value).toLowerCase();
                    if (searchTerm && !card.dataset.title.includes(searchTerm)) {
                        show = false;
                    }

                    // Category filter
                    const selectedCategories = Array.from(categoryCheckboxes)
                        .filter(cb => cb.checked && cb.value !== 'all')
                        .map(cb => cb.value);
                    
                    if (selectedCategories.length > 0 && !selectedCategories.includes(card.dataset.category)) {
                        show = false;
                    }

                    // Level filter
                    const selectedLevels = Array.from(levelCheckboxes)
                        .filter(cb => cb.checked && cb.value !== 'all')
                        .map(cb => cb.value);
                    
                    if (selectedLevels.length > 0 && !selectedLevels.includes(card.dataset.level)) {
                        show = false;
                    }

                    // Price filter
                    const selectedPrices = Array.from(priceCheckboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);
                    
                    if (selectedPrices.length > 0) {
                        let priceMatch = false;
                        const price = parseFloat(card.dataset.price);
                        
                        selectedPrices.forEach(range => {
                            const [min, max] = range.split('-').map(Number);
                            if (price >= min && price <= max) {
                                priceMatch = true;
                            }
                        });
                        
                        if (!priceMatch) show = false;
                    }

                    card.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });

                courseCount.textContent = visibleCount + ' formation' + (visibleCount > 1 ? 's' : '') + ' disponible' + (visibleCount > 1 ? 's' : '');
            }

            function sortCourses() {
                const sortValue = sortSelect.value;
                const cards = Array.from(courseCards);

                cards.sort((a, b) => {
                    switch (sortValue) {
                        case 'price-low':
                            return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                        case 'price-high':
                            return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                        case 'recent':
                        default:
                            return 0;
                    }
                });

                courseGrid.innerHTML = '';
                cards.forEach(card => courseGrid.appendChild(card));
            }

            searchInput.addEventListener('input', filterCourses);
            globalSearch.addEventListener('input', filterCourses);
            categoryCheckboxes.forEach(cb => cb.addEventListener('change', filterCourses));
            levelCheckboxes.forEach(cb => cb.addEventListener('change', filterCourses));
            priceCheckboxes.forEach(cb => cb.addEventListener('change', filterCourses));
            sortSelect.addEventListener('change', sortCourses);
        });
    </script>



<script>
/* Toggle open class on click */
document.querySelector('.user-btn').addEventListener('click', function(e){
  e.stopPropagation();                       // keep the click from bubbling
  this.parentElement.classList.toggle('open');
});
/* Close when clicking anywhere else */
document.addEventListener('click', () =>
  document.querySelector('.user-menu').classList.remove('open')
);
</script>

</body>

</html>
