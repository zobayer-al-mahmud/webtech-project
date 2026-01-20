<?php
require_once __DIR__ . '/common.php';

function redirectToLogin(string $error = '', string $username = ''): void {
	if ($error) $_SESSION['loginErr'] = $error;
	if ($username) $_SESSION['username'] = $username;
	redirect('../View/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirectToLogin();
}
// Handle role-specific dashboard redirects via common function if needed, but here we validate login.

$usernameOrEmail = trim((string)($_POST['username'] ?? $_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($usernameOrEmail === '') redirectToLogin('Username/Email field is required');
if ($password === '') redirectToLogin('Password field is required', $usernameOrEmail);

$db = new DatabaseConnection();
$conn = $db->openConnection();
if (!$conn) {
	redirectToLogin('Database connection failed', $usernameOrEmail);
}

try {
	$stmt = $conn->prepare('SELECT user_id, username, email, password, full_name, user_role, phone, is_active, filepath FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1');
	$stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result ? $result->fetch_assoc() : null;
	$stmt->close();

	if (!$user || $password !== (string)$user['password']) {
		redirectToLogin('Invalid username/email or password', $usernameOrEmail);
	}

	$_SESSION['user_id'] = $user['user_id'];
	$_SESSION['username'] = $user['username'];
	$_SESSION['email'] = $user['email'];
	$_SESSION['full_name'] = $user['full_name'];
	$_SESSION['phone'] = $user['phone'];
	$_SESSION['user_role'] = $user['user_role'];
	$_SESSION['filepath'] = $user['filepath'];

    if (isset($_POST['remember_me'])) {
        setcookie('remember_me', $user['username'], time() + (86400 * 30), "/");
    }

	$_SESSION['isLoggedIn'] = true;

	redirectToRoleDashboard((string)$user['user_role']);
} finally {
	$db->closeConnection($conn);
}
?>

