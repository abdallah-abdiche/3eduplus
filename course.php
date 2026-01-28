<?php
session_start();
require_once "config.php";
require_once "auth.php";

checkAuth();
$user = getCurrentUser();
$user_id = $user['id'];

// Get formation ID from URL
$formation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($formation_id === 0) {
    header('Location: inscription.php');
    exit();
}

// Check if user is enrolled in this course
$check_query = "SELECT i.*, f.* FROM inscriptions i 
                JOIN formations f ON i.formation_id = f.formation_id 
                WHERE i.user_id = ? AND i.formation_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $user_id, $formation_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    // User not enrolled - redirect
    $_SESSION['error'] = "Vous n'êtes pas inscrit à cette formation.";
    header('Location: inscription.php');
    exit();
}

$course = $result->fetch_assoc();
$check_stmt->close();

// Get cart count
$cart_count = 0;
$count_sql = "SELECT COUNT(*) as count FROM panier WHERE utilisateur_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
if ($count_result->num_rows > 0) {
    $cart_count = $count_result->fetch_assoc()['count'];
}
$count_stmt->close();

// Fetch quiz
$quiz_query = "SELECT * FROM quizzes WHERE formation_id = ?";
$q_stmt = $conn->prepare($quiz_query);
$q_stmt->bind_param("i", $formation_id);
$q_stmt->execute();
$quiz = $q_stmt->get_result()->fetch_assoc();
$q_stmt->close();

// Fetch course videos
$videos_query = "SELECT * FROM course_videos WHERE formation_id = ? ORDER BY orders ASC";
$v_stmt = $conn->prepare($videos_query);
$v_stmt->bind_param("i", $formation_id);
$v_stmt->execute();
$videos = $v_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$v_stmt->close();

// Fetch progress
$watched_query = "SELECT video_id FROM user_video_progress WHERE user_id = ? AND video_type = 'course'";
$w_stmt = $conn->prepare($watched_query);
$w_stmt->bind_param("i", $user_id);
$w_stmt->execute();
$watched_result = $w_stmt->get_result();
$watched_ids = [];
while ($row = $watched_result->fetch_assoc()) {
    $watched_ids[] = $row['video_id'];
}
$w_stmt->close();

