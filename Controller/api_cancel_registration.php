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

	// Create NEW cancelled row first, then remove old row(s). Keep the cancelled row in DB.
	if ($hasStatus) {
		$conn->begin_transaction();
		try {
			if ($hasSnapshot) {
				$stmt = $conn->prepare("INSERT INTO event_registrations (event_id, student_id, student_username, student_email, student_full_name, student_phone, status) VALUES (?, ?, ?, ?, ?, ?, 'cancelled')");
				$studentUsername = (string)($student['username'] ?? '');
				$studentEmail = (string)($student['email'] ?? '');
				$studentFullName = (string)($student['full_name'] ?? '');
				$studentPhone = $student['phone'] ?? null;
				$stmt->bind_param('iissss', $eventId, $studentId, $studentUsername, $studentEmail, $studentFullName, $studentPhone);
				$ok = $stmt->execute();
				$inserted = $stmt->affected_rows > 0;
				$stmt->close();
			} else {
				$stmt = $conn->prepare("INSERT INTO event_registrations (event_id, student_id, status) VALUES (?, ?, 'cancelled')");
				$stmt->bind_param('ii', $eventId, $studentId);
				$ok = $stmt->execute();
				$inserted = $stmt->affected_rows > 0;
				$stmt->close();
			}

			$stmt = $conn->prepare('DELETE FROM event_registrations WHERE event_id = ? AND student_id = ? AND registration_id <> LAST_INSERT_ID()');
			$stmt->bind_param('ii', $eventId, $studentId);
			$stmt->execute();
			$stmt->close();

			$conn->commit();
			apiJson(['ok' => $ok, 'cancelled' => $inserted]);
		} catch (mysqli_sql_exception $e) {
			$conn->rollback();
			apiJson(['ok' => false, 'error' => 'Failed to cancel registration'], 500);
		}
	}

	// Backward-compatible: if no status column, just delete.
	apiJson(['ok' => true, 'cancelled' => true, 'note' => 'No status column in event_registrations; cancellation history not stored']);
} finally {
	$conn->close();
}

?>
