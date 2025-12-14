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

    // Simple validation (you can extend this)
    if ($name === '' || $email === '') {
        $error = "Name and email are required.";
    } else {
        // Use prepared statement to avoid SQL injection
        $stmt = $conn->prepare("UPDATE utilisateurs 
                                SET Nom_Complet = ?, Email = ?, Wilaya = ?, 
                                    numero_tlf_utilisateur = ?, gender = ?
                                WHERE user_id = ?");
        $stmt->bind_param("sssssi", $name, $email, $wilaya, $phone, $gender, $user_id);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - 3edu+</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="CRUD.css">
    <link rel="icon" href="../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        .form-header {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .form-header h2 {
            color: #333;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            border-color: #4361ee;
            outline: none;
        }
        .btn-submit {
            background: #4361ee;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-submit:hover {
            background: #3f37c9;
        }
        .btn-cancel {
            background: #f8f9fa;
            color: #333;
            padding: 12px 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        .btn-cancel:hover {
            background: #e9ecef;
        }
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
                        <li class="nav-item active">
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
                    <h1 class="header-logo">Edit User</h1>
                </div>
                <div class="header-right">
                    <div class="user-profile">
                         <img src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fstatic.vecteezy.com%2Fsystem%2Fresources%2Fpreviews%2F000%2F290%2F610%2Foriginal%2Fadministration-vector-icon.jpg&f=1&nofb=1&ipt=0c0a886cbda8307543dc1e414a300f5a4d50a9c8884b6fd80567d4bf75248a31" class="admin-avatar">
                        <span class="user-name">Admin</span>
                    </div>
                </div>
            </header>

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
                            <label>Gender</label>
                            <select name="gender">
                                <option value="Male" <?= ($user['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= ($user['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>

                        <div style="margin-top: 30px;">
                            <button type="submit" class="btn-submit">Save Changes</button>
                            <a href="users.php" class="btn-cancel">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>