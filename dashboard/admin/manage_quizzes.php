<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin']);

$message = '';

if (isset($_POST['add_quiz'])) {
    $titre = $_POST['titre'];
    $target = $_POST['target'];
    $target_id = (int)$_POST['target_id'];
    
    if ($target === 'formation') {
        $stmt = $conn->prepare("INSERT INTO quizzes (formation_id, titre) VALUES (?, ?)");
    } else {
        $stmt = $conn->prepare("INSERT INTO quizzes (evenement_id, titre) VALUES (?, ?)");
    }
    
    $stmt->bind_param("is", $target_id, $titre);
    if ($stmt->execute()) {
        $message = "Quiz ajouté avec succès !";
    }
    $stmt->close();
}

$quizzes_query = "SELECT q.*, f.titre as formation_titre, e.titre as event_titre 
                  FROM quizzes q 
                  LEFT JOIN formations f ON q.formation_id = f.formation_id 
                  LEFT JOIN evenements e ON q.evenement_id = e.evenement_id";
$quizzes = $conn->query($quizzes_query)->fetch_all(MYSQLI_ASSOC);

$formations = $conn->query("SELECT formation_id, titre FROM formations")->fetch_all(MYSQLI_ASSOC);
$events = $conn->query("SELECT evenement_id, titre FROM evenements")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Quizzes - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="CRUD.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .quiz-form { background: white; padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .quiz-form h3 { margin-bottom: 20px; color: #1e3a8a; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: flex-end; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 0.9rem; font-weight: 500; }
        .form-group select, .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
        .btn-add { background: #28a745; color: white; padding: 11px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .quiz-table { width: 100%; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .quiz-table th, .quiz-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .badge-formation { background: #e0f2fe; color: #0369a1; }
        .badge-event { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <?php include 'header.php'; ?>
            
            <div class="dashboard-content">
                <div class="page-header">
                    <h2><i class="fas fa-question-circle"></i> Gestion des Quizzes</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>

                <div class="quiz-form">
                    <h3>Ajouter un nouveau Quiz</h3>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Titre du Quiz</label>
                                <input type="text" name="titre" required placeholder="Ex: Évaluation finale">
                            </div>
                            <div class="form-group">
                                <label>Type de cible</label>
                                <select name="target" id="target-select" onchange="toggleSelect()">
                                    <option value="formation">Formation</option>
                                    <option value="evenement">Événement</option>
                                </select>
                            </div>
                            <div class="form-group" id="formation-select-group">
                                <label>Sélectionner la formation</label>
                                <select name="target_id" id="formation-id">
                                    <?php foreach ($formations as $f): ?>
                                        <option value="<?php echo $f['formation_id']; ?>"><?php echo htmlspecialchars($f['titre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" id="event-select-group" style="display:none;">
                                <label>Sélectionner l'événement</label>
                                <select name="target_id" id="event-id" disabled>
                                    <?php foreach ($events as $e): ?>
                                        <option value="<?php echo $e['evenement_id']; ?>"><?php echo htmlspecialchars($e['titre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="add_quiz" class="btn-add">Ajouter</button>
                        </div>
                    </form>
                </div>

                <div class="quiz-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Cible</th>
                                <th>Nom</th>
                                <th>Questions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizzes as $q): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($q['titre']); ?></strong></td>
                                    <td>
                                        <span class="badge <?php echo $q['formation_id'] ? 'badge-formation' : 'badge-event'; ?>">
                                            <?php echo $q['formation_id'] ? 'Formation' : 'Événement'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($q['formation_titre'] ?? $q['event_titre']); ?></td>
                                    <td>
                                        <?php 
                                        $qid = $q['id'];
                                        $count = $conn->query("SELECT COUNT(*) FROM questions WHERE quiz_id = $qid")->fetch_row()[0];
                                        echo $count;
                                        ?>
                                    </td>
                                    <td>
                                        <a href="manage_questions.php?quiz_id=<?php echo $q['id']; ?>" class="btn-edit" title="Gérer les questions">
                                            <i class="fas fa-list"></i> Questions
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSelect() {
            const target = document.getElementById('target-select').value;
            const fGroup = document.getElementById('formation-select-group');
            const eGroup = document.getElementById('event-select-group');
            const fSelect = document.getElementById('formation-id');
            const eSelect = document.getElementById('event-id');

            if (target === 'formation') {
                fGroup.style.display = 'block';
                eGroup.style.display = 'none';
                fSelect.disabled = false;
                eSelect.disabled = true;
            } else {
                fGroup.style.display = 'none';
                eGroup.style.display = 'block';
                fSelect.disabled = true;
                eSelect.disabled = false;
            }
        }
    </script>
</body>
</html>
