<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';
$user = requireRoleOrRedirect('organizer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Organizer Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Organizer Panel</h2>
            <nav>
                <ul>
                    <li><a href="club_organizer_dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="#">My Events</a></li>
                    <li><a href="#">Create Event</a></li>
                    <li><a href="#">Participants</a></li>
                    <li><a href="my_club.php">My Club</a></li>
                    <li><a href="../../Auth/Controller/logout.php">Logout</a></li>
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
                    <h3>Total Events Created</h3>
                    <div class="summary-number" id="totalCreated">0</div>
                </div>
                <div class="summary-card">
                    <h3>Upcoming Events</h3>
                    <div class="summary-number" id="upcomingApproved">0</div>
                </div>
                <div class="summary-card">
                    <h3>Total Club Members</h3>
                    <div class="summary-number" id="totalClubMembers">0</div>
                </div>
                <div class="summary-card">
                    <h3>Pending Club Requests</h3>
                    <div class="summary-number" id="pendingClubRequests">0</div>
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
                            <th>Action</th>
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="upcomingEventsBody"></tbody>
                </table>
            </section>

            <!-- Club Members List -->
            <section class="data-section">
                <h2>Club Members</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Club</th>
                            <th>Joined At</th>
                        </tr>
                    </thead>
                    <tbody id="clubMembersBody"></tbody>
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

        async function cancelEvent(eventId) {
            if (!confirm('Are you sure you want to cancel this event? This cannot be undone.')) {
                return;
            }
            try {
                const res = await fetch('../Controller/cancel_event.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ event_id: eventId })
                });
                const data = await res.json();
                if (data.ok) {
                    alert('Event cancelled successfully');
                    performSearch();
                } else {
                    alert('Failed to cancel event: ' + (data.error || 'Unknown error'));
                }
            } catch (e) {
                console.error(e);
                alert('Error processing request');
            }
        }

        async function loadOrganizerDashboard(search = '') {
            try {
                const res = await fetch('../Controller/organizer_dashboard.php?search=' + encodeURIComponent(search), { cache: 'no-store' });
                const data = await res.json();
                if (!data.ok) return;

                document.getElementById('totalCreated').textContent = data.totalCreated;
                document.getElementById('upcomingApproved').textContent = data.upcomingApproved;
                document.getElementById('totalClubMembers').textContent = data.totalClubMembers || 0;
                document.getElementById('pendingClubRequests').textContent = data.pendingClubRequests || 0;

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
                        <td>
                            <button onclick="cancelEvent(${ev.event_id})" class="btn-cancel">Cancel</button>
                        </td>
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
                        <td>
                            <button onclick="cancelEvent(${ev.event_id})" class="btn-cancel">Cancel</button>
                        </td>
					`;
					pendingBody.appendChild(tr);
				}

                const membersBody = document.getElementById('clubMembersBody');
                membersBody.innerHTML = '';
                if (data.clubMembers) {
                    for (const m of data.clubMembers) {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${escapeHtml(m.full_name)}</td>
                            <td>${escapeHtml(m.email)}</td>
                            <td>${escapeHtml(m.phone || '-')}</td>
                            <td>${escapeHtml(m.club_name)}</td>
                            <td>${escapeHtml(m.joined_at)}</td>
                        `;
                        membersBody.appendChild(tr);
                    }
                }
            } catch (e) {
                // ignore
            }
        }

        async function loadParticipants(search = '') {
            try {
                const res = await fetch('../Controller/organizer_participants.php?search=' + encodeURIComponent(search), { cache: 'no-store' });
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
            
            const eventDate = document.getElementById('event_date').value;
            const eventTime = document.getElementById('event_time').value;
            const capacity = document.getElementById('capacity').value;
            const title = document.getElementById('title').value.trim();
            
            if (!title) {
                if (msg) { msg.textContent = 'Title is required'; msg.style.color = 'red'; }
                return;
            }

            const today = new Date().toISOString().split('T')[0];
            if (eventDate < today) {
                if (msg) { msg.textContent = 'Event date cannot be in the past'; msg.style.color = 'red'; }
                return;
            }
            
            if (parseInt(capacity) <= 0) {
                 if (msg) { msg.textContent = 'Capacity must be greater than 0'; msg.style.color = 'red'; }
                 return;
            }

            const form = new FormData(e.currentTarget);
            const res = await fetch('../Controller/create_event.php', { method: 'POST', body: form });
            const data = await res.json();

            if (!data.ok) {
                if (msg) { msg.textContent = data.error || 'Failed to create event'; msg.style.color = 'red'; }
                return;
            }

            e.currentTarget.reset();
			if (msg) { msg.textContent = 'Event created successfully'; msg.style.color = 'green'; }
            const search = document.getElementById('globalSearch').value;
            await loadOrganizerDashboard(search);
            await loadParticipants(search);
        });

        function performSearch() {
            const query = document.getElementById('globalSearch').value;
            loadOrganizerDashboard(query);
            loadParticipants(query);
        }

        document.getElementById('globalSearch').addEventListener('keyup', performSearch);

        performSearch();
        
        setInterval(() => {
            performSearch();
        }, 5000);
    </script>
</body>
</html>
