<?php
require_once 'config/config.php';

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
                    <li><a href="logout.php">Logout</a></li>
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
                    <div class="summary-number">12</div>
                </div>
                <div class="summary-card">
                    <h3>Registered Events</h3>
                    <div class="summary-number">4</div>
                </div>
                <div class="summary-card">
                    <h3>Notifications</h3>
                    <div class="summary-number">3</div>
                </div>
            </section>

            <!-- Notifications -->
            <section class="data-section">
                <h2>Notifications</h2>
                <div class="notification-list">
                    <div class="notification-item">
                        <p><strong>Reminder:</strong> Annual Tech Summit 2025 is tomorrow at 10:00 AM</p>
                        <span class="notification-date">2025-12-28</span>
                    </div>
                    <div class="notification-item">
                        <p><strong>New Event:</strong> Art Exhibition has been scheduled for January 20</p>
                        <span class="notification-date">2025-12-27</span>
                    </div>
                    <div class="notification-item">
                        <p><strong>Update:</strong> Workshop: Web Development venue changed to Lab 3</p>
                        <span class="notification-date">2025-12-26</span>
                    </div>
                </div>
            </section>

            <!-- Upcoming Events -->
            <section class="data-section">
                <h2>Upcoming Events</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Club</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Annual Tech Summit 2025</td>
                            <td>Tech Innovation Club</td>
                            <td>2025-01-15</td>
                            <td>Main Hall</td>
                            <td><button class="btn-register">Register</button></td>
                        </tr>
                        <tr>
                            <td>Workshop: Web Development</td>
                            <td>Tech Innovation Club</td>
                            <td>2025-01-18</td>
                            <td>Computer Lab</td>
                            <td><button class="btn-register">Register</button></td>
                        </tr>
                        <tr>
                            <td>Art Exhibition</td>
                            <td>Art & Design Society</td>
                            <td>2025-01-20</td>
                            <td>Gallery Room</td>
                            <td><button class="btn-register">Register</button></td>
                        </tr>
                        <tr>
                            <td>Coding Competition</td>
                            <td>Tech Innovation Club</td>
                            <td>2025-01-22</td>
                            <td>Tech Center</td>
                            <td><button class="btn-register">Register</button></td>
                        </tr>
                        <tr>
                            <td>Sports Day</td>
                            <td>Sports Enthusiasts</td>
                            <td>2025-01-25</td>
                            <td>Sports Complex</td>
                            <td><button class="btn-register">Register</button></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Registered Events -->
            <section class="data-section">
                <h2>My Registered Events</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Club</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Annual Tech Summit 2025</td>
                            <td>Tech Innovation Club</td>
                            <td>2025-01-15</td>
                            <td>Main Hall</td>
                            <td><button class="btn-cancel">Cancel Registration</button></td>
                        </tr>
                        <tr>
                            <td>Workshop: Web Development</td>
                            <td>Tech Innovation Club</td>
                            <td>2025-01-18</td>
                            <td>Computer Lab</td>
                            <td><button class="btn-cancel">Cancel Registration</button></td>
                        </tr>
                        <tr>
                            <td>Art Exhibition</td>
                            <td>Art & Design Society</td>
                            <td>2025-01-20</td>
                            <td>Gallery Room</td>
                            <td><button class="btn-cancel">Cancel Registration</button></td>
                        </tr>
                        <tr>
                            <td>Coding Competition</td>
                            <td>Tech Innovation Club</td>
                            <td>2025-01-22</td>
                            <td>Tech Center</td>
                            <td><button class="btn-cancel">Cancel Registration</button></td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
