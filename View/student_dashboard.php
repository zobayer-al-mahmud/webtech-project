<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';
$user = requireRoleOrRedirect('student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Student Panel</h2>
            <nav>
                <ul>
                    <li><a href="student_dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="#">Events</a></li>
                    <li><a href="#">My Registrations</a></li>
                    <li><a href="clubs.php">Clubs</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="../../Auth/Controller/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <h1>Student Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></span>
                    <a href="profile.php">
                        <?php if (!empty($_SESSION['filepath'])): ?>
                            <img src="../../<?php echo htmlspecialchars($_SESSION['filepath']); ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #000; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #ddd; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #555;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
            </header>

            <!-- Search Bar -->
            <div class="search-container" style="margin: 20px 0;">
                <input type="text" id="globalSearch" placeholder="Search data..." style="padding: 10px; width: 100%; max-width: 400px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <!-- Summary Cards -->
            <section class="summary-section">
                <div class="summary-card">
                    <h3>Upcoming Events</h3>
                    <div class="summary-number" id="upcomingCount">0</div>
                </div>
                <div class="summary-card">
                    <h3>Registered Events</h3>
                    <div class="summary-number" id="registeredCount">0</div>
                </div>
                <div class="summary-card">
                    <h3>Joined Clubs</h3>
                    <div class="summary-number" id="joinedClubsCount">0</div>
                </div>
            </section>

            <!-- Upcoming Events -->
            <section class="data-section">
                <h2>Upcoming Events</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Organizer</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="upcomingEventsBody"></tbody>
                </table>
            </section>

            <!-- Registered Events -->
            <section class="data-section">
                <h2>My Registered Events</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Organizer</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="registeredEventsBody"></tbody>
                </table>
            </section>

            <!-- Joined Clubs -->
            <section class="data-section">
                <h2>My Clubs</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Club Name</th>
                            <th>Organizer</th>
                            <th>Description</th>
                            <th>Joined Date</th>
                        </tr>
                    </thead>
                    <tbody id="joinedClubsBody"></tbody>
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

        async function loadStudentDashboard(search = '') {
            try {
                const res = await fetch('../Controller/student_dashboard.php?search=' + encodeURIComponent(search), { cache: 'no-store' });
                const data = await res.json();
                if (!data.ok) return;

                document.getElementById('upcomingCount').textContent = data.upcomingCount;
                document.getElementById('registeredCount').textContent = data.registeredCount;
                document.getElementById('joinedClubsCount').textContent = data.joinedClubsCount || 0;

                const upcomingBody = document.getElementById('upcomingEventsBody');
                upcomingBody.innerHTML = '';
                for (const ev of data.upcomingEvents) {
                    const tr = document.createElement('tr');
                    const action = ev.is_registered
                        ? '<button class="btn-cancel" data-cancel="1" data-event-id="' + ev.event_id + '">Cancel</button>'
                        : '<button class="btn-register" data-register="1" data-event-id="' + ev.event_id + '">Register</button>';
                    tr.innerHTML = `
                        <td>${escapeHtml(ev.title)}</td>
                        <td>${escapeHtml(ev.organizer_username)}</td>
                        <td>${escapeHtml(ev.event_date)}</td>
                        <td>${escapeHtml(ev.event_time)}</td>
                        <td>${escapeHtml(ev.location)}</td>
                        <td>${action}</td>
                    `;
                    upcomingBody.appendChild(tr);
                }

                const registeredBody = document.getElementById('registeredEventsBody');
                registeredBody.innerHTML = '';
                for (const ev of data.registeredEvents) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(ev.title)}</td>
                        <td>${escapeHtml(ev.organizer_username)}</td>
                        <td>${escapeHtml(ev.event_date)}</td>
                        <td>${escapeHtml(ev.event_time)}</td>
                        <td>${escapeHtml(ev.location)}</td>
                        <td><button class="btn-cancel" data-cancel="1" data-event-id="${ev.event_id}">Cancel Registration</button></td>
                    `;
                    registeredBody.appendChild(tr);
                }

                const joinedClubsBody = document.getElementById('joinedClubsBody');
                joinedClubsBody.innerHTML = '';
                if (data.joinedClubs) {
                    for (const club of data.joinedClubs) {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${escapeHtml(club.club_name)}</td>
                            <td>${escapeHtml(club.organizer_name)}</td>
                            <td>${escapeHtml(club.description)}</td>
                            <td>${escapeHtml(club.joined_at)}</td>
                        `;
                        joinedClubsBody.appendChild(tr);
                    }
                }
            } catch (e) {
                // ignore
            }
        }

        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('button[data-event-id]');
            if (!btn) return;
            const eventId = btn.getAttribute('data-event-id');

            const form = new FormData();
            form.append('event_id', eventId);

            if (btn.hasAttribute('data-register')) {
                const res = await fetch('../Controller/register_event.php', { method: 'POST', body: form });
                const data = await res.json();
                if (!data.ok) {
                    alert(data.error || 'Failed to register');
                    return;
                }
                const search = document.getElementById('globalSearch').value;
                await loadStudentDashboard(search);
                return;
            }

            if (btn.hasAttribute('data-cancel')) {
                const res = await fetch('../Controller/cancel_registration.php', { method: 'POST', body: form });
                const data = await res.json();
                if (!data.ok) {
                    alert(data.error || 'Failed to cancel');
                    return;
                }
                const search = document.getElementById('globalSearch').value;
                await loadStudentDashboard(search);
            }
        });

        function performSearch() {
            const query = document.getElementById('globalSearch').value;
            loadStudentDashboard(query);
        }

        document.getElementById('globalSearch').addEventListener('keyup', performSearch);

        performSearch();
        
        setInterval(() => {
            performSearch();
        }, 5000);
    </script>
</body>
</html>
