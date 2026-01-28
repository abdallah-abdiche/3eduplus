<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../signup.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Étudiant';

// Fetch enrolled courses
$courses_sql = "
    SELECT f.*, p.statut, p.date_paiement 
    FROM formations f
    JOIN paiement_formations pf ON f.formation_id = pf.formation_id
    JOIN paiements p ON pf.paiement_id = p.paiement_id
    WHERE p.user_id = ? AND p.statut = 'paid'
    ORDER BY p.date_paiement DESC";

$stmt = $conn->prepare($courses_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Statistics
$total_courses = count($enrolled_courses);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace Étudiant - 3edu+</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #7c3aed;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            height: 100vh;
            color: white;
            position: fixed;
            padding: 30px 0;
        }

        .sidebar-brand {
            padding: 0 30px 40px;
            text-align: center;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li {
            padding: 5px 20px;
        }

        .sidebar-menu a {
            color: #94a3b8;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            border-radius: 10px;
            transition: all 0.2s;
            font-weight: 500;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar-menu a.active {
            background: var(--primary);
        }

        .main-content {
            margin-left: 260px;
            flex-grow: 1;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 30px;
            border-radius: 20px;
            color: white;
            margin-bottom: 40px;
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2);
            position: relative;
            overflow: hidden;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: #eff6ff;
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .course-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            transition: transform 0.2s;
            border: 1px solid #e2e8f0;
        }

        .course-card:hover {
            transform: translateY(-5px);
        }

        .course-img {
            height: 160px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .course-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .course-body {
            padding: 20px;
        }

        .course-title {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--text);
        }

        .course-meta {
            color: var(--text-light);
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        .play-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .play-btn:hover {
            background: var(--secondary);
        }

        .no-courses {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 20px;
            grid-column: 1 / -1;
        }

        .badge-new {
            background: #dcfce7;
            color: #166534;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="../../LogoEdu.png" width="120" alt="3edu+">
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" class="active"><i class="fas fa-columns"></i> Dashboard</a></li>
            <li><a href="../../formation.php"><i class="fas fa-book"></i> Catalogue</a></li>
            <li><a href="#"><i class="fas fa-certificate"></i> Certificats</a></li>
            <li><a href="#"><i class="fas fa-wallet"></i> Paiements</a></li>
            <li style="margin-top: 40px;"><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <div>
                <h1 style="margin: 0; font-size: 1.8rem;">Mon Dashboard</h1>
                <p style="color: var(--text-light); margin: 5px 0 0;">Heureux de vous revoir, <?php echo htmlspecialchars($user_name); ?> !</p>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="text-align: right;">
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($user_name); ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-light);">Apprenant</div>
                </div>
                <div style="width: 45px; height: 45px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--primary);">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="welcome-card">
            <h2 style="margin: 0 0 10px;">Continuez votre apprentissage !</h2>
            <p style="margin: 0; opacity: 0.9; max-width: 500px;">Développez vos compétences chaque jour et atteignez vos objectifs professionnels.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-book-open"></i></div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $total_courses; ?></div>
                    <div style="color: var(--text-light); font-size: 0.85rem;">Formations suivies</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fef3c7; color: #b45309;"><i class="fas fa-clock"></i></div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;">--</div>
                    <div style="color: var(--text-light); font-size: 0.85rem;">Heures d'étude</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #dcfce7; color: #166534;"><i class="fas fa-award"></i></div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;">0</div>
                    <div style="color: var(--text-light); font-size: 0.85rem;">Certificats obtenus</div>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 25px;">Mes Formations</h3>
        <div class="courses-grid">
            <?php if (count($enrolled_courses) > 0): ?>
                <?php foreach($enrolled_courses as $c): ?>
                    <div class="course-card">
                        <div class="course-img">
                            <img src="../../<?php echo htmlspecialchars($c['formationImageUrl'] ?: 'logo.png'); ?>" alt="Course" onerror="this.src='../../logo.png'">
                        </div>
                        <div class="course-body">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                <span style="font-size: 0.75rem; color: var(--primary); font-weight: 600; text-transform: uppercase;"><?php echo htmlspecialchars($c['categorie']); ?></span>
                                <?php if (strtotime($c['date_paiement']) > strtotime('-7 days')): ?>
                                    <span class="badge-new">Nouveau</span>
                                <?php endif; ?>
                            </div>
                            <div class="course-title"><?php echo htmlspecialchars($c['titre']); ?></div>
                            <div class="course-meta">
                                <i class="fas fa-calendar-alt"></i> Inscrit le <?php echo date('d/m/Y', strtotime($c['date_paiement'])); ?>
                            </div>
                            <a href="../../course_player.php?id=<?php echo $c['formation_id']; ?>" class="play-btn">
                                <i class="fas fa-play-circle"></i> Commencer
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-courses">
                    <i class="fas fa-book-reader" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 20px; display: block;"></i>
                    <h3 style="margin: 0 0 10px;">Vous n'êtes inscrit à aucune formation</h3>
                    <p style="color: var(--text-light); margin-bottom: 25px;">Explorez notre catalogue pour trouver la formation qui vous convient.</p>
                    <a href="../../formation.php" class="play-btn" style="display: inline-flex; width: auto; padding: 12px 30px;">Voir le catalogue</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
