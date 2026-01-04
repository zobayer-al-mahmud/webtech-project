<?php
require_once __DIR__ . '/../Model/DatabaseConnection.php';

header('Content-Type: text/plain; charset=utf-8');

$email = trim((string)($_POST['Email'] ?? $_POST['email'] ?? ''));

if ($email === '') {
	echo 'Email Empty';
	exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	echo 'Invalid Email';
	exit;
}

$db = new DatabaseConnection();
$conn = $db->openConnection();

if (!$conn) {
	echo 'Database Error';
	exit;
}

try {
	$stmt = $conn->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
	$stmt->bind_param('s', $email);
	$stmt->execute();
	$result = $stmt->get_result();
	$stmt->close();

	if ($result && $result->num_rows > 0) {
		echo 'Email Already Used';
	} else {
		echo 'Unique Email';
	}
} finally {
	$db->closeConnection($conn);
}

?>
