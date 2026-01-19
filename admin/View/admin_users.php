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
    <title>Users - Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: white;
            display: inline-block;
        }
        .role-admin { background-color: #dc3545; }
        .role-organizer { background-color: #007bff; }
        .role-student { background-color: #28a745; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="admin_users.php" class="active">Users</a></li>
                    <li><a href="admin_clubs.php">Clubs</a></li>
                    <li><a href="#">Events</a></li>
                    <li><a href="#">Approvals</a></li>
                    <li><a href="#">Settings</a></li>
                    <li><a href="../../Auth/Controller/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>User Management</h1>
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
            
            <div class="search-container" style="margin: 20px 0;">
                <input type="text" id="userSearch" placeholder="Search users..." style="padding: 10px; width: 100%; max-width: 400px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <section class="data-section">
                <h2>All Users</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody id="usersBody"></tbody>
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

        async function loadUsers(search = '') {
            try {
                const res = await fetch('../Controller/get_users.php?search=' + encodeURIComponent(search), { cache: 'no-store' });
                const json = await res.json();
                
                const tbody = document.getElementById('usersBody');
                tbody.innerHTML = '';
                
                if (!json.ok || !json.users || json.users.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7">No users found.</td></tr>';
                    return;
                }

                json.users.forEach(u => {
                    const tr = document.createElement('tr');
                    const roleClass = 'role-' + u.user_role;
                    const statusClass = u.is_active == 1 ? 'status-active' : 'status-inactive';
                    const statusText = u.is_active == 1 ? 'Active' : 'Inactive';
                    
                    tr.innerHTML = `
                        <td>${escapeHtml(u.full_name)}</td>
                        <td>${escapeHtml(u.username)}</td>
                        <td>${escapeHtml(u.email)}</td>
                        <td>${escapeHtml(u.phone)}</td>
                        <td><span class="badge ${roleClass}">${escapeHtml(u.user_role.toUpperCase())}</span></td>
                        <td><span class="${statusClass}">${statusText}</span></td>
                        <td>${escapeHtml(u.created_at)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (e) {
                console.error(e);
            }
        }

        document.getElementById('userSearch').addEventListener('keyup', (e) => {
            loadUsers(e.target.value);
        });

        // Initial load
        loadUsers();
    </script>
</body>
</html>
