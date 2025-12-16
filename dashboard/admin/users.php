<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin']);

$username = "root";
$password = "";
$database = "3eduplus";
$servername = "localhost";
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle messages
$msg = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') $msg = "User deleted successfully.";
    if ($_GET['msg'] == 'error') $msg = "Error deleting user.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin Dashboard</title>
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
                <?php if ($msg): ?>
                    <div class="message <?php echo strpos($msg, 'Error') !== false ? 'error' : 'success'; ?>" style="padding: 10px; margin-bottom: 20px; border-radius: 4px; background-color: <?php echo strpos($msg, 'Error') !== false ? '#fee2e2' : '#dcfce7'; ?>; color: <?php echo strpos($msg, 'Error') !== false ? '#991b1b' : '#166534'; ?>;">
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <h3>Users Management</h3>
                    <p>Manage all registered users from this dashboard.</p>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Wilaya</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch users with roles
                            $sql = "SELECT u.*, r.nom_role FROM utilisateurs u LEFT JOIN roles r ON u.role_id = r.role_id";
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['user_id'] . "</td>";
                                    echo "<td>" . $row['Nom_Complet'] . "</td>";
                                    echo "<td>" . $row['Email'] . "</td>";
                                    echo "<td>" . $row['Wilaya'] . "</td>";
                                    echo "<td>" . $row['numero_tlf_utilisateur'] . "</td>";
                                    echo "<td>" . ($row['nom_role'] ? $row['nom_role'] : 'User') . "</td>";
                                    echo "<td>
                                            <a href='edit_users.php?user_id=" . $row['user_id'] . "' class='btn btn-primary btn-sm'>Edit</a>
                                            <a href='delete_user.php?id=" . $row['user_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No users found</td></tr>";
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
