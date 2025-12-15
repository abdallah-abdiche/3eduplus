<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

checkAuth();
$user = getCurrentUser();

$cart_query = "SELECT p.formation_id, p.prix_unitaire, f.titre, f.prix as prix_courant
               FROM panier p
               JOIN formations f ON p.formation_id = f.formation_id
               WHERE p.utilisateur_id = ?
               ORDER BY p.date_ajout DESC";
$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bind_param("i", $user['id']);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_items = [];
$total = 0;

while ($item = $cart_result->fetch_assoc()) {
    $cart_items[] = $item;
    $total += $item['prix_unitaire'];
}
$cart_stmt->close();

if (isset($_POST['remove_item'])) {
    $item_id = (int)$_POST['item_id'];
    $delete_stmt = $conn->prepare("DELETE FROM panier WHERE id = ? AND utilisateur_id = ?");
    $delete_stmt->bind_param("ii", $item_id, $user['id']);
    $delete_stmt->execute();
    $delete_stmt->close();
    header('Location: cart.php');
    exit();
}

if (isset($_POST['checkout'])) {
    if (count($cart_items) > 0) {
        $_SESSION['checkout_total'] = $total;
        $_SESSION['checkout_items'] = $cart_items;
        header('Location: payment.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - 3edu+</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { margin-bottom: 30px; }
        .header h1 { margin: 0 0 10px 0; }
        .header a { color: #007bff; text-decoration: none; }
        .header a:hover { text-decoration: underline; }
        .cart-table { width: 100%; border-collapse: collapse; background: white; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); table-layout: fixed; }
        .cart-table th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; }
        .cart-table td { padding: 15px; border-bottom: 1px solid #dee2e6; word-wrap: break-word; overflow: hidden; text-overflow: ellipsis; }
        .cart-table td:first-child { max-width: 400px; }
        .cart-table tr:hover { background: #f9f9f9; }
        .remove-btn { 
            background: linear-gradient(135deg, #dc3545, #c82333); 
            color: white; 
            border: none; 
            padding: 27px 65px; 
            border-radius: 10px; 
            cursor: pointer; 
            font-size: 16px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.35);
        }
        .remove-btn:hover { 
            background: linear-gradient(135deg, #c82333, #a71d2a); 
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 53, 69, 0.45);
        }
        .remove-btn i {
            font-size: 16px;
        }
        .cart-summary { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: right; }
        .summary-row { display: flex; justify-content: flex-end; margin: 10px 0; }
        .summary-total { font-size: 24px; font-weight: bold; color: #007bff; border-top: 2px solid #dee2e6; padding-top: 15px; margin-top: 15px; }
        .checkout-btn { background: #28a745; color: white; border: none; padding: 12px 30px; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 15px; }
        .checkout-btn:hover { background: #218838; }
        .checkout-btn:disabled { background: #ccc; cursor: not-allowed; }
        .empty-message { text-align: center; padding: 40px; background: white; border-radius: 8px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Votre Panier</h1>
            <a href="formation.php"><i class="fas fa-arrow-left"></i> Retour aux formations</a>
        </div>

        <?php if (count($cart_items) > 0): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Formation</th>
                        <th style="text-align: center;">Prix</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['titre']); ?></td>
                            <td style="text-align: center;"><?php echo number_format($item['prix_unitaire'], 2, ',', ' '); ?> $</td>
                            <td style="text-align: center;">
                                <form method="POST" action="cart.php" style="display: inline;">
                                    <input type="hidden" name="item_id" value="<?php echo $item['formation_id']; ?>">
                                    <button type="submit" name="remove_item" class="remove-btn" onclick="return confirm('Êtes-vous sûr?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Sous-total:</span>
                    <span><?php echo number_format($total, 2, ',', ' '); ?> $</span>
                </div>
                <div class="summary-row">
                    <span>Frais (TVA):</span>
                    <span><?php echo number_format($total * 0.17, 2, ',', ' '); ?> $</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span><?php echo number_format($total * 1.17, 2, ',', ' '); ?> $</span>
                </div>
                <form method="POST" action="cart.php" style="margin: 0;">
                    <button type="submit" name="checkout" class="checkout-btn">
                        <i class="fas fa-credit-card"></i> Procéder au paiement
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="empty-message">
                <i class="fas fa-shopping-cart" style="font-size: 48px; margin-bottom: 20px; display: block;"></i>
                <p style="font-size: 18px;">Votre panier est vide.</p>
                <a href="formation.php" style="color: #007bff; text-decoration: none; margin-top: 20px; display: inline-block;">Consulter nos formations</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
