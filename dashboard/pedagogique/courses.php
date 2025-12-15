<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Pédagogique']);

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$message = "";

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'created') $message = "New course created successfully";
    if ($_GET['msg'] == 'deleted') $message = "Course deleted successfully";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $titre = $conn->real_escape_string($_POST['titre']);
    $description = $conn->real_escape_string($_POST['description']);
    $categorie = $conn->real_escape_string($_POST['categorie']);
    $prix = $conn->real_escape_string($_POST['prix']);
    $duree = $conn->real_escape_string($_POST['duree']);
    $niveau = $conn->real_escape_string($_POST['niveau']);
    
    $formationImageUrl = $conn->real_escape_string($_POST['formation_image_url']);
    
    $video_url = "";
    
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $target_dir = "../../uploads/videos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["video_file"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . "_video." . $file_extension;
        $target_file = $target_dir . $file_name;
        
        $allowed = ['mp4', 'webm', 'ogg'];
        if (in_array(strtolower($file_extension), $allowed)) {
            if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_file)) {
                $video_url = "uploads/videos/" . $file_name;
            } else {
                $message .= " Error uploading video file.";
            }
        } else {
            $message .= " Invalid video format. Allowed: mp4, webm, ogg.";
        }
    } elseif (!empty($_POST['video_url_input'])) {
        $video_url = $conn->real_escape_string($_POST['video_url_input']);
    }

    $sql = "INSERT INTO formations (titre, description, categorie, prix, duree, niveau, formationImageUrl, video_url, createur_id) 
            VALUES ('$titre', '$description', '$categorie', '$prix', '$duree', '$niveau', '$formationImageUrl', '$video_url', '$user_id')";

    if ($conn->query($sql) === TRUE) {
        header("Location: courses.php?msg=created");
        exit();
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Pedagogique Dashboard</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;
        }
        .btn-submit {
            background: #4caf50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;
        }
        .message { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .course-img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        
        .users-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .users-table th, .users-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .users-table th { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <img src="../../LogoEdu.png" alt="3edu+" class="logo">
                <span>3edu+ - Pédagogique</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Accueil
                </a>
                <a href="courses.php" class="nav-link active">
                    <i class="fas fa-book"></i> Mes Cours
                </a>
                <div class="nav-user">
                    <span><?php echo htmlspecialchars($user_name); ?></span>
                    <a href="../../logout.php" class="dropdown-item logout" style="margin-left: 10px; color: #e91e63;">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container dashboard-container" style="margin-top: 80px;">
        <header class="dashboard-header" style="margin-bottom: 20px;">
            <div class="header-left">
                <h1 class="header-logo">Gestion des Formations</h1>
            </div>
        </header>

        <div class="dashboard-content">
            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <h3>Ajouter une Nouvelle Formation</h3>
                <form method="POST" action="courses.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Titre</label>
                        <input type="text" name="titre" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" required rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Catégorie</label>
                        <input type="text" name="categorie" placeholder="ex. Développement Web" required>
                    </div>
                    <div class="form-group">
                        <label>Prix (DA)</label>
                        <input type="number" step="0.01" name="prix" required>
                    </div>
                    <div class="form-group">
                        <label>Durée</label>
                        <input type="text" name="duree" placeholder="ex. 10 heures">
                    </div>
                    <div class="form-group">
                        <label>Niveau</label>
                        <select name="niveau">
                            <option value="Débutant">Débutant</option>
                            <option value="Intermédiaire">Intermédiaire</option>
                            <option value="Avancé">Avancé</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Image URL</label>
                        <input type="text" name="formation_image_url" placeholder="https://example.com/image.jpg" required>
                    </div>
                    <div class="form-group">
                        <label>Vidéo du Cours (Optionnel)</label>
                        <p style="font-size: 0.9em; color: #666; margin-bottom: 5px;">Entrez une URL (YouTube) OU téléchargez un fichier.</p>
                        <input type="text" name="video_url_input" placeholder="https://youtube.com/..." style="margin-bottom: 10px;">
                        <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg">
                    </div>
                    <button type="submit" name="add_course" class="btn-submit">Créer la Formation</button>
                </form>
            </div>

            <div class="table-container">
                <h3>Mes Formations</h3>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Titre</th>
                            <th>Prix</th>
                            <th>Niveau</th>
                            <th>Vidéo</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM formations WHERE createur_id = '$user_id' ORDER BY date_creation DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $imgUrl = $row['formationImageUrl'];
                                if (strpos($imgUrl, 'http') === 0) {
                                    $imagePath = $imgUrl;
                                } elseif ($imgUrl) {
                                    $imagePath = "../../" . $imgUrl;
                                } else {
                                    $imagePath = "../../LogoEdu.png";
                                }
                                echo "<tr>";
                                echo "<td>" . $row['formation_id'] . "</td>";
                                echo "<td><img src='" . $imagePath . "' class='course-img-thumb'></td>";
                                echo "<td>" . htmlspecialchars($row['titre']) . "</td>";
                                echo "<td>" . $row['prix'] . " DA</td>";
                                echo "<td>" . $row['niveau'] . "</td>";
                                
                                $videoLink = "";
                                if (!empty($row['video_url'])) {
                                    $videoLink = "<a href='../../" . $row['video_url'] . "' target='_blank' style='color: #2563eb;'><i class='fas fa-video'></i> Voir</a>";
                                    if (strpos($row['video_url'], 'http') === 0) {
                                        $videoLink = "<a href='" . $row['video_url'] . "' target='_blank' style='color: #2563eb;'><i class='fas fa-video'></i> Voir</a>";
                                    }
                                } else {
                                    $videoLink = "<span style='color: #999;'>Aucune</span>";
                                }
                                echo "<td>" . $videoLink . "</td>";
                                
                                echo "<td>" . date('d/m/Y', strtotime($row['date_creation'])) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>Aucune formation trouvée. Ajoutez votre première formation !</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
