<?php
session_start();

// Admin check - vital for security
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // If not admin via session flag, check role just in case (redundant but safe) or redirect
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
         header("Location: /3eduplus/signup.php"); 
         exit();
    }
}

$username = "root";
$password = "";
$database = "3eduplus";
$servername = "localhost";
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Add Course
$message = "";

// Check for messages from redirects
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'created') $message = "New course created successfully";
    if ($_GET['msg'] == 'deleted') $message = "Course deleted successfully";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="CRUD.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <?php include 'header.php'; ?>

            <div class="dashboard-content">
                <?php if ($message): ?>
                    <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Course Button -->
                <div class="action-bar">
                    <a href="add_course.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Course
                    </a>
                </div>

                <!-- Courses Table -->
                <div class="table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Level</th>
                                <th>Video</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM formations ORDER BY date_creation DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $imgUrl = $row['formationImageUrl'];
                                    // Check if it's an external URL (starts with http) or local upload
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
                                    echo "<td>" . $row['titre'] . "</td>";
                                    echo "<td>" . $row['categorie'] . "</td>";
                                    echo "<td>" . $row['prix'] . " €</td>";
                                    echo "<td>" . $row['niveau'] . "</td>";
                                    
                                    // Display video icon/link
                                    $videoLink = "";
                                    if (!empty($row['video_url'])) {
                                        $videoLink = "<a href='../../" . $row['video_url'] . "' target='_blank' style='color: #2563eb;'><i class='fas fa-video'></i> View</a>";
                                        if (strpos($row['video_url'], 'http') === 0) {
                                            $videoLink = "<a href='" . $row['video_url'] . "' target='_blank' style='color: #2563eb;'><i class='fas fa-video'></i> View</a>";
                                        }
                                    } else {
                                        $videoLink = "<span style='color: #999;'>No Video</span>";
                                    }
                                    echo "<td>" . $videoLink . "</td>";
                                    
                                    echo "<td>" . $row['date_creation'] . "</td>";
                                    echo "<td>
                                            <a href='delete_course.php?id=" . $row['formation_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No courses found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php $conn->close(); ?>
