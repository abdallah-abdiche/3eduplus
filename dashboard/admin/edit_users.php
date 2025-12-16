<?php
session_start();
require_once '../../config.php';

// Check that user_id is provided
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo "Invalid user.";
    exit();
}

$user_id = (int) $_GET['user_id'];

// Handle form submission (update user)
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = $_POST['Nom_Complet'] ?? '';
    $email  = $_POST['Email'] ?? '';
    $wilaya = $_POST['Wilaya'] ?? '';
    $phone  = $_POST['numero_tlf_utilisateur'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $role_id = !empty($_POST['role_id']) ? $_POST['role_id'] : NULL;

    // Simple validation (you can extend this)
    if ($name === '' || $email === '') {
        $error = "Name and email are required.";
    } else {
        // Use prepared statement to avoid SQL injection
        $stmt = $conn->prepare("UPDATE utilisateurs 
                                SET Nom_Complet = ?, Email = ?, Wilaya = ?, 
                                    numero_tlf_utilisateur = ?, gender = ?, role_id = ?
                                WHERE user_id = ?");
        $stmt->bind_param("sssssii", $name, $email, $wilaya, $phone, $gender, $role_id, $user_id);

        if ($stmt->execute()) {
            $success = "User updated successfully.";
            // Refresh user data
        } else {
            $error = "Error updating user: " . $conn->error;
        }

        $stmt->close();
    }
}

// Fetch user data to display in the form
$result = $conn->query("SELECT * FROM utilisateurs WHERE user_id = $user_id");
if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// Fetch all roles for the dropdown
$roles_result = $conn->query("SELECT * FROM roles ORDER BY role_id ASC");
$roles = [];
if ($roles_result->num_rows > 0) {
    while($row = $roles_result->fetch_assoc()) {
        $roles[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="CRUD.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .form-container {
            background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-submit { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <?php include 'header.php'; ?>

            <div class="dashboard-content">
                <div class="form-container">
                    <div class="form-header">
                        <h2>Update User Information</h2>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="Nom_Complet" value="<?= htmlspecialchars($user['Nom_Complet'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="Email" value="<?= htmlspecialchars($user['Email'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Wilaya</label>
                            <input type="text" name="Wilaya" value="<?= htmlspecialchars($user['Wilaya'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="numero_tlf_utilisateur" value="<?= htmlspecialchars($user['numero_tlf_utilisateur'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Role (Employee Management)</label>
                            <select name="role_id">
                                <option value="">No Role (Regular User)</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['role_id'] ?>" <?= ($user['role_id'] == $role['role_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['nom_role']) ?> - <?= htmlspecialchars($role['description']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p style="font-size: 0.85em; color: #666; margin-top: 5px;">
                                - <b>Apprenant</b>: Standard student.<br>
                                - <b>Commercial</b>: Manage sales & marketing.<br>
                                - <b>Pédagogique</b>: Create & manage courses.<br>
                                - <b>Marketing</b>: View analytics.<br>
                                - <b>Admin</b>: Full access.
                            </p>
                        </div>

                        <div style="margin-top: 30px;">
                            <button type="submit" class="btn-submit">Save Changes</button>
                            <a href="users.php" class="back-link" style="display:block; text-align:center; margin-top:10px;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>