<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

checkAuth();
$user = getCurrentUser();

// Check if coming from cart
if (!isset($_SESSION['checkout_total']) || !isset($_SESSION['checkout_items'])) {
    header('Location: cart.php');
    exit();
}

$total = $_SESSION['checkout_total'];
$items = $_SESSION['checkout_items'];

// Handle payment submission
if (isset($_POST['process_payment'])) {
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Carte bancaire';
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create payment record
        $transaction_ref = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
        $payment_stmt = $conn->prepare("INSERT INTO paiements (utilisateur_id, montant_total, methode_paiement, reference_transaction) VALUES (?, ?, ?, ?)");
        $payment_stmt->bind_param("idss", $user['id'], $total, $payment_method, $transaction_ref);
        $payment_stmt->execute();
        $payment_id = $conn->insert_id;
        $payment_stmt->close();
        
        // Add payment-formation relationships and create enrollments
        $formation_stmt = $conn->prepare("INSERT INTO paiement_formations (paiement_id, formation_id, prix_paye) VALUES (?, ?, ?)");
        $enrollment_stmt = $conn->prepare("INSERT INTO inscriptions (utilisateur_id, formation_id) VALUES (?, ?)");
        
        foreach ($items as $item) {
            $formation_id = $item['formation_id'];
            $prix = $item['prix_unitaire'];
            
            $formation_stmt->bind_param("iid", $payment_id, $formation_id, $prix);
            $formation_stmt->execute();
            
            $enrollment_stmt->bind_param("ii", $user['id'], $formation_id);
            $enrollment_stmt->execute();
        }
        $formation_stmt->close();
        $enrollment_stmt->close();
        
        // Generate receipt
        $numero_recu = 'REC-' . date('Ymd') . '-' . str_pad($payment_id, 6, '0', STR_PAD_LEFT);
        
        // Create receipt HTML
        $formations_list = implode(', ', array_map(function($item) { return htmlspecialchars($item['titre']); }, $items));
        
        $receipt_html = generateReceiptHTML($numero_recu, $user, $items, $total, $transaction_ref);
        
        $receipt_stmt = $conn->prepare("INSERT INTO recus (paiement_id, utilisateur_id, numero_recu, formations_achetees, montant_total, contenu_html) VALUES (?, ?, ?, ?, ?, ?)");
        $receipt_stmt->bind_param("iissds", $payment_id, $user['id'], $numero_recu, $formations_list, $total, $receipt_html);
        $receipt_stmt->execute();
        $receipt_id = $conn->insert_id;
        $receipt_stmt->close();
        
        $conn->commit();
        
        // Clear cart
        $clear_cart = $conn->prepare("DELETE FROM panier WHERE utilisateur_id = ?");
        $clear_cart->bind_param("i", $user['id']);
        $clear_cart->execute();
        $clear_cart->close();
        
        // Clear session
        unset($_SESSION['checkout_total'], $_SESSION['checkout_items']);
        $_SESSION['receipt_id'] = $receipt_id;
        $_SESSION['numero_recu'] = $numero_recu;
        
        header('Location: receipt.php?id=' . $receipt_id);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Erreur lors du traitement du paiement: ' . $e->getMessage();
    }
}

