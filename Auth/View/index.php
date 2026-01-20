<?php
require_once __DIR__ . '/../Controller/common.php';

if (isLoggedIn()) {
    $user = getCurrentUser();
    redirectToRoleDashboard($user['role']);
}

redirect('login.php');
?>
