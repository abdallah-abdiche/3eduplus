<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

checkAuth();
$user = getCurrentUser();

// Check if coming from cart or event signup
if (!isset($_SESSION['checkout_total']) || !isset($_SESSION['checkout_items'])) {
    header('Location: formation.php');
    exit();
}

$checkout_type = isset($_SESSION['checkout_type']) ? $_SESSION['checkout_type'] : 'formation';

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
        $payment_stmt = $conn->prepare("INSERT INTO paiements (user_id, montant, montant_total, methode_paiement, reference_transaction, statut, date_paiement) VALUES (?, ?, ?, ?, ?, 'Validé', NOW())");
        $payment_stmt->bind_param("iddss", $user['id'], $total, $total, $payment_method, $transaction_ref);
        $payment_stmt->execute();
        $payment_id = $conn->insert_id;
        $payment_stmt->close();
        
        if ($checkout_type === 'event') {
            // Add payment-event relationship and create event enrollment
            $event_id = $_SESSION['checkout_event_id'];
            $event_stmt = $conn->prepare("INSERT INTO event_inscriptions (evenement_id, user_id) VALUES (?, ?)");
            $event_stmt->bind_param("ii", $event_id, $user['id']);
            $event_stmt->execute();
            $event_stmt->close();
            
            $items_list_str = "Événement: " . $items[0]['titre'];
        } else {
            // Add payment-formation relationships and create enrollments
            $formation_stmt = $conn->prepare("INSERT INTO paiement_formations (paiement_id, formation_id, prix_paye) VALUES (?, ?, ?)");
            $enrollment_stmt = $conn->prepare("INSERT INTO inscriptions (user_id, formation_id, statut_inscription) VALUES (?, ?, ?)");
            $status = "Validé";
            
            foreach ($items as $item) {
                $formation_id = $item['formation_id'] ?? $item['id'];
                $prix = $item['prix_unitaire'];
                
                $formation_stmt->bind_param("iid", $payment_id, $formation_id, $prix);
                $formation_stmt->execute();
                
                $enrollment_stmt->bind_param("iis", $user['id'], $formation_id, $status);
                $enrollment_stmt->execute();
            }
            $formation_stmt->close();
            $enrollment_stmt->close();
            
            $items_list_str = implode(', ', array_map(function($item) { return htmlspecialchars($item['titre']); }, $items));
        }
        
        // Generate receipt
        $numero_recu = 'REC-' . date('Ymd') . '-' . str_pad($payment_id, 6, '0', STR_PAD_LEFT);
        
        $receipt_html = generateReceiptHTML($numero_recu, $user, $items, $total, $transaction_ref, $checkout_type);
        
        $receipt_stmt = $conn->prepare("INSERT INTO recus (paiement_id, utilisateur_id, numero_recu, formations_achetees, montant_total, contenu_html) VALUES (?, ?, ?, ?, ?, ?)");
        $receipt_stmt->bind_param("iissds", $payment_id, $user['id'], $numero_recu, $items_list_str, $total, $receipt_html);
        $receipt_stmt->execute();
        $receipt_id = $conn->insert_id;
        $receipt_stmt->close();
        
        $conn->commit();
        
        // Clear cart if type is formation
        if ($checkout_type === 'formation') {
            $clear_cart = $conn->prepare("DELETE FROM panier WHERE utilisateur_id = ?");
            $clear_cart->bind_param("i", $user['id']);
            $clear_cart->execute();
            $clear_cart->close();
        }
        
        // Clear session
        unset($_SESSION['checkout_total'], $_SESSION['checkout_items'], $_SESSION['checkout_type'], $_SESSION['checkout_event_id']);
        $_SESSION['receipt_id'] = $receipt_id;
        $_SESSION['numero_recu'] = $numero_recu;
        
        $redirect_to = 'receipt.php?id=' . $receipt_id;
        header('Location: ' . $redirect_to);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Erreur lors du traitement du paiement: ' . $e->getMessage();
    }
}

