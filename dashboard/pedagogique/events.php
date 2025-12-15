<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

checkAuth();
checkRole(['Pédagogique']);

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event'])) {
        $titre = trim($_POST['titre']);
        $description = trim($_POST['description']);
        $categorie = trim($_POST['categorie']);
        $type_evenement = $_POST['type_evenement'];
        $date_evenement = $_POST['date_evenement'];
        $heure_debut = $_POST['heure_debut'];
        $heure_fin = $_POST['heure_fin'];
        $lieu = trim($_POST['lieu']);
        $prix = (float)$_POST['prix'];
        $max_participants = (int)$_POST['max_participants'];
        $instructeur = trim($_POST['instructeur']);
        $image_url = trim($_POST['image_url']);
        
        $stmt = $conn->prepare("INSERT INTO evenements (titre, description, categorie, type_evenement, date_evenement, heure_debut, heure_fin, lieu, prix, max_participants, instructeur, image_url, createur_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssdissi", $titre, $description, $categorie, $type_evenement, $date_evenement, $heure_debut, $heure_fin, $lieu, $prix, $max_participants, $instructeur, $image_url, $user_id);
        
        if ($stmt->execute()) {
            $message = "Événement créé avec succès!";
        } else {
            $error = "Erreur lors de la création: " . $conn->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_event'])) {
        $event_id = (int)$_POST['event_id'];
        $stmt = $conn->prepare("DELETE FROM evenements WHERE evenement_id = ?");
        $stmt->bind_param("i", $event_id);
        if ($stmt->execute()) {
            $message = "Événement supprimé avec succès!";
        } else {
            $error = "Erreur lors de la suppression.";
        }
        $stmt->close();
    }
}

// Fetch all events
$events_query = "SELECT e.*, 
                 (SELECT COUNT(*) FROM event_inscriptions WHERE evenement_id = e.evenement_id) as participants_inscrits
                 FROM evenements e 
                 ORDER BY e.date_evenement DESC";
