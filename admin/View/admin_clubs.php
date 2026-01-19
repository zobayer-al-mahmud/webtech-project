<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id']) || empty($_SESSION['username']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../../Auth/View/login.php');
    exit();
}
$user = [
    'full_name' => $_SESSION['full_name'] ?? 'Admin',
    'filepath' => $_SESSION['filepath'] ?? ''
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clubs - Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="admin_users.php">Users</a></li>
                    <li><a href="admin_clubs.php" class="active">Clubs</a></li>
                    <li><a href="#">Events</a></li>
                    <li><a href="#">Approvals</a></li>
                    <li><a href="#">Settings</a></li>
                    <li><a href="../../Auth/Controller/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Clubs Overview</h1>
                <div class="user-info">
                    <span>Welcome, <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></span>
                    <a href="profile.php">
                        <?php if (!empty($user['filepath'])): ?>
                            <img src="../../<?php echo htmlspecialchars($user['filepath']); ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #000;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #ddd; display: flex; align-items: center; justify-content: center;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
            </header>

            <section class="data-section">
                <h2>All Clubs</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Club Name</th>
                            <th>Organizer</th>
                            <th>Description</th>
                            <th>Approved Members</th>
                        </tr>
                    </thead>
                    <tbody id="clubsBody"></tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        async function loadClubs() {
            try {
                const res = await fetch('../Controller/get_all_clubs.php', { cache: 'no-store' });
                const json = await res.json();
                
                const tbody = document.getElementById('clubsBody');
                tbody.innerHTML = '';
                
                if (!json.ok || !json.clubs) {
                    tbody.innerHTML = '<tr><td colspan="4">No clubs found.</td></tr>';
                    return;
                }

                json.clubs.forEach(c => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(c.club_name)}</td>
                        <td>${escapeHtml(c.organizer_name)}</td>
                        <td>${escapeHtml(c.description)}</td>
                        <td>${escapeHtml(c.member_count)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (e) {
                console.error(e);
            }
        }

        loadClubs();
    </script>
</body>
</html>
