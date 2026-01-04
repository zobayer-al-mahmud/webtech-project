<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Model/DatabaseConnection.php';

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

function getDBConnection() {
    return (new DatabaseConnection())->openConnection();
}

function closeDBConnection($conn): void {
    if ($conn) {
        (new DatabaseConnection())->closeConnection($conn);
    }
}

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
    
    if (!$user || $password !== $user['password_hash']) {
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
    <style>
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            user-select: none;
            font-size: 18px;
            color: #666;
        }
        .toggle-password:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <h1> University Events</h1>
                <p>Event & Club Management System</p>
            </div>

            <form method="POST" action="../Controller/loginValidation.php">
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
                    <div class="password-wrapper">
                           <input type="password" id="password" name="password" 
                               placeholder="Enter your password">
                        <span class="toggle-password" onclick="togglePassword('password')" title="Show/Hide password">
                            <img id="passwordToggleIcon" src="https://img.icons8.com/?size=100&id=85137&format=png&color=000000" alt="Show/Hide" width="18" height="18">
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Login
                </button>

                <div class="form-footer">
                    <p>Don't have an account? <a href="signup.php" class="link">Register here</a></p>
                </div>
            </form>
        </div>
    </div>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            const img = icon.querySelector('img');

            const showIcon = 'https://img.icons8.com/?size=100&id=85137&format=png&color=000000';
            const hideIcon = 'https://img.icons8.com/?size=100&id=85130&format=png&color=000000';
            
            
            
            if (field.type === 'password') {
                field.type = 'text';
                if (img) img.src = hideIcon;
            } else {
                field.type = 'password';
                if (img) img.src = showIcon;
            }
        }
    </script>
</body>
</html>
