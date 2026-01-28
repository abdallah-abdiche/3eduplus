<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin']);

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $titre = $conn->real_escape_string($_POST['titre']);
    $description = $conn->real_escape_string($_POST['description']);
    $categorie = $conn->real_escape_string($_POST['categorie']);
    $prix = $conn->real_escape_string($_POST['prix']);
    $duree = $conn->real_escape_string($_POST['duree']);
    $niveau = $conn->real_escape_string($_POST['niveau']);
    
    // --- QUERY PREPARATION ---
    $formationImageUrl = "";
    $video_url = "";
    
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
            } else {
                $message .= " Error uploading image file.";
            }
        } else {
            $message .= " Invalid image format.";
        }
    } elseif (!empty($_POST['formation_image_url'])) {
        $formationImageUrl = $conn->real_escape_string($_POST['formation_image_url']);
    } else {
        // Default image if nothing provided
        $formationImageUrl = "logo.png"; 
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
            } else {
                $message .= " Error uploading video file.";
            }
        } else {
            $message .= " Invalid video format.";
        }
    } elseif (!empty($_POST['video_url_input'])) {
        $video_url = $conn->real_escape_string($_POST['video_url_input']);
    }

    // Insert
    if (empty($message)) {
        $sql = "INSERT INTO formations (titre, description, categorie, prix, duree, niveau, formationImageUrl, video_url) 
                VALUES ('$titre', '$description', '$categorie', '$prix', '$duree', '$niveau', '$formationImageUrl', '$video_url')";

        if ($conn->query($sql) === TRUE) {
            header("Location: courses.php?msg=created");
            exit();
        } else {
            $message = "Database Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - Admin Dashboard</title>
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
                <div class="form-container" style="max-width: 800px; margin: 0 auto; background: white; padding: 35px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div class="form-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                        <div>
                            <h2 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 5px;">Create New Course</h2>
                            <p style="color: #64748b; font-size: 0.9rem;">Fill in the details to launch a new training program.</p>
                        </div>
                        <a href="courses.php" class="btn btn-danger" style="background: #fee2e2; color: #ef4444;"><i class="fas fa-arrow-left"></i> Back</a>
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
                                <input type="text" name="titre" required>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="categorie" placeholder="e.g. Web Development" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" required rows="4"></textarea>
                        </div>

                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 15px;">
                            <div class="form-group">
                                <label>Price (DA)</label>
                                <input type="number" step="0.01" name="prix" required>
                            </div>
                            <div class="form-group">
                                <label>Duration</label>
                                <input type="text" name="duree" placeholder="e.g. 15h 30m">
                            </div>
                            <div class="form-group">
                                <label>Level</label>
                                <select name="niveau">
                                    <option value="Débutant">Beginner</option>
                                    <option value="Intermédiaire">Intermediate</option>
                                    <option value="Avancé">Advanced</option>
                                </select>
                            </div>
                        </div>

                        <!-- IMAGE UPLOAD SECTION -->
                        <div class="form-group" style="margin-top: 30px; padding: 25px; background: #fafafa; border-radius: 12px; border: 1px solid #f1f5f9;">
                            <label style="display: block; margin-bottom: 15px; font-weight: 700; color: #334155;"><i class="fas fa-image" style="color: #4f46e5;"></i> Course Image</label>
                            
                            <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                                <label style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Option 1: Upload File</label>
                                <input type="file" name="image_file" accept="image/*" style="width: 100%;">
                            </div>
                            
                            <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <label style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Option 2: Image URL</label>
                                <input type="text" name="formation_image_url" placeholder="https://example.com/banner.jpg" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                        </div>

                        <!-- VIDEO UPLOAD SECTION -->
                        <div class="form-group" style="margin-top: 25px; padding: 25px; background: #fafafa; border-radius: 12px; border: 1px solid #f1f5f9;">
                            <label style="display: block; margin-bottom: 15px; font-weight: 700; color: #334155;"><i class="fas fa-video" style="color: #4f46e5;"></i> Course Overview Video (Optional)</label>
                            
                            <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                                <label style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Option 1: Upload Video File</label>
                                <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg" style="width: 100%;">
                            </div>

                            <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <label style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Option 2: Video URL</label>
                                <input type="text" name="video_url_input" placeholder="https://youtube.com/..." style="width: 100%; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                        </div>

                        <div style="margin-top: 40px; border-top: 1px solid #f1f5f9; padding-top: 25px; text-align: right;">
                             <button type="submit" name="add_course" class="btn btn-primary" style="background: #4f46e5; padding: 12px 30px; font-weight: 600; border-radius: 10px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                                <i class="fas fa-plus-circle"></i> Create Course
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
