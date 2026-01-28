<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin']);

$formation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($formation_id <= 0) {
    header("Location: courses.php");
    exit();
}

// Fetch course details
$stmt = $conn->prepare("SELECT titre FROM formations WHERE formation_id = ?");
$stmt->bind_param("i", $formation_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
if (!$course) {
    header("Location: courses.php");
    exit();
}

$message = "";
$error = "";

// Handle add video
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_video'])) {
    $title = $conn->real_escape_string($_POST['video_title']);
    $order = intval($_POST['video_order']);
    $duration = $conn->real_escape_string($_POST['video_duration']);
    $video_url = "";

    if ($_POST['video_source'] == 'upload' && isset($_FILES['video_file'])) {
        // ... (rest of the upload logic is same)
        $file = $_FILES['video_file'];
        $upload_dir = "../../uploads/courses/videos/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_name = "vid_" . uniqid() . "." . $file_ext;
        $target_path = $upload_dir . $new_name;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $video_url = "uploads/courses/videos/" . $new_name;
        } else {
            $error = "Failed to upload video file.";
        }
    } else {
        $video_url = $conn->real_escape_string($_POST['video_url']);
    }

    if ($video_url) {
        $add_stmt = $conn->prepare("INSERT INTO course_videos (formation_id, title, video_url, duration, orders) VALUES (?, ?, ?, ?, ?)");
        $add_stmt->bind_param("isssi", $formation_id, $title, $video_url, $duration, $order);
        if ($add_stmt->execute()) {
            $message = "Video added successfully!";
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}

// Handle delete video
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Get file path to delete if it's an upload
    $stmt = $conn->prepare("SELECT video_url FROM course_videos WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $vid = $stmt->get_result()->fetch_assoc();
    if ($vid && strpos($vid['video_url'], 'uploads/') === 0) {
        $full_path = "../../" . $vid['video_url'];
        if (file_exists($full_path)) unlink($full_path);
    }

    $conn->query("DELETE FROM course_videos WHERE id = $delete_id AND formation_id = $formation_id");
    header("Location: manage_playlist.php?id=$formation_id&msg=deleted");
    exit();
}

// Fetch playlist
$videos_res = $conn->query("SELECT * FROM course_videos WHERE formation_id = $formation_id ORDER BY orders ASC, id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Playlist - <?php echo htmlspecialchars($course['titre']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="CRUD.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .video-card { background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; }
        .video-info { display: flex; align-items: center; gap: 15px; }
        .video-order { background: #f1f5f9; color: #4f46e5; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .video-title { font-weight: 500; color: #1e293b; }
        .video-url { font-size: 0.8rem; color: #64748b; word-break: break-all; max-width: 400px; }
        .source-toggle { display: flex; gap: 10px; margin-bottom: 15px; }
        .source-btn { flex: 1; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; background: #f8fafc; font-size: 0.85rem; }
        .source-btn.active { background: #4f46e5; color: white; border-color: #4f46e5; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <?php include 'header.php'; ?>
            <div class="dashboard-content">
                <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2>Manage Playlist: <?php echo htmlspecialchars($course['titre']); ?></h2>
                        <a href="courses.php" style="color: #4f46e5;"><i class="fas fa-arrow-left"></i> Back to Courses</a>
                    </div>
                </div>

                <?php if ($message || $error || isset($_GET['msg'])): ?>
                    <div style="padding: 15px; background: <?php echo $error ? '#fee2e2' : '#dcfce7'; ?>; color: <?php echo $error ? '#991b1b' : '#166534'; ?>; border-radius: 8px; margin-bottom: 20px;">
                        <?php echo $error ?: ($message ?: 'Operation successful'); ?>
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 380px; gap: 25px;">
                    <div class="playlist-container">
                        <h3>Videos List</h3>
                        <?php if ($videos_res->num_rows > 0): ?>
                            <?php while($v = $videos_res->fetch_assoc()): ?>
                                <div class="video-card">
                                    <div class="video-info">
                                        <div class="video-order"><?php echo $v['orders']; ?></div>
                                        <div>
                                            <div class="video-title"><?php echo htmlspecialchars($v['title']); ?> <small style="color: #64748b;">(<?php echo htmlspecialchars($v['duration']); ?>)</small></div>
                                            <div class="video-url">
                                                <?php if(strpos($v['video_url'], 'uploads/') === 0): ?>
                                                    <i class="fas fa-file-video"></i> Uploaded: <?php echo basename($v['video_url']); ?>
                                                <?php else: ?>
                                                    <i class="fas fa-link"></i> <?php echo htmlspecialchars($v['video_url']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="video-actions">
                                        <a href="?id=<?php echo $formation_id; ?>&delete_id=<?php echo $v['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this video?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0;"><p>No videos found.</p></div>
                        <?php endif; ?>
                    </div>

                    <div class="add-video-sidebar" style="position: sticky; top: 100px; height: fit-content;">
                        <div class="add-video-form" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
                            <h3 style="margin-bottom: 20px; color: #1e293b; font-size: 1.25rem;">Add New Lesson</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label>Lesson Title</label>
                                <input type="text" name="video_title" required class="form-control" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>

                            <div class="form-group" style="margin-bottom: 15px;">
                                <label>Duration (e.g. 10:30)</label>
                                <input type="text" name="video_duration" placeholder="10:00" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            
                            <label>Video Source</label>
                            <div class="source-toggle">
                                <button type="button" class="source-btn active" id="btn-link" onclick="setSource('link')"><i class="fas fa-link"></i> External Link</button>
                                <button type="button" class="source-btn" id="btn-upload" onclick="setSource('upload')"><i class="fas fa-upload"></i> Upload File</button>
                            </div>
                            <input type="hidden" name="video_source" id="video_source" value="link">

                            <div id="source-link-input" style="margin-bottom: 15px;">
                                <label>Video URL</label>
                                <input type="text" name="video_url" placeholder="YouTube or MP4 Link" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>

                            <div id="source-upload-input" style="margin-bottom: 15px; display: none;">
                                <label>Choose Video File</label>
                                <input type="file" name="video_file" accept="video/*" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label>Sort Order</label>
                                <input type="number" name="video_order" value="<?php echo ($videos_res->num_rows + 1); ?>" required style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                            
                            <button type="submit" name="add_video" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600;"><i class="fas fa-plus"></i> Add to Playlist</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function setSource(source) {
            document.getElementById('video_source').value = source;
            document.getElementById('btn-link').classList.toggle('active', source === 'link');
            document.getElementById('btn-upload').classList.toggle('active', source === 'upload');
            document.getElementById('source-link-input').style.display = source === 'link' ? 'block' : 'none';
            document.getElementById('source-upload-input').style.display = source === 'upload' ? 'block' : 'none';
        }
    </script>
</body>
</html>
