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
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['user_role'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $error = '';
    
    if (!$full_name || !$username || !$email || !$role || !$password || !$confirm) {
        $error = "All fields except phone are required";
    } elseif (strlen($full_name) < 3) {
        $error = "Full name must be at least 3 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = "Username: 3-20 characters, letters/numbers/underscore only";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (!in_array($role, ['student', 'organizer'])) {
        $error = "Please select a valid role";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || 
              !preg_match('/[0-9]/', $password) || !preg_match('/[@#$%^&*!]/', $password)) {
        $error = "Password needs: uppercase, lowercase, number, special character";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match";
    }
    
    if ($error) {
        $_SESSION['error'] = $error;
        $_SESSION['form'] = compact('full_name', 'username', 'email', 'phone', 'role');
        header("Location: signup.php");
        exit();
    }
    
    $conn = getDBConnection();
    if (!$conn) {
        $_SESSION['error'] = "Connection failed";
        header("Location: signup.php");
        exit();
    }
    
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Username or email already exists";
        $_SESSION['form'] = compact('full_name', 'username', 'email', 'phone', 'role');
        $stmt->close();
        closeDBConnection($conn);
        header("Location: signup.php");
        exit();
    }
    $stmt->close();
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name, user_role, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $password, $full_name, $role, $phone);
    
    if ($stmt->execute()) {
        $_SESSION['registerSuccess'] = "Registration successful! Please login.";
        $stmt->close();
        closeDBConnection($conn);
        header("Location: login.php");
        exit();
    }
    
    $_SESSION['error'] = "Registration failed. Try again.";
    $stmt->close();
    closeDBConnection($conn);
    header("Location: signup.php");
    exit();
}

$form = $_SESSION['form'] ?? [];
$error = $_SESSION['error'] ?? '';
unset($_SESSION['form'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - University Event Management</title>
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

            <form method="POST" action="../Controller/signUpValidation.php">
                <h2>Register</h2>
                
                <?php if ($error): ?>
                <div class="alert alert-error" style="display: block;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($form['full_name'] ?? ''); ?>"
                           placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($form['username'] ?? ''); ?>"
                           placeholder="Choose a username">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($form['email'] ?? ''); ?>"
                           placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="phone">Phone (Optional)</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($form['phone'] ?? ''); ?>"
                           inputmode="numeric"
                           pattern="\d{11}"
                           maxlength="11"
                           placeholder="Enter your phone number">
                </div>

                <div class="form-group">
                    <label for="user_role">I am a</label>
                    <select id="user_role" name="user_role">
                        <option value="">Select your role</option>
                        <option value="student" <?php echo ($form['role'] ?? '') === 'student' ? 'selected' : ''; ?>>Student</option>
                        <option value="organizer" <?php echo ($form['role'] ?? '') === 'organizer' ? 'selected' : ''; ?>>Event Organizer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" 
                               placeholder="Create a password">
                        <span class="toggle-password" onclick="togglePassword('password', 'passwordToggleIcon')" title="Show/Hide password">
                            <img id="passwordToggleIcon" src="https://img.icons8.com/?size=100&id=85137&format=png&color=000000" alt="Show/Hide" width="18" height="18">
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm your password">
                        <span class="toggle-password" onclick="togglePassword('confirm_password', 'confirmPasswordToggleIcon')" title="Show/Hide password">
                            <img id="confirmPasswordToggleIcon" src="https://img.icons8.com/?size=100&id=85137&format=png&color=000000" alt="Show/Hide" width="18" height="18">
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Register</button>

                <div class="form-footer">
                    <p>Already have an account? <a href="login.php" class="link">Login here</a></p>
                </div>
            </form>
        </div>
    </div>
    <script>
        function togglePassword(fieldId, iconImgId) {
            const field = document.getElementById(fieldId);
            const img = document.getElementById(iconImgId);

            // Per your icons: unhide=85137, hide=85130
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
