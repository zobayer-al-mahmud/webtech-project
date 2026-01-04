<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Model/DatabaseConnection.php';

function redirect(string $page): void {
    header('Location: ' . $page);
    exit();
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['username']);
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
    ];
}

// If user is already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    $user = getCurrentUser();
    $role = $user['role'];
    
    // Redirect based on role
    if ($role === 'admin') {
        redirect('admin_dashboard.php');
    } elseif ($role === 'organizer') {
        redirect('club_organizer_dashboard.php');
    } else {
        redirect('student_dashboard.php');
    }
}

// Otherwise redirect to login
redirect('login.php');

?>
