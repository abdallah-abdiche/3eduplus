<<<<<<< HEAD
=======
<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin', 'Marketing']);

// --- ANALYTICS ---

// 1. Most Popular Courses
$popular_sql = "SELECT f.titre, COUNT(pf.id) as enrolled 
                FROM formations f 
                LEFT JOIN paiement_formations pf ON f.formation_id = pf.formation_id 
                GROUP BY f.formation_id 
                ORDER BY enrolled DESC LIMIT 5";
$popular_result = $conn->query($popular_sql);


$revenue_cat_sql = "SELECT f.categorie, SUM(pf.prix_paye) as total_revenue
                    FROM paiement_formations pf
                    JOIN formations f ON pf.formation_id = f.formation_id
                    JOIN paiements p ON pf.paiement_id = p.paiement_id
                    WHERE p.statut = 'paid'
                    GROUP BY f.categorie";
$revenue_cat_result = $conn->query($revenue_cat_sql);


$cat_labels = [];
$cat_data = [];
while ($row = $revenue_cat_result->fetch_assoc()) {
    $cat_labels[] = $row['categorie'];
    $cat_data[] = $row['total_revenue'];
}
?>
>>>>>>> 3e34b36 (newe version)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - 3edu+</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
      
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
                        <li class="nav-item">
                            <a href="purchased-courses.php" class="nav-link">
                                <i class="fas fa-chevron-down nav-arrow"></i>
                                <span>Inscriptions</span>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a href="reports.php" class="nav-link">
                                <i class="fas fa-chevron-down nav-arrow"></i>
                                <span>Reports</span>
                            </a>
                        </li>
                       
                    </ul>
                </div>

                <div class="nav-section">
                    <p class="nav-section-title">OTHERS</p>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="fas fa-chevron-down nav-arrow"></i>
                                <span>Charts</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="fas fa-chevron-down nav-arrow"></i>
                                <span>UI Elements</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="fas fa-chevron-down nav-arrow"></i>
                                <span>Authentication</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="sidebar-promo">
                <div class="promo-card">
                    <h3>#1 Admin Dashboard</h3>
                    <p></p>
                </div>
            </div>
        </aside>

      
        <main class="main-content">
        
            <header class="dashboard-header">
                <div class="header-left">
                    <h1 class="header-logo">Dashboard</h1>
                </div>
                <div class="header-center">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search or type command..." class="search-input">
                        <span class="search-shortcut">K</span>
                    </div>
                </div>
                <div class="header-right">
                    <button class="header-icon-btn dark-mode-toggle" title="Dark Mode">
                        <i class="fas fa-moon"></i>
                    </button>
                    <button class="header-icon-btn notifications-btn" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <div class="user-profile">
                        <img src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fstatic.vecteezy.com%2Fsystem%2Fresources%2Fpreviews%2F000%2F290%2F610%2Foriginal%2Fadministration-vector-icon.jpg&f=1&nofb=1&ipt=0c0a886cbda8307543dc1e414a300f5a4d50a9c8884b6fd80567d4bf75248a31" class="admin-avatar">
                        <span class="user-name">Account</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </header> 
        </main>
    </div>
     <script src="account.js"></script>

<<<<<<< HEAD
=======
    <script>
        const ctxOriginal = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxOriginal, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($cat_labels); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($cat_data); ?>,
                    backgroundColor: [
                        '#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
    <script src="account.js"></script>
>>>>>>> 3e34b36 (newe version)
</body>
</html>

