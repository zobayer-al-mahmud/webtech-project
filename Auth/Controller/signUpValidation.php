<?php
require_once __DIR__ . '/../Model/DatabaseConnection.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function redirectToSignup(): void {
	header('Location: ../View/signup.php');
	exit();
}

function redirectToLoginWithSuccess(string $message): void {
	$_SESSION['registerSuccess'] = $message;
	header('Location: ../View/login.php');
	exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirectToSignup();
}

$fullName = trim((string)($_POST['full_name'] ?? ''));
$username = trim((string)($_POST['username'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$role = trim((string)($_POST['user_role'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$confirm = (string)($_POST['confirm_password'] ?? '');

// Optional file upload (kept compatible with the code you provided)
$uploadFile = $_FILES['fileUpload'] ?? null;
$filePath = null;

$error = '';
if ($fullName === '' || $username === '' || $email === '' || $role === '' || $password === '' || $confirm === '') {
	$error = 'All fields except phone are required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$error = 'Invalid email format';
	} elseif ($phone !== '' && !preg_match('/^\d{11}$/', $phone)) {
	$error = 'Phone number must be an 11 digit number';
} elseif (!in_array($role, ['student', 'organizer'], true)) {
	$error = 'Please select a valid role';
} elseif ($password !== $confirm) {
	$error = 'Passwords do not match';
}

if ($error !== '') {
	$_SESSION['error'] = $error;
	$_SESSION['form'] = [
		'full_name' => $fullName,
		'username' => $username,
		'email' => $email,
		'phone' => $phone,
		'role' => $role,
	];
	redirectToSignup();
}

// Profile Picture Upload
$uploadFile = $_FILES['profile_pic'] ?? null;
$filePath = null;

if ($uploadFile && isset($uploadFile['tmp_name']) && is_uploaded_file($uploadFile['tmp_name'])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($uploadFile['tmp_name']);

    if (!in_array($mimeType, $allowedTypes, true)) {
        $_SESSION['error'] = 'Invalid image type. Only JPG, PNG, GIF, and WEBP are allowed.';
        $_SESSION['form'] = [
            'full_name' => $fullName,
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
        ];
        redirectToSignup();
    }

    $targetDir = __DIR__ . '/../../uploads/profile_pics/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    // Generate unique filename
    $ext = pathinfo((string)$uploadFile['name'], PATHINFO_EXTENSION);
    $newFileName = uniqid('pfp_', true) . '.' . $ext;
    $destAbs = $targetDir . $newFileName;
    
    if (move_uploaded_file($uploadFile['tmp_name'], $destAbs)) {
        $filePath = 'uploads/profile_pics/' . $newFileName;
    } else {
        $_SESSION['error'] = 'Failed to upload profile picture';
        redirectToSignup();
    }
}

$db = new DatabaseConnection();
$conn = $db->openConnection();
if (!$conn) {
	$_SESSION['error'] = 'Database connection failed';
	$_SESSION['form'] = [
		'full_name' => $fullName,
		'username' => $username,
		'email' => $email,
		'phone' => $phone,
		'role' => $role,
	];
	redirectToSignup();
}

try {
	$stmt = $conn->prepare('SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1');
	$stmt->bind_param('ss', $username, $email);
	$stmt->execute();
	$exists = $stmt->get_result();
	$alreadyExists = $exists && $exists->num_rows > 0;
	$stmt->close();

	if ($alreadyExists) {
		$_SESSION['error'] = 'Username or email already exists';
		$_SESSION['form'] = [
			'full_name' => $fullName,
			'username' => $username,
			'email' => $email,
			'phone' => $phone,
			'role' => $role,
		];
		redirectToSignup();
	}

	// Insert new user
	$stmt = $conn->prepare('INSERT INTO users (username, email, password, full_name, user_role, phone, filepath) VALUES (?, ?, ?, ?, ?, ?, ?)');
	$stmt->bind_param('sssssss', $username, $email, $password, $fullName, $role, $phone, $filePath);
	
	if ($stmt->execute()) {
		redirectToLoginWithSuccess('Registration successful! Please login.');
	} else {
		throw new Exception('Insert failed: ' . $stmt->error);
	}
} catch (Exception $e) {
	$_SESSION['error'] = 'Registration failed. Please try again later.';
	$_SESSION['form'] = [
		'full_name' => $fullName,
		'username' => $username,
		'email' => $email,
		'phone' => $phone,
		'role' => $role,
	];
	redirectToSignup();
} finally {
	$db->closeConnection();
}

?>
