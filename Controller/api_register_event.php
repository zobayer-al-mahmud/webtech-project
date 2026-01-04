<?php
require_once __DIR__ . '/_api_common.php';

apiRequireRole('student');
apiRequirePost();

$studentId = (int)($_SESSION['user_id'] ?? 0);
$eventId = (int)($_POST['event_id'] ?? 0);
if ($eventId <= 0) {
	apiJson(['ok' => false, 'error' => 'Invalid event_id'], 400);
}

$conn = apiDbOrFail();
try {
	$hasStatus = false;
	$col = $conn->query("SHOW COLUMNS FROM event_registrations LIKE 'status'");
	if ($col && $col->num_rows > 0) {
		$hasStatus = true;
	}
	$hasSnapshot = false;
	$col = $conn->query("SHOW COLUMNS FROM event_registrations LIKE 'student_full_name'");
	if ($col && $col->num_rows > 0) {
		$hasSnapshot = true;
	}

	// Ensure event exists, approved, upcoming
	$stmt = $conn->prepare("SELECT capacity FROM events WHERE event_id = ? AND status = 'approved' AND event_date >= CURDATE() LIMIT 1");
	$stmt->bind_param('i', $eventId);
	$stmt->execute();
	$event = $stmt->get_result()->fetch_assoc();
	$stmt->close();

	if (!$event) {
		apiJson(['ok' => false, 'error' => 'Event not available'], 404);
	}

	$capacity = (int)($event['capacity'] ?? 0);
	$stmt = $hasStatus
		? $conn->prepare("SELECT COUNT(*) AS c FROM event_registrations WHERE event_id = ? AND status = 'registered'")
		: $conn->prepare('SELECT COUNT(*) AS c FROM event_registrations WHERE event_id = ?');
	$stmt->bind_param('i', $eventId);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	$current = (int)($row['c'] ?? 0);
	$stmt->close();

	if ($capacity > 0 && $current >= $capacity) {
		apiJson(['ok' => false, 'error' => 'Event is full'], 409);
	}

	// Fetch student info for snapshot columns
	$student = null;
	$stmt = $conn->prepare('SELECT username, email, full_name, phone FROM users WHERE user_id = ? LIMIT 1');
	$stmt->bind_param('i', $studentId);
	$stmt->execute();
	$student = $stmt->get_result()->fetch_assoc();
	$stmt->close();

	if (!$student) {
		apiJson(['ok' => false, 'error' => 'Student not found'], 404);
	}

	$conn->begin_transaction();
	try {
		// Create NEW row first
		if ($hasStatus && $hasSnapshot) {
			$stmt = $conn->prepare("INSERT INTO event_registrations (event_id, student_id, student_username, student_email, student_full_name, student_phone, status) VALUES (?, ?, ?, ?, ?, ?, 'registered')");
			$studentUsername = (string)($student['username'] ?? '');
			$studentEmail = (string)($student['email'] ?? '');
			$studentFullName = (string)($student['full_name'] ?? '');
			$studentPhone = $student['phone'] ?? null;
			$stmt->bind_param('iissss', $eventId, $studentId, $studentUsername, $studentEmail, $studentFullName, $studentPhone);
			$ok = $stmt->execute();
			$inserted = $stmt->affected_rows > 0;
			$stmt->close();
		} elseif ($hasStatus) {
			$stmt = $conn->prepare("INSERT INTO event_registrations (event_id, student_id, status) VALUES (?, ?, 'registered')");
			$stmt->bind_param('ii', $eventId, $studentId);
			$ok = $stmt->execute();
			$inserted = $stmt->affected_rows > 0;
			$stmt->close();
		} else {
			$stmt = $conn->prepare('INSERT INTO event_registrations (event_id, student_id) VALUES (?, ?)');
			$stmt->bind_param('ii', $eventId, $studentId);
			$ok = $stmt->execute();
			$inserted = $stmt->affected_rows > 0;
			$stmt->close();
		}

		// Then remove OLD row(s) for this event/student (typically the opposite status)
		$stmt = $conn->prepare('DELETE FROM event_registrations WHERE event_id = ? AND student_id = ? AND registration_id <> LAST_INSERT_ID()');
		$stmt->bind_param('ii', $eventId, $studentId);
		$stmt->execute();
		$stmt->close();

		$conn->commit();
	} catch (mysqli_sql_exception $e) {
		$conn->rollback();
		apiJson(['ok' => false, 'error' => 'Failed to register'], 500);
	}

	apiJson(['ok' => $ok, 'registered' => $inserted]);
} finally {
	$conn->close();
}

?>
