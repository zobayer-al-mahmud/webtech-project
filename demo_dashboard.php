<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - University Events</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <nav class="navbar">
        <h1>🎓 University Event Management</h1>
        <div class="user-info">
            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
            <p>You're logged in as <strong><?php echo ucfirst($user['role']); ?></strong></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Username</h3>
                <div class="stat-number"><?php echo htmlspecialchars($user['username']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Email</h3>
                <div class="stat-number" style="font-size: 1.2rem;"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Role</h3>
                <div class="stat-number"><?php echo ucfirst($user['role']); ?></div>
            </div>
        </div>
    </div>
</body>
</html>
