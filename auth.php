<?php
/**
 * auth.php - Authentication and Authorization Middleware
 * Include this file at the top of protected pages to verify session and role
 * Note: session_start() should be called before including this file
 */

// Check if user is logged in
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /3eduplusV2/signup.php');
        exit();
    }
}

// Check if user has required role
function checkRole($required_roles) {
    checkAuth();
    
    $user_role = $_SESSION['user_role'] ?? null;
    
    // Convert single role to array
    if (is_string($required_roles)) {
        $required_roles = [$required_roles];
    }
    
    // Bypass for legacy admin session
    if (in_array('Admin', $required_roles) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        return;
    }

    if (!in_array($user_role, $required_roles)) {
        http_response_code(403);
        echo "Accès refusé. Vous n'avez pas les permissions nécessaires. Role actuel: " . ($user_role ? $user_role : 'Aucun');
        exit();
    }
}

// Get current user info
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

// Logout function
function logout() {
    session_destroy();
    header('Location: /3eduplusV2/signup.php');
    exit();
}

// Redirect based on role
function redirectByRole($user_role = null) {
    if ($user_role === null) {
        $user_role = $_SESSION['user_role'] ?? 'Apprenant';
    }
    
    $redirects = [
        'Admin' => '/3eduplusV2/dashboard/admin/index.php',
        'Commercial' => '/3eduplusV2/dashboard/commercial/index.php',
        'Pédagogique' => '/3eduplusV2/dashboard/pedagogique/index.php',
        'Marketing' => '/3eduplusV2/dashboard/marketing/index.php',
        'Apprenant' => '/3eduplusV2/formation.php'
    ];
    
    $redirect_url = $redirects[$user_role] ?? $redirects['Apprenant'];
    
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect_url = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
    }
    
    header('Location: ' . $redirect_url);
    exit();
}

// Get dashboard URL based on role
function getDashboardUrl($user_role = null) {
    if ($user_role === null) {
        $user_role = $_SESSION['user_role'] ?? 'Apprenant';
    }
    
    $dashboards = [
        'Admin' => 'dashboard/admin/index.html',
        'Commercial' => 'dashboard/commercial/index.php',
        'Pédagogique' => 'dashboard/pedagogique/index.php',
        'Marketing' => 'dashboard/marketing/index.php',
        'Apprenant' => 'formation.php'
    ];
    
    return $dashboards[$user_role] ?? $dashboards['Apprenant'];
}
?>