$events_result = $conn->query($events_query);
$events = $events_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Événements - 3edu+</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="icon" href="../../LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .events-container { padding: 20px; max-width: 1400px; margin: 0 auto; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title { font-size: 1.8rem; color: #1a1a2e; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #007bff, #0056b3); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,123,255,0.3); }
        .btn-danger { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
        .btn-danger:hover { transform: translateY(-2px); }
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; }
        
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .events-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .event-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.3s, box-shadow 0.3s; }
        .event-card:hover { transform: translateY(-5px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        .event-header { padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .event-type { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 10px; }
        .event-type.online { background: rgba(255,255,255,0.2); }
        .event-type.in-person { background: rgba(40,167,69,0.8); }
        .event-title { font-size: 1.2rem; margin: 0; line-height: 1.4; }
        .event-body { padding: 20px; }
        .event-meta { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; font-size: 0.9rem; color: #666; }
        .event-meta-item { display: flex; align-items: center; gap: 6px; }
        .event-meta-item i { color: #007bff; }
        .event-description { color: #555; font-size: 0.9rem; line-height: 1.6; margin-bottom: 15px; }
        .event-stats { display: flex; justify-content: space-between; padding-top: 15px; border-top: 1px solid #eee; }
        .stat { text-align: center; }
        .stat-value { font-size: 1.2rem; font-weight: 700; color: #1a1a2e; }
        .stat-label { font-size: 0.75rem; color: #888; }
        .event-actions { display: flex; gap: 10px; margin-top: 15px; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; border-radius: 16px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { margin: 0; font-size: 1.4rem; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666; }
        .modal-body { padding: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-control { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; }
        .form-control:focus { outline: none; border-color: #007bff; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        .navbar { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .nav-brand { display: flex; align-items: center; gap: 15px; color: white; }
        .nav-brand img { height: 50px; }
        .nav-menu { display: flex; gap: 20px; }
        .nav-link { color: rgba(255,255,255,0.8); text-decoration: none; padding: 8px 15px; border-radius: 8px; transition: all 0.3s; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
        .nav-user { display: flex; align-items: center; gap: 10px; color: white; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <img src="../../LogoEdu.png" alt="3edu+">
            <span>3edu+ - Pédagogique</span>
        </div>
        <div class="nav-menu">
            <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Accueil</a>
            <a href="courses.php" class="nav-link"><i class="fas fa-book"></i> Cours</a>
            <a href="events.php" class="nav-link active"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="../../index.php" class="nav-link"><i class="fas fa-globe"></i> Site</a>
        </div>
        <div class="nav-user">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=random" alt="Avatar" style="width: 35px; height: 35px; border-radius: 50%;">
            <span><?php echo htmlspecialchars($user_name); ?></span>
            <a href="../../logout.php" class="nav-link" style="margin-left: 10px;"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <div class="events-container">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-calendar-alt"></i> Gestion des Événements</h1>
            <button class="btn btn-primary" onclick="openModal()">
                <i class="fas fa-plus"></i> Nouvel Événement
            </button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="events-grid">
            <?php if (count($events) > 0): ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <div class="event-header">
                            <span class="event-type <?php echo $event['type_evenement']; ?>">
                                <?php echo $event['type_evenement'] === 'online' ? 'En ligne' : 'Présentiel'; ?>
                            </span>
                            <h3 class="event-title"><?php echo htmlspecialchars($event['titre']); ?></h3>
                        </div>
                        <div class="event-body">
                            <div class="event-meta">
                                <div class="event-meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?></span>
                                </div>
                                <div class="event-meta-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo date('H:i', strtotime($event['heure_debut'])); ?> - <?php echo date('H:i', strtotime($event['heure_fin'])); ?></span>
                                </div>
                                <div class="event-meta-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($event['lieu'] ?? 'Non défini'); ?></span>
                                </div>
                            </div>
                            <p class="event-description"><?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 150)); ?>...</p>
                            <div class="event-stats">
                                <div class="stat">
                                    <div class="stat-value"><?php echo $event['participants_inscrits']; ?>/<?php echo $event['max_participants']; ?></div>
                                    <div class="stat-label">Inscrits</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-value"><?php echo $event['prix'] > 0 ? $event['prix'] . '€' : 'Gratuit'; ?></div>
                                    <div class="stat-label">Prix</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-value"><?php echo htmlspecialchars($event['categorie'] ?? 'N/A'); ?></div>
                                    <div class="stat-label">Catégorie</div>
                                </div>
                            </div>
                            <div class="event-actions">
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement?');">
                                    <input type="hidden" name="event_id" value="<?php echo $event['evenement_id']; ?>">
                                    <button type="submit" name="delete_event" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: white; border-radius: 16px;">
                    <i class="fas fa-calendar-times" style="font-size: 60px; color: #ccc; margin-bottom: 20px;"></i>
                    <h3>Aucun événement</h3>
                    <p style="color: #666;">Créez votre premier événement en cliquant sur le bouton ci-dessus.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal" id="eventModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-plus"></i> Nouvel Événement</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Titre de l'événement *</label>
                        <input type="text" name="titre" class="form-control" required placeholder="Ex: Webinaire React">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Décrivez votre événement..."></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Catégorie</label>
                            <input type="text" name="categorie" class="form-control" placeholder="Ex: Développement Web">
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type_evenement" class="form-control">
                                <option value="online">En ligne</option>
                                <option value="in-person">Présentiel</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date *</label>
                            <input type="date" name="date_evenement" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Lieu</label>
                            <input type="text" name="lieu" class="form-control" placeholder="Ex: En ligne ou Alger">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Heure début</label>
                            <input type="time" name="heure_debut" class="form-control" value="14:00">
                        </div>
                        <div class="form-group">
                            <label>Heure fin</label>
                            <input type="time" name="heure_fin" class="form-control" value="16:00">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Prix (€)</label>
                            <input type="number" name="prix" class="form-control" value="0" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Max participants</label>
                            <input type="number" name="max_participants" class="form-control" value="100" min="1">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Instructeur</label>
                        <input type="text" name="instructeur" class="form-control" value="<?php echo htmlspecialchars($user_name); ?>">
                    </div>
                    <div class="form-group">
                        <label>URL Image (optionnel)</label>
                        <input type="url" name="image_url" class="form-control" placeholder="https://...">
                    </div>
                    <button type="submit" name="add_event" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-plus"></i> Créer l'événement
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('eventModal').classList.add('active');
        }
        function closeModal() {
            document.getElementById('eventModal').classList.remove('active');
        }
        document.getElementById('eventModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
