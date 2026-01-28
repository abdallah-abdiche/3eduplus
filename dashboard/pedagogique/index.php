<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Directeur pédagogique', 'Admin']);

$user_name = $_SESSION['user_name'];

// --- Stats ---
// 1. Total Courses
$courses_count = $conn->query("SELECT COUNT(*) as count FROM formations")->fetch_assoc()['count'] ?? 0;

// 2. Total Instructors
$instructors_count = $conn->query("
    SELECT COUNT(*) as count 
    FROM utilisateurs u 
    JOIN roles r ON u.role_id = r.role_id 
    WHERE r.nom_role = 'Formateur'
")->fetch_assoc()['count'] ?? 0;

// 3. Enrollment Count
$enrollment_count = $conn->query("SELECT COUNT(*) as count FROM inscriptions")->fetch_assoc()['count'] ?? 0;

// 4. Recent Courses
$recent_courses = $conn->query("SELECT titre, date_creation, niveau FROM formations ORDER BY date_creation DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pédagogique - 3edu+</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="../commercial/dashboard.css"> <!-- Reuse styled css -->
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand"><span>Direction Pédagogique</span></div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">Accueil</a>
                <a href="../../formation.php" class="nav-link">Catalogue</a>
                <?php if (($_SESSION['user_role'] ?? '') === 'Admin' || ($_SESSION['is_admin'] ?? false)): ?>
                    <a href="../admin/index.php" class="nav-link" style="background: #4f46e5; color: white; border-radius: 6px; padding: 8px 15px;">
                        <i class="fas fa-shield-alt"></i> Admin
                    </a>
                <?php endif; ?>
                <a href="../../logout.php" class="nav-link">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container dashboard-container">
        <section class="welcome-section">
            <div class="welcome-content">
                <h1>Bienvenue, <span class="highlight"><?php echo htmlspecialchars($user_name); ?></span></h1>
                <p class="subtitle">Supervision des programmes et des formateurs</p>
            </div>
            <div class="welcome-actions">
                <a href="manage_courses.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau Cours</a>
            </div>
        </section>

        <section class="stats-section">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd;"><i class="fas fa-book" style="color: #1565c0;"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $courses_count; ?></div>
                    <div class="stat-label">Formations Actives</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0;"><i class="fas fa-chalkboard-teacher" style="color: #ef6c00;"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $instructors_count; ?></div>
                    <div class="stat-label">Formateurs</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9;"><i class="fas fa-user-graduate" style="color: #2e7d32;"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $enrollment_count; ?></div>
                    <div class="stat-label">Inscriptions</div>
                </div>
            </div>
        </section>

        <div class="dashboard-grid">
            <section class="dashboard-section" style="grid-column: 1 / -1;">
                <h2><i class="fas fa-clock"></i> Dernières Formations Ajoutées</h2>
                <?php if ($recent_courses && $recent_courses->num_rows > 0): ?>
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Niveau</th>
                                <th>Date Création</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($c = $recent_courses->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['titre']); ?></td>
                                <td><span class="badge"><?php echo htmlspecialchars($c['niveau']); ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($c['date_creation'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Aucune formation récente.</p>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>
</html>
