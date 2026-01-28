<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin', 'Administrateur']);

$payments_res = $conn->query("
    SELECT p.*, u.Nom_Complet, u.Email 
    FROM paiements p
    LEFT JOIN utilisateurs u ON p.user_id = u.user_id
    ORDER BY p.date_paiement DESC
");
$payments = $payments_res->fetch_all(MYSQLI_ASSOC);

// Calculate total revenue
$total_revenue = 0;
foreach($payments as $p) {
    if ($p['statut'] == 'paid') {
        $total_revenue += floatval($p['montant']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Paiements - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="CRUD.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .stats-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
        .stat-value { font-size: 1.8rem; font-weight: 700; color: #4f46e5; margin-top: 10px; }
        .status-paid { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <?php include 'header.php'; ?>
            <div class="dashboard-content">
                <h2>Historique des Paiements</h2>

                <div class="stats-summary">
                    <div class="stat-box">
                        <div>Revenu Total</div>
                        <div class="stat-value"><?php echo number_format($total_revenue, 0); ?> DA</div>
                    </div>
                    <div class="stat-box">
                        <div>Transactions Réussies</div>
                        <div class="stat-value"><?php 
                            $count = 0;
                            foreach($payments as $p) if($p['statut'] == 'paid') $count++;
                            echo $count;
                        ?></div>
                    </div>
                    <div class="stat-box">
                        <div>Dernier Paiement</div>
                        <div class="stat-value" style="font-size: 1.1rem;"><?php echo !empty($payments) ? date('d/m/Y', strtotime($payments[0]['date_paiement'])) : '--'; ?></div>
                    </div>
                </div>

                <div class="table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Montant</th>
                                <th>Méthode</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td>#<?php echo $p['paiement_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($p['Nom_Complet'] ?: 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['Email'] ?: 'N/A'); ?></td>
                                    <td><?php echo number_format($p['montant'], 0); ?> DA</td>
                                    <td><?php echo ucfirst($p['methode_paiement']); ?></td>
                                    <td>
                                        <span class="<?php echo $p['statut'] == 'paid' ? 'status-paid' : 'status-pending'; ?>">
                                            <?php echo ucfirst($p['statut']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($p['date_paiement'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
