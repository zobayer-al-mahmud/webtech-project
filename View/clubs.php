<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';
$user = requireRoleOrRedirect('student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clubs - Student Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        /* Modal for Join Reason */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
            position: relative;
        }
        .close-modal {
            position: absolute;
            top: 10px; right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            min-height: 100px;
            font-family: inherit;
        }
        /* Reuse standard btn-primary for join button inside modal */
        .btn-join-modal {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .btn-join-modal:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Student Panel</h2>
            <nav>
                <ul>
                    <li><a href="student_dashboard.php">Dashboard</a></li>
                    <li><a href="#">Events</a></li>
                    <li><a href="#">My Registrations</a></li>
                    <li><a href="clubs.php" class="active">Clubs</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="../../Auth/Controller/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <h1>Clubs</h1>
                <div class="user-info">
                    <span>Welcome, <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></span>
                    <a href="profile.php">
                         <?php if (!empty($user['filepath'])): ?>
                            <img src="../../<?php echo htmlspecialchars($user['filepath']); ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #ddd; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #555;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
            </header>

            <section class="data-section">
                <h2>Explore Clubs</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Club Name</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="clubsBody"></tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- Join Modal -->
    <div id="joinModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="modalClubName">Join Club</h2>
            <p>Tell us why you want to join this club.</p>
            <form id="joinForm">
                <input type="hidden" id="modalClubId" name="club_id">
                <div class="form-group">
                    <label for="reason">Reason (Optional)</label>
                    <textarea id="reason" name="reason" placeholder="I'm interested because..."></textarea>
                </div>
                <button type="submit" class="btn-join-modal">Send Join Request</button>
            </form>
        </div>
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
                const res = await fetch('../Controller/get_clubs.php', { cache: 'no-store' });
                const data = await res.json();
                
                const tbody = document.getElementById('clubsBody');
                tbody.innerHTML = '';
                
                if (!data.ok || !data.clubs || data.clubs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3">No clubs found at the moment.</td></tr>';
                    return;
                }

                data.clubs.forEach(club => {
                    const tr = document.createElement('tr');
                    
                    let actionHtml = '';
                    if (club.membership_status) {
                        actionHtml = `<span style="font-weight:bold; color: #555;">${club.membership_status.toUpperCase()}</span>`;
                    } else {
                        // Use existing btn-primary class which is blue
                        actionHtml = `<button class="btn-primary" onclick="openJoinModal(${club.club_id}, '${escapeHtml(club.club_name).replace(/'/g, "\\'")}')">Join Club</button>`;
                    }

                    tr.innerHTML = `
                        <td>${escapeHtml(club.club_name)}</td>
                        <td>${escapeHtml(club.description)}</td>
                        <td>${actionHtml}</td>
                    `;
                    tbody.appendChild(tr);
                });

            } catch (e) {
                console.error(e);
                const tbody = document.getElementById('clubsBody');
                if (tbody) tbody.innerHTML = '<tr><td colspan="3">Error loading clubs.</td></tr>';
            }
        }

        const modal = document.getElementById('joinModal');
        const closeModal = document.querySelector('.close-modal');
        const form = document.getElementById('joinForm');

        function openJoinModal(clubId, clubName) {
            document.getElementById('modalClubId').value = clubId;
            document.getElementById('modalClubName').textContent = 'Join ' + clubName;
            document.getElementById('reason').value = '';
            modal.classList.add('active');
        }

        closeModal.onclick = () => modal.classList.remove('active');
        window.onclick = (e) => {
            if (e.target === modal) modal.classList.remove('active');
        };

        form.onsubmit = async (e) => {
            e.preventDefault();
            const clubId = document.getElementById('modalClubId').value;
            const reason = document.getElementById('reason').value;

            try {
                const res = await fetch('../Controller/join_club.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ club_id: clubId, reason: reason })
                });
                const data = await res.json();
                
                if (data.ok) {
                    alert('Request sent successfully!');
                    modal.classList.remove('active');
                    loadClubs();
                } else {
                    alert('Failed: ' + (data.error || 'Unknown error'));
                }
            } catch (ex) {
                alert('Error submitting request');
            }
        };

        loadClubs();
    </script>
</body>
</html>
