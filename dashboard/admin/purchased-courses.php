<?php
session_start();

// Admin check
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

// Fetch purchased courses via Payments
$sql = "SELECT p.date_paiement, p.statut, 
               u.Nom_Complet, u.Email, u.image_utilisateur,
               f.titre, pf.prix_paye as prix, f.formationImageUrl
        FROM paiements p
        JOIN utilisateurs u ON p.user_id = u.user_id
        JOIN paiement_formations pf ON p.paiement_id = pf.paiement_id
        JOIN formations f ON pf.formation_id = f.formation_id
        ORDER BY p.date_paiement DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchased Courses - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="CRUD.css">
    <link rel="icon" href="../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .table-thumb { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px; vertical-align: middle; }
        .course-thumb { border-radius: 4px; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8em; font-weight: bold; }
        .status-en-cours { background: #e0f2fe; color: #0369a1; }
        .status-completee { background: #dcfce7; color: #166534; }
        .status-abandonnee { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="dashboard-container">
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
                        <li class="nav-item">
                            <a href="courses.php" class="nav-link">
                                <i class="fas fa-chevron-down nav-arrow"></i>
                                <span>Courses</span>
                            </a>
                        </li>
                        <li class="nav-item active">
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

        <main class="main-content">
            <header class="dashboard-header">
                <div class="header-left">
                    <h1 class="header-logo">Purchased Courses</h1>
                </div>
                <div class="header-right">
                    <div class="user-profile">
                         <img src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fstatic.vecteezy.com%2Fsystem%2Fresources%2Fpreviews%2F000%2F290%2F610%2Foriginal%2Fadministration-vector-icon.jpg&f=1&nofb=1&ipt=0c0a886cbda8307543dc1e414a300f5a4d50a9c8884b6fd80567d4bf75248a31" class="admin-avatar">
                        <span class="user-name">Admin</span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="table-container">
                    <table width="100%" border="1" class="users-table" cellpadding="10" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Price</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    // Student image
                                    $userImg = !empty($row['image_utilisateur']) ? "../../" . $row['image_utilisateur'] : "https://via.placeholder.com/40";
                                    // Course image
                                    $courseImgUrl = $row['formationImageUrl'];
                                    if (strpos($courseImgUrl, 'http') === 0) {
                                        $courseImg = $courseImgUrl;
                                    } elseif ($courseImgUrl) {
                                        $courseImg = "../../" . $courseImgUrl;
                                    } else {
                                        $courseImg = "../LogoEdu.png";
                                    }

                                    $statusClass = 'status-' . strtolower(str_replace(' ', '-', $row['statut']));

                                    echo "<tr>";
                                    echo "<td>
                                            <img src='$userImg' class='table-thumb'>
                                            <div>
                                                <strong>{$row['Nom_Complet']}</strong><br>
                                                <small>{$row['Email']}</small>
                                            </div>
                                          </td>";
                                    echo "<td>
                                            <img src='$courseImg' class='table-thumb course-thumb'>
                                            {$row['titre']}
                                          </td>";
                                    echo "<td>{$row['prix']} €</td>";
                                    echo "<td>" . date('Y-m-d', strtotime($row['date_paiement'])) . "</td>";
                                    echo "<td><span class='status-badge $statusClass'>{$row['statut']}</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No subscriptions found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
   <script src="account.js"></script>
 </body>
</html>
<?php $conn->close(); ?>
