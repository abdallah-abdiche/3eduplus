<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Admin', 'Administrateur']);

$message = "";
$error = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM evenements WHERE evenement_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Événement supprimé avec succès.";
    } else {
        $error = "Erreur lors de la suppression.";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_event'])) {
    $title = $conn->real_escape_string($_POST['titre']);
    $desc = $conn->real_escape_string($_POST['description']);
    $cat = $conn->real_escape_string($_POST['categorie']);
    $date = $_POST['date_evenement'];
    $price = floatval($_POST['prix']);
    $type = $_POST['type_evenement'];
    $id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE evenements SET titre=?, description=?, categorie=?, date_evenement=?, prix=?, type_evenement=? WHERE evenement_id=?");
        $stmt->bind_param("ssssdsi", $title, $desc, $cat, $date, $price, $type, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO evenements (titre, description, categorie, date_evenement, prix, type_evenement) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssds", $title, $desc, $cat, $date, $price, $type);
    }

    if ($stmt->execute()) {
        $message = "Opération réussie.";
    } else {
        $error = "Erreur: " . $conn->error;
    }
}

$events = $conn->query("SELECT * FROM evenements ORDER BY date_evenement DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Événements - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="CRUD.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .form-card { background: white; padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .events-table img { width: 50px; height: 50px; border-radius: 5px; object-fit: cover; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
        .badge-online { background: #dcfce7; color: #166534; }
        .badge-person { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <?php include 'header.php'; ?>
            <div class="dashboard-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h2>Gestion des Événements</h2>
                    <button onclick="showAddForm()" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvel Événement</button>
                </div>

                <?php if ($message || $error): ?>
                    <div style="padding: 15px; background: <?php echo $error ? '#fee2e2' : '#dcfce7'; ?>; color: <?php echo $error ? '#991b1b' : '#166534'; ?>; border-radius: 8px; margin-bottom: 20px;">
                        <?php echo $error ?: $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form (Initial hidden) -->
                <div id="eventForm" class="form-card" style="display: none;">
                    <h3 id="formTitle">Ajouter un Événement</h3>
                    <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                        <input type="hidden" name="event_id" id="event_id">
                        <div class="form-group">
                            <label>Titre</label>
                            <input type="text" name="titre" id="titre" required>
                        </div>
                        <div class="form-group">
                            <label>Catégorie</label>
                            <select name="categorie" id="categorie">
                                <option value="Webinaire">Webinaire</option>
                                <option value="Atelier">Atelier</option>
                                <option value="Conférence">Conférence</option>
                                <option value="Bootcamp">Bootcamp</option>
                            </select>
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Description</label>
                            <textarea name="description" id="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="date_evenement" id="date_evenement" required>
                        </div>
                        <div class="form-group">
                            <label>Prix (DA)</label>
                            <input type="number" name="prix" id="prix" value="0">
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type_evenement" id="type_evenement">
                                <option value="online">En ligne</option>
                                <option value="in-person">Présentiel</option>
                            </select>
                        </div>
                        <div style="grid-column: span 2; display: flex; gap: 10px;">
                            <button type="submit" name="save_event" class="btn btn-primary">Enregistrer</button>
                            <button type="button" onclick="hideForm()" class="btn btn-secondary">Annuler</button>
                        </div>
                    </form>
                </div>

                <div class="table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Date</th>
                                <th>Prix</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $e): ?>
                                <tr>
                                    <td><?php echo $e['evenement_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($e['titre']); ?></strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($e['date_evenement'])); ?></td>
                                    <td><?php echo number_format($e['prix'], 0); ?> DA</td>
                                    <td>
                                        <span class="badge <?php echo $e['type_evenement'] == 'online' ? 'badge-online' : 'badge-person'; ?>">
                                            <?php echo $e['type_evenement'] == 'online' ? 'Online' : 'In-Person'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button onclick='editEvent(<?php echo json_encode($e); ?>)' class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                        <a href="?delete=<?php echo $e['evenement_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet événement?')"><i class="fas fa-trash"></i></a>
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
        function showAddForm() {
            document.getElementById('eventForm').style.display = 'block';
            document.getElementById('formTitle').innerText = 'Ajouter un Événement';
            document.getElementById('event_id').value = '';
            document.getElementById('titre').value = '';
            document.getElementById('description').value = '';
            document.getElementById('prix').value = '0';
        }
        function hideForm() {
            document.getElementById('eventForm').style.display = 'none';
        }
        function editEvent(e) {
            document.getElementById('eventForm').style.display = 'block';
            document.getElementById('formTitle').innerText = 'Modifier l\'Événement';
            document.getElementById('event_id').value = e.evenement_id;
            document.getElementById('titre').value = e.titre;
            document.getElementById('description').value = e.description;
            document.getElementById('categorie').value = e.categorie;
            document.getElementById('date_evenement').value = e.date_evenement;
            document.getElementById('prix').value = e.prix;
            document.getElementById('type_evenement').value = e.type_evenement;
        }
    </script>
</body>
</html>
