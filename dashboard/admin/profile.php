<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin']);

$user_id = $_SESSION['user_id'];

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Nom_Complet'])) {
    $name = $conn->real_escape_string($_POST['Nom_Complet']);
    $phone = $conn->real_escape_string($_POST['numero_tlf_utilisateur']);
    $wilaya = $conn->real_escape_string($_POST['Wilaya']);

    $update_fields = "Nom_Complet='$name', numero_tlf_utilisateur='$phone', Wilaya='$wilaya'";

    // Handle Image Upload
    if (isset($_FILES['image_utilisateur']) && $_FILES['image_utilisateur']['error'] == 0) {
        $target_dir = "../../uploads/profiles/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["image_utilisateur"]["name"], PATHINFO_EXTENSION);
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array(strtolower($file_extension), $allowed_types)) {
            $file_name = uniqid() . "_profile." . $file_extension;
            $target_file = $target_dir . $file_name;
            $db_path = "uploads/profiles/" . $file_name; // Path stored in DB relative to root
            
            if (move_uploaded_file($_FILES["image_utilisateur"]["tmp_name"], $target_file)) {
                $update_fields .= ", image_utilisateur='$db_path'";
            } else {
                 $message .= " Failed to upload image.";
            }
        } else {
             $message .= " Invalid file type.";
        }
    }

    $update_sql = "UPDATE utilisateurs SET $update_fields WHERE user_id='$user_id'";
    if ($conn->query($update_sql) === TRUE) {
        $message = "Profile updated successfully!";
        $_SESSION['user_name'] = $name;
    } else {
        $message = "Error updating profile: " . $conn->error;
    }
}

$sql = "SELECT * FROM utilisateurs WHERE user_id = '$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="account/profile.css"> 
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <?php include 'header.php'; ?>

            <div class="dashboard-content">
                <div class="container" style="max-width: 100%; display: flex; gap: 20px;">
                    <div class="profile-sidebar" style="background: white; padding: 20px; border-radius: 8px; width: 300px; text-align: center;">
                        <?php 
                        $profile_img = !empty($user['image_utilisateur']) && file_exists("../../" . $user['image_utilisateur']) 
                            ? "../../" . $user['image_utilisateur'] 
                            : "https://ui-avatars.com/api/?name=" . urlencode($user['Nom_Complet']) . "&background=random&size=128";
                        ?>
                        <img src="<?php echo htmlspecialchars($profile_img); ?>" alt="Profile" class="profile-image" style="width: 100px; height: 100px; border-radius: 50%; margin-bottom: 10px; object-fit: cover; border: 2px solid #e2e8f0;">
                        <div class="profile-name" style="font-weight: bold; font-size: 1.2em;"><?php echo htmlspecialchars($user['Nom_Complet']); ?></div>
                        <div class="profile-email" style="color: #666;"><?php echo $user['Email']; ?></div>
                    </div>

                    <div class="main-content" style="flex: 1; background: white; padding: 20px; border-radius: 8px;">
                         <div class="tabs">
                            <div class="tab active" style="margin-bottom: 20px; font-weight: bold; border-bottom: 2px solid #2563eb; display: inline-block; padding-bottom: 5px;">Edit Profile</div>
                        </div>

                        <div id="settingsTab">
                            <?php if ($message): ?>
                                <div class="alert" style="padding: 10px; margin-bottom: 20px; border-radius: 4px; background-color: <?php echo strpos($message, 'Error') !== false ? '#fee2e2' : '#dcfce7'; ?>; color: <?php echo strpos($message, 'Error') !== false ? '#991b1b' : '#166534'; ?>;">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                    <div class="form-group">
                                        <label for="username" style="display: block; margin-bottom: 5px;">Full Name</label>
                                        <input type="text" id="username" name="Nom_Complet" value="<?php echo htmlspecialchars($user['Nom_Complet']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    </div>
                                    <div class="form-group">
                                        <label for="email" style="display: block; margin-bottom: 5px;">Email</label>
                                        <input type="email" id="email" name="Email" value="<?php echo htmlspecialchars($user['Email']); ?>" readonly style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                                    </div>
                                </div>

                                <div class="form-group full-width" style="margin-top: 15px;">
                                    <label for="profile_image" style="display: block; margin-bottom: 5px;">Profile Picture</label>
                                    <input type="file" id="profile_image" name="image_utilisateur" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <small style="color: #666;">Allowed formats: jpg, jpeg, png, gif</small>
                                </div>

                                <div class="form-group full-width" style="margin-top: 15px;">
                                    <label for="phone" style="display: block; margin-bottom: 5px;">Phone Number</label>
                                    <input type="tel" id="phone" name="numero_tlf_utilisateur" value="<?php echo htmlspecialchars($user['numero_tlf_utilisateur']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>

                                <div class="form-group full-width" style="margin-top: 15px;">
                                    <label for="wilaya" style="display: block; margin-bottom: 5px;">Wilaya</label>
                                    <input type="text" id="wilaya" name="Wilaya" value="<?php echo htmlspecialchars($user['Wilaya']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>

                                <div class="button-container" style="margin-top: 20px;">
                                    <button type="submit" class="save-button" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Save Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- JavaScript functionality inline or removed -->
</body>
</html>
