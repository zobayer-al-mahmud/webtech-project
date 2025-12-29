<?php
require_once 'config/config.php';

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
                    <li><a href="logout.php">Logout</a></li>
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
                    <div class="summary-number">245</div>
                </div>
                <div class="summary-card">
                    <h3>Total Clubs</h3>
                    <div class="summary-number">18</div>
                </div>
                <div class="summary-card">
                    <h3>Total Events</h3>
                    <div class="summary-number">42</div>
                </div>
                <div class="summary-card">
                    <h3>Pending Approvals</h3>
                    <div class="summary-number">7</div>
                </div>
            </section>

            <!-- Club Approval Requests -->
            <section class="data-section">
                <h2>Club Approval Requests</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Club Name</th>
                            <th>Organizer</th>
                            <th>Category</th>
                            <th>Date Requested</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Tech Innovation Club</td>
                            <td>John Smith</td>
                            <td>Technology</td>
                            <td>2025-12-28</td>
                            <td>
                                <button class="btn-approve">Approve</button>
                                <button class="btn-reject">Reject</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Art & Design Society</td>
                            <td>Emily Davis</td>
                            <td>Arts</td>
                            <td>2025-12-27</td>
                            <td>
                                <button class="btn-approve">Approve</button>
                                <button class="btn-reject">Reject</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Sports Enthusiasts</td>
                            <td>Michael Brown</td>
                            <td>Sports</td>
                            <td>2025-12-26</td>
                            <td>
                                <button class="btn-approve">Approve</button>
                                <button class="btn-reject">Reject</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Event Approval Requests -->
            <section class="data-section">
                <h2>Event Approval Requests</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Club</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Annual Tech Summit 2025</td>
                            <td>Tech Innovation Club</td>
                            <td>2025-01-15</td>
                            <td>Main Hall</td>
                            <td>
                                <button class="btn-approve">Approve</button>
                                <button class="btn-reject">Reject</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Art Exhibition</td>
                            <td>Art & Design Society</td>
                            <td>2025-01-20</td>
                            <td>Gallery Room</td>
                            <td>
                                <button class="btn-approve">Approve</button>
                                <button class="btn-reject">Reject</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Sports Day</td>
                            <td>Sports Enthusiasts</td>
                            <td>2025-01-25</td>
                            <td>Sports Complex</td>
                            <td>
                                <button class="btn-approve">Approve</button>
                                <button class="btn-reject">Reject</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Workshop: Web Development</td>
                            <td>Tech Innovation Club</td>
                            <td>2025-01-18</td>
                            <td>Computer Lab</td>
                            <td>
                                <button class="btn-approve">Approve</button>
                                <button class="btn-reject">Reject</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
