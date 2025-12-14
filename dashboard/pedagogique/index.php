<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

// Check if user is logged in
checkAuth();

// Check if user is pedagogique
checkRole(['Pedagogique']);

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// Get total formations
$formations_query = "SELECT COUNT(*) as total FROM formations";
$result = $conn->query($formations_query);
$formations_stats = $result->fetch_assoc();
$total_formations = $formations_stats['total'] ?? 0;

// Get total enrollments
$enrollments_query = "SELECT COUNT(*) as total FROM inscriptions";
$result = $conn->query($enrollments_query);
$enrollments_stats = $result->fetch_assoc();
$total_enrollments = $enrollments_stats['total'] ?? 0;

// Get total students (Apprenant role)
$students_query = "SELECT COUNT(*) as total FROM utilisateurs u 
                   LEFT JOIN roles r ON u.role_id = r.role_id 
                   WHERE r.nom_role = 'Apprenant'";
$result = $conn->query($students_query);
$students_stats = $result->fetch_assoc();
$total_students = $students_stats['total'] ?? 0;

// Calculate average students per course
$avg_per_course = $total_formations > 0 ? round($total_enrollments / $total_formations, 1) : 0;

// Get all formations with enrollment count
$all_formations_query = "SELECT f.formation_id, f.titre, f.description, f.duree, f.prix, f.niveau,
                         COUNT(i.session_id) as enrollments
                         FROM formations f
                         LEFT JOIN inscriptions i ON f.formation_id = i.session_id
                         GROUP BY f.formation_id
                         ORDER BY enrollments DESC";
$formations_result = $conn->query($all_formations_query);
$all_formations = $formations_result->fetch_all(MYSQLI_ASSOC);

// Get recent enrollments
$recent_enrollments_query = "SELECT i.date_inscription, u.Nom_Complet, u.Email, f.titre
                             FROM inscriptions i
                             JOIN utilisateurs u ON i.user_id = u.user_id
                             JOIN formations f ON i.session_id = f.formation_id
                             ORDER BY i.date_inscription DESC
                             LIMIT 10";
$recent_result = $conn->query($recent_enrollments_query);
$recent_enrollments = $recent_result->fetch_all(MYSQLI_ASSOC);

// Get course level distribution
$level_query = "SELECT niveau, COUNT(*) as count FROM formations GROUP BY niveau ORDER BY count DESC";
$level_result = $conn->query($level_query);
$level_distribution = $level_result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Pédagogique - 3edu+</title>
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
                <span>3edu+ - Pédagogique</span>
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
                <p class="subtitle">Gérez vos formations et suivez la progression des apprenants</p>
            </div>
            <div class="welcome-actions">
                <a href="#add-course" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle Formation
                </a>
            </div>
        </section>

        <!-- KPI Cards -->
        <section class="stats-section">
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0;">
                    <i class="fas fa-graduation-cap" style="color: #ff9800;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_formations); ?></div>
                    <div class="stat-label">Formations</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd;">
                    <i class="fas fa-users" style="color: #2196f3;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_students); ?></div>
                    <div class="stat-label">Apprenants</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9;">
                    <i class="fas fa-clipboard-list" style="color: #4caf50;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($total_enrollments); ?></div>
                    <div class="stat-label">Inscriptions</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fce4ec;">
                    <i class="fas fa-chart-bar" style="color: #e91e63;"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $avg_per_course; ?></div>
                    <div class="stat-label">Moy. par Cours</div>
                </div>
            </div>
        </section>

        <!-- Main Content Grid -->
        <div class="dashboard-grid">
            <!-- Course Level Distribution -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-layer-group"></i> Niveaux des Formations</h2>
                </div>

                <?php if (count($level_distribution) > 0): ?>
                    <div class="level-chart">
                        <?php 
                        $colors = ['#ff9800', '#2196f3', '#4caf50', '#e91e63', '#9c27b0'];
                        foreach ($level_distribution as $index => $level): 
                            $percentage = $total_formations > 0 ? round(($level['count'] / $total_formations) * 100, 1) : 0;
                            $color = $colors[$index % count($colors)];
                        ?>
                            <div class="level-item">
                                <div class="level-info">
                                    <span class="level-color" style="background: <?php echo $color; ?>;"></span>
                                    <span class="level-name"><?php echo htmlspecialchars($level['niveau'] ?? 'Non défini'); ?></span>
                                </div>
                                <div class="level-bar-container">
                                    <div class="level-bar" style="width: <?php echo $percentage; ?>%; background: <?php echo $color; ?>;"></div>
                                </div>
                                <span class="level-value"><?php echo $level['count']; ?> (<?php echo $percentage; ?>%)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-layer-group"></i>
                        <h3>Aucune Donnée</h3>
                        <p>Aucune formation disponible</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- All Formations -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-book-open"></i> Toutes les Formations</h2>
                </div>

                <?php if (count($all_formations) > 0): ?>
                    <div class="formations-list">
                        <?php foreach ($all_formations as $formation): ?>
                            <div class="formation-card">
                                <div class="formation-header">
                                    <h3><?php echo htmlspecialchars($formation['titre']); ?></h3>
                                    <span class="formation-level"><?php echo htmlspecialchars($formation['niveau'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="formation-stats">
                                    <span><i class="fas fa-users"></i> <?php echo $formation['enrollments']; ?> inscrits</span>
                                    <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($formation['duree'] ?? 'N/A'); ?></span>
                                    <span><i class="fas fa-tag"></i> <?php echo number_format($formation['prix'], 2); ?> DA</span>
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

        <!-- Recent Enrollments -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-user-graduate"></i> Inscriptions Récentes</h2>
            </div>

            <?php if (count($recent_enrollments) > 0): ?>
                <div class="enrollments-table-container">
                    <table class="enrollments-table">
                        <thead>
                            <tr>
                                <th>Apprenant</th>
                                <th>Email</th>
                                <th>Formation</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_enrollments as $enrollment): ?>
                                <tr>
                                    <td class="student-name">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($enrollment['Nom_Complet']); ?>&size=32&background=random" alt="Avatar" class="table-avatar">
                                        <?php echo htmlspecialchars($enrollment['Nom_Complet']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($enrollment['Email']); ?></td>
                                    <td><span class="course-badge"><?php echo htmlspecialchars($enrollment['titre']); ?></span></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($enrollment['date_inscription'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Aucune Inscription</h3>
                    <p>Aucune inscription récente</p>
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
                        <span class="badge badge-pedagogique">Pédagogique</span>
                    </p>
                </div>
                <div class="info-item">
                    <label>Département</label>
                    <p>Formation & Enseignement</p>
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
