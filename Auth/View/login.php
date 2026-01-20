<?php
require_once __DIR__ . '/../Controller/common.php';

// Auto-login from cookie
if (!isLoggedIn() && isset($_COOKIE['remember_me'])) {
    $conn = (new DatabaseConnection())->openConnection();
    if ($conn) {
        $stmt = $conn->prepare("SELECT user_id, username, email, full_name, user_role, phone, filepath FROM users WHERE username = ? AND is_active = 1");
        $stmt->bind_param("s", $_COOKIE['remember_me']);
        $stmt->execute();
        $autoUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        (new DatabaseConnection())->closeConnection($conn);

        if ($autoUser) {
            $_SESSION['user_id'] = $autoUser['user_id'];
            $_SESSION['username'] = $autoUser['username'];
            $_SESSION['email'] = $autoUser['email'];
            $_SESSION['full_name'] = $autoUser['full_name'];
            $_SESSION['user_role'] = $autoUser['user_role'];
            $_SESSION['phone'] = $autoUser['phone'] ?? '';
            $_SESSION['filepath'] = $autoUser['filepath'] ?? '';
            $_SESSION['isLoggedIn'] = true;
        }
    }
}

if (isLoggedIn()) {
    $user = getCurrentUser();
    redirectToRoleDashboard($user['role']);
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
    <link rel="stylesheet" href="assets/css/auth.css">
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
            <div class="auth-header" style="display: flex; align-items: center; justify-content: center; gap: 15px; text-align: left; margin-bottom: 25px;">
                <img src="https://portal.aiub.edu/Content/Images/aiub_logo_92x92.png" alt="AIUB Logo" style="width: 85px; height: auto;">
                <div>
                    <h2 style="font-family: sans-serif; color: #005a9c; font-size: 14px; font-weight: 600; margin: 0; line-height: 1.2; text-transform: uppercase;">
                        American International<br>University-Bangladesh
                    </h2>
                    <p style="margin: 4px 0 0 0; color: #666; font-size: 10px; font-style: italic;">
                        — where leaders are created.
                    </p>
                    <p style="margin: 8px 0 0 0; color: #444; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                        Club and Event Management System
                    </p>
                </div>
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

                <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="remember_me" name="remember_me" style="width: auto;">
                    <label for="remember_me" style="margin: 0; font-weight: normal;">Remember Me</label>
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
