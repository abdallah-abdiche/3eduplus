<?php
session_start();
require_once 'config.php';

// Initialize error and success messages
$error_message = '';
$success_message = '';
$login_error = '';
$login_success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $wilaya = isset($_POST['wilaya']) ? trim($_POST['wilaya']) : '';
    $numero = isset($_POST['numero_tlf_utilisateur']) ? trim($_POST['numero_tlf_utilisateur']) : '';
    $date = isset($_POST['date_registration']) ? $_POST['date_registration'] : '';
    $image = isset($_POST['image_utilisateur']) ? $_POST['image_utilisateur'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : 'Apprenant';

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($wilaya) || empty($numero) || empty($date) || empty($gender) || empty($role)) {
        $error_message = '❌ Tous les champs requis doivent être remplis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = '❌ Adresse email invalide.';
    } elseif (strlen($password) < 6) {
        $error_message = '❌ Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT user_id FROM utilisateurs WHERE Email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $result = $checkEmail->get_result();

        if ($result->num_rows > 0) {
            $error_message = '❌ Cet email est déjà utilisé. Veuillez en utiliser un autre.';
            $checkEmail->close();
        } else {
            $checkEmail->close();
            
            // Get role_id from roles table based on role name
            $roleStmt = $conn->prepare("SELECT role_id FROM roles WHERE nom_role = ?");
            $roleStmt->bind_param("s", $role);
            $roleStmt->execute();
            $roleResult = $roleStmt->get_result();
            
            if ($roleResult->num_rows > 0) {
                $roleRow = $roleResult->fetch_assoc();
                $role_id = $roleRow['role_id'];
                $roleStmt->close();
                
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user with role_id
                $stmt = $conn->prepare("INSERT INTO utilisateurs 
                    (Nom_Complet, Mot_de_passe, Email, Wilaya, numero_tlf_utilisateur, date_registration, image_utilisateur, gender, role_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("ssssssssi", $username, $hashed_password, $email, $wilaya, $numero, $date, $image, $gender, $role_id);

                if ($stmt->execute()) {
                    $success_message = '✅ Compte créé avec succès!';
                    $stmt->close();
                } else {
                    $error_message = '❌ Erreur lors de la création du compte. Veuillez réessayer.';
                    $stmt->close();
                }
            } else {
                $error_message = '❌ Rôle sélectionné invalide. Veuillez réessayer.';
                $roleStmt->close();
            }
        }
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signin'])) {
    $login_email = isset($_POST['login_email']) ? trim($_POST['login_email']) : '';
    $login_password = isset($_POST['login_password']) ? $_POST['login_password'] : '';

    if (empty($login_email) || empty($login_password)) {
        $login_error = '❌ Veuillez remplir tous les champs.';
    } else {
        $stmt = $conn->prepare("SELECT u.user_id, u.Mot_de_passe, u.Nom_Complet, u.Email, u.role_id, r.nom_role FROM utilisateurs u LEFT JOIN roles r ON u.role_id = r.role_id WHERE u.Email = ?");
        $stmt->bind_param("s", $login_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($login_password, $user['Mot_de_passe'])) {
                // Successful login
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_email'] = $user['Email'];
                $_SESSION['user_name'] = $user['Nom_Complet'];
                $_SESSION['user_role'] = $user['nom_role'] ?? 'Apprenant';
                
                // Redirect based on role
                require_once 'auth.php';
                redirectByRole($_SESSION['user_role']);
                exit();
            } else {
                $login_error = '❌ Email ou mot de passe incorrect.';
            }
        } else {
            $login_error = '❌ Email ou mot de passe incorrect.';
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3edu+ - Inscription</title>
    <link rel="stylesheet" href="signup.css">
    <link rel="icon" href="./LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .alert-message {
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 5px;
            font-size: 15px;
            animation: slideIn 0.3s ease-in-out;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>
    <script>
        // Auto-hide validation error messages after page refresh immediately
        window.addEventListener('load', function() {
            const errorAlert = document.querySelector('.alert-error');
            if (errorAlert) {
                const errorText = errorAlert.textContent;
                
                // List of validation errors that should auto-hide
                const validationErrors = [
                    'Le mot de passe doit contenir au moins 6 caractères',
                    'Tous les champs requis doivent être remplis',
                    'Adresse email invalide'
                ];
                
                // Check if this is a validation error
                const isValidationError = validationErrors.some(err => errorText.includes(err));
                
                if (isValidationError) {
                    // Auto-hide validation errors after 4 seconds
                    setTimeout(function() {
                        errorAlert.style.animation = 'fadeOut 0.5s ease-in-out';
                        setTimeout(function() {
                            errorAlert.style.display = 'none';
                        }, 500);
                    }, 4000);
                }
                // Keep other errors (like "email already exists") visible
            }
        });
    </script>
</head>

<body>

    <div class="auth-container">
        <div class="auth-card">

            <div class="auth-header">
                <img src="./LogoEdu.png" alt="3edu+ Logo" class="auth-logo">
                <h1>Bienvenue sur 3edu+</h1>
                <p>Créez votre compte et commencez votre apprentissage</p>
            </div>

            <!-- Display error or success messages for signup -->
            <?php if (!empty($error_message)): ?>
                <div class="alert-message alert-error" id="signup-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert-message alert-success" id="signup-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <div class="auth-tabs">
                <button class="tab-btn active" data-tab="login">
                    <i class="fas fa-sign-in-alt"></i>
                    Connexion
                </button>
                <button class="tab-btn" data-tab="signup">
                    <i class="fas fa-user-plus"></i>
                    Inscription
                </button>
            </div>

            <!-- LOGIN FORM -->
            <form class="auth-form active" id="login-form" method="post" action="signup.php">
                <?php if (!empty($login_error)): ?>
                    <div class="alert-message alert-error">
                        <?php echo htmlspecialchars($login_error); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="login-email">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="login-email" name="login_email" placeholder="votre@email.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="login-password">Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="login-password" name="login_password" placeholder="Votre mot de passe" required>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        <b>Se souvenir de moi</b>
                    </label>
                    <a href="#" class="forgot-password">Mot de passe oublié?</a>
                </div>

                <button type="submit" class="auth-btn" name="signin">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>

                <div class="divider">
                    <span>Ou continuer avec</span>
                </div>

                <div class="social-buttons">
                    <button type="button" class="social-btn google">
                        <i class="fab fa-google"></i>
                        Google
                    </button>
                    <button type="button" class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                        Facebook
                    </button>
                </div>

                <div class="form-footer">
                    <p>Pas encore de compte? <a href="#" class="switch-tab" data-tab="signup">Créer un compte</a></p>
                </div>
            </form>

            <!-- SIGNUP FORM -->
            <form class="auth-form" id="signup-form" method="post" action="signup.php">
                <div class="form-group">
                    <label for="signup-name">Nom complet</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="signup-name" name="name" placeholder="Votre nom complet" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="signup-email">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="signup-email" name="email" placeholder="votre@email.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="signup-password">Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="signup-password" name="password" placeholder="Créer un mot de passe (min 6 caractères)" required minlength="6">
                    </div>
                </div>

                <div class="form-group">
                    <label for="Wilaya">Wilaya</label>
                    <div class="input-wrapper">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" id="Wilaya" name="wilaya" placeholder="Votre wilaya" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="numero_tlf_utilisateur">Numéro de téléphone</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="numero_tlf_utilisateur" name="numero_tlf_utilisateur" placeholder="Ex: +213..." required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="date_registration">Date d'inscription</label>
                    <div class="input-wrapper">
                        <i class="fas fa-calendar-alt"></i>
                        <input type="date" id="date_registration" name="date_registration" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image_utilisateur">Image utilisateur (optionnel)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-image"></i>
                        <input type="file" id="image_utilisateur" name="image_utilisateur" accept="image/*">
                    </div>
                </div>

                <div class="form-group">
                    <label>Genre</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <select id="gender" name="gender" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Male">Homme</option>
                            <option value="Female">Femme</option>
                            <option value="Other">Autre</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Rôle / Type de Compte</label>
                    <div class="input-wrapper">
                        <i class="fas fa-briefcase"></i>
                        <select id="role" name="role" required>
                            <option value="">-- Sélectionnez votre rôle --</option>
                            <option value="Apprenant">Apprenant (Étudiant)</option>
                            <option value="Commercial">Commercial</option>
                            <option value="Pédagogique">Pédagogique (Formateur)</option>
                            <option value="Marketing">Marketing</option>
                        </select>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="terms" required>
                        <span class="checkmark"></span>
                        <b>J'accepte les <a href="#">conditions d'utilisation</a> et la <a href="#">politique de confidentialité</a></b>
                    </label>
                </div>

                <button type="submit" class="auth-btn" name="register">
                    <i class="fas fa-user-plus"></i>
                    Créer un compte
                </button>

                <div class="divider">
                    <span>Ou continuer avec</span>
                </div>

                <div class="social-buttons">
                    <button type="button" class="social-btn google">
                        <i class="fab fa-google"></i>
                        Google
                    </button>
                    <button type="button" class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                        Facebook
                    </button>
                </div>

                <div class="form-footer">
                    <p>Déjà un compte? <a href="#" class="switch-tab" data-tab="login">Se connecter</a></p>
                </div>
            </form>

            <div class="auth-footer">
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = btn.getAttribute('data-tab');
                
                // Remove active class from all tabs
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                // Remove active class from all forms
                document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
                
                // Add active class to clicked tab
                btn.classList.add('active');
                // Add active class to corresponding form
                document.getElementById(tabName + '-form').classList.add('active');
            });
        });

        // Switch tab links
        document.querySelectorAll('.switch-tab').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = link.getAttribute('data-tab');
                
                // Find and click the corresponding tab button
                const tabBtn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
                if (tabBtn) tabBtn.click();
            });
        });

        // Auto-hide validation error messages
        window.addEventListener('load', function() {
            const errorAlerts = document.querySelectorAll('.alert-error');
            errorAlerts.forEach(errorAlert => {
                const errorText = errorAlert.textContent;
                
                // List of validation errors that should auto-hide
                const validationErrors = [
                    'Le mot de passe doit contenir au moins 6 caractères',
                    'Tous les champs requis doivent être remplis',
                    'Adresse email invalide',
                    'Veuillez remplir tous les champs'
                ];
                
                // Check if this is a validation error
                const isValidationError = validationErrors.some(err => errorText.includes(err));
                
                if (isValidationError) {
                    // Auto-hide validation errors after 4 seconds
                    setTimeout(function() {
                        errorAlert.style.animation = 'fadeOut 0.5s ease-in-out';
                        setTimeout(function() {
                            errorAlert.style.display = 'none';
                        }, 500);
                    }, 4000);
                }
                // Keep other errors (like "email already exists") visible
            });
        });
    </script>

</body>

</html>
