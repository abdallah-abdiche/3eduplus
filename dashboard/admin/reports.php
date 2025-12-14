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

// 2. Revenue by Category
$revenue_cat_sql = "SELECT f.categorie, SUM(pf.prix_paye) as total_revenue
                    FROM paiement_formations pf
                    JOIN formations f ON pf.formation_id = f.formation_id
                    JOIN paiements p ON pf.paiement_id = p.paiement_id
                    WHERE p.statut = 'paid'
                    GROUP BY f.categorie";
$revenue_cat_result = $conn->query($revenue_cat_sql);

// Prepare data for JS
$cat_labels = [];
$cat_data = [];
while ($row = $revenue_cat_result->fetch_assoc()) {
    $cat_labels[] = $row['categorie'];
    $cat_data[] = $row['total_revenue'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="CRUD.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <?php include 'header.php'; ?>

            <div class="dashboard-content">
                
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Revenue by Category</h3>
                        </div>
                        <div class="chart-body">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card" style="flex: 1; min-width: 300px;">
                        <h3 class="chart-title" style="margin-bottom: 1rem;">Most Popular Courses</h3>
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Enrollments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($popular_result->num_rows > 0) {
                                    while($row = $popular_result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row['titre'] . "</td>";
                                        echo "<td>" . $row['enrolled'] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2'>No data available</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

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
</body>
</html>
<?php $conn->close(); ?>
