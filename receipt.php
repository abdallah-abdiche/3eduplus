<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

checkAuth();
$user = getCurrentUser();

$receipt_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($receipt_id === 0) {
    header('Location: formation.php');
    exit();
}

// Fetch receipt from database
$receipt_stmt = $conn->prepare("SELECT * FROM recus WHERE id = ? AND utilisateur_id = ?");
$receipt_stmt->bind_param("ii", $receipt_id, $user['id']);
$receipt_stmt->execute();
$receipt_result = $receipt_stmt->get_result();

if ($receipt_result->num_rows === 0) {
    echo "Reçu non trouvé.";
    exit();
}

$receipt = $receipt_result->fetch_assoc();
$receipt_stmt->close();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu - 3edu+</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .receipt-wrapper { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .receipt-header { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 40px; text-align: center; border-radius: 8px 8px 0 0; }
        .receipt-header h1 { margin: 0; font-size: 32px; }
        .receipt-header p { margin: 10px 0 0 0; opacity: 0.9; }
        .receipt-content { padding: 40px; }
        .receipt-section { margin: 30px 0; }
        .receipt-section h3 { margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .receipt-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-items { margin: 20px 0; }
        .item-row { display: grid; grid-template-columns: 1fr 200px; gap: 20px; padding: 15px 0; border-bottom: 1px solid #eee; }
        .item-row:last-child { border-bottom: none; }
        .total-section { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-top: 30px; }
        .total-row { display: flex; justify-content: space-between; font-size: 16px; padding: 8px 0; }
        .total-amount { font-size: 28px; font-weight: bold; color: #007bff; padding: 15px 0; }
        .receipt-footer { text-align: center; color: #666; margin-top: 30px; font-size: 12px; }
        .action-buttons { display: flex; gap: 15px; justify-content: center; margin-top: 30px; }
        .btn { padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .success-message { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
        @media print {
            body { background: white; }
            .action-buttons { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-message">
            <i class="fas fa-check-circle"></i> Merci pour votre achat! Votre reçu est ci-dessous.
        </div>

        <div class="receipt-wrapper">
            <div class="receipt-header">
                <h1><i class="fas fa-receipt"></i> REÇU DE PAIEMENT</h1>
                <p>3edu+ - Formation en ligne</p>
            </div>

            <div class="receipt-content">
                <div class="receipt-section">
                    <h3>Informations du reçu</h3>
                    <div class="receipt-row">
                        <span>N° Reçu:</span>
                        <strong><?php echo htmlspecialchars($receipt['numero_recu']); ?></strong>
                    </div>
                    <div class="receipt-row">
                        <span>Date d'émission:</span>
                        <strong><?php echo date('d/m/Y H:i', strtotime($receipt['date_emission'])); ?></strong>
                    </div>
                </div>

                <div class="receipt-section">
                    <h3>Informations du client</h3>
                    <div class="receipt-row">
                        <span>Nom:</span>
                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                    </div>
                    <div class="receipt-row">
                        <span>Email:</span>
                        <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                    </div>
                </div>

                <div class="receipt-section">
                    <h3>Formations achetées</h3>
                    <div class="receipt-items">
                        <?php echo $receipt['contenu_html']; ?>
                    </div>
                </div>

                <div class="total-section">
                    <div class="total-row">
                        <span>Sous-total:</span>
                        <span><?php echo number_format($receipt['montant_total'], 2, ',', ' '); ?> DA</span>
                    </div>
                    <div class="total-row">
                        <span>TVA (17%):</span>
                        <span><?php echo number_format($receipt['montant_total'] * 0.17, 2, ',', ' '); ?> DA</span>
                    </div>
                    <div class="total-row total-amount">
                        <span>TOTAL:</span>
                        <span><?php echo number_format($receipt['montant_total'] * 1.17, 2, ',', ' '); ?> DA</span>
                    </div>
                </div>

                <div class="receipt-footer">
                    <p><i class="fas fa-shield-alt"></i> Paiement 100% sécurisé</p>
                    <p>Merci d'avoir choisi 3edu+. Vous pouvez maintenant accéder à vos formations dans votre tableau de bord.</p>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="inscription.php" class="btn btn-primary">
                <i class="fas fa-graduation-cap"></i> Voir mes inscriptions
            </a>
            <a href="formation.php" class="btn btn-secondary">
                <i class="fas fa-book"></i> Voir autres formations
            </a>
            <button class="btn btn-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>
    </div>
</body>
</html>
