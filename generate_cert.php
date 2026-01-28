<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

checkAuth();

$formation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

if ($formation_id <= 0) exit("Invalid ID");

// Fetch Certificate data
$stmt = $conn->prepare("
    SELECT c.*, f.titre, u.Nom_Complet 
    FROM certificats c
    JOIN formations f ON c.formation_id = f.formation_id
    JOIN utilisateurs u ON c.user_id = u.user_id
    WHERE c.user_id = ? AND c.formation_id = ?
");
$stmt->bind_param("ii", $user_id, $formation_id);
$stmt->execute();
$cert = $stmt->get_result()->fetch_assoc();

if (!$cert) exit("Certificat non trouvé. Vous devez réussir le quiz d'abord.");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat - <?php echo htmlspecialchars($cert['Nom_Complet']); ?></title>
    <style>
        body { background: #525659; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: 'Times New Roman', serif; }
        .certificate-page { 
            width: 800px; height: 600px; background: white; padding: 50px; 
            border: 20px solid #4f46e5; position: relative; box-shadow: 0 0 20px rgba(0,0,0,0.5);
            background-image: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)), url('LogoEdu.png');
            background-size: cover; background-position: center;
        }
        .cert-header { text-align: center; color: #1e293b; }
        .cert-header h1 { font-size: 3rem; margin: 0; text-transform: uppercase; color: #4f46e5; }
        .cert-body { text-align: center; margin-top: 50px; }
        .cert-body p { font-size: 1.5rem; color: #475569; }
        .student-name { font-size: 2.5rem; font-weight: bold; color: #1e293b; margin: 20px 0; border-bottom: 2px solid #cbd5e1; display: inline-block; padding: 0 50px; }
        .course-title { font-size: 1.8rem; font-style: italic; color: #4f46e5; }
        .cert-footer { margin-top: 50px; display: flex; justify-content: space-between; align-items: flex-end; }
        .signature { text-align: center; border-top: 1px solid #000; width: 200px; padding-top: 10px; }
        .verify-code { position: absolute; bottom: 20px; right: 20px; font-size: 0.8rem; color: #94a3b8; }
        .print-btn { position: fixed; top: 20px; right: 20px; background: #10b981; color: white; padding: 10px 20px; border-radius: 5px; cursor: pointer; border: none; font-weight: bold; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Imprimer / Sauvegarder PDF</button>

    <div class="certificate-page">
        <div class="cert-header">
            <img src="LogoEdu.png" width="120" style="margin-bottom: 20px;">
            <h1>Certificat de Réussite</h1>
        </div>

        <div class="cert-body">
            <p>Ce certificat est fièrement décerné à</p>
            <div class="student-name"><?php echo htmlspecialchars($cert['Nom_Complet']); ?></div>
            <p>Pour avoir complété avec succès la formation professionnelle en :</p>
            <div class="course-title"><?php echo htmlspecialchars($cert['titre']); ?></div>
            <p style="margin-top: 30px; font-size: 1.1rem;">Délivré le <?php echo date('d F Y', strtotime($cert['date_obtention'])); ?></p>
        </div>

        <div class="cert-footer">
            <div class="signature">Directeur 3edu+</div>
            <img src="LogoEdu.png" width="60" style="opacity: 0.2;">
            <div class="signature">Sceau Officiel</div>
        </div>

        <div class="verify-code">Code de vérification : <?php echo $cert['code_verification']; ?></div>
    </div>
</body>
</html>
