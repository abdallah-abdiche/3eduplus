<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3edu+ - Connexion</title>
    <link rel="stylesheet" href="login.css">
    <link rel="icon" href="./LogoEdu.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="auth-container">
        <div class="auth-card">
     
            <div class="auth-header">
                <img src="./LogoEdu.png" alt="3edu+ Logo" class="auth-logo">
                <h1>Bienvenue sur 3edu+</h1>
                <p>Accédez à vos formations et développez vos compétences</p>
            </div>

            <div class="auth-tabs">
                <button class="tab-btn active">
                    <i class="fas fa-sign-in-alt"></i>
                    Connexion
                </button>
                <a href="signup.html" class="tab-btn">
                    <i class="fas fa-user-plus"></i>
                    Inscription
                </a>
            </div>

            <form class="auth-form active" id="login-form" method="post" action="signup.php" >
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="login-email" name="email" placeholder="Abdallah@email.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="login-password">Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="login-password" name="password" placeholder="Your Password" required>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Se souvenir de moi
                    </label>
                    <a href="#" class="forgot-link">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="auth-btn" name="login">
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
                
            </form>
              
            <div class="auth-footer">
                <a href="index.html" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</body>
</html>