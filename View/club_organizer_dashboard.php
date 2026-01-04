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
if ($user['role'] !== 'organizer') {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Organizer Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css?v=4">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Organizer Panel</h2>
            <nav>
                <ul>
                    <li><a href="club_organizer_dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="#">My Events</a></li>
                    <li><a href="#">Create Event</a></li>
                    <li><a href="#">Participants</a></li>
                    <li><a href="#">My Club</a></li>
                    <li><a href="../Controller/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <h1>Club Organizer Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></span>
                </div>
            </header>

            <!-- Summary Cards -->
            <section class="summary-section">
                <div class="summary-card">
                    <h3>Total Events Created</h3>
                    <div class="summary-number" id="totalCreated">0</div>
                </div>
                <div class="summary-card">
                    <h3>Upcoming Events</h3>
                    <div class="summary-number" id="upcomingApproved">0</div>
                </div>
            </section>

            <!-- Create New Event Form -->
            <section class="data-section">
                <h2>Create New Event</h2>
                <form class="event-form" id="createEventForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Event Title</label>
                            <input type="text" name="title" id="title" placeholder="Enter event title" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="event_date" id="event_date" required>
                        </div>
                        <div class="form-group">
                            <label>Time</label>
                            <input type="time" name="event_time" id="event_time" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" id="location" placeholder="Enter location" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Capacity</label>
                            <input type="number" name="capacity" id="capacity" min="1" step="1" placeholder="Enter capacity" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Create Event</button>
                    <div id="createEventMsg"></div>
                </form>
            </section>

            <!-- Pending Approval Events -->
            <section class="data-section">
                <h2>Pending Approval Events</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Capacity</th>
                        </tr>
                    </thead>
                    <tbody id="pendingEventsBody"></tbody>
                </table>
            </section>

            <!-- Upcoming Events -->
            <section class="data-section">
                <h2>Upcoming Events</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Capacity</th>
                        </tr>
                    </thead>
                    <tbody id="upcomingEventsBody"></tbody>
                </table>
            </section>

            <!-- Participant List -->
            <section class="data-section">
                <h2>Participant List</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Student Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
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
        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        async function loadOrganizerDashboard() {
            try {
                const res = await fetch('../Controller/api_organizer_dashboard.php', { cache: 'no-store' });
                const data = await res.json();
                if (!data.ok) return;

                document.getElementById('totalCreated').textContent = data.totalCreated;
                document.getElementById('upcomingApproved').textContent = data.upcomingApproved;

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
                    `;
                    tbody.appendChild(tr);
                }

				const pendingBody = document.getElementById('pendingEventsBody');
				pendingBody.innerHTML = '';
				for (const ev of (data.pendingEvents || [])) {
					const tr = document.createElement('tr');
					tr.innerHTML = `
						<td>${escapeHtml(ev.title)}</td>
						<td>${escapeHtml(ev.event_date)}</td>
						<td>${escapeHtml(ev.event_time)}</td>
						<td>${escapeHtml(ev.location)}</td>
						<td>${escapeHtml(ev.capacity)}</td>
					`;
					pendingBody.appendChild(tr);
				}
            } catch (e) {
                // ignore
            }
        }

        async function loadParticipants() {
            try {
                const res = await fetch('../Controller/api_organizer_participants.php', { cache: 'no-store' });
                const data = await res.json();
                if (!data.ok) return;

                const tbody = document.getElementById('participantsBody');
                tbody.innerHTML = '';

                for (const p of data.participants) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(p.event_title)}</td>
                        <td>${escapeHtml(p.full_name)}</td>
                        <td>${escapeHtml(p.username)}</td>
                        <td>${escapeHtml(p.email)}</td>
                        <td>${escapeHtml(p.phone ?? '')}</td>
                        <td>${escapeHtml(p.status)}</td>
                        <td>${escapeHtml(p.created_at)}</td>
                    `;
                    tbody.appendChild(tr);
                }
            } catch (e) {
                // ignore
            }
        }

        document.getElementById('createEventForm').addEventListener('submit', async (e) => {
            e.preventDefault();
			const msg = document.getElementById('createEventMsg');
			if (msg) msg.textContent = '';

            const form = new FormData(e.currentTarget);
            const res = await fetch('../Controller/api_create_event.php', { method: 'POST', body: form });
            const data = await res.json();

            if (!data.ok) {
                if (msg) msg.textContent = data.error || 'Failed to create event';
                return;
            }

            e.currentTarget.reset();
			if (msg) msg.textContent = 'Event created successfully';
            await loadOrganizerDashboard();
            await loadParticipants();
        });

        loadOrganizerDashboard();
        loadParticipants();
        setInterval(() => {
            loadOrganizerDashboard();
            loadParticipants();
        }, 5000);
    </script>
</body>
</html>
