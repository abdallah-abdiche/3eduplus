<?php
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /3eduplus/signup.php');
        exit();
    }
}

function checkRole($required_roles) {
    checkAuth();
    
    $user_role = $_SESSION['user_role'] ?? null;
    
    if (is_string($required_roles)) {
        $required_roles = [$required_roles];
    }

    // Admin and Administrateur have access to EVERYTHING
    if ($user_role === 'Admin' || $user_role === 'Administrateur') {
        return true;
    }
    
    // Check if user has the required role
    if (in_array($user_role, $required_roles)) {
        return true;
    }
    
    // Check is_admin flag
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        return true;
    }

    // Access denied
    http_response_code(403);
    echo "Accès refusé. Vous n'avez pas les permissions nécessaires. ";
    echo "<br><br><a href='/3eduplus/make_me_admin.php' style='color: blue;'>Cliquez ici pour devenir Admin</a>";
    echo "<br><a href='/3eduplus/logout.php' style='color: red;'>Se déconnecter</a>";
    exit();
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'role' => $_SESSION['user_role'] ?? null
    ];
}

function logout() {
    session_destroy();
    header('Location: /3eduplus/signup.php');
    exit();
}

function redirectByRole($user_role = null) {
    if ($user_role === null) {
        $user_role = $_SESSION['user_role'] ?? 'Apprenant';
    }
    
    $redirects = [
        'Administrateur' => '/3eduplus/dashboard/admin/index.php',
        'Admin' => '/3eduplus/dashboard/admin/index.php',
        'Commercial' => '/3eduplus/dashboard/commercial/index.php',
        'Assistante commerciale' => '/3eduplus/dashboard/assistante/index.php',
        'Directeur pédagogique' => '/3eduplus/dashboard/pedagogique/index.php',
        'Responsable marketing' => '/3eduplus/dashboard/marketing/index.php',
        'Formateur' => '/3eduplus/dashboard/formateur/index.php',
        'Apprenant' => '/3eduplus/dashboard/apprenant/index.php'
    ];
    
    $redirect_url = $redirects[$user_role] ?? $redirects['Apprenant'];
    
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect_url = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
    }
    
    header('Location: ' . $redirect_url);
    exit();
}

function getDashboardUrl($user_role = null) {
    if ($user_role === null) {
        $user_role = $_SESSION['user_role'] ?? 'Apprenant';
    }
    
    $dashboards = [
        'Administrateur' => 'dashboard/admin/index.php',
        'Admin' => 'dashboard/admin/index.php',
        'Commercial' => 'dashboard/commercial/index.php',
        'Assistante commerciale' => 'dashboard/assistante/index.php',
        'Directeur pédagogique' => 'dashboard/pedagogique/index.php',
        'Responsable marketing' => 'dashboard/marketing/index.php',
        'Formateur' => 'dashboard/formateur/index.php',
        'Apprenant' => 'dashboard/apprenant/index.php'
    ];
    
    return $dashboards[$user_role] ?? $dashboards['Apprenant'];
}
?>
