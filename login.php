<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    $user = getCurrentUser();
    $role = $user['role'];
    
    // Redirect based on role
    if ($role === 'admin') {
        redirect('admin_dashboard.php');
    } elseif ($role === 'organizer') {
        redirect('club_organizer_dashboard.php');
    } else {
        redirect('student_dashboard.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$username || !$password) {
        $_SESSION['loginErr'] = "Please enter both username and password";
        $_SESSION['username'] = $username;
        header("Location: login.php");
        exit();
    }
    
    $conn = getDBConnection();
    if (!$conn) {
        $_SESSION['loginErr'] = "Connection failed";
        header("Location: login.php");
        exit();
    }
    
    $stmt = $conn->prepare("SELECT user_id, username, email, password_hash, full_name, user_role 
                            FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    closeDBConnection($conn);
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION['loginErr'] = "Invalid username or password";
        $_SESSION['username'] = $username;
        header("Location: login.php");
        exit();
    }
    
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['user_role'] = $user['user_role'];
    $_SESSION['isLoggedIn'] = true;
    
    // Redirect based on role
    $role = $user['user_role'];
    if ($role === 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($role === 'organizer') {
        header("Location: club_organizer_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

$username = $_SESSION['username'] ?? '';
$loginErr = $_SESSION['loginErr'] ?? '';
$success = $_SESSION['registerSuccess'] ?? '';
unset($_SESSION['username'], $_SESSION['loginErr'], $_SESSION['registerSuccess']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - University Event Management</title>
    <link rel="stylesheet" href="assets/css/auth.css?v=2">
</head>
<body>
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>🎓 University Events</h1>
                <p>Event & Club Management System</p>
            </div>

            <form method="POST">
                <h2>Login</h2>
                
                <?php if ($success): ?>
                <div class="alert alert-success" style="display: block;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($loginErr): ?>
                <div class="alert alert-error" style="display: block;">
                    <?php echo htmlspecialchars($loginErr); ?>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($username); ?>"
                           placeholder="Enter your username or email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Enter your password">
                </div>

                <button type="submit" class="btn btn-primary">
                    Login
                </button>

                <div class="form-footer">
                    <p>Don't have an account? <a href="register.php" class="link">Register here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
