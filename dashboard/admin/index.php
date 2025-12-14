<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

// Check auth and role
checkAuth();

// If user is not Admin, redirect them to their specific dashboard instead of showing access denied
if (($_SESSION['user_role'] ?? '') !== 'Admin' && !($_SESSION['is_admin'] ?? false)) {
    redirectByRole();
}

checkRole(['Admin']);

$user_name = $_SESSION['user_name'];

// --- FETCH DASHBOARD DATA ---

// 1. Total Students (Role = Apprenant)
$students_query = "SELECT COUNT(*) as total FROM utilisateurs u 
                   LEFT JOIN roles r ON u.role_id = r.role_id 
                   WHERE r.nom_role = 'Apprenant'";
$students_result = $conn->query($students_query);
$total_students = $students_result->fetch_assoc()['total'];

// 2. Course Sales (Count of purchased courses in inscriptions)
$sales_query = "SELECT COUNT(*) as total FROM inscriptions";
$sales_result = $conn->query($sales_query);
$total_sales = $sales_result->fetch_assoc()['total'];

// 3. Monthly Revenue (Current Month)
$current_month = date('m');
$current_year = date('Y');
$revenue_query = "SELECT SUM(montant) as revenue FROM paiements 
                  WHERE MONTH(date_paiement) = '$current_month' AND YEAR(date_paiement) = '$current_year' AND statut = 'paid'";
$revenue_result = $conn->query($revenue_query);
$monthly_revenue = $revenue_result->fetch_assoc()['revenue'] ?? 0;

// 4. Target Calculation (Example target: 50% growth from last month or static)
// For now, let's use a static target or just display the revenue
$target_revenue = 200000; // Example static target 200,000 DA
$progress_percentage = $target_revenue > 0 ? ($monthly_revenue / $target_revenue) * 100 : 0;
$progress_percentage = min(100, round($progress_percentage, 1));

// 5. Chart Data: Monthly Sales (Last 6 months)
$chart_months = [];
$chart_sales = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $month_name = date('M', strtotime("-$i months"));
    $chart_months[] = $month_name;
    
    $query = "SELECT SUM(montant) as total FROM paiements 
              WHERE DATE_FORMAT(date_paiement, '%Y-%m') = '$date' AND statut = 'paid'";
    $res = $conn->query($query);
    $row = $res->fetch_assoc();
    $chart_sales[] = $row['total'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - 3edu+</title>
    <link rel="stylesheet" href="style.css">
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

                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-icon customers">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="metric-info">
                            <p class="metric-label">Students</p>
                            <h2 class="metric-value"><?php echo number_format($total_students); ?></h2>
                            <p class="metric-change positive">
                                <i class="fas fa-check-circle"></i> Active
                            </p>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-icon orders">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="metric-info">
                            <p class="metric-label">Course Sales</p>
                            <h2 class="metric-value"><?php echo number_format($total_sales); ?></h2>
                            <p class="metric-change positive">
                                <i class="fas fa-chart-line"></i> Total
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
                            <p>You earned <strong><?php echo number_format($monthly_revenue); ?> DA</strong> this month.</p>
                        </div>
                    </div>
                </div>


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
            </div>
        </main>
    </div>
    
    <script>
        // Pass PHP data to JS
        const monthLabels = <?php echo json_encode($chart_months); ?>;
        const salesData = <?php echo json_encode($chart_sales); ?>;

        // Chart.js Configuration
        const ctx = document.getElementById('monthlySalesChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.5)'); // #2563eb
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Revenue (DA)',
                    data: salesData,
                    backgroundColor: gradient,
                    borderColor: '#2563eb',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                    pointRadius: 4
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
                        padding: 10,
                        callbacks: {
                             label: function(context) { return context.parsed.y + ' DA'; }
                        }
                    }
                }
            }
        });
    </script>
    <script src="dashboard.js"></script>
</body>
</html>
