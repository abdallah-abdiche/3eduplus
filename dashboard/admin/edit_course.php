<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin']);

$message = "";
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id <= 0) {
    header("Location: courses.php");
    exit();
}

// Fetch course data
$stmt = $conn->prepare("SELECT * FROM formations WHERE formation_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: courses.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_course'])) {
    $titre = $conn->real_escape_string($_POST['titre']);
    $description = $conn->real_escape_string($_POST['description']);
    $categorie = $conn->real_escape_string($_POST['categorie']);
    $prix = $conn->real_escape_string($_POST['prix']);
    $duree = $conn->real_escape_string($_POST['duree']);
    $niveau = $conn->real_escape_string($_POST['niveau']);
    
    $formationImageUrl = $course['formationImageUrl'];
    $video_url = $course['video_url'];
    
    // --- HANDLE IMAGE UPLOAD OR URL ---
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $target_dir_img = "../../uploads/courses/";
        if (!file_exists($target_dir_img)) {
            mkdir($target_dir_img, 0777, true);
        }
        $file_extension = pathinfo($_FILES["image_file"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . "_img." . $file_extension;
        $target_file = $target_dir_img . $file_name;
        
        $allowed_img = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($file_extension), $allowed_img)) {
            if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
                $formationImageUrl = "uploads/courses/" . $file_name;
            }
        }
    } elseif (!empty($_POST['formation_image_url'])) {
        $formationImageUrl = $conn->real_escape_string($_POST['formation_image_url']);
    }

    // --- HANDLE VIDEO UPLOAD OR URL ---
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $target_dir_vid = "../../uploads/videos/";
        if (!file_exists($target_dir_vid)) {
            mkdir($target_dir_vid, 0777, true);
        }
        $file_extension = pathinfo($_FILES["video_file"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . "_video." . $file_extension;
        $target_file = $target_dir_vid . $file_name;
        
        $allowed_vid = ['mp4', 'webm', 'ogg'];
        if (in_array(strtolower($file_extension), $allowed_vid)) {
            if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_file)) {
                $video_url = "uploads/videos/" . $file_name;
            }
        }
    } elseif (!empty($_POST['video_url_input'])) {
        $video_url = $conn->real_escape_string($_POST['video_url_input']);
    }

    // Update
    $update_stmt = $conn->prepare("UPDATE formations SET titre=?, description=?, categorie=?, prix=?, duree=?, niveau=?, formationImageUrl=?, video_url=? WHERE formation_id=?");
    $update_stmt->bind_param("sss d sss i", $titre, $description, $categorie, $prix, $duree, $niveau, $formationImageUrl, $video_url, $course_id);

    if ($update_stmt->execute()) {
        header("Location: courses.php?msg=updated");
        exit();
    } else {
        $message = "Database Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="CRUD.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <?php include 'header.php'; ?>

            <div class="dashboard-content">
                <div class="form-container" style="max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div class="form-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-bottom: 30px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                        <div style="flex: 1;">
                            <h2 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 5px;">Edit Course</h2>
                            <p style="color: #64748b; font-size: 0.95rem; line-height: 1.4;"><?php echo htmlspecialchars($course['titre']); ?></p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <a href="manage_playlist.php?id=<?php echo $course_id; ?>" class="btn" style="background: #f1f5f9; color: #4f46e5; border: 1px solid #e2e8f0;"><i class="fas fa-play-circle"></i> Playlist</a>
                            <a href="courses.php" class="btn btn-danger" style="background: #fee2e2; color: #ef4444;"><i class="fas fa-arrow-left"></i> Back</a>
                        </div>
                    </div>
                    
                    <?php if ($message): ?>
                        <div class="alert" style="padding: 15px; margin-bottom: 20px; background: #fee2e2; color: #991b1b; border-radius: 4px;">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="titre" value="<?php echo htmlspecialchars($course['titre']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="categorie" value="<?php echo htmlspecialchars($course['categorie']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 20px;">
                            <label style="font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Description</label>
                            <textarea name="description" required rows="4" style="border: 1px solid #e2e8f0; border-radius: 8px;"><?php echo htmlspecialchars($course['description']); ?></textarea>
                        </div>

                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 15px;">
                            <div class="form-group">
                                <label>Price (DA)</label>
                                <input type="number" step="0.01" name="prix" value="<?php echo htmlspecialchars($course['prix']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Duration</label>
                                <input type="text" name="duree" value="<?php echo htmlspecialchars($course['duree']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Level</label>
                                <select name="niveau">
                                    <option value="Débutant" <?php echo $course['niveau'] == 'Débutant' ? 'selected' : ''; ?>>Beginner</option>
                                    <option value="Intermédiaire" <?php echo $course['niveau'] == 'Intermédiaire' ? 'selected' : ''; ?>>Intermediate</option>
                                    <option value="Avancé" <?php echo $course['niveau'] == 'Avancé' ? 'selected' : ''; ?>>Advanced</option>
                                </select>
                            </div>
                        </div>

                        <!-- IMAGE UPLOAD SECTION -->
                        <div class="form-group" style="margin-top: 30px; padding: 25px; background: #fafafa; border-radius: 12px; border: 1px solid #f1f5f9; position: relative;">
                            <label style="display: block; margin-bottom: 15px; font-weight: 700; color: #334155; font-size: 1rem;"><i class="fas fa-image" style="color: #4f46e5;"></i> Course Image</label>
                            
                            <div style="display: flex; gap: 20px; align-items: flex-start;">
                                <?php if ($course['formationImageUrl']): ?>
                                    <div style="flex-shrink: 0; position: relative;">
                                        <img src="../../<?php echo htmlspecialchars($course['formationImageUrl']); ?>" style="width: 140px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);" onerror="this.src='../../logo.png'">
                                        <p style="text-align: center; font-size: 0.75rem; color: #94a3b8; margin-top: 5px;">Current Banner</p>
                                    </div>
                                <?php endif; ?>

                                <div style="flex-grow: 1;">
                                    <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                                        <label style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Option 1: Upload New Image</label>
                                        <input type="file" name="image_file" accept="image/*" class="custom-file-input">
                                    </div>
                                    
                                    <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <label style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Option 2: Image URL</label>
                                        <input type="text" name="formation_image_url" value="<?php echo (strpos($course['formationImageUrl'], 'http') === 0) ? htmlspecialchars($course['formationImageUrl']) : ''; ?>" placeholder="https://example.com/banner.jpg" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 6px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- VIDEO UPLOAD SECTION -->
                        <div class="form-group" style="margin-top: 25px; padding: 25px; background: #fafafa; border-radius: 12px; border: 1px solid #f1f5f9;">
                            <label style="display: block; margin-bottom: 15px; font-weight: 700; color: #334155; font-size: 1rem;"><i class="fas fa-video" style="color: #4f46e5;"></i> Course Overview Video (Optional)</label>
                            
                            <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                                <label style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Option 1: Upload Video File</label>
                                <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg" style="width: 100%;">
                                <?php if ($course['video_url'] && strpos($course['video_url'], 'uploads/') === 0): ?>
                                    <p style="font-size: 0.75rem; color: #10b981; margin-top: 5px;"><i class="fas fa-check-circle"></i> Currently: <?php echo basename($course['video_url']); ?></p>
                                <?php endif; ?>
                            </div>

                            <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <label style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Option 2: Video URL (YouTube/External)</label>
                                <input type="text" name="video_url_input" value="<?php echo (strpos($course['video_url'], 'http') === 0) ? htmlspecialchars($course['video_url']) : ''; ?>" placeholder="https://youtube.com/watch?v=..." style="width: 100%; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                        </div>

                        <div style="margin-top: 40px; border-top: 1px solid #f1f5f9; padding-top: 25px; text-align: right;">
                             <button type="submit" name="update_course" class="btn btn-primary" style="background: #4f46e5; padding: 12px 30px; font-weight: 600; font-size: 1rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                                <i class="fas fa-save"></i> Save All Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
