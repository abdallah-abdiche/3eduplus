<?php
session_start();
if (!isset($_SESSION['quiz_result'])) {
    header('Location: inscription.php');
    exit();
}
$result = $_SESSION['quiz_result'];
unset($_SESSION['quiz_result']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat du Quiz</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .result-container { max-width: 600px; margin: 100px auto; text-align: center; padding: 40px; background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .score-circle { width: 150px; height: 150px; border-radius: 50%; border: 10px solid #28a745; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; font-size: 2rem; font-weight: 800; color: #28a745; }
        .btn { display: inline-block; padding: 12px 30px; background: #007bff; color: white; border-radius: 8px; text-decoration: none; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="result-container">
        <?php if ($result['percentage'] >= 70): ?>
            <i class="fas fa-trophy" style="font-size: 4rem; color: #ffc107; margin-bottom: 20px;"></i>
            <h1>Félicitations !</h1>
        <?php else: ?>
            <i class="fas fa-redo" style="font-size: 4rem; color: #dc3545; margin-bottom: 20px;"></i>
            <h1>Continuez vos efforts !</h1>
        <?php endif; ?>
        
        <div class="score-circle">
            <?php echo $result['score']; ?>/<?php echo $result['total']; ?>
        </div>
        
        <p>Votre score est de <?php echo round($result['percentage']); ?>%</p>
        
        <a href="inscription.php" class="btn">Retour à mes inscriptions</a>
    </div>
</body>
</html>
