<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

// Check if user is logged in
checkAuth();

// Check if user is commercial
checkRole(['Commercial']);

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// Get sales statistics
$sales_query = "SELECT COUNT(*) as total_sales, SUM(p.montant) as total_revenue
                FROM paiements p
                WHERE p.date_paiement >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result = $conn->query($sales_query);
$sales_stats = $result->fetch_assoc();

// Get recent transactions
$transactions_query = "SELECT p.paiement_id as id, p.montant, p.montant as montant_total, p.methode_paiement, p.statut as statut_paiement, p.date_paiement, 
                              u.Nom_Complet, u.Email
                       FROM paiements p
                       JOIN utilisateurs u ON p.user_id = u.user_id
                       ORDER BY p.date_paiement DESC
                       LIMIT 10";
$transactions_result = $conn->query($transactions_query);
$transactions = $transactions_result->fetch_all(MYSQLI_ASSOC);

// Get top selling courses
$top_courses_query = "SELECT f.titre, COUNT(i.inscription_id) as total_enrolled, SUM(f.prix) as total_revenue
                      FROM inscriptions i
                      JOIN formations f ON i.formation_id = f.formation_id
                      GROUP BY f.formation_id
                      ORDER BY total_enrolled DESC
                      LIMIT 5";
$top_courses_result = $conn->query($top_courses_query);
$top_courses = $top_courses_result->fetch_all(MYSQLI_ASSOC);

// Get customer statistics
$customer_stats_query = "SELECT 
                        COUNT(DISTINCT p.user_id) as total_customers,
                        COUNT(DISTINCT CASE WHEN p.statut = 'paid' THEN p.user_id END) as paying_customers,
                        AVG(p.montant) as avg_transaction
                        FROM paiements p";
$customer_result = $conn->query($customer_stats_query);
$customer_stats = $customer_result->fetch_assoc();

$total_sales = $sales_stats['total_sales'] ?? 0;
$total_revenue = $sales_stats['total_revenue'] ?? 0;
$total_customers = $customer_stats['total_customers'] ?? 0;
$avg_transaction = $customer_stats['avg_transaction'] ?? 0;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Commercial - 3edu+</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="dashboard.css?v=<?php echo time(); ?>">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <img src="../../LogoEdu.png" alt="3edu+" class="logo">
                <span>3edu+ - Tableau de Bord Commercial</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-home"></i> Accueil
                </a>
                <a href="../../formation.php" class="nav-link">
                    <i class="fas fa-book"></i> Cours
                </a>
                <a href="../../index.php" class="nav-link">
                    <i class="fas fa-globe"></i> Site
                </a>
                <?php if (($_SESSION['user_role'] ?? '') === 'Admin' || ($_SESSION['is_admin'] ?? false)): ?>
                    <a href="../admin/index.php" class="nav-link" style="background: #8e24aa; color: white; border-radius: 6px; padding: 8px 15px;">
                        <i class="fas fa-shield-alt"></i> Admin
                    </a>
                <?php endif; ?>
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
                <p class="subtitle">Gérez vos ventes et analysez les performances</p>
            </div>
            <div class="welcome-actions">
                <a href="../../formation.php" class="btn btn-primary">
                    <i class="fas fa-chart-line"></i> Analyse Détaillée
                </a>
            </div>
        </section>

        <!-- KPI Cards -->
        <section class="stats-section">
            <div class="stat-card">
                <div class="stat-icon" style="background: #f3e5f5;">
                    <i class="fas fa-dollar-sign" style="color: #8e24aa;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_revenue, 2); ?> DA</div>
                    <div class="stat-label">Revenu (30 jours)</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd;">
                    <i class="fas fa-shopping-cart" style="color: #1976d2;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $total_sales; ?></div>
                    <div class="stat-label">Ventes (30 jours)</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #f3e5f5;">
                    <i class="fas fa-users" style="color: #7b1fa2;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $total_customers; ?></div>
                    <div class="stat-label">Clients</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0;">
                    <i class="fas fa-chart-bar" style="color: #f57c00;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($avg_transaction, 2); ?> DA</div>
                    <div class="stat-label">Moyenne Transaction</div>
                </div>
            </div>
        </section>

        <!-- Main Content Grid -->
        <div class="dashboard-grid">
            <!-- Recent Transactions -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-exchange-alt"></i> Transactions Récentes</h2>
                </div>

                <?php if (count($transactions) > 0): ?>
                    <div class="transactions-list">
                        <table class="transactions-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Email</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td class="client-name">
                                            <?php echo htmlspecialchars($transaction['Nom_Complet']); ?>
                                        </td>
                                        <td class="client-email">
                                            <?php echo htmlspecialchars($transaction['Email']); ?>
                                        </td>
                                        <td class="amount">
                                            <?php echo number_format($transaction['montant_total'], 2); ?> DA
                                        </td>
                                        <td><?php echo htmlspecialchars($transaction['methode_paiement']); ?></td>
                                        <td>
                                            <span class="payment-status payment-<?php echo strtolower(str_replace(' ', '-', $transaction['statut_paiement'])); ?>">
                                                <?php echo $transaction['statut_paiement']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($transaction['date_paiement'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune Transaction</h3>
                        <p>Aucune transaction n'a été enregistrée</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Top Selling Courses -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-star"></i> Cours Populaires</h2>
                </div>

                <?php if (count($top_courses) > 0): ?>
                    <div class="courses-list">
                        <?php foreach ($top_courses as $index => $course): ?>
                            <div class="course-item">
                                <div class="rank">
                                    <span class="rank-number">#<?php echo $index + 1; ?></span>
                                </div>
                                <div class="course-info">
                                    <h3><?php echo htmlspecialchars($course['titre']); ?></h3>
                                    <p class="course-stats">
                                        <span class="stat">
                                            <i class="fas fa-users"></i> <?php echo $course['total_enrolled']; ?> inscrits
                                        </span>
                                    </p>
                                </div>
                                <div class="course-revenue">
                                    <div class="revenue-amount"><?php echo number_format($course['total_revenue'], 2); ?> DA</div>
                                    <div class="revenue-label">Revenu</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book"></i>
                        <h3>Aucun Cours</h3>
                        <p>Aucun cours disponible</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- User Info Section -->
        <section class="dashboard-section user-info-section">
            <div class="section-header">
                <h2><i class="fas fa-user-circle"></i> Informations Professionnelles</h2>
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
                    <label>Rôle</label>
                    <p>
                        <span class="badge badge-commercial">Commercial</span>
                    </p>
                </div>
                <div class="info-item">
                    <label>Département</label>
                    <p>Ventes & Marketing</p>
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