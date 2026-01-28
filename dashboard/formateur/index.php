<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Formateur', 'Admin']);

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fix table name if needed for sessions
$sessions_query = "SELECT s.*, f.titre FROM sessions s JOIN formations f ON s.session_id = f.formation_id LIMIT 5";
$sessions_result = $conn->query($sessions_query);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Formateur - 3edu+</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="../commercial/dashboard.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand"><span>Dashboard Formateur</span></div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">Mes Sessions</a>
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
        <h1>Espace Formateur: <?php echo htmlspecialchars($user_name); ?></h1>
        
        <div class="dashboard-grid">
            <section class="dashboard-section">
                <h2><i class="fas fa-calendar-alt"></i> Mes Sessions à animer</h2>
                <p>Consultez votre planning de formation.</p>
                <a href="#" class="btn btn-primary">Voir le planning</a>
            </section>
            <section class="dashboard-section">
                <h2><i class="fas fa-users"></i> Liste des Apprenants</h2>
                <p>Informations sur vos stagiaires.</p>
                <a href="#" class="btn btn-primary">Voir les apprenants</a>
            </section>
            <section class="dashboard-section">
                <h2><i class="fas fa-file-pdf"></i> Documents Pédagogiques</h2>
                <p>Gérez vos supports de cours.</p>
                <a href="#" class="btn btn-primary">Mes documents</a>
            </section>
        </div>
    </div>
</body>
</html>
