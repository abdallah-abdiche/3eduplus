<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Assistante commerciale', 'Admin']);

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get counts
$pending_payments = $conn->query("SELECT COUNT(*) as count FROM paiements WHERE statut = 'En attente'")->fetch_assoc()['count'] ?? 0;
$messages_count = $conn->query("SELECT COUNT(*) as count FROM messages WHERE lu = 0")->fetch_assoc()['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Assistante Commerciale - 3edu+</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="../commercial/dashboard.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand"><span>Dashboard Assistante</span></div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">Accueil</a>
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
        <h1>Bienvenue, <?php echo htmlspecialchars($user_name); ?></h1>
        
        <section class="stats-section">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $pending_payments; ?></div>
                    <div class="stat-label">Paiements en attente</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $messages_count; ?></div>
                    <div class="stat-label">Nouveaux messages</div>
                </div>
            </div>
        </section>

        <div class="dashboard-grid">
            <section class="dashboard-section">
                <h2><i class="fas fa-users"></i> Services Clients</h2>
                <p>Messagerie et support client.</p>
                <a href="#" class="btn btn-primary">Accéder à la messagerie</a>
            </section>
            <section class="dashboard-section">
                <h2><i class="fas fa-check-circle"></i> Gestion des Paiements</h2>
                <p>Valider les virements bancaires.</p>
                <a href="#" class="btn btn-primary">Voir les paiements</a>
            </section>
            <section class="dashboard-section">
                <h2><i class="fas fa-quote-left"></i> Témoignages</h2>
                <p>Modérer les avis des apprenants.</p>
                <a href="#" class="btn btn-primary">Gérer les témoignages</a>
            </section>
        </div>
    </div>
</body>
</html>
