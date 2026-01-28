<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin']);

$user_name = $_SESSION['user_name'];

// --- FETCH DASHBOARD DATA ---

// 1. Total Students (Role = Apprenant)
$students_query = "SELECT COUNT(*) as total FROM utilisateurs u 
                   LEFT JOIN roles r ON u.role_id = r.role_id 
                   WHERE r.nom_role = 'Apprenant'";
$students_result = $conn->query($students_query);
$total_students = $students_result ? $students_result->fetch_assoc()['total'] : 0;

// 2. Total Users
$users_query = "SELECT COUNT(*) as total FROM utilisateurs";
$users_result = $conn->query($users_query);
$total_users = $users_result ? $users_result->fetch_assoc()['total'] : 0;

// 3. Total Courses
$courses_query = "SELECT COUNT(*) as total FROM formations";
$courses_result = $conn->query($courses_query);
$total_courses = $courses_result ? $courses_result->fetch_assoc()['total'] : 0;

// 4. Course Sales (Enrollments)
$sales_query = "SELECT COUNT(*) as total FROM inscriptions";
$sales_result = $conn->query($sales_query);
$total_sales = $sales_result ? $sales_result->fetch_assoc()['total'] : 0;

// 5. Monthly Revenue
$current_month = date('m');
$current_year = date('Y');
$revenue_query = "SELECT SUM(montant) as revenue FROM paiements 
                  WHERE MONTH(date_paiement) = '$current_month' AND YEAR(date_paiement) = '$current_year' AND statut = 'paid'";
$revenue_result = $conn->query($revenue_query);
$monthly_revenue = $revenue_result ? ($revenue_result->fetch_assoc()['revenue'] ?? 0) : 0;

// Target calculation
$target_revenue = 200000;
$progress_percentage = $target_revenue > 0 ? ($monthly_revenue / $target_revenue) * 100 : 0;
$progress_percentage = min(100, round($progress_percentage, 1));