// Calculate progress percentage
$total_videos = count($videos);
$watched_count = 0;
foreach($videos as $v) {
    if (in_array($v['id'], $watched_ids)) $watched_count++;
}
$progress_percent = $total_videos > 0 ? round(($watched_count / $total_videos) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['titre']); ?> - 3edu+</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="./LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .course-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #007bff;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: #0056b3;
        }
        
        .course-header {
            background: linear-gradient(135deg, #1a6dcc 0%, #022d63 100%);
            padding: 40px;
            border-radius: 16px;
            color: white;
            margin-bottom: 30px;
        }
        .course-header h1 {
            font-size: 2rem;
            margin: 0 0 15px 0;
            line-height: 1.3;
        }
        .course-meta {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            opacity: 0.9;
        }
        .meta-item i {
            font-size: 1.1rem;
        }
        
        .video-container {
            background: #000;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
        }
        .video-wrapper iframe,
        .video-wrapper video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .no-video {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 100px 20px;
            color: #666;
            background: #f8f9fa;
        }
        .no-video i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #ccc;
        }
        
        .course-content-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .course-description {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .course-description h2 {
            font-size: 1.5rem;
            margin: 0 0 20px 0;
            color: #022d63;
        }
        .course-description p {
            line-height: 1.8;
            color: #555;
        }
        
        .course-sidebar {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            height: fit-content;
        }
        .sidebar-section {
            margin-bottom: 25px;
        }
        .sidebar-section:last-child {
            margin-bottom: 0;
        }
        .sidebar-section h3 {
            font-size: 1.1rem;
            margin: 0 0 15px 0;
            color: #022d63;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-section h3 i {
            color: #007bff;
        }
        
        .progress-bar {
            background: #e9ecef;
            border-radius: 10px;
            height: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        .progress-fill {
            background: linear-gradient(90deg, #28a745, #20c997);
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        .progress-text {
            font-size: 0.9rem;
            color: #666;
        }
        
        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .info-list li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #555;
        }
        .info-list li:last-child {
            border-bottom: none;
        }
        .info-list i {
            color: #007bff;
            width: 20px;
            text-align: center;
        }
        
        .certificate-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #c9a227 0%, #f0d060 50%, #c9a227 100%);
            color: #1a1a1a;
            text-decoration: none;
            padding: 15px 25px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(201, 162, 39, 0.3);
        }
        .certificate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(201, 162, 39, 0.4);
        }
        .certificate-btn i {
            font-size: 1.2rem;
        }
        
        .enrolled-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        @media (max-width: 900px) {
            .course-content-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <header class="header-nav">
        <div class="logocontainer">
            <a href="index.php"><img src="./LogoEdu.png" width="150" height="100" alt="3edu+ Logo"></a>
        </div>

        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="formation.php">Formations</a></li>
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="about.php">À propos</a></li>
                <li><a href="inscription.php" class="active">Inscriptions</a></li>
            </ul>
        </nav>

        <div class="nav-actions">
            <form action="search_results.php" method="GET" class="search-container">
                <input type="text" name="q" placeholder="Rechercher des formations..." class="search-input">
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
            <div class="user-menu">
                <button class="user-btn">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($user['name']); ?>
                </button>
                <div class="user-dropdown">
                    <a href="<?php echo getDashboardUrl($user['role']); ?>">Mon Tableau de bord</a>
                    <a href="logout.php">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <div class="course-page">
        <a href="inscription.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour à mes formations
        </a>
        
        <div class="course-header">
            <span class="enrolled-badge">
                <i class="fas fa-check-circle"></i> Inscrit
            </span>
            <h1><?php echo htmlspecialchars($course['titre']); ?></h1>
            <div class="course-meta">
                <div class="meta-item">
                    <i class="fas fa-layer-group"></i>
                    <span><?php echo htmlspecialchars($course['niveau'] ?? 'Tous niveaux'); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <span><?php echo htmlspecialchars($course['duree'] ?? 'Non spécifié'); ?> heures</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-folder"></i>
                    <span><?php echo htmlspecialchars($course['categorie'] ?? 'Général'); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Inscrit le <?php echo date('d/m/Y', strtotime($course['date_inscription'])); ?></span>
                </div>
            </div>
        </div>

        <div class="video-container">
            <?php if (!empty($videos)): ?>
                <div class="video-wrapper" id="video-wrapper">
                    <?php 
                    $active_video = $videos[0];
                    $video_url = $active_video['video_url'];
                    // Track first video automatically
                    ?>
                    <script>
                        function trackVideo(id) {
                            fetch('track_progress.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `video_id=${id}&type=course`
                            }).then(res => res.json()).then(data => {
                                if(data.success) {
                                    // Refresh progress bar in UI
                                }
                            });
                        }
                        
                        function loadVideo(url, id, title) {
                            const wrapper = document.getElementById('video-wrapper');
                            let html = '';
                            if (url.includes('youtube.com') || url.includes('youtu.be')) {
                                let ytId = url.split('v=')[1] || url.split('/').pop();
                                html = `<iframe src="https://www.youtube.com/embed/${ytId}" frameborder="0" allowfullscreen></iframe>`;
                            } else {
                                html = `<video controls autoplay onplay="trackVideo(${id})"><source src="${url}" type="video/mp4"></video>`;
                            }
                            wrapper.innerHTML = html;
                            // If iframe, track immediately on click
                            if(url.includes('youtube.com')) trackVideo(id);
                            
                            document.getElementById('current-video-title').innerText = title;
                        }
                    </script>
                    <?php
                    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $video_url, $matches)) {
                        $youtube_id = $matches[1];
                        echo '<iframe src="https://www.youtube.com/embed/' . htmlspecialchars($youtube_id) . '" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>';
                    } else {
                        echo '<video controls onplay="trackVideo('.$active_video['id'].')"><source src="' . htmlspecialchars($video_url) . '" type="video/mp4"></video>';
                    }
                    ?>
                </div>
            <?php else: ?>
                <div class="no-video">
                    <i class="fas fa-video-slash"></i>
                    <h3>Vidéo non disponible</h3>
                    <p>La vidéo de cette formation sera bientôt disponible.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="course-content-section">
            <div class="course-description">
                <h2><i class="fas fa-info-circle"></i> Description du cours</h2>
                <h3 id="current-video-title" style="color: #007bff; margin: 10px 0;"><?php echo !empty($videos) ? htmlspecialchars($videos[0]['title']) : ''; ?></h3>
                <p><?php echo nl2br(htmlspecialchars($course['description'] ?? 'Aucune description disponible pour cette formation.')); ?></p>
            </div>

            <div class="course-sidebar">
                <div class="sidebar-section">
                    <h3><i class="fas fa-chart-line"></i> Votre progression</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%;"></div>
                    </div>
                    <p class="progress-text"><?php echo $progress_percent; ?>% complété</p>
                </div>

                <?php if (!empty($videos)): ?>
                <div class="sidebar-section">
                    <h3><i class="fas fa-play-circle"></i> Playlist</h3>
                    <div class="playlist-container" style="max-height: 250px; overflow-y: auto;">
                        <?php foreach($videos as $v): ?>
                            <div onclick="loadVideo('<?php echo $v['video_url']; ?>', <?php echo $v['id']; ?>, '<?php echo addslashes($v['title']); ?>')" 
                                 style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; <?php echo in_array($v['id'], $watched_ids) ? 'color: #28a745;' : ''; ?>">
                                <i class="<?php echo in_array($v['id'], $watched_ids) ? 'fas fa-check-circle' : 'far fa-play-circle'; ?>"></i>
                                <span><?php echo htmlspecialchars($v['title']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="sidebar-section">
                    <h3><i class="fas fa-certificate"></i> Certificat</h3>
                    <?php if ($progress_percent >= 100): ?>
                        <a href="generate_certificate.php?id=<?php echo $formation_id; ?>" class="certificate-btn">
                            <i class="fas fa-award"></i> Obtenir mon certificat
                        </a>
                    <?php else: ?>
                        <div class="certificate-btn" style="background: #e2e8f0; color: #94a3b8; cursor: not-allowed; box-shadow: none;">
                            <i class="fas fa-lock"></i> Complétez les vidéos
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($quiz): ?>
                <div class="sidebar-section">
                    <h3><i class="fas fa-question-circle"></i> Évaluation</h3>
                    <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="certificate-btn" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
                        <i class="fas fa-play-circle"></i> Commencer le Quiz
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer" style="margin-top: 50px;">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="./LogoEdu.png" alt="3edu+ Logo" width="150" height="100">
                    <p>Votre partenaire de formation professionnelle.</p>
                </div>
            </div>
            <div class="footer-section">
                <h3>Liens rapides</h3>
                <ul class="footer-links">
                    <li><a href="formation.php">Nos formations</a></li>
                    <li><a href="evenements.php">Événements</a></li>
                    <li><a href="about.php">À propos</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; 2025 3edu+ Centre de Formation.</p>
            </div>
        </div>
    </footer>
   <script>
    document.querySelector('.user-btn').addEventListener('click', function(e){
      e.stopPropagation();
      this.parentElement.classList.toggle('open');
    });
    document.addEventListener('click', () =>
      document.querySelector('.user-menu').classList.remove('open')
    );
    </script>
</body>
</html>