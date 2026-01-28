<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Responsable marketing', 'Admin']);

$user_name = $_SESSION['user_name'];

// --- Stats ---
// 1. Total Users
$users_count = $conn->query("SELECT COUNT(*) as count FROM utilisateurs")->fetch_assoc()['count'] ?? 0;

// 2. New Users (Last 30 days)
$new_users = $conn->query("SELECT COUNT(*) as count FROM utilisateurs WHERE date_registration >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'] ?? 0;

// 3. Total Events
$events_count = $conn->query("SELECT COUNT(*) as count FROM evenements")->fetch_assoc()['count'] ?? 0;

// 4. Paid Inscriptions
$paid_sales = $conn->query("SELECT COUNT(*) as count FROM paiements WHERE statut = 'paid'")->fetch_assoc()['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Marketing - 3edu+</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="../commercial/dashboard.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand"><span>Marketing & Communication</span></div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">Accueil</a>
                <a href="../../events.php" class="nav-link">Événements</a>
                <?php if (($_SESSION['user_role'] ?? '') === 'Admin' || ($_SESSION['is_admin'] ?? false)): ?>
                    <a href="../admin/index.php" class="nav-link" style="background: #4f46e5; color: white; border-radius: 6px; padding: 8px 15px;">
                        <i class="fas fa-shield-alt"></i> Admin Dashboard
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
                <p class="subtitle">Anlayse de l'audience et campagnes</p>
            </div>
        </section>

        <section class="stats-section">
            <div class="stat-card">
                <div class="stat-icon" style="background: #f3e5f5;"><i class="fas fa-users" style="color: #8e24aa;"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $users_count; ?></div>
                    <div class="stat-label">Utilisateurs Totaux</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e1bee7;"><i class="fas fa-user-plus" style="color: #8e24aa;"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $new_users; ?></div>
                    <div class="stat-label">Nouveaux (30j)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e0f7fa;"><i class="fas fa-calendar-check" style="color: #006064;"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $events_count; ?></div>
                    <div class="stat-label">Événements</div>
                </div>
            </div>
             <div class="stat-card">
                <div class="stat-icon" style="background: #fff8e1;"><i class="fas fa-shopping-bag" style="color: #ff6f00;"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $paid_sales; ?></div>
                    <div class="stat-label">Ventes Confirmées</div>
                </div>
            </div>
        </section>

        <div class="dashboard-grid">
            <section class="dashboard-section">
                <h2><i class="fas fa-bullhorn"></i> Campagnes Actives</h2>
                <div class="empty-state">
                    <p>Aucune campagne active pour le moment.</p>
                    <button class="btn btn-primary">Créer une campagne</button>
                </div>
            </section>
            
             <section class="dashboard-section">
                <h2><i class="fas fa-chart-pie"></i> Canaux d'acquisition</h2>
                <p>Données non disponibles.</p>
            </section>
        </div>
    </div>
</body>
</html>
