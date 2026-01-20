<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';
$user = requireRoleOrRedirect('organizer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Club - Organizer Dashboard</title>
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
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="#">My Events</a></li>
                    <li><a href="#">Create Event</a></li>
                    <li><a href="#">Participants</a></li>
                    <li><a href="my_club.php" class="active">My Club</a></li>
                    <li><a href="../../Auth/Controller/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <h1>My Club Management</h1>
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

            <div id="clubContent">
                <p>Loading club data...</p>
            </div>
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

        async function loadClubData() {
            try {
                const res = await fetch('../Controller/get_club_members.php', { cache: 'no-store' });
                const json = await res.json();
                
                const container = document.getElementById('clubContent');
                container.innerHTML = '';

                if (!json.ok || !json.data || json.data.length === 0) {
                    container.innerHTML = '<p>No club found for this organizer.</p>';
                    return;
                }

                json.data.forEach(item => {
                    const section = document.createElement('section');
                    section.className = 'data-section';
                    
                    const h2 = document.createElement('h2');
                    h2.textContent = item.club_info.club_name;
                    section.appendChild(h2);

                    const table = document.createElement('table');
                    table.className = 'data-table';
                    table.innerHTML = `
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    `;
                    
                    const tbody = table.querySelector('tbody');
                    item.members.forEach(m => {
                        const tr = document.createElement('tr');
                        
                        let action = '';
                        if (m.status === 'pending') {
                            action = `<button class="btn-primary" onclick="approveMember(${m.membership_id})">Approve</button>`;
                        } else {
                            action = '-';
                        }
                        
                        tr.innerHTML = `
                            <td>${escapeHtml(m.full_name)}</td>
                            <td>${escapeHtml(m.email)}</td>
                            <td>${escapeHtml(m.request_reason)}</td>
                            <td>${escapeHtml(m.status)}</td>
                            <td>${action}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                    
                    if (item.members.length === 0) {
                        const tr = document.createElement('tr');
                        tr.innerHTML = '<td colspan="5" style="text-align:center;">No members yet.</td>';
                        tbody.appendChild(tr);
                    }

                    section.appendChild(table);
                    container.appendChild(section);
                });

            } catch (e) {
                console.error(e);
                document.getElementById('clubContent').innerHTML = '<p>Error loading data.</p>';
            }
        }

        async function approveMember(id) {
            if (!confirm('Approve this student?')) return;
            
            try {
                const res = await fetch('../Controller/approve_membership.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ membership_id: id })
                });
                const data = await res.json();
                if (data.ok) {
                    alert('Approved!');
                    loadClubData();
                } else {
                    alert('Error: ' + (data.error || 'Unknown'));
                }
            } catch (e) {
                alert('Connection error');
            }
        }

        loadClubData();
    </script>
</body>
</html>
