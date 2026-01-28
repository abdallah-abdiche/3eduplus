<?php
session_start();
require_once "config.php";
require_once "auth.php";

checkAuth();
$user = getCurrentUser();
$user_id = $user['id'];

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($event_id === 0) {
    header('Location: inscription.php');
    exit();
}

// Check enrollment
$check_query = "SELECT ei.*, e.* FROM event_inscriptions ei 
                JOIN evenements e ON ei.evenement_id = e.evenement_id 
                WHERE ei.user_id = ? AND ei.evenement_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $user_id, $event_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header('Location: inscription.php');
    exit();
}

$event = $res->fetch_assoc();
$stmt->close();

// Fetch videos
$video_query = "SELECT * FROM evenement_videos WHERE evenement_id = ? ORDER BY ordre ASC";
$v_stmt = $conn->prepare($video_query);
$v_stmt->bind_param("i", $event_id);
$v_stmt->execute();
$videos = $v_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$v_stmt->close();

// Fetch quiz
$quiz_query = "SELECT * FROM quizzes WHERE evenement_id = ?";
$q_stmt = $conn->prepare($quiz_query);
$q_stmt->bind_param("i", $event_id);
$q_stmt->execute();
$quiz = $q_stmt->get_result()->fetch_assoc();
$q_stmt->close();

$cart_count = 0; // Not critical here
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['titre']); ?> - Contenu</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .event-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        .video-player { width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 12px; overflow: hidden; margin-bottom: 30px; }
        .playlist { background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; }
        .playlist h3 { margin-bottom: 15px; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        .video-item { display: flex; align-items: center; gap: 15px; padding: 12px; border-radius: 8px; cursor: pointer; transition: 0.3s; margin-bottom: 8px; border: 1px solid transparent; }
        .video-item:hover { background: #ecfdf5; border-color: #28a745; }
        .video-item.active { background: #28a745; color: white; }
        .quiz-section { margin-top: 40px; text-align: center; padding: 40px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; border-radius: 16px; display: none; }
        .quiz-btn { display: inline-block; background: white; color: #28a745; padding: 15px 40px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 1.1rem; transition: 0.3s; }
        .quiz-btn:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <header class="header-nav">
        <!-- Re-use simple header -->
        <div class="logocontainer"><a href="index.php"><img src="./LogoEdu.png" width="120"></a></div>
        <nav><ul class="nav-links"><li><a href="inscription.php">Retour à mes inscriptions</a></li></ul></nav>
    </header>

    <div class="event-container">
        <h1><?php echo htmlspecialchars($event['titre']); ?></h1>
        <p style="color: #64748b; margin-bottom: 30px;">Date de l'événement: <?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?></p>

        <div class="video-player" id="main-player">
            <?php if (!empty($videos)): ?>
                <iframe width="100%" height="100%" src="<?php echo $videos[0]['video_url']; ?>" frameborder="0" allowfullscreen></iframe>
            <?php else: ?>
                <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: white;">
                    <p>Aucune vidéo disponible pour cet événement.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="playlist">
            <h3>Liste des vidéos</h3>
            <?php if (!empty($videos)): ?>
                <div id="video-list">
                    <?php foreach ($videos as $index => $v): ?>
                        <div class="video-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                             onclick="playVideo('<?php echo $v['video_url']; ?>', <?php echo $index; ?>, <?php echo count($videos); ?>)">
                            <i class="fas fa-play-circle"></i>
                            <span><?php echo htmlspecialchars($v['titre']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>La playlist est vide.</p>
            <?php endif; ?>
        </div>

        <?php if ($quiz): ?>
            <div id="quiz-block" class="quiz-section">
                <h2>Félicitations !</h2>
                <p>Vous avez terminé toutes les vidéos. Êtes-vous prêt pour le quiz ?</p>
                <div style="margin-top: 20px;">
                    <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="quiz-btn">Commencer le Quiz</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        let videosSeen = new Set();
        videosSeen.add(0);

        function playVideo(url, index, total) {
            const player = document.getElementById('main-player');
            player.innerHTML = `<iframe width="100%" height="100%" src="${url}" frameborder="0" allowfullscreen></iframe>`;
            
            // Update active state
            document.querySelectorAll('.video-item').forEach((item, i) => {
                item.classList.toggle('active', i === index);
            });

            videosSeen.add(index);
            
            // Show quiz button if all videos seen (simulated)
            if (videosSeen.size === total) {
                const quizBlock = document.getElementById('quiz-block');
                if (quizBlock) quizBlock.style.display = 'block';
            }
        }
        
        // Auto-show quiz if only 1 video or already finished
        window.onload = () => {
            if (<?php echo count($videos); ?> === 1 && document.getElementById('quiz-block')) {
                document.getElementById('quiz-block').style.display = 'block';
            }
        };
    </script>
</body>
</html>
