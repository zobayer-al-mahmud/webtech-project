<?php
require_once 'config/config.php';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('demo_dashboard.php');
}

// Otherwise redirect to login
redirect('login.php');
?>
