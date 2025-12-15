<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
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
    
    $niveau = $conn->real_escape_string($_POST['niveau']);
    
    // Handle Image URL
    $formationImageUrl = $conn->real_escape_string($_POST['formation_image_url']);

    // Insert
    $sql = "INSERT INTO formations (titre, description, categorie, prix, duree, niveau, formationImageUrl) 
            VALUES ('$titre', '$description', '$categorie', '$prix', '$duree', '$niveau', '$formationImageUrl')";

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
    <title>Courses - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="CRUD.css">
    <link rel="icon" href="../LogoEdu.png" type="image/png">
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
            background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;
        }
        .btn-submit:hover { background: #1d4ed8; }
        .message { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .course-img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="dashboard-container">
<<<<<<< HEAD
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                     <img src="../LogoEdu.png" alt="3edu+ Logo">
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <p class="nav-section-title">MENU</p>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="index.html" class="nav-link">
                                <i class="fas fa-chevron-up nav-arrow"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="users.php" class="nav-link">
                                <i class="fas fa-chevron-down nav-arrow"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a href="courses.php" class="nav-link">
                                <i class="fas fa-chevron-down nav-arrow"></i>
                                <span>Courses</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="purchased-courses.php" class="nav-link">
                                <i class="fas fa-chevron-down nav-arrow"></i>
                                <span>Inscriptions</span>
                            </a>
                        </li>
                         <li class="nav-item">
                            <a href="reports.php" class="nav-link">
                                <i class="fas fa-chevron-down nav-arrow"></i>
                                <span>Reports</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>
=======
        <?php include 'sidebar.php'; ?>
>>>>>>> 3e34b36 (newe version)

        <main class="main-content">
            <header class="dashboard-header">
                <div class="header-left">
                    <h1 class="header-logo">Courses Management</h1>
                </div>
                <div class="header-right">
                    <div class="user-profile">
                         <img src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fstatic.vecteezy.com%2Fsystem%2Fresources%2Fpreviews%2F000%2F290%2F610%2Foriginal%2Fadministration-vector-icon.jpg&f=1&nofb=1&ipt=0c0a886cbda8307543dc1e414a300f5a4d50a9c8884b6fd80567d4bf75248a31" class="admin-avatar">
                        <span class="user-name">Admin</span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if ($message): ?>
                    <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

<<<<<<< HEAD
                <!-- Add Course Form -->
                <div class="form-container">
                    <h3>Add New Course</h3>
                    <form method="POST" action="courses.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="titre" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <input type="text" name="categorie" placeholder="e.g. Development" required>
                        </div>
                        <div class="form-group">
                            <label>Price (€)</label>
                            <input type="number" step="0.01" name="prix" required>
                        </div>
                        <div class="form-group">
                            <label>Duration</label>
                            <input type="text" name="duree" placeholder="e.g. 10 hours">
                        </div>
                        <div class="form-group">
                            <label>Level</label>
                            <select name="niveau">
                                <option value="Débutant">Beginner</option>
                                <option value="Intermédiaire">Intermediate</option>
                                <option value="Avancé">Advanced</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Course Image URL</label>
                            <input type="text" name="formation_image_url" placeholder="https://example.com/image.jpg" required>
                        </div>
                        <button type="submit" name="add_course" class="btn-submit">Add Course</button>
                    </form>
=======
                <div class="action-bar" style="margin-bottom: 20px; text-align: right;">
                    <a href="add_course.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Course
                    </a>
>>>>>>> 3e34b36 (newe version)
                </div>

                <div class="table-container">
                    <table width="100%" border="1" class="users-table" cellpadding="10" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Level</th>
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
                                    if (strpos($imgUrl, 'http') === 0) {
                                        $imagePath = $imgUrl;
                                    } elseif ($imgUrl) {
                                        $imagePath = "../../" . $imgUrl;
                                    } else {
                                        $imagePath = "../LogoEdu.png";
                                    }
                                    echo "<tr>";
                                    echo "<td>" . $row['formation_id'] . "</td>";
                                    echo "<td><img src='" . $imagePath . "' class='course-img-thumb'></td>";
                                    echo "<td>" . $row['titre'] . "</td>";
                                    echo "<td>" . $row['categorie'] . "</td>";
                                    echo "<td>" . $row['prix'] . " €</td>";
                                    echo "<td>" . $row['niveau'] . "</td>";
<<<<<<< HEAD
=======
                                    
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
                                    
>>>>>>> 3e34b36 (newe version)
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
<script src="account.js"></script>
</html>
<?php $conn->close(); ?>
