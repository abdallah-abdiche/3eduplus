<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Check if user is logged in
checkAuth();

$user = getCurrentUser();

// Fetch formations from database
$query = "SELECT * FROM formations ORDER BY date_creation DESC";
$result = $conn->query($query);
$formations = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $formations[] = $row;
    }
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $formation_id = isset($_POST['formation_id']) ? (int)$_POST['formation_id'] : 0;
    
    if ($formation_id > 0) {
        $check_stmt = $conn->prepare("SELECT formation_id, prix FROM formations WHERE formation_id = ?");
        $check_stmt->bind_param("i", $formation_id);
        $check_stmt->execute();
        $formation_result = $check_stmt->get_result();
        
        if ($formation_result->num_rows > 0) {
            $formation = $formation_result->fetch_assoc();
            
            // Check if already in cart
            $cart_check = $conn->prepare("SELECT id FROM panier WHERE utilisateur_id = ? AND formation_id = ?");
            $cart_check->bind_param("ii", $user['id'], $formation_id);
            $cart_check->execute();
            
            if ($cart_check->get_result()->num_rows > 0) {
                $_SESSION['message'] = ['type' => 'info', 'text' => 'Cette formation est déjà dans votre panier.'];
            } else {
                $insert_stmt = $conn->prepare("INSERT INTO panier (utilisateur_id, formation_id, prix_unitaire) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("iid", $user['id'], $formation_id, $formation['prix']);
                
                if ($insert_stmt->execute()) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Formation ajoutée au panier!'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de l\'ajout au panier.'];
                }
                $insert_stmt->close();
            }
            $cart_check->close();
        }
        $check_stmt->close();
    }
    
    header('Location: courses.php');
    exit();
}

// Get cart count
$cart_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM panier WHERE utilisateur_id = ?");
$cart_count_stmt->bind_param("i", $user['id']);
$cart_count_stmt->execute();
$cart_count = $cart_count_stmt->get_result()->fetch_assoc()['count'];
$cart_count_stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formations - 3edu+</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; }
        .user-info { display: flex; gap: 20px; align-items: center; }
        .cart-link { display: flex; align-items: center; gap: 8px; padding: 10px 15px; background: #007bff; color: white; border-radius: 5px; text-decoration: none; }
        .cart-badge { background: #dc3545; padding: 2px 8px; border-radius: 50%; font-size: 12px; }
        .message { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .message.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .courses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .course-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
        .course-card:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .course-image { width: 100%; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; }
        .course-content { padding: 20px; }
        .course-category { display: inline-block; background: #e7f3ff; color: #007bff; padding: 4px 12px; border-radius: 20px; font-size: 12px; margin-bottom: 10px; }
        .course-title { font-size: 18px; font-weight: bold; margin: 10px 0; }
        .course-description { color: #666; font-size: 14px; margin: 10px 0; line-height: 1.5; }
        .course-meta { display: flex; justify-content: space-between; align-items: center; margin: 15px 0; font-size: 13px; color: #999; }
        .course-price { font-size: 24px; font-weight: bold; color: #007bff; margin: 15px 0; }
        .course-button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; transition: background 0.3s; }
        .course-button:hover { background: #0056b3; }
        .logout-btn { padding: 10px 15px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .logout-btn:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Nos Formations</h1>
                <p style="margin: 5px 0; color: #666;">Bienvenue, <?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <div class="user-info">
                <a href="cart.php" class="cart-link">
                    <i class="fas fa-shopping-cart"></i>
                    Panier
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <button class="logout-btn" onclick="if(confirm('Êtes-vous sûr?')) window.location.href='logout.php'">
                    Déconnexion
                </button>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message']['type']; ?>">
                <?php echo htmlspecialchars($_SESSION['message']['text']); ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="courses-grid">
            <?php if (!empty($formations)): ?>
                <?php foreach ($formations as $formation): ?>
                    <div class="course-card">
                        <div class="course-image">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="course-content">
                            <span class="course-category"><?php echo htmlspecialchars($formation['categorie'] ?? 'Sans catégorie'); ?></span>
                            <h3 class="course-title"><?php echo htmlspecialchars($formation['titre']); ?></h3>
                            <p class="course-description"><?php echo htmlspecialchars(substr($formation['description'] ?? '', 0, 80)) . '...'; ?></p>
                            <div class="course-meta">
                                <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($formation['duree'] ?? 'N/A'); ?></span>
                                <span><i class="fas fa-star"></i> <?php echo htmlspecialchars($formation['niveau'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="course-price"><?php echo number_format($formation['prix'], 2, ',', ' '); ?> DA</div>
                            <form method="POST" action="courses.php" style="margin: 0;">
                                <input type="hidden" name="formation_id" value="<?php echo $formation['formation_id']; ?>">
                                <button type="submit" name="add_to_cart" class="course-button">
                                    <i class="fas fa-plus"></i> Ajouter au panier
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <p style="color: #999; font-size: 18px;">Aucune formation disponible pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
