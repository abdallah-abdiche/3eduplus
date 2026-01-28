<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin']);

$message = '';
$error = '';

// Handle Add Session
if (isset($_POST['add_session'])) {
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $lieu = $_POST['lieu'];
    $formation_id = (int)$_POST['formation_id'];

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO sessions (date_debut, date_fin, lieu) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $date_debut, $date_fin, $lieu);
        $stmt->execute();
        $new_session_id = $conn->insert_id;
        $stmt->close();

        // Link to formation in planning table
        $stmt = $conn->prepare("INSERT INTO planning (formation_id, session_id, date_debut, date_fin, titre) VALUES (?, ?, ?, ?, ?)");
        $titre_p = "Session active";
        $stmt->bind_param("iisss", $formation_id, $new_session_id, $date_debut, $date_fin, $titre_p);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $message = "Session ajoutée et planifiée !";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Erreur: " . $e->getMessage();
    }
}

// Handle Delete Session
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM sessions WHERE session_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Session supprimée.";
    }
    $stmt->close();
}

// Fetch sessions with formation names
$sessions_query = "SELECT s.*, p.formation_id, f.titre as formation_titre 
                  FROM sessions s 
                  LEFT JOIN planning p ON s.session_id = p.session_id 
                  LEFT JOIN formations f ON p.formation_id = f.formation_id 
                  ORDER BY s.date_debut DESC";
$sessions_res = $conn->query($sessions_query);
$sessions = $sessions_res->fetch_all(MYSQLI_ASSOC);

// Fetch all formations for the dropdown
$formations_res = $conn->query("SELECT formation_id, titre FROM formations ORDER BY titre");
$all_formations = $formations_res->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Sessions - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .page-container { display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 40px; background: #f8fafc; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .btn-add { background: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .sessions-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .sessions-table th, .sessions-table td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .sessions-table th { background: #f1f5f9; color: #475569; font-weight: 600; }
        .btn-delete { color: #ef4444; border: none; background: none; cursor: pointer; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="page-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="card">
                <h2>Gérer les Sessions de Formation</h2>
                <p style="color: #64748b; margin-bottom: 20px;">Créez et gérez les créneaux de formation disponibles.</p>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Formation associée</label>
                        <select name="formation_id" required style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                            <option value="">Sélectionnez une formation...</option>
                            <?php foreach($all_formations as $f): ?>
                                <option value="<?php echo $f['formation_id']; ?>"><?php echo htmlspecialchars($f['titre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date Début</label>
                        <input type="datetime-local" name="date_debut" required>
                    </div>
                    <div class="form-group">
                        <label>Date Fin</label>
                        <input type="datetime-local" name="date_fin" required>
                    </div>
                    <div class="form-group">
                        <label>Lieu / Mode</label>
                        <input type="text" name="lieu" placeholder="Ex: Paris, Distanciel..." required>
                    </div>
                    <div class="form-group" style="display: flex; align-items: end;">
                        <button type="submit" name="add_session" class="btn-add" style="width: 100%;">Ajouter la Session</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h3>Sessions existantes</h3>
                <table class="sessions-table">
                    <thead>
                        <tr>
                            <th>Formation</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Lieu</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($sessions as $s): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($s['formation_titre'] ?? 'N/A'); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($s['date_debut'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($s['date_fin'])); ?></td>
                                <td><?php echo htmlspecialchars($s['lieu']); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $s['session_id']; ?>" class="btn-delete" onclick="return confirm('Supprimer cette session ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #64748b; padding: 40px;">Aucune session enregistrée.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
