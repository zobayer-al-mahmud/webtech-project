<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();
if ($user['role'] !== 'admin') {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css?v=4">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="#">Users</a></li>
                    <li><a href="#">Clubs</a></li>
                    <li><a href="#">Events</a></li>
                    <li><a href="#">Approvals</a></li>
                    <li><a href="#">Settings</a></li>
                    <li><a href="../Controller/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></span>
                </div>
            </header>

            <!-- Summary Cards -->
            <section class="summary-section">
                <div class="summary-card">
                    <h3>Total Users</h3>
                    <div class="summary-number" id="totalUsers">0</div>
                </div>
                <div class="summary-card">
                    <h3>Total Events</h3>
                    <div class="summary-number" id="totalEvents">0</div>
                </div>
                <div class="summary-card">
                    <h3>Pending Event Approval Requests</h3>
                    <div class="summary-number" id="pendingApprovals">0</div>
                </div>
            </section>

            <!-- Event Approval Requests -->
            <section class="data-section">
                <h2>Event Approval Requests</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Organizer</th>
                            <th>Organizer Email</th>
                            <th>Organizer Phone</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pendingEventsBody"></tbody>
                </table>
            </section>

            <section class="data-section">
                <h2>Upcoming Events (Approved)</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Organizer</th>
                            <th>Organizer Email</th>
                            <th>Organizer Phone</th>
                        </tr>
                    </thead>
                    <tbody id="upcomingEventsBody"></tbody>
                </table>
            </section>

            <section class="data-section">
                <h2>Participant Info</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Organizer</th>
                            <th>Student Name</th>
                            <th>Student Username</th>
                            <th>Student Email</th>
                            <th>Student Phone</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody id="participantsBody"></tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        async function loadAdminDashboard() {
            try {
                const res = await fetch('../Controller/api_admin_dashboard.php', { cache: 'no-store' });
                const data = await res.json();
                if (!data.ok) return;

                document.getElementById('totalUsers').textContent = data.totalUsers;
                document.getElementById('totalEvents').textContent = data.totalEvents;
                document.getElementById('pendingApprovals').textContent = data.pendingApprovals;

                const tbody = document.getElementById('pendingEventsBody');
                tbody.innerHTML = '';

                for (const ev of data.pendingEvents) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(ev.title)}</td>
                        <td>${escapeHtml(ev.organizer_name || ev.organizer_username || '')}</td>
                        <td>${escapeHtml(ev.organizer_email || '')}</td>
                        <td>${escapeHtml(ev.organizer_phone || '')}</td>
                        <td>${escapeHtml(ev.event_date)}</td>
                        <td>${escapeHtml(ev.event_time)}</td>
                        <td>${escapeHtml(ev.location)}</td>
                        <td>${escapeHtml(ev.capacity)}</td>
                        <td>
                            <button class="btn-approve" data-action="approve" data-event-id="${ev.event_id}">Approve</button>
                            <button class="btn-reject" data-action="reject" data-event-id="${ev.event_id}">Reject</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                }
            } catch (e) {
                // ignore
            }
        }

        async function loadAdminUpcomingEvents() {
            try {
                const res = await fetch('../Controller/api_admin_upcoming_events.php', { cache: 'no-store' });
                const data = await res.json();
                if (!data.ok) return;

                const tbody = document.getElementById('upcomingEventsBody');
                tbody.innerHTML = '';
                for (const ev of data.upcomingEvents) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(ev.title)}</td>
                        <td>${escapeHtml(ev.event_date)}</td>
                        <td>${escapeHtml(ev.event_time)}</td>
                        <td>${escapeHtml(ev.location)}</td>
                        <td>${escapeHtml(ev.capacity)}</td>
                        <td>${escapeHtml(ev.organizer_name || ev.organizer_username || '')}</td>
                        <td>${escapeHtml(ev.organizer_email || '')}</td>
                        <td>${escapeHtml(ev.organizer_phone || '')}</td>
                    `;
                    tbody.appendChild(tr);
                }
            } catch (e) {
                // ignore
            }
        }

        async function loadAdminParticipants() {
            try {
                const res = await fetch('../Controller/api_admin_participants.php', { cache: 'no-store' });
                const data = await res.json();
                if (!data.ok) return;

                const tbody = document.getElementById('participantsBody');
                tbody.innerHTML = '';
                for (const p of data.participants) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(p.event_title)}</td>
                        <td>${escapeHtml(p.organizer_name || p.organizer_username || '')}</td>
                        <td>${escapeHtml(p.student_name || '')}</td>
                        <td>${escapeHtml(p.student_username || '')}</td>
                        <td>${escapeHtml(p.student_email || '')}</td>
                        <td>${escapeHtml(p.student_phone || '')}</td>
                        <td>${escapeHtml(p.status || '')}</td>
                        <td>${escapeHtml(p.created_at || '')}</td>
                    `;
                    tbody.appendChild(tr);
                }
            } catch (e) {
                // ignore
            }
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('button[data-event-id]');
            if (!btn) return;
            const eventId = btn.getAttribute('data-event-id');
            const action = btn.getAttribute('data-action') || 'approve';

            const form = new FormData();
            form.append('event_id', eventId);

			let url = '../Controller/api_approve_event.php';
			if (action === 'reject') {
				url = '../Controller/api_reject_event.php';
			}

            const res = await fetch(url, { method: 'POST', body: form });
            const data = await res.json();
            if (!data.ok) {
                alert(data.error || 'Action failed');
                return;
            }
			await loadAdminDashboard();
			await loadAdminUpcomingEvents();
			await loadAdminParticipants();
        });

        loadAdminDashboard();
		loadAdminUpcomingEvents();
		loadAdminParticipants();
        setInterval(() => {
			loadAdminDashboard();
			loadAdminUpcomingEvents();
			loadAdminParticipants();
		}, 5000);
    </script>
</body>
</html>