// Chart Data: Monthly Sales (Last 6 months)
$chart_months = [];
$chart_sales = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $month_name = date('M', strtotime("-$i months"));
    $chart_months[] = $month_name;
    
    $query = "SELECT SUM(montant) as total FROM paiements 
              WHERE DATE_FORMAT(date_paiement, '%Y-%m') = '$date' AND statut = 'paid'";
    $res = $conn->query($query);
    $row = $res ? $res->fetch_assoc() : null;
    $chart_sales[] = $row['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - 3edu+</title>
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
                <!-- Stats Cards -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-icon customers">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="metric-info">
                            <p class="metric-label">Total Users</p>
                            <h2 class="metric-value"><?php echo number_format($total_users); ?></h2>
                            <p class="metric-change positive">
                                <i class="fas fa-user-graduate"></i> <?php echo $total_students; ?> Students
                            </p>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-icon" style="background: #ede9fe; color: #7c3aed;">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="metric-info">
                            <p class="metric-label">Total Courses</p>
                            <h2 class="metric-value"><?php echo number_format($total_courses); ?></h2>
                            <p class="metric-change positive">
                                <i class="fas fa-graduation-cap"></i> Active
                            </p>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-icon orders">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="metric-info">
                            <p class="metric-label">Enrollments</p>
                            <h2 class="metric-value"><?php echo number_format($total_sales); ?></h2>
                            <p class="metric-change positive">
                                <i class="fas fa-chart-line"></i> Total Sales
                            </p>
                        </div>
                    </div>

                    <div class="metric-card monthly-target">
                        <div class="target-header">
                            <div>
                                <h3 class="target-title">Monthly Revenue</h3>
                                <p class="target-subtitle">Target: <?php echo number_format($target_revenue); ?> DA</p>
                            </div>
                        </div>
                        <div class="target-progress">
                            <div class="circular-progress">
                                <svg class="progress-ring" width="120" height="120">
                                    <circle class="progress-ring-circle-bg" cx="60" cy="60" r="54"></circle>
                                    <circle class="progress-ring-circle" cx="60" cy="60" r="54"
                                        style="stroke-dashoffset: <?php echo 339 - (339 * $progress_percentage / 100); ?>; stroke-dasharray: 339;"></circle>
                                </svg>
                                <div class="progress-text">
                                    <span class="progress-percentage"><?php echo $progress_percentage; ?>%</span>
                                </div>
                            </div>
                        </div>
                        <div class="target-message">
                            <p>Earned <strong><?php echo number_format($monthly_revenue); ?> DA</strong></p>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="charts-grid">
                    <div class="chart-card" style="width: 100%;">
                        <div class="chart-header">
                            <h3 class="chart-title">Revenue Overview (Last 6 Months)</h3>
                        </div>
                        <div class="chart-body">
                            <canvas id="monthlySalesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Users Section -->
                <div class="users-section" style="margin-top: 30px; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <div>
                            <h3 style="margin: 0; color: #1e293b; font-size: 1.2rem;"><i class="fas fa-users" style="color: #4f46e5; margin-right: 10px;"></i>Recent Users</h3>
                            <p style="margin: 5px 0 0; color: #64748b; font-size: 0.9rem;">Latest registered users</p>
                        </div>
                        <a href="users.php" style="background: #eff6ff; color: #2563eb; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                            View All <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>
                        </a>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                            <thead>
                                <tr style="background-color: #f8fafc;">
                                    <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0;">User</th>
                                    <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Role</th>
                                    <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Date</th>
                                    <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recent_users_sql = "SELECT u.*, r.nom_role 
                                                     FROM utilisateurs u 
                                                     LEFT JOIN roles r ON u.role_id = r.role_id 
                                                     ORDER BY u.date_registration DESC LIMIT 5";
                                $recent_users_res = $conn->query($recent_users_sql);
                                if ($recent_users_res && $recent_users_res->num_rows > 0) {
                                    while($u = $recent_users_res->fetch_assoc()) {
                                        $initial = strtoupper(substr($u['Nom_Complet'], 0, 1));
                                        $bg_colors = ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6'];
                                        $bg_color = $bg_colors[$u['user_id'] % 5];
                                        
                                        echo "<tr>";
                                        echo "<td style='padding: 15px; border-bottom: 1px solid #f1f5f9;'>
                                                <div style='display: flex; align-items: center; gap: 15px;'>
                                                    <div style='width: 40px; height: 40px; border-radius: 50%; background: {$bg_color}; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;'>{$initial}</div>
                                                    <div>
                                                        <div style='font-weight: 600; color: #1e293b;'>".htmlspecialchars($u['Nom_Complet'])."</div>
                                                        <div style='font-size: 0.85rem; color: #64748b;'>".htmlspecialchars($u['Email'])."</div>
                                                    </div>
                                                </div>
                                              </td>";
                                        echo "<td style='padding: 15px; border-bottom: 1px solid #f1f5f9;'>
                                                <span style='padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; background: #f1f5f9; color: #475569; font-weight: 500;'>".htmlspecialchars($u['nom_role'] ?? 'User')."</span>
                                              </td>";
                                        echo "<td style='padding: 15px; border-bottom: 1px solid #f1f5f9; color: #64748b;'>
                                                ".date('M d, Y', strtotime($u['date_registration']))."
                                              </td>";
                                        echo "<td style='padding: 15px; border-bottom: 1px solid #f1f5f9;'>
                                                <a href='edit_users.php?user_id={$u['user_id']}' style='color: #4f46e5; text-decoration: none; margin-right: 10px;'><i class='fas fa-edit'></i></a>
                                                <a href='delete_user.php?id={$u['user_id']}' style='color: #ef4444; text-decoration: none;'><i class='fas fa-trash'></i></a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' style='padding: 20px; text-align: center; color: #64748b;'>No users found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Courses Section -->
                <div class="courses-section" style="margin-top: 30px; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <div>
                            <h3 style="margin: 0; color: #1e293b; font-size: 1.2rem;"><i class="fas fa-book-open" style="color: #4f46e5; margin-right: 10px;"></i>Recent Courses</h3>
                            <p style="margin: 5px 0 0; color: #64748b; font-size: 0.9rem;">Latest courses on the platform</p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <a href="add_course.php" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px;">
                                <i class="fas fa-plus"></i> Add Course
                            </a>
                            <a href="courses.php" style="background: #eff6ff; color: #2563eb; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                                View All <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                            <thead>
                                <tr style="background-color: #f8fafc;">
                                    <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Course</th>
                                    <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Category</th>
                                    <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Price</th>
                                    <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Level</th>
                                    <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Enrolled</th>
                                    <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recent_courses_sql = "SELECT f.*, 
                                                       (SELECT COUNT(*) FROM inscriptions i WHERE i.formation_id = f.formation_id) as enrolled_count
                                                       FROM formations f 
                                                       ORDER BY f.formation_id DESC LIMIT 5";
                                $recent_courses_res = $conn->query($recent_courses_sql);
                                if ($recent_courses_res && $recent_courses_res->num_rows > 0) {
                                    while($c = $recent_courses_res->fetch_assoc()) {
                                        $level_colors = [
                                            'Débutant' => ['#dcfce7', '#166534'],
                                            'Intermédiaire' => ['#fef3c7', '#b45309'],
                                            'Avancé' => ['#fee2e2', '#991b1b']
                                        ];
                                        $level_style = $level_colors[$c['niveau']] ?? ['#f1f5f9', '#475569'];
                                        
                                        echo "<tr>";
                                        echo "<td style='padding: 15px; border-bottom: 1px solid #f1f5f9;'>
                                                <div style='display: flex; align-items: center; gap: 15px;'>
                                                    <img src='../../".htmlspecialchars($c['formationImageUrl'] ?? $c['image'] ?? 'logo.png')."' 
                                                         alt='Course' 
                                                         style='width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #f0f0f0;'
                                                         onerror=\"this.src='../../logo.png'\">
                                                    <div>
                                                        <div style='font-weight: 600; color: #1e293b;'>".htmlspecialchars($c['titre'])."</div>
                                                        <div style='font-size: 0.8rem; color: #64748b;'>".($c['duree'] ?? 'N/A')."</div>
                                                    </div>
                                                </div>
                                              </td>";
                                        echo "<td style='padding: 15px; border-bottom: 1px solid #f1f5f9;'>
                                                <span style='padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; background: #f1f5f9; color: #475569; font-weight: 500;'>".htmlspecialchars($c['categorie'] ?? 'General')."</span>
                                              </td>";
                                        echo "<td style='padding: 15px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #4f46e5;'>
                                                ".number_format($c['prix'], 0)." DA
                                              </td>";
                                        echo "<td style='padding: 15px; border-bottom: 1px solid #f1f5f9;'>
                                                <span style='padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; background: {$level_style[0]}; color: {$level_style[1]}; font-weight: 500;'>".htmlspecialchars($c['niveau'] ?? 'N/A')."</span>
                                              </td>";
                                        echo "<td style='padding: 15px; border-bottom: 1px solid #f1f5f9;'>
                                                <span style='display: inline-flex; align-items: center; gap: 5px; color: #64748b; font-weight: 500;'>
                                                    <i class='fas fa-users' style='color: #4f46e5;'></i> ".$c['enrolled_count']."
                                                </span>
                                              </td>";
                                        echo "<td style='padding: 15px; border-bottom: 1px solid #f1f5f9;'>
                                                <a href='edit_course.php?id=".$c['formation_id']."' style='color: #4f46e5; text-decoration: none; margin-right: 10px;' title='Edit'>
                                                    <i class='fas fa-edit'></i>
                                                </a>
                                                <a href='delete_course.php?id=".$c['formation_id']."' style='color: #ef4444; text-decoration: none;' title='Delete'>
                                                    <i class='fas fa-trash'></i>
                                                </a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' style='padding: 40px; text-align: center; color: #64748b;'>
                                            <i class='fas fa-book' style='font-size: 2rem; color: #cbd5e1; margin-bottom: 10px; display: block;'></i>
                                            No courses yet. <a href='add_course.php' style='color: #4f46e5;'>Add your first course</a>
                                          </td></tr>";
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
        // Chart.js Configuration
        const monthLabels = <?php echo json_encode($chart_months); ?>;
        const salesData = <?php echo json_encode($chart_sales); ?>;

        const ctx = document.getElementById('monthlySalesChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.5)');
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Revenue (DA)',
                    data: salesData,
                    backgroundColor: gradient,
                    borderColor: '#4f46e5',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5] },
                        ticks: { callback: function(value) { return value + ' DA'; } }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        callbacks: {
                            label: function(context) { return context.parsed.y + ' DA'; }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
