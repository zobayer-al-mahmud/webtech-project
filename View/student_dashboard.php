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
if ($user['role'] !== 'student') {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css?v=4">
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
                    <li><a href="#">Clubs</a></li>
                    <li><a href="#">Profile</a></li>
                    <li><a href="../Controller/logout.php">Logout</a></li>
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
                </div>
            </header>

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

        async function loadStudentDashboard() {
            try {
                const res = await fetch('../Controller/api_student_dashboard.php', { cache: 'no-store' });
                const data = await res.json();
                if (!data.ok) return;

                document.getElementById('upcomingCount').textContent = data.upcomingCount;
                document.getElementById('registeredCount').textContent = data.registeredCount;

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
                const res = await fetch('../Controller/api_register_event.php', { method: 'POST', body: form });
                const data = await res.json();
                if (!data.ok) {
                    alert(data.error || 'Failed to register');
                    return;
                }
                await loadStudentDashboard();
                return;
            }

            if (btn.hasAttribute('data-cancel')) {
                const res = await fetch('../Controller/api_cancel_registration.php', { method: 'POST', body: form });
                const data = await res.json();
                if (!data.ok) {
                    alert(data.error || 'Failed to cancel');
                    return;
                }
                await loadStudentDashboard();
            }
        });

        loadStudentDashboard();
        setInterval(loadStudentDashboard, 5000);
    </script>
</body>
</html>