function generateReceiptHTML($numero_recu, $user, $items, $total, $transaction_ref, $type = 'formation') {
    $now = date('d/m/Y H:i');
    $label = ($type === 'event') ? 'Événement' : 'Formation';
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
                    <span>{$now}</span>
                </div>
            </div>
            
            <div class="items">
                <h3>{$label}(s) acheté(e)s:</h3>
    HTML;
    
    foreach ($items as $item) {
        $html .= <<<HTML
                <div class="item-row">
                    <div style="display: flex; justify-content: space-between;">
                        <span>{$item['titre']}</span>
                        <span>{$item['prix_unitaire']} DA</span>
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
                    <span>{$total} DA</span>
                </div>
                <div class="receipt-row">
                    <span>TVA (17%):</span>
                    <span>{$tva} DA</span>
                </div>
                <div class="receipt-row receipt-total">
                    <span>TOTAL:</span>
                    <span>{$total_with_tva} DA</span>
                </div>
            </div>
            
            <p style="text-align: center; color: #666; margin-top: 30px; font-size: 12px;">
                Merci pour votre achat! Vous pouvez maintenant accéder à votre contenu.
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

        /* Loading Overlay */
        #loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            z-index: 9999;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .loader {
            width: 80px;
            height: 80px;
            border: 8px solid #f3f3f3;
            border-top: 8px solid #06D6A0;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 1.5rem;
            color: #1e3a8a;
            font-weight: 600;
        }
        .loading-subtext {
            color: #64748b;
            margin-top: 10px;
        }

        /* Payment Form Details */
        .payment-details-section {
            margin-top: 20px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            display: none;
        }
        .payment-details-section.active {
            display: block;
        }
        .input-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            font-size: 0.9rem;
            color: #475569;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div id="loading-overlay">
        <div class="loader"></div>
        <div class="loading-text">Traitement de votre paiement en cours...</div>
        <div class="loading-subtext">Veuillez ne pas fermer cette fenêtre.</div>
    </div>

    <div class="container">
        <a href="<?php echo ($checkout_type === 'event') ? 'evenements.php' : 'cart.php'; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        
        <div class="payment-card">
            <h1><i class="fas fa-credit-card"></i> Finaliser le paiement</h1>
            
            <div class="order-summary">
                <h3 style="margin-top: 0;">Résumé de la commande</h3>
                <?php foreach ($items as $item): ?>
                    <div class="summary-item">
                        <span><?php echo htmlspecialchars($item['titre']); ?></span>
                        <span><?php echo number_format($item['prix_unitaire'], 2, ',', ' '); ?> DA</span>
                    </div>
                <?php endforeach; ?>
                <div class="summary-item">
                    <span>TVA (17%)</span>
                    <span><?php echo number_format($total * 0.17, 2, ',', ' '); ?> DA</span>
                </div>
                <div class="summary-total">
                    Total: <?php echo number_format($total * 1.17, 2, ',', ' '); ?> DA
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
                </div>

                <!-- Card Details Section -->
                <div id="card-details" class="payment-details-section active">
                    <div class="input-group">
                        <label>Numéro de carte</label>
                        <input type="text" placeholder="0000 0000 0000 0000" maxlength="19">
                    </div>
                    <div class="input-row">
                        <div class="input-group">
                            <label>Date d'expiration</label>
                            <input type="text" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="input-group">
                            <label>CVV</label>
                            <input type="password" placeholder="***" maxlength="3">
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Nom sur la carte</label>
                        <input type="text" placeholder="Ex: Jean Dupont">
                    </div>
                </div>

                <!-- Bank Transfer Section -->
                <div id="bank-details" class="payment-details-section">
                    <div class="input-group">
                        <label>Numéro de compte (RIB/IBAN)</label>
                        <input type="text" placeholder="Saisissez votre numéro de compte">
                    </div>
                    <p style="font-size: 0.85rem; color: #64748b;">
                        Veuillez indiquer votre numéro de compte pour que nous puissions valider le virement.
                    </p>
                </div>

                <!-- Portefeuille Details Section (Optional) -->
                <div id="wallet-details" class="payment-details-section">
                    <div class="input-group">
                        <label>Numéro de téléphone associé au portefeuille</label>
                        <input type="text" placeholder="Ex: 05 50 12 34 56">
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" name="process_payment" class="pay-btn">
                        <i class="fas fa-lock"></i> Effectuer le paiement sécurisé
                    </button>
                </div>
            </form>

            <p style="text-align: center; color: #666; margin-top: 20px; font-size: 12px;">
                <i class="fas fa-shield-alt"></i> Paiement 100% sécurisé
            </p>
        </div>
    </div>

    <script>
        // Toggle payment details based on selection
        const radioButtons = document.querySelectorAll('input[name="payment_method"]');
        const cardSection = document.getElementById('card-details');
        const bankSection = document.getElementById('bank-details');
        const walletSection = document.getElementById('wallet-details');

        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                // Hide all sections
                cardSection.classList.remove('active');
                bankSection.classList.remove('active');
                walletSection.classList.remove('active');

                // Show selected section
                if (this.value === 'Carte bancaire') {
                    cardSection.classList.add('active');
                } else if (this.value === 'Virement bancaire') {
                    bankSection.classList.add('active');
                } else if (this.value === 'Portefeuille électronique') {
                    walletSection.classList.add('active');
                }
            });
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const overlay = document.getElementById('loading-overlay');
            
            // Show loading overlay
            overlay.style.display = 'flex';
            
            // Wait 5 seconds before submitting
            setTimeout(function() {
                // Create a hidden input to simulate 'process_payment' button click since we prevented default
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'process_payment';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);
                
                form.submit();
            }, 5000);
        });
    </script>
</body>
</html>
