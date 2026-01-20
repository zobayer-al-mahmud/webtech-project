<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';
$user = requireRoleOrRedirect('organizer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Organizer</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Organizer Panel</h2>
            <nav>
                <ul>
                    <li><a href="club_organizer_dashboard.php">Dashboard</a></li>
                    <li><a href="profile.php" class="active">My Profile</a></li>
                    <li><a href="#">My Events</a></li>
                    <li><a href="#">Create Event</a></li>
                    <li><a href="#">Participants</a></li>
                    <li><a href="#">My Club</a></li>
                    <li><a href="../../Auth/Controller/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <h1>My Profile</h1>
                <div class="user-info">
                    <span>Welcome, <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></span>
                    <a href="profile.php">
                        <?php if (!empty($user['filepath'])): ?>
                            <img src="../../<?php echo htmlspecialchars($user['filepath']); ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #000; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #ddd; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #555;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
            </header>

            <section class="data-section">
                <h2>Profile Information</h2>
                <div style="text-align: center; margin-bottom: 20px;">
                    <?php if (!empty($user['filepath'])): ?>
                        <img src="../../<?php echo htmlspecialchars($user['filepath']); ?>" alt="Profile Picture" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #000;">
                    <?php else: ?>
                        <div style="width: 150px; height: 150px; border-radius: 50%; background-color: #ddd; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; color: #555; font-size: 64px; border: 4px solid #000;">
                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <table class="data-table">
                    <tbody>
                        <tr>
                            <th>Full Name</th>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
