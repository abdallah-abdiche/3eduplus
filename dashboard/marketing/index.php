<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

// Check if user is logged in
checkAuth();

// Check if user is marketing
checkRole(['Marketing']);

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// Get user statistics
$users_query = "SELECT COUNT(*) as total_users FROM utilisateurs";
$result = $conn->query($users_query);
$users_stats = $result->fetch_assoc();
$total_users = $users_stats['total_users'] ?? 0;

// Get new users (count all users as we don't have date column)
$new_users_query = "SELECT COUNT(*) as new_users FROM utilisateurs";
$result = $conn->query($new_users_query);
$new_users_stats = $result->fetch_assoc();
$new_users = $new_users_stats['new_users'] ?? 0;

// Get user growth rate
$growth_rate = $total_users > 0 ? round(($new_users / $total_users) * 100, 1) : 0;

// Get engagement stats (based on inscriptions)
$engagement_query = "SELECT COUNT(*) as total_enrollments FROM inscriptions";
$result = $conn->query($engagement_query);
$engagement_stats = $result->fetch_assoc();
$total_enrollments = $engagement_stats['total_enrollments'] ?? 0;

// Get conversion rate (enrolled users / total users)
$conversion_rate = $total_users > 0 ? round(($total_enrollments / $total_users) * 100, 1) : 0;

// Get user role distribution
$role_query = "SELECT r.nom_role as Role, COUNT(*) as count 
               FROM utilisateurs u 
               LEFT JOIN roles r ON u.role_id = r.role_id 
               GROUP BY u.role_id 
               ORDER BY count DESC";
$role_result = $conn->query($role_query);
$role_distribution = $role_result->fetch_all(MYSQLI_ASSOC);

// Get recent signups
$recent_users_query = "SELECT u.user_id, u.Nom_Complet, u.Email, r.nom_role as Role 
                       FROM utilisateurs u 
                       LEFT JOIN roles r ON u.role_id = r.role_id 
                       ORDER BY u.user_id DESC 
                       LIMIT 10";
$recent_result = $conn->query($recent_users_query);
$recent_users = $recent_result->fetch_all(MYSQLI_ASSOC);

// Get top formations by enrollment
$top_formations_query = "SELECT f.titre, COUNT(*) as enrollments 
                         FROM formations f 
                         LEFT JOIN inscriptions i ON f.formation_id = i.session_id 
                         GROUP BY f.formation_id 
                         ORDER BY enrollments DESC 
                         LIMIT 5";
$top_formations_result = $conn->query($top_formations_query);
$top_formations = $top_formations_result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Marketing - 3edu+</title>
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
                <span>3edu+ - Marketing</span>
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
                <p class="subtitle">Analysez les tendances et optimisez vos campagnes marketing</p>
            </div>
            <div class="welcome-actions">
                <a href="#campaigns" class="btn btn-primary">
                    <i class="fas fa-bullhorn"></i> Nouvelle Campagne
                </a>
            </div>
        </section>

        <!-- KPI Cards -->
        <section class="stats-section">
            <div class="stat-card">
                <div class="stat-icon" style="background: #f3e5f5;">
                    <i class="fas fa-users" style="color: #9c27b0;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_users); ?></div>
                    <div class="stat-label">Utilisateurs Total</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9;">
                    <i class="fas fa-user-plus" style="color: #4caf50;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $new_users; ?></div>
                    <div class="stat-label">Nouveaux (30j)</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd;">
                    <i class="fas fa-chart-line" style="color: #2196f3;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $growth_rate; ?>%</div>
                    <div class="stat-label">Taux de Croissance</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0;">
                    <i class="fas fa-percentage" style="color: #ff9800;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $conversion_rate; ?>%</div>
                    <div class="stat-label">Taux de Conversion</div>
                </div>
            </div>
        </section>

        <!-- Main Content Grid -->
        <div class="dashboard-grid">
            <!-- Audience Distribution -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-pie-chart"></i> Distribution des Utilisateurs</h2>
                </div>

                <?php if (count($role_distribution) > 0): ?>
                    <div class="audience-chart">
                        <?php 
                        $colors = ['#9c27b0', '#e91e63', '#673ab7', '#3f51b5', '#2196f3', '#00bcd4'];
                        foreach ($role_distribution as $index => $role): 
                            $percentage = $total_users > 0 ? round(($role['count'] / $total_users) * 100, 1) : 0;
                            $color = $colors[$index % count($colors)];
                        ?>
                            <div class="audience-item">
                                <div class="audience-info">
                                    <span class="audience-color" style="background: <?php echo $color; ?>;"></span>
                                    <span class="audience-name"><?php echo htmlspecialchars($role['Role']); ?></span>
                                </div>
                                <div class="audience-bar-container">
                                    <div class="audience-bar" style="width: <?php echo $percentage; ?>%; background: <?php echo $color; ?>;"></div>
                                </div>
                                <span class="audience-value"><?php echo $role['count']; ?> (<?php echo $percentage; ?>%)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-pie"></i>
                        <h3>Aucune Donnée</h3>
                        <p>Aucune donnée d'audience disponible</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Top Formations -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-fire"></i> Formations Populaires</h2>
                </div>

                <?php if (count($top_formations) > 0): ?>
                    <div class="formations-list">
                        <?php foreach ($top_formations as $index => $formation): ?>
                            <div class="formation-item">
                                <div class="formation-rank">#<?php echo $index + 1; ?></div>
                                <div class="formation-info">
                                    <h3><?php echo htmlspecialchars($formation['titre']); ?></h3>
                                    <p><i class="fas fa-users"></i> <?php echo $formation['enrollments']; ?> inscriptions</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book"></i>
                        <h3>Aucune Formation</h3>
                        <p>Aucune formation disponible</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- Recent Users -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-user-clock"></i> Inscriptions Récentes</h2>
            </div>

            <?php if (count($recent_users) > 0): ?>
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td class="user-name-cell">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['Nom_Complet']); ?>&size=32&background=random" alt="Avatar" class="table-avatar">
                                        <?php echo htmlspecialchars($user['Nom_Complet']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo strtolower($user['Role']); ?>">
                                            <?php echo $user['Role']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>Aucun Utilisateur</h3>
                    <p>Aucun utilisateur inscrit</p>
                </div>
            <?php endif; ?>
        </section>

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
                        <span class="badge badge-marketing">Marketing</span>
                    </p>
                </div>
                <div class="info-item">
                    <label>Département</label>
                    <p>Marketing & Communication</p>
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
