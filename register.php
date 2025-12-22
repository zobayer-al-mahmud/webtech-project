<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('demo_dashboard.php');
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
        header("Location: register.php");
        exit();
    }
    
    $conn = getDBConnection();
    if (!$conn) {
        $_SESSION['error'] = "Connection failed";
        header("Location: register.php");
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
        header("Location: register.php");
        exit();
    }
    $stmt->close();
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name, user_role, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $hash, $full_name, $role, $phone);
    
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
    header("Location: register.php");
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
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>🎓 University Events</h1>
                <p>Event & Club Management System</p>
            </div>

            <form method="POST">
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
                    <input type="password" id="password" name="password" 
                           placeholder="Create a password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm your password">
                </div>

                <button type="submit" class="btn btn-primary">Register</button>

                <div class="form-footer">
                    <p>Already have an account? <a href="login.php" class="link">Login here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
