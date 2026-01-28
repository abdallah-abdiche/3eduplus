<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

checkAuth();

$formation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$video_id = isset($_GET['video_id']) ? intval($_GET['video_id']) : 0;

if ($formation_id <= 0) {
    header("Location: formation.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'Admin' || $_SESSION['user_role'] == 'Administrateur'));

// 1. Check access
$has_access = $is_admin;
if (!$has_access) {
    $access_stmt = $conn->prepare("
        SELECT 1 FROM paiements p 
        JOIN paiement_formations pf ON p.paiement_id = pf.paiement_id 
        WHERE p.user_id = ? AND pf.formation_id = ? AND p.statut = 'paid'
        UNION
        SELECT 1 FROM inscriptions WHERE user_id = ? AND formation_id = ?
    ");
    try {
        $access_stmt->bind_param("iiii", $user_id, $formation_id, $user_id, $formation_id);
        $access_stmt->execute();
        $has_access = $access_stmt->get_result()->num_rows > 0;
    } catch (Exception $e) {
        $access_stmt = $conn->prepare("SELECT 1 FROM paiements p JOIN paiement_formations pf ON p.paiement_id = pf.paiement_id WHERE p.user_id = ? AND pf.formation_id = ? AND p.statut = 'paid'");
        $access_stmt->bind_param("ii", $user_id, $formation_id);
        $access_stmt->execute();
        $has_access = $access_stmt->get_result()->num_rows > 0;
    }
}
if (!$has_access) { header("Location: formation.php?error=access_denied"); exit(); }

// 2. Fetch Data
$course_stmt = $conn->prepare("SELECT * FROM formations WHERE formation_id = ?");
$course_stmt->bind_param("i", $formation_id);
$course_stmt->execute();
$course = $course_stmt->get_result()->fetch_assoc();

$playlist_res = $conn->query("SELECT * FROM course_videos WHERE formation_id = $formation_id ORDER BY orders ASC, id ASC");
$playlist = $playlist_res->fetch_all(MYSQLI_ASSOC);

// Fetch Completed Videos
$completed_res = $conn->query("SELECT video_id FROM user_progress WHERE user_id = $user_id AND formation_id = $formation_id");
$completed_videos = [];
while($row = $completed_res->fetch_assoc()) $completed_videos[] = $row['video_id'];

// Check if all completed
$is_fully_completed = (count($playlist) > 0 && count($completed_videos) >= count($playlist));

// 3. Determine current video
$current_video = null;
if ($video_id > 0) {
    foreach ($playlist as $v) { if ($v['id'] == $video_id) { $current_video = $v; break; } }
}
if (!$current_video && count($playlist) > 0) $current_video = $playlist[0];

function getEmbedUrl($url) {
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        $id = $match[1] ?? '';
        return "https://www.youtube.com/embed/$id?autoplay=1&enablejsapi=1";
    }
    return $url;
}
$is_youtube = (strpos($current_video['video_url'] ?? '', 'youtube.com') !== false || strpos($current_video['video_url'] ?? '', 'youtu.be') !== false);

// Handle local video path
$video_src = $current_video['video_url'] ?? '';
if (!$is_youtube && $video_src && strpos($video_src, 'http') !== 0) {
    $video_src = "/3eduplus/" . $video_src;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($course['titre']); ?> - Player</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: 'Inter', sans-serif; height: 100vh; display: flex; flex-direction: column; overflow: hidden; margin: 0; }
        .player-header { background: #1e293b; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; height: 60px; box-sizing: border-box; }
        .main-layout { display: grid; grid-template-columns: 1fr 380px; flex-grow: 1; overflow: hidden; }
        .video-container { background: #000; display: flex; flex-direction: column; position: relative; }
        .video-wrapper { flex-grow: 1; position: relative; }
        .video-wrapper iframe, .video-wrapper video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        .video-info-bar { padding: 20px; background: #1e293b; border-top: 1px solid #334155; }
        .playlist-sidebar { background: #1e293b; border-left: 1px solid #334155; display: flex; flex-direction: column; }
        .playlist-header { padding: 20px; border-bottom: 1px solid #334155; }
        .playlist-items { flex-grow: 1; overflow-y: auto; }
        .playlist-item { padding: 15px 20px; display: flex; gap: 15px; cursor: pointer; border-bottom: 1px solid #334155; transition: background 0.2s; text-decoration: none; color: inherit; align-items: center; }
        .playlist-item:hover { background: #334155; }
        .playlist-item.active { background: #4f46e5; }
        .playlist-item.completed .check-mark { color: #10b981; }
        .check-mark { font-size: 1.2rem; color: #475569; }
        .active .check-mark { color: #fff !important; }
        .btn-cert { background: #10b981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; display: none; }
        .quiz-banner { background: #4f46e5; padding: 15px; text-align: center; border-radius: 10px; margin: 10px; display: <?php echo $is_fully_completed ? 'block' : 'none'; ?>; }
    </style>
</head>
<body>
    <header class="player-header">
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="formation.php" style="color:#94a3b8; text-decoration:none;"><i class="fas fa-arrow-left"></i> Retour</a>
            <span style="font-weight: 600;"><?php echo htmlspecialchars($course['titre']); ?></span>
        </div>
        <div id="cert_header_btn" style="display: <?php echo $is_fully_completed ? 'block' : 'none'; ?>;">
            <a href="quiz.php?id=<?php echo $formation_id; ?>" class="btn-cert" style="display: block;"><i class="fas fa-award"></i> Passer le Quiz Final</a>
        </div>
    </header>

    <div class="main-layout">
        <div class="video-container">
            <div class="video-wrapper">
                <?php if ($current_video): ?>
                    <?php if ($is_youtube): ?>
                        <iframe id="yt-player" src="<?php echo getEmbedUrl($current_video['video_url']); ?>" frameborder="0" allowfullscreen></iframe>
                    <?php else: ?>
                        <video id="local-player" controls autoplay onended="markAsDone(<?php echo $current_video['id']; ?>)">
                            <source src="<?php echo htmlspecialchars($video_src); ?>" type="video/mp4">
                            Votre navigateur ne supporte pas la balise vidéo.
                        </video>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="video-info-bar">
                <h1 style="font-size: 1.2rem; margin: 0 0 10px;"><?php echo htmlspecialchars($current_video['title'] ?? 'Intro'); ?></h1>
                <button onclick="markAsDone(<?php echo $current_video['id']; ?>)" id="manual-done-btn" style="background: #334155; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
                    <i class="fas fa-check"></i> Marquer comme terminé
                </button>
            </div>
        </div>

        <aside class="playlist-sidebar">
            <div class="quiz-banner" id="quiz-banner">
                <p style="margin: 0 0 10px; font-weight: 600;">🎉 Cours terminé !</p>
                <a href="quiz.php?id=<?php echo $formation_id; ?>" style="color: white; text-decoration: underline; font-weight: bold;">Accéder au Quiz Final</a>
            </div>
            <div class="playlist-header"><h3>Contenu du cours</h3></div>
            <div class="playlist-items" id="playlist-list">
                <?php foreach ($playlist as $v): $done = in_array($v['id'], $completed_videos); ?>
                    <a href="?id=<?php echo $formation_id; ?>&video_id=<?php echo $v['id']; ?>" class="playlist-item <?php echo $v['id'] == ($current_video['id'] ?? -1) ? 'active' : ''; ?> <?php echo $done ? 'completed' : ''; ?>" id="vid-<?php echo $v['id']; ?>">
                        <div class="check-mark"><i class="fas <?php echo $done ? 'fa-check-circle' : 'fa-circle'; ?>"></i></div>
                        <div class="item-content">
                            <div class="item-title"><?php echo htmlspecialchars($v['title']); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>

    <script>
        function markAsDone(videoId) {
            const formData = new FormData();
            formData.append('video_id', videoId);
            formData.append('formation_id', <?php echo $formation_id; ?>);

            fetch('mark_video_completed.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const item = document.getElementById('vid-' + videoId);
                    if(item) {
                        item.classList.add('completed');
                        item.querySelector('.check-mark i').className = 'fas fa-check-circle';
                    }
                    checkAllVideosDone();
                }
            });
        }

        function checkAllVideosDone() {
            const total = document.querySelectorAll('.playlist-item').length;
            const completed = document.querySelectorAll('.playlist-item.completed').length;
            if (total > 0 && completed >= total) {
                document.getElementById('cert_header_btn').style.display = 'block';
                document.getElementById('quiz-banner').style.display = 'block';
            }
        }

        // YouTube API support to auto-mark done
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        var player;
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('yt-player', {
                events: { 'onStateChange': onPlayerStateChange }
            });
        }
        function onPlayerStateChange(event) {
            if (event.data == YT.PlayerState.ENDED) {
                markAsDone(<?php echo $current_video['id'] ?? 0; ?>);
            }
        }
    </script>
</body>
</html>
