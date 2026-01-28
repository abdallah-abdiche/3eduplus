<?php
session_start();
require_once "config.php";
require_once "auth.php";

checkAuth();
$user = getCurrentUser();
$user_id = $user['id'];

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($quiz_id === 0) {
    header('Location: inscription.php');
    exit();
}

// Fetch quiz details
$quiz_query = "SELECT * FROM quizzes WHERE id = ?";
$q_stmt = $conn->prepare($quiz_query);
$q_stmt->bind_param("i", $quiz_id);
$q_stmt->execute();
$quiz = $q_stmt->get_result()->fetch_assoc();
$q_stmt->close();

if (!$quiz) {
    header('Location: inscription.php');
    exit();
}

// Fetch questions and options
$questions_query = "SELECT q.*, o.option_text, o.option_index 
                   FROM questions q
                   LEFT JOIN options o ON q.id = o.question_id
                   WHERE q.quiz_id = ?
                   ORDER BY q.id, o.option_index";

$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$questions_data = [];
while ($row = $result->fetch_assoc()) {
    $q_id = $row['id'];
    if (!isset($questions_data[$q_id])) {
        $questions_data[$q_id] = [
            'id' => $row['id'],
            'question_text' => $row['question_text'],
            'correct_option' => $row['correct_option'],
            'options' => []
        ];
    }
    if ($row['option_text'] !== null) {
        $questions_data[$q_id]['options'][] = [
            'text' => $row['option_text'],
            'index' => $row['option_index']
        ];
    }
}
$questions = array_values($questions_data);
$stmt->close();

if (isset($_POST['submit_quiz'])) {
    $score = 0;
    foreach ($questions as $q) {
        $q_id = $q['id'];
        if (isset($_POST['question_'.$q_id]) && (int)$_POST['question_'.$q_id] === (int)$q['correct_option']) {
            $score++;
        }
    }
    $total = count($questions);
    $percentage = ($total > 0) ? ($score / $total) * 100 : 0;
    
    $_SESSION['quiz_result'] = [
        'score' => $score,
        'total' => $total,
        'percentage' => $percentage
    ];
    header("Location: quiz_result.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($quiz['titre']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .quiz-container { max-width: 800px; margin: 40px auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .question { margin-bottom: 30px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .question h4 { margin-bottom: 15px; }
        .option { display: block; padding: 10px; margin-bottom: 5px; background: #f8fafc; border-radius: 4px; cursor: pointer; transition: 0.2s; }
        .option:hover { background: #e2e8f0; }
        .option input { margin-right: 10px; }
        .submit-btn { width: 100%; padding: 15px; background: #28a745; color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>
    <div class="quiz-container">
        <h1><?php echo htmlspecialchars($quiz['titre']); ?></h1>
        <form method="POST">
            <?php foreach ($questions as $idx => $q): ?>
                <div class="question">
                    <h4><?php echo ($idx + 1) . ". " . htmlspecialchars($q['question_text']); ?></h4>
                    <?php foreach ($q['options'] as $o): ?>
                        <label class="option">
                            <input type="radio" name="question_<?php echo $q['id']; ?>" value="<?php echo $o['index']; ?>" required>
                            <?php echo htmlspecialchars($o['text']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" name="submit_quiz" class="submit-btn">Soumettre mes réponses</button>
        </form>
    </div>
</body>
</html>
