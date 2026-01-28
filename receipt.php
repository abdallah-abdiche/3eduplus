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

// Calculate amounts
$subtotal = $receipt['montant_total'];
$tax_rate = 0.17;
$tax_amount = $subtotal * $tax_rate;
$total = $subtotal * (1 + $tax_rate);

// Detect if it's an event
$is_event = (strpos($receipt['formations_achetees'], 'Événement:') !== false);
$item_label = $is_event ? 'Événement' : 'Formation';
$redirect_url = $is_event ? 'evenements.php' : 'inscription.php';
$redirect_label = $is_event ? 'Voir mes événements' : 'Voir mes formations';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu <?php echo htmlspecialchars($receipt['numero_recu']); ?> - 3edu+</title>
    <link rel="icon" href="./LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding: 20px;
        }

        .receipt-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Success Message */
        .success-banner {
            background: linear-gradient(135deg, #06D6A0, #00B4D8);
            color: white;
            padding: 25px 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 20px;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-banner i {
            font-size: 3rem;
        }

        .success-content h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .success-content p {
            opacity: 0.9;
        }

        /* Receipt Card */
        .receipt-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Receipt Header */
        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .receipt-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .receipt-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        .receipt-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
        }

        .receipt-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
        }

        /* Receipt Content */
        .receipt-content {
            padding: 40px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .info-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid #667eea;
        }

        .info-section h3 {
            color: #667eea;
            font-size: 1.1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
            font-size: 0.9rem;
        }

        .info-value {
            font-weight: 600;
            color: #333;
        }

        /* Items Section */
        .items-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .items-section h3 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .receipt-items {
            background: white;
            border-radius: 10px;
            padding: 20px;
        }

        /* Total Section */
        .total-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            padding: 30px;
            border-radius: 15px;
            border: 2px solid #667eea;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 1.1rem;
        }

        .total-row.divider {
            border-top: 2px dashed #667eea;
            margin-top: 15px;
            padding-top: 20px;
        }

        .total-row.final {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            border-top: 2px solid #f0f0f0;
        }

        .receipt-footer p {
            color: #666;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #06D6A0, #00B4D8);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(6, 214, 160, 0.4);
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .success-banner,
            .action-buttons {
                display: none !important;
            }

            .receipt-card {
                box-shadow: none;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .receipt-header h1 {
                font-size: 2rem;
            }

            .total-row.final {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Success Banner -->
        <div class="success-banner">
            <i class="fas fa-check-circle"></i>
            <div class="success-content">
                <h2>Paiement réussi !</h2>
                <p>Merci pour votre achat. Vous pouvez maintenant accéder à vos <?php echo $is_event ? 'événements' : 'formations'; ?>.</p>
            </div>
        </div>

        <!-- Receipt Card -->
        <div class="receipt-card">
            <!-- Header -->
            <div class="receipt-header">
                <div class="receipt-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h1>REÇU DE PAIEMENT</h1>
                <p>3edu+ - Centre de Formation Professionnelle</p>
            </div>

            <!-- Content -->
            <div class="receipt-content">
                <!-- Info Grid -->
                <div class="info-grid">
                    <!-- Receipt Info -->
                    <div class="info-section">
                        <h3>
                            <i class="fas fa-receipt"></i>
                            Informations du reçu
                        </h3>
                        <div class="info-row">
                            <span class="info-label">Numéro de reçu</span>
                            <span class="info-value"><?php echo htmlspecialchars($receipt['numero_recu']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date d'émission</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($receipt['date_emission'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Heure</span>
                            <span class="info-value"><?php echo date('H:i', strtotime($receipt['date_emission'])); ?></span>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="info-section">
                        <h3>
                            <i class="fas fa-user"></i>
                            Informations client
                        </h3>
                        <div class="info-row">
                            <span class="info-label">Nom complet</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['nom'] ?? $user['name'] ?? $user['user_name'] ?? 'Client'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">ID Client</span>
                            <span class="info-value">#<?php echo htmlspecialchars($user['id']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- QR Code & Verification Section -->
                <div style="text-align: center; margin: 30px 0; padding: 30px; background: #fff; border: 2px dashed #e2e8f0; border-radius: 15px;">
                    <div style="margin-bottom: 15px;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($receipt['numero_recu']); ?>" alt="QR Code" style="width: 150px; height: 150px;">
                    </div>
                    <div style="color: #667eea; font-weight: 700; font-size: 1.2rem; letter-spacing: 2px;">
                        <?php echo htmlspecialchars($receipt['numero_recu']); ?>
                    </div>
                    <p style="color: #64748b; font-size: 0.85rem; margin-top: 5px;">Scannez ce code pour vérifier l'authenticité de votre reçu</p>
                    
                    <div style="display: inline-flex; align-items: center; gap: 8px; margin-top: 20px; padding: 8px 16px; background: #ecfdf5; color: #059669; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">
                        <i class="fas fa-check-shield"></i>
                        VÉRIFIÉ ET SÉCURISÉ
                    </div>
                </div>

                <!-- Items Section -->
                <div class="items-section">
                    <h3>
                        <i class="fas <?php echo $is_event ? 'fa-calendar-alt' : 'fa-graduation-cap'; ?>"></i>
                        <?php echo $item_label; ?>(s) acheté(e)s
                    </h3>
                    <div class="receipt-items">
                        <?php echo $receipt['contenu_html']; ?>
                    </div>
                </div>

                <!-- Total Section -->
                <div class="total-section">
                    <div class="total-row">
                        <span>Sous-total</span>
                        <span><?php echo number_format($subtotal, 0, ',', ' '); ?> DA</span>
                    </div>
                    <div class="total-row">
                        <span>TVA (17%)</span>
                        <span><?php echo number_format($tax_amount, 0, ',', ' '); ?> DA</span>
                    </div>
                    <div class="total-row divider final">
                        <span>TOTAL PAYÉ</span>
                        <span><?php echo number_format($total, 0, ',', ' '); ?> DA</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="receipt-footer">
                <p>
                    <i class="fas fa-shield-alt"></i>
                    <strong>Paiement 100% sécurisé</strong>
                </p>
                <p>
                    <i class="fas fa-check-circle"></i>
                    Merci d'avoir choisi 3edu+ pour votre formation professionnelle.
                </p>
                <p style="font-size: 0.9rem; margin-top: 15px;">
                    Vous pouvez maintenant accéder à vos <?php echo $is_event ? 'événements' : 'formations'; ?> depuis votre tableau de bord.
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="<?php echo $redirect_url; ?>" class="btn btn-success">
                <i class="fas <?php echo $is_event ? 'fa-calendar-alt' : 'fa-graduation-cap'; ?>"></i>
                <?php echo $redirect_label; ?>
            </a>
            <a href="formation.php" class="btn btn-secondary">
                <i class="fas fa-book"></i>
                Autres formations
            </a>
            <button class="btn btn-secondary" onclick="window.print()">
                <i class="fas fa-print"></i>
                Imprimer le reçu
            </button>
            <a href="<?php echo getDashboardUrl($user['role']); ?>" class="btn btn-primary">
                <i class="fas fa-tachometer-alt"></i>
                Tableau de bord
            </a>
        </div>
    </div>
</body>
</html>
