<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

// Check auth and role
checkAuth();
checkRole(['Admin']);

// Fetch inscriptions with details
$sql = "SELECT pf.id as inscription_id, p.date_paiement as date_inscription, 
               u.Nom_Complet, u.Email, 
               f.titre as formation_titre, f.prix
        FROM paiement_formations pf
        JOIN paiements p ON pf.paiement_id = p.paiement_id
        JOIN utilisateurs u ON p.user_id = u.user_id
        JOIN formations f ON pf.formation_id = f.formation_id
        WHERE p.statut = 'paid'
        ORDER BY p.date_paiement DESC";
$result = $conn->query($sql);
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
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Price</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['inscription_id'] . "</td>";
                                    echo "<td>" . $row['Nom_Complet'] . "</td>";
                                    echo "<td>" . $row['Email'] . "</td>";
                                    echo "<td>" . $row['formation_titre'] . "</td>";
                                    echo "<td>" . $row['prix'] . " DA</td>";
                                    echo "<td>" . $row['date_inscription'] . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No inscriptions found</td></tr>";
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
