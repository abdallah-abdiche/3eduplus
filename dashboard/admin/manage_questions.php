<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin']);

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
if ($quiz_id === 0) {
    header('Location: manage_quizzes.php');
    exit();
}

$quiz = $conn->query("SELECT * FROM quizzes WHERE id = $quiz_id")->fetch_assoc();

if (isset($_POST['add_question'])) {
    $text = $_POST['question_text'];
    $correct = (int)$_POST['correct_index'];
    $options = $_POST['options'];

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, correct_option) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $quiz_id, $text, $correct);
        $stmt->execute();
        $new_q_id = $conn->insert_id;
        $stmt->close();

        $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, option_index) VALUES (?, ?, ?)");
        foreach ($options as $idx => $opt_text) {
            $opt_stmt->bind_param("isi", $new_q_id, $opt_text, $idx);
            $opt_stmt->execute();
        }
        $opt_stmt->close();
        
        $conn->commit();
        $message = "Question ajoutée !";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Erreur: " . $e->getMessage();
    }
}

$questions_query = "SELECT q.*, o.option_text, o.option_index 
                   FROM questions q
                   LEFT JOIN options o ON q.id = o.question_id
                   WHERE q.quiz_id = $quiz_id
                   ORDER BY q.id, o.option_index";
$result = $conn->query($questions_query);
$questions = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $q_id = $row['id'];
        if (!isset($questions[$q_id])) {
            $questions[$q_id] = [
                'id' => $row['id'],
                'question_text' => $row['question_text'],
                'correct_option' => $row['correct_option'],
                'options' => []
            ];
        }
        if ($row['option_text'] !== null) {
            $questions[$q_id]['options'][] = [
                'text' => $row['option_text'],
                'index' => $row['option_index']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les Questions - <?php echo htmlspecialchars($quiz['titre']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .container { max-width: 900px; margin: 40px auto; padding: 20px; }
        .q-form { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .q-card { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 5px solid #28a745; position: relative; }
        .opt-item { padding: 8px; margin: 5px 0; background: #f8fafc; border-radius: 4px; display: flex; align-items: center; gap: 10px; }
        .correct { background: #dcfce7; color: #166534; font-weight: bold; border: 1px solid #28a745; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
        .opt-input-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .btn-save { background: #28a745; color: white; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; font-weight: 700; width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        <a href="manage_quizzes.php" style="color: #007bff; text-decoration: none; margin-bottom: 20px; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Retour aux Quizzes
        </a>

        <h2>Gérer les Questions : <?php echo htmlspecialchars($quiz['titre']); ?></h2>

        <div class="q-form">
            <h3>Ajouter une Question</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Texte de la Question</label>
                    <textarea name="question_text" required placeholder="Saisissez la question ici..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Options de réponse (Cochez la bonne réponse)</label>
                    <?php for($i=0; $i<4; $i++): ?>
                        <div class="opt-input-row">
                            <input type="radio" name="correct_index" value="<?php echo $i; ?>" <?php echo $i===0 ? 'checked' : ''; ?>>
                            <input type="text" name="options[]" placeholder="Option <?php echo $i+1; ?>" required>
                        </div>
                    <?php endfor; ?>
                </div>

                <button type="submit" name="add_question" class="btn-save">Enregistrer la Question</button>
            </form>
        </div>

        <div class="questions-list">
            <h3>Questions existantes (<?php echo count($questions); ?>)</h3>
            <?php foreach ($questions as $idx => $q): ?>
                <div class="q-card">
                    <h4><?php echo ($idx + 1) . ". " . htmlspecialchars($q['question_text']); ?></h4>
                    <div style="margin-top: 15px;">
                        <?php foreach ($q['options'] as $o): ?>
                            <div class="opt-item <?php echo $o['index'] == $q['correct_option'] ? 'correct' : ''; ?>">
                                <?php if($o['index'] == $q['correct_option']): ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php else: ?>
                                    <i class="fas fa-circle" style="font-size: 0.5rem; color: #cbd5e1;"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($o['text']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (count($questions) === 0): ?>
                <p style="text-align: center; color: #64748b; padding: 40px;">Aucune question ajoutée pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
