<?php
session_start();
require_once "config.php";
require_once "auth.php";

checkAuth();
$user = getCurrentUser();
$user_id = $user['id'];

// Get formation ID from URL
$formation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($formation_id === 0) {
    header('Location: inscription.php');
    exit();
}

// Check if user is enrolled in this course
$check_query = "SELECT i.*, f.* FROM inscriptions i 
                JOIN formations f ON i.formation_id = f.formation_id 
                WHERE i.user_id = ? AND i.formation_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $user_id, $formation_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Vous n'êtes pas inscrit à cette formation.";
    header('Location: inscription.php');
    exit();
}

$course = $result->fetch_assoc();
$check_stmt->close();

// Check if certificate already exists
$cert_check = $conn->prepare("SELECT * FROM certifications WHERE user_id = ? AND formation_id = ?");
$cert_check->bind_param("ii", $user_id, $formation_id);
$cert_check->execute();
$existing_cert = $cert_check->get_result()->fetch_assoc();
$cert_check->close();

$certificate = null;

// Generate new certificate if doesn't exist
if (!$existing_cert) {
    // Generate unique certificate number
    $numero_certificat = 'CERT-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('Y');
    $date_obtention = date('Y-m-d');
    $type_certificat = "Certificat de Réussite";
    $nom_complet = $user['name'];
    $titre_formation = $course['titre'];
    
    $insert = $conn->prepare("INSERT INTO certifications (user_id, formation_id, date_obtention, type_certificat, nom_complet, titre_formation, numero_certificat) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("iisssss", $user_id, $formation_id, $date_obtention, $type_certificat, $nom_complet, $titre_formation, $numero_certificat);
    
    if ($insert->execute()) {
        $certificate = [
            'certificat_id' => $conn->insert_id,
            'user_id' => $user_id,
            'formation_id' => $formation_id,
            'date_obtention' => $date_obtention,
            'type_certificat' => $type_certificat,
            'nom_complet' => $nom_complet,
            'titre_formation' => $titre_formation,
            'numero_certificat' => $numero_certificat
        ];
    }
    $insert->close();
} else {
    $certificate = $existing_cert;
}

if (!$certificate) {
    $_SESSION['error'] = "Erreur lors de la génération du certificat.";
    header('Location: course.php?id=' . $formation_id);
    exit();
}

// Format date for display
$date_formatted = date('d F Y', strtotime($certificate['date_obtention']));
$months_fr = [
    'January' => 'Janvier', 'February' => 'Février', 'March' => 'Mars',
    'April' => 'Avril', 'May' => 'Mai', 'June' => 'Juin',
    'July' => 'Juillet', 'August' => 'Août', 'September' => 'Septembre',
    'October' => 'Octobre', 'November' => 'Novembre', 'December' => 'Décembre'
];
foreach ($months_fr as $en => $fr) {
    $date_formatted = str_replace($en, $fr, $date_formatted);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificat - <?php echo htmlspecialchars($certificate['titre_formation']); ?></title>
    <link rel="icon" href="./LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .page-header {
            max-width: 900px;
            margin: 0 auto 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .back-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Segoe UI', sans-serif;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        .back-link:hover {
            opacity: 0.8;
        }
        .download-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Segoe UI', sans-serif;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        .download-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .certificate-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .certificate {
            background: linear-gradient(145deg, #fffef8 0%, #f5f3e8 100%);
            border: 15px solid #1a5276;
            border-radius: 10px;
            padding: 60px 80px;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .certificate::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 3px solid #c9a227;
            border-radius: 5px;
            pointer-events: none;
        }
        
        .certificate-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo-container {
            margin-bottom: 20px;
        }
        .logo-container img {
            height: 80px;
        }
        
        .certificate-title {
            font-size: 48px;
            color: #1a5276;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 8px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .certificate-subtitle {
            font-size: 18px;
            color: #666;
            letter-spacing: 4px;
            text-transform: uppercase;
        }
        
        .certificate-body {
            text-align: center;
            margin: 40px 0;
        }
        
        .presented-to {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .recipient-name {
            font-size: 42px;
            color: #1a5276;
            font-style: italic;
            margin-bottom: 30px;
            border-bottom: 3px solid #c9a227;
            display: inline-block;
            padding-bottom: 10px;
        }
        
        .completion-text {
            font-size: 18px;
            color: #444;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        
        .course-name {
            font-size: 28px;
            color: #1a5276;
            font-weight: bold;
            margin: 20px 0;
            padding: 15px 30px;
            background: linear-gradient(90deg, transparent, rgba(201, 162, 39, 0.1), transparent);
            display: inline-block;
        }
        
        .certificate-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 60px;
            padding-top: 30px;
        }
        
        .signature-block {
            text-align: center;
            flex: 1;
        }
        
        .signature-line {
            width: 200px;
            height: 2px;
            background: #1a5276;
            margin: 0 auto 10px;
        }
        
        .signature-name {
            font-size: 16px;
            color: #1a5276;
            font-weight: bold;
        }
        
        .signature-title {
            font-size: 12px;
            color: #666;
        }
        
        .seal-container {
            flex: 1;
            text-align: center;
        }
        
        .seal {
            width: 100px;
            height: 100px;
            border: 4px solid #c9a227;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            background: linear-gradient(135deg, #f9f3dc 0%, #e8dbb5 100%);
        }
        
        .seal i {
            font-size: 40px;
            color: #c9a227;
        }
        
        .date-block {
            flex: 1;
            text-align: center;
        }
        
        .date-value {
            font-size: 16px;
            color: #1a5276;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .date-label {
            font-size: 12px;
            color: #666;
        }
        
        .certificate-number {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #888;
            font-family: 'Courier New', monospace;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .page-header {
                display: none;
            }
            .certificate-container {
                max-width: 100%;
            }
            .certificate {
                box-shadow: none;
                border-width: 10px;
                padding: 40px 50px;
            }
        }
        
        @media (max-width: 768px) {
            .certificate {
                padding: 30px 20px;
                border-width: 8px;
            }
            .certificate-title {
                font-size: 28px;
                letter-spacing: 4px;
            }
            .recipient-name {
                font-size: 28px;
            }
            .course-name {
                font-size: 20px;
            }
            .certificate-footer {
                flex-direction: column;
                gap: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <a href="course.php?id=<?php echo $formation_id; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour au cours
        </a>
        <button class="download-btn" onclick="window.print()">
            <i class="fas fa-download"></i> Télécharger PDF
        </button>
    </div>

    <div class="certificate-container">
        <div class="certificate">
            <div class="certificate-header">
                <div class="logo-container">
                    <img src="./LogoEdu.png" alt="3edu+ Logo">
                </div>
                <h1 class="certificate-title">Certificat</h1>
                <p class="certificate-subtitle"><?php echo htmlspecialchars($certificate['type_certificat']); ?></p>
            </div>

            <div class="certificate-body">
                <p class="presented-to">Ce certificat est décerné à</p>
                <h2 class="recipient-name"><?php echo htmlspecialchars($certificate['nom_complet']); ?></h2>
                <p class="completion-text">
                    Pour avoir complété avec succès la formation
                </p>
                <div class="course-name">
                    <?php echo htmlspecialchars($certificate['titre_formation']); ?>
                </div>
                <p class="completion-text">
                    dispensée par le centre de formation 3edu+
                </p>
            </div>

            <div class="certificate-footer">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <p class="signature-name">Direction 3edu+</p>
                    <p class="signature-title">Directeur de Formation</p>
                </div>

                <div class="seal-container">
                    <div class="seal">
                        <i class="fas fa-award"></i>
                    </div>
                </div>

                <div class="date-block">
                    <p class="date-value"><?php echo $date_formatted; ?></p>
                    <p class="date-label">Date d'obtention</p>
                </div>
            </div>

            <div class="certificate-number">
                N° <?php echo htmlspecialchars($certificate['numero_certificat']); ?>
            </div>
        </div>
    </div>
</body>
</html>
