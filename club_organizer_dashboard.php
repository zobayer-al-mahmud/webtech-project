<?php
require_once 'config/config.php';

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
                    <li><a href="logout.php">Logout</a></li>
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
                    <h3>My Events</h3>
                    <div class="summary-number">8</div>
                </div>
                <div class="summary-card">
                    <h3>Upcoming Events</h3>
                    <div class="summary-number">3</div>
                </div>
                <div class="summary-card">
                    <h3>Total Participants</h3>
                    <div class="summary-number">156</div>
                </div>
            </section>

            <!-- Create New Event Form -->
            <section class="data-section">
                <h2>Create New Event</h2>
                <form class="event-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Event Title</label>
                            <input type="text" placeholder="Enter event title">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date">
                        </div>
                        <div class="form-group">
                            <label>Time</label>
                            <input type="time">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" placeholder="Enter location">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea rows="4" placeholder="Enter event description"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Create Event</button>
                </form>
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
                            <th>Registered</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Annual Tech Summit 2025</td>
                            <td>2025-01-15</td>
                            <td>10:00 AM</td>
                            <td>Main Hall</td>
                            <td>45</td>
                            <td>Approved</td>
                        </tr>
                        <tr>
                            <td>Workshop: Web Development</td>
                            <td>2025-01-18</td>
                            <td>02:00 PM</td>
                            <td>Computer Lab</td>
                            <td>28</td>
                            <td>Pending</td>
                        </tr>
                        <tr>
                            <td>Coding Competition</td>
                            <td>2025-01-22</td>
                            <td>09:00 AM</td>
                            <td>Tech Center</td>
                            <td>35</td>
                            <td>Approved</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Participant List -->
            <section class="data-section">
                <h2>Participant List</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Event</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Alice Johnson</td>
                            <td>alice@university.edu</td>
                            <td>Annual Tech Summit 2025</td>
                            <td>2025-12-20</td>
                        </tr>
                        <tr>
                            <td>Bob Williams</td>
                            <td>bob@university.edu</td>
                            <td>Annual Tech Summit 2025</td>
                            <td>2025-12-21</td>
                        </tr>
                        <tr>
                            <td>Carol Martinez</td>
                            <td>carol@university.edu</td>
                            <td>Workshop: Web Development</td>
                            <td>2025-12-22</td>
                        </tr>
                        <tr>
                            <td>David Lee</td>
                            <td>david@university.edu</td>
                            <td>Coding Competition</td>
                            <td>2025-12-23</td>
                        </tr>
                        <tr>
                            <td>Emma Wilson</td>
                            <td>emma@university.edu</td>
                            <td>Annual Tech Summit 2025</td>
                            <td>2025-12-24</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
