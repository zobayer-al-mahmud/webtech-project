<?php
require_once 'config/config.php';

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
