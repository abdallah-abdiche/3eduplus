<?php
session_start();
require_once '../config.php';

// Check that user_id is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid user.";
    exit();
}

$user_id = (int) $_GET['id'];

// Handle form submission (update user)
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
    <title>Edit User</title>
</head>
<body>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <p style="color:green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post" action="">
    Full Name:
    <input type="text" name="Nom_Complet" value="<?= htmlspecialchars($user['Nom_Complet'] ?? '') ?>"><br>

    Email:
    <input type="email" name="Email" value="<?= htmlspecialchars($user['Email'] ?? '') ?>"><br>

    Wilaya:
    <input type="text" name="Wilaya" value="<?= htmlspecialchars($user['Wilaya'] ?? '') ?>"><br>

    Phone:
    <input type="text" name="numero_tlf_utilisateur" value="<?= htmlspecialchars($user['numero_tlf_utilisateur'] ?? '') ?>"><br>

    Gender:
    <input type="text" name="gender" value="<?= htmlspecialchars($user['gender'] ?? '') ?>"><br>

    <button type="submit">Save</button>
</form>

</body>
</html>