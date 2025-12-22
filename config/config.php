<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application configuration
define('APP_NAME', 'University Event & Club Management System');
define('BASE_URL', 'http://localhost/web tech project/');
define('PASSWORD_MIN_LENGTH', 8);

// Include database connection
require_once __DIR__ . '/database.php';

// Helper function to redirect
function redirect($page) {
    header("Location: " . BASE_URL . $page);
    exit();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Helper function to check user role
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Helper function to get current user
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['user_role']
        ];
    }
    return null;
}
?>
