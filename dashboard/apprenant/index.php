<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

// Check if user is logged in
checkAuth();

// Check if user is a student (Apprenant)
checkRole(['Apprenant']);

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// Get student's enrolled courses
$enrolled_query = "SELECT f.formation_id, f.titre, f.categorie, f.prix, f.formationImageUrl, i.statut_inscription, i.date_inscription
                   FROM inscriptions i
                   JOIN formations f ON i.session_id = f.formation_id
                   WHERE i.user_id = ?
                   ORDER BY i.date_inscription DESC";
$stmt = $conn->prepare($enrolled_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrolled_result = $stmt->get_result();
$enrolled_courses = $enrolled_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get student's payment history
$payments_query = "SELECT p.paiement_id, p.montant, p.statut, p.date_paiement
                   FROM paiements p
                   WHERE p.user_id = ?
                   ORDER BY p.date_paiement DESC
                   LIMIT 5";
$stmt = $conn->prepare($payments_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$payments_result = $stmt->get_result();
$payments = $payments_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get stats
$total_enrolled = count($enrolled_courses);
$completed_courses = count(array_filter($enrolled_courses, fn($c) => $c['statut_inscription'] === 'Complétée'));
$in_progress = count(array_filter($enrolled_courses, fn($c) => $c['statut_inscription'] === 'En cours'));

$total_spent = 0;
foreach ($payments as $payment) {
    if ($payment['statut'] === 'Complété') {
        $total_spent += $payment['montant'];
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Étudiant - 3edu+</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <img src="../../LogoEdu.png" alt="3edu+" class="logo">
                <span>3edu+ - Tableau de Bord Étudiant</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-home"></i> Accueil
                </a>
                <a href="../../formation.php" class="nav-link">
                    <i class="fas fa-book"></i> Cours
                </a>
                <a href="../../cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Panier
                </a>
                <div class="nav-user">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=random" alt="Avatar" class="user-avatar">
                    <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                    <div class="dropdown-menu">
                        <a href="#profile" class="dropdown-item">
                            <i class="fas fa-user"></i> Profil
                        </a>
                        <a href="#settings" class="dropdown-item">
                            <i class="fas fa-cog"></i> Paramètres
                        </a>
                        <hr>
                        <a href="../../logout.php" class="dropdown-item logout">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container dashboard-container">
        <!-- Welcome Section -->
        <section class="welcome-section">
            <div class="welcome-content">
                <h1>Bienvenue, <span class="highlight"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>!</span></h1>
                <p class="subtitle">Gérez vos cours et suivez votre progression d'apprentissage</p>
            </div>
            <div class="welcome-actions">
                <a href="../../formation.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Découvrir Cours
                </a>
            </div>
        </section>

        <!-- Stats Cards -->
        <section class="stats-section">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd;">
                    <i class="fas fa-book-open" style="color: #1976d2;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $total_enrolled; ?></div>
                    <div class="stat-label">Cours Inscrits</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #f3e5f5;">
                    <i class="fas fa-check-circle" style="color: #7b1fa2;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $completed_courses; ?></div>
                    <div class="stat-label">Complétés</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #f1f8e9;">
                    <i class="fas fa-hourglass-half" style="color: #689f38;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $in_progress; ?></div>
                    <div class="stat-label">En Cours</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0;">
                    <i class="fas fa-credit-card" style="color: #f57c00;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_spent, 2); ?> DA</div>
                    <div class="stat-label">Total Dépensé</div>
                </div>
            </div>
        </section>

        <!-- Main Content Grid -->
        <div class="dashboard-grid">
            <!-- Enrolled Courses Section -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-graduation-cap"></i> Mes Cours</h2>
                    <a href="../../formation.php" class="link-more">Voir plus <i class="fas fa-arrow-right"></i></a>
                </div>

                <?php if (count($enrolled_courses) > 0): ?>
                    <div class="courses-list">
                        <?php foreach ($enrolled_courses as $course): ?>
                            <div class="course-card">
                                <div class="course-image">
                                    <?php if ($course['formationImageUrl']): ?>
                                        <img src="<?php echo htmlspecialchars($course['formationImageUrl']); ?>" alt="<?php echo htmlspecialchars($course['titre']); ?>">
                                    <?php else: ?>
                                        <div class="course-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="course-content">
                                    <h3><?php echo htmlspecialchars($course['titre']); ?></h3>
                                    <p class="course-description"><?php echo htmlspecialchars($course['categorie'] ?? 'Général'); ?></p>
                                    
                                    <!-- Progress Bar -->
                                    <div class="progress-container">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: 75%"></div>
                                        </div>
                                        <span class="progress-text">75%</span>
                                    </div>

                                    <!-- Status Badge -->
                                    <div class="course-footer">
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $course['statut_inscription'])); ?>">
                                            <?php echo $course['statut_inscription']; ?>
                                        </span>
                                        <span class="course-price"><?php echo number_format($course['prix'], 2); ?> DA</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>Aucun cours inscrit</h3>
                        <p>Commencez votre apprentissage en vous inscrivant à des cours</p>
                        <a href="../../formation.php" class="btn btn-primary">Découvrir les Cours</a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Payment History Section -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Historique des Paiements</h2>
                </div>

                <?php if (count($payments) > 0): ?>
                    <div class="payments-list">
                        <table class="payments-table">
                            <thead>
                                <tr>
                                    <th>ID Paiement</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td class="transaction-ref">
                                            <code>#<?php echo htmlspecialchars($payment['paiement_id']); ?></code>
                                        </td>
                                        <td class="amount">
                                            <?php echo number_format($payment['montant'], 2); ?> DA
                                        </td>
                                        <td>
                                            <span class="payment-status payment-<?php echo strtolower(str_replace(' ', '-', $payment['statut'])); ?>">
                                                <?php echo $payment['statut']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($payment['date_paiement'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-credit-card"></i>
                        <h3>Aucun paiement</h3>
                        <p>Vous n'avez pas encore effectué de paiement</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- User Info Section -->
        <section class="dashboard-section user-info-section">
            <div class="section-header">
                <h2><i class="fas fa-user-circle"></i> Informations Personnelles</h2>
                <a href="#edit-profile" class="link-more">Modifier <i class="fas fa-edit"></i></a>
            </div>
            <div class="user-info-grid">
                <div class="info-item">
                    <label>Nom Complet</label>
                    <p><?php echo htmlspecialchars($user_name); ?></p>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <p><?php echo htmlspecialchars($user_email); ?></p>
                </div>
                <div class="info-item">
                    <label>Type de Compte</label>
                    <p>
                        <span class="badge badge-primary">Apprenant</span>
                    </p>
                </div>
                <div class="info-item">
                    <label>Date d'Inscription</label>
                    <p><?php echo date('d/m/Y'); ?></p>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>À Propos</h3>
                    <p>3edu+ est une plateforme d'apprentissage en ligne complète</p>
                </div>
                <div class="footer-section">
                    <h3>Liens Utiles</h3>
                    <ul>
                        <li><a href="#about">À Propos</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="#privacy">Confidentialité</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>Email: info@3eduplus.com</p>
                    <p>Tel: +213 XXX XXX XXX</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 3edu+ - Tous les droits réservés</p>
            </div>
        </div>
    </footer>

    <script>
        // Dropdown menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const userAvatar = document.querySelector('.user-avatar');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            if (userAvatar && dropdownMenu) {
                userAvatar.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.style.display = 
                        dropdownMenu.style.display === 'block' ? 'none' : 'block';
                });

                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.nav-user')) {
                        dropdownMenu.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>
