<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

// Check auth and role
checkAuth();
checkRole(['Admin']);

// Check if formation_id column exists in inscriptions table
$check_column_query = "SHOW COLUMNS FROM inscriptions LIKE 'formation_id'";
$column_check = $conn->query($check_column_query);
$has_formation_id = ($column_check && $column_check->num_rows > 0);

$all_inscriptions = [];
$error_message = null;

try {
    // First, get from paiement_formations (purchases) - this should always work
    $purchases_query = "SELECT 
        pf.id as inscription_id,
        p.date_paiement as date_inscription, 
        u.Nom_Complet,
        u.Email, 
        f.titre as formation_titre,
        f.prix,
        'purchases' as source
    FROM paiement_formations pf
    JOIN paiements p ON pf.paiement_id = p.paiement_id
    LEFT JOIN utilisateurs u ON p.user_id = u.user_id
    JOIN formations f ON pf.formation_id = f.formation_id
    WHERE p.statut = 'paid'
    ORDER BY p.date_paiement DESC";
    
    $purchases_result = $conn->query($purchases_query);
    if ($purchases_result) {
        while ($row = $purchases_result->fetch_assoc()) {
            $all_inscriptions[] = $row;
        }
    }
    
    // If formation_id column exists in inscriptions, also fetch from there
    if ($has_formation_id) {
        $inscriptions_query = "SELECT 
            i.inscription_id,
            i.date_inscription,
            u.Nom_Complet,
            u.Email,
            f.titre as formation_titre,
            f.prix,
            'inscriptions' as source
        FROM inscriptions i
        LEFT JOIN utilisateurs u ON i.user_id = u.user_id
        LEFT JOIN formations f ON i.formation_id = f.formation_id
        WHERE i.user_id IS NOT NULL AND f.formation_id IS NOT NULL
        ORDER BY i.date_inscription DESC";
        
        $inscriptions_result = $conn->query($inscriptions_query);
        if ($inscriptions_result) {
            while ($row = $inscriptions_result->fetch_assoc()) {
                $all_inscriptions[] = $row;
            }
        }
    }
    
    // Sort combined results by date
    usort($all_inscriptions, function($a, $b) {
        return strtotime($b['date_inscription']) - strtotime($a['date_inscription']);
    });
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscriptions - Admin Dashboard</title>
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
                <div class="table-container">
                    <h3>Inscriptions List</h3>
                    <?php if ($error_message): ?>
                        <div style="padding: 15px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">
                            <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$has_formation_id): ?>
                        <div style="padding: 15px; background: #fff3cd; color: #856404; border: 1px solid #ffeeba; border-radius: 5px; margin-bottom: 20px;">
                            <strong>⚠️ Warning:</strong> The 'formation_id' column is missing from the inscriptions table. 
                            <a href="../../fix_inscriptions_table.php" style="color: #004085; text-decoration: underline;">Click here to fix it</a>
                        </div>
                    <?php endif; ?>
                    
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Price</th>
                                <th>Date</th>
                                <th>Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (count($all_inscriptions) > 0) {
                                foreach($all_inscriptions as $row) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['inscription_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Nom_Complet'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Email'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['formation_titre'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['prix'] ?? '0') . " DA</td>";
                                    echo "<td>" . htmlspecialchars($row['date_inscription'] ?? 'N/A') . "</td>";
                                    echo "<td><span style='padding: 3px 8px; border-radius: 3px; font-size: 0.85em; background: " . 
                                         ($row['source'] == 'inscriptions' ? '#d1ecf1' : '#d4edda') . "'>" . 
                                         htmlspecialchars(ucfirst($row['source'])) . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' style='text-align: center; padding: 30px;'>No inscriptions found</td></tr>";
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
