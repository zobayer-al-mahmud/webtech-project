<?php
require_once __DIR__ . '/../Model/DatabaseConnection.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function redirectToLogin(): void {
	header('Location: ../View/login.php');
	exit();
}

function redirectToRoleDashboard(string $role): void {
	if ($role === 'admin') {
		header('Location: ../View/admin_dashboard.php');
		exit();
	}
	if ($role === 'organizer') {
		header('Location: ../View/club_organizer_dashboard.php');
		exit();
	}
	header('Location: ../View/student_dashboard.php');
	exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirectToLogin();
}

$usernameOrEmail = trim((string)($_POST['username'] ?? $_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

$errors = [];
if ($usernameOrEmail === '') {
	$errors['username'] = 'Username/Email field is required';
}
if ($password === '') {
	$errors['password'] = 'Password field is required';
}

if (!empty($errors)) {
	$_SESSION['loginErr'] = $errors['username'] ?? $errors['password'] ?? 'Please fill the form properly';
	$_SESSION['username'] = $usernameOrEmail;
	redirectToLogin();
}

$db = new DatabaseConnection();
$conn = $db->openConnection();
if (!$conn) {
	$_SESSION['loginErr'] = 'Database connection failed';
	$_SESSION['username'] = $usernameOrEmail;
	redirectToLogin();
}

try {
	$stmt = $conn->prepare('SELECT user_id, username, email, password_hash, full_name, user_role, is_active FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1');
	$stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result ? $result->fetch_assoc() : null;
	$stmt->close();

	if (!$user || $password !== (string)$user['password_hash']) {
		$_SESSION['loginErr'] = 'Invalid username/email or password';
		$_SESSION['username'] = $usernameOrEmail;
		redirectToLogin();
	}

	$_SESSION['user_id'] = $user['user_id'];
	$_SESSION['username'] = $user['username'];
	$_SESSION['email'] = $user['email'];
	$_SESSION['full_name'] = $user['full_name'];
	$_SESSION['user_role'] = $user['user_role'];
	$_SESSION['isLoggedIn'] = true;

	redirectToRoleDashboard((string)$user['user_role']);
} finally {
	$db->closeConnection($conn);
}

?>

