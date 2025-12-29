<?php
require_once '../config/config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php?error=invalid_request');
}

// Get and sanitize input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

// Validate inputs
if (empty($username) || empty($password)) {
    redirect('login.php?error=empty_fields');
}

// Get database connection
$conn = getDBConnection();
if (!$conn) {
    redirect('login.php?error=database_error');
}

// Check for recent failed login attempts (prevent brute force)
$ip_address = $_SERVER['REMOTE_ADDR'];
$lockout_time = date('Y-m-d H:i:s', time() - LOGIN_LOCKOUT_TIME);

$stmt = $conn->prepare("SELECT COUNT(*) as attempt_count FROM login_attempts 
                        WHERE username = ? AND success = 0 AND attempt_time > ?");
$stmt->bind_param("ss", $username, $lockout_time);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$failed_attempts = $row['attempt_count'];
$stmt->close();

if ($failed_attempts >= MAX_LOGIN_ATTEMPTS) {
    // Log this attempt
    $stmt = $conn->prepare("INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $username, $ip_address);
    $stmt->execute();
    $stmt->close();
    
    closeDBConnection($conn);
    redirect('login.php?error=account_locked');
}

// Get user from database
$stmt = $conn->prepare("SELECT user_id, username, email, password_hash, full_name, user_role, is_active 
                        FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Log failed attempt
    $stmt = $conn->prepare("INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $username, $ip_address);
    $stmt->execute();
    $stmt->close();
    
    closeDBConnection($conn);
    redirect('login.php?error=invalid_credentials');
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if account is active
if (!$user['is_active']) {
    closeDBConnection($conn);
    redirect('login.php?error=account_disabled');
}

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    // Log failed attempt
    $stmt = $conn->prepare("INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $username, $ip_address);
    $stmt->execute();
    $stmt->close();
    
    closeDBConnection($conn);
    
    // Add error logging for debugging
    error_log("Failed login attempt for username: $username from IP: $ip_address");
    
    redirect('login.php?error=invalid_credentials');
}

// Log successful attempt
$stmt = $conn->prepare("INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, 1)");
$stmt->bind_param("ss", $username, $ip_address);
$stmt->execute();
$stmt->close();

// Clear old failed attempts for this user
$conn->query("DELETE FROM login_attempts WHERE username = '$username' AND success = 0");

// Set session variables
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['user_role'] = $user['user_role'];
$_SESSION['logged_in'] = true;

// Set remember me cookie if checked
if ($remember_me) {
    $token = bin2hex(random_bytes(32));
    setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
    // In production, store this token in database
}

closeDBConnection($conn);

// Redirect to appropriate dashboard based on role
switch ($user['user_role']) {
    case 'admin':
        redirect('admin_dashboard.php');
        break;
    case 'organizer':
        redirect('club_organizer_dashboard.php');
        break;
    case 'student':
        redirect('student_dashboard.php');
        break;
    default:
        redirect('student_dashboard.php');
}
?>