function generateReceiptHTML($numero_recu, $user, $items, $total, $transaction_ref) {
    $html = <<<HTML
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; }
            .receipt { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
            .receipt-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 20px; }
            .receipt-header h1 { margin: 0; color: #007bff; }
            .receipt-info { margin: 20px 0; }
            .receipt-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
            .receipt-total { font-weight: bold; font-size: 18px; padding: 20px 0; text-align: right; }
            .items { margin: 20px 0; }
            .item-row { padding: 10px 0; border-bottom: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class="receipt">
            <div class="receipt-header">
                <h1>REÇU DE PAIEMENT</h1>
                <p style="margin: 5px 0;">3edu+ - Formation en ligne</p>
            </div>
            
            <div class="receipt-info">
                <div class="receipt-row">
                    <span>N° Reçu:</span>
                    <span>{$numero_recu}</span>
                </div>
                <div class="receipt-row">
                    <span>Référence Transaction:</span>
                    <span>{$transaction_ref}</span>
                </div>
                <div class="receipt-row">
                    <span>Date:</span>
                    <span>
            </div>
            
            <div class="items">
                <h3>Formations achetées:</h3>
    HTML;
    
    foreach ($items as $item) {
        $html .= <<<HTML
                <div class="item-row">
                    <div style="display: flex; justify-content: space-between;">
                        <span>{$item['titre']}</span>
                        <span>{$item['prix_unitaire']} $</span>
                    </div>
                </div>
        HTML;
    }
    
    $tva = $total * 0.17;
    $total_with_tva = $total * 1.17;
    
    $html .= <<<HTML
            </div>
            
            <div style="border-top: 2px solid #007bff; padding-top: 20px; margin-top: 20px;">
                <div class="receipt-row">
                    <span>Sous-total:</span>
                    <span>{$total} $</span>
                </div>
                <div class="receipt-row">
                    <span>TVA (17%):</span>
                    <span>{$tva} $</span>
                </div>
                <div class="receipt-row receipt-total">
                    <span>TOTAL:</span>
                    <span>{$total_with_tva} $</span>
                </div>
            </div>
            
            <p style="text-align: center; color: #666; margin-top: 30px; font-size: 12px;">
                Merci pour votre achat! Vous pouvez maintenant accéder à vos formations.
            </p>
        </div>
    </body>
    </html>
    HTML;
    
    return $html;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - 3edu+</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .payment-card { background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .payment-card h1 { margin-top: 0; color: #007bff; }
        .order-summary { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .summary-item { display: flex; justify-content: space-between; margin: 10px 0; padding: 10px 0; border-bottom: 1px solid #dee2e6; }
        .summary-item:last-child { border-bottom: none; }
        .summary-total { font-size: 20px; font-weight: bold; color: #007bff; text-align: right; padding-top: 10px; }
        .form-group { margin: 20px 0; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #007bff; }
        .payment-methods { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; }
        .payment-method { padding: 15px; border: 2px solid #ddd; border-radius: 5px; cursor: pointer; text-align: center; transition: all 0.3s; }
        .payment-method:hover { border-color: #007bff; background: #f0f7ff; }
        .payment-method input[type="radio"] { margin-right: 8px; }
        .payment-method input[type="radio"]:checked { accent-color: #007bff; }
        .pay-btn { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .pay-btn:hover { background: #218838; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="cart.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour au panier</a>
        
        <div class="payment-card">
            <h1><i class="fas fa-credit-card"></i> Finaliser le paiement</h1>
            
            <div class="order-summary">
                <h3 style="margin-top: 0;">Résumé de la commande</h3>
                <?php foreach ($items as $item): ?>
                    <div class="summary-item">
                        <span><?php echo htmlspecialchars($item['titre']); ?></span>
                        <span><?php echo number_format($item['prix_unitaire'], 2, ',', ' '); ?> $</span>
                    </div>
                <?php endforeach; ?>
                <div class="summary-item">
                    <span>TVA (17%)</span>
                    <span><?php echo number_format($total * 0.17, 2, ',', ' '); ?> $</span>
                </div>
                <div class="summary-total">
                    Total: <?php echo number_format($total * 1.17, 2, ',', ' '); ?> $
                </div>
            </div>

            <form method="POST" action="payment.php">
                <h3>Méthode de paiement</h3>
                <div class="payment-methods">
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="Carte bancaire" checked>
                        <i class="fas fa-credit-card" style="font-size: 24px; display: block; margin: 10px 0;"></i>
                        Carte bancaire
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="Portefeuille électronique">
                        <i class="fas fa-wallet" style="font-size: 24px; display: block; margin: 10px 0;"></i>
                        Portefeuille
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="Virement bancaire">
                        <i class="fas fa-bank" style="font-size: 24px; display: block; margin: 10px 0;"></i>
                        Virement
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="Paiement à la livraison">
                        <i class="fas fa-truck" style="font-size: 24px; display: block; margin: 10px 0;"></i>
                        À la livraison
                    </label>
                </div>

                <button type="submit" name="process_payment" class="pay-btn">
                    <i class="fas fa-lock"></i> Effectuer le paiement sécurisé
                </button>
            </form>

            <p style="text-align: center; color: #666; margin-top: 20px; font-size: 12px;">
                <i class="fas fa-shield-alt"></i> Paiement 100% sécurisé
            </p>
        </div>
    </div>
</body>
</html>
