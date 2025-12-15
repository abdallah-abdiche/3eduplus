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
    
<<<<<<< HEAD
=======
    if (in_array('Admin', $required_roles) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        return;
    }

>>>>>>> 3e34b36 (newe version)
    if (!in_array($user_role, $required_roles)) {
        http_response_code(403);
        echo "Accès refusé. Vous n'avez pas les permissions nécessaires.";
        exit();
    }
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
<<<<<<< HEAD
        'Admin' => '/3eduplus/dashboard/admin/index.html',
=======
        'Admin' => '/3eduplus/dashboard/admin/index.php',
>>>>>>> 3e34b36 (newe version)
        'Commercial' => '/3eduplus/dashboard/commercial/index.php',
        'Pédagogique' => '/3eduplus/dashboard/pedagogique/index.php',
        'Marketing' => '/3eduplus/dashboard/marketing/index.php',
        'Apprenant' => '/3eduplus/formation.php'
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
        'Admin' => 'dashboard/admin/index.php',
        'Commercial' => 'dashboard/commercial/index.php',
        'Pédagogique' => 'dashboard/pedagogique/index.php',
        'Marketing' => 'dashboard/marketing/index.php',
        'Apprenant' => 'formation.php'
    ];
    
    return $dashboards[$user_role] ?? $dashboards['Apprenant'];
}
?>
