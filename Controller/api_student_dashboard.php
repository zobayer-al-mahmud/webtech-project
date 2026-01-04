<?php
require_once __DIR__ . '/_api_common.php';

apiRequireRole('student');

$studentId = (int)($_SESSION['user_id'] ?? 0);

$conn = apiDbOrFail();
try {
	$hasStatus = false;
	$col = $conn->query("SHOW COLUMNS FROM event_registrations LIKE 'status'");
	if ($col && $col->num_rows > 0) {
		$hasStatus = true;
	}

	$upcomingEvents = [];
	$registeredEvents = [];

	if ($hasStatus) {
		$stmt = $conn->prepare(
			"SELECT e.event_id, e.title, e.event_date, e.event_time, e.location, e.capacity,
			        u.username AS organizer_username,
			        CASE WHEN lr.status = 'registered' THEN 1 ELSE 0 END AS is_registered
			 FROM events e
			 JOIN users u ON u.user_id = e.organizer_id
			 LEFT JOIN (
				SELECT r1.event_id, r1.student_id, r1.status
				FROM event_registrations r1
				JOIN (
					SELECT event_id, student_id, MAX(registration_id) AS max_id
					FROM event_registrations
					WHERE student_id = ?
					GROUP BY event_id, student_id
				) r2 ON r2.max_id = r1.registration_id
			 ) lr ON lr.event_id = e.event_id AND lr.student_id = ?
			 WHERE e.status = 'approved' AND e.event_date >= CURDATE()
			 ORDER BY e.event_date ASC, e.event_time ASC"
		);
		$stmt->bind_param('ii', $studentId, $studentId);
	} else {
		$stmt = $conn->prepare(
			"SELECT e.event_id, e.title, e.event_date, e.event_time, e.location, e.capacity,
			        u.username AS organizer_username,
			        CASE WHEN r.registration_id IS NULL THEN 0 ELSE 1 END AS is_registered
			 FROM events e
			 JOIN users u ON u.user_id = e.organizer_id
			 LEFT JOIN event_registrations r
			        ON r.event_id = e.event_id AND r.student_id = ?
			 WHERE e.status = 'approved' AND e.event_date >= CURDATE()
			 ORDER BY e.event_date ASC, e.event_time ASC"
		);
		$stmt->bind_param('i', $studentId);
	}
	$stmt->execute();
	$res = $stmt->get_result();
	while ($row = $res->fetch_assoc()) {
		$row['is_registered'] = (int)$row['is_registered'] === 1;
		$upcomingEvents[] = $row;
	}
	$stmt->close();

	if ($hasStatus) {
		$stmt = $conn->prepare(
			"SELECT e.event_id, e.title, e.event_date, e.event_time, e.location,
			        u.username AS organizer_username
			 FROM events e
			 JOIN users u ON u.user_id = e.organizer_id
			 JOIN (
				SELECT r1.event_id, r1.student_id, r1.status
				FROM event_registrations r1
				JOIN (
					SELECT event_id, student_id, MAX(registration_id) AS max_id
					FROM event_registrations
					WHERE student_id = ?
					GROUP BY event_id, student_id
				) r2 ON r2.max_id = r1.registration_id
			 ) lr ON lr.event_id = e.event_id AND lr.student_id = ?
			 WHERE lr.status = 'registered' AND e.status = 'approved' AND e.event_date >= CURDATE()
			 ORDER BY e.event_date ASC, e.event_time ASC"
		);
		$stmt->bind_param('ii', $studentId, $studentId);
	} else {
		$stmt = $conn->prepare(
			"SELECT e.event_id, e.title, e.event_date, e.event_time, e.location,
			        u.username AS organizer_username
			 FROM event_registrations r
			 JOIN events e ON e.event_id = r.event_id
			 JOIN users u ON u.user_id = e.organizer_id
			 WHERE r.student_id = ? AND e.status = 'approved' AND e.event_date >= CURDATE()
			 ORDER BY e.event_date ASC, e.event_time ASC"
		);
		$stmt->bind_param('i', $studentId);
	}
	$stmt->execute();
	$res = $stmt->get_result();
	while ($row = $res->fetch_assoc()) {
		$registeredEvents[] = $row;
	}
	$stmt->close();

	apiJson([
		'ok' => true,
		'upcomingCount' => count($upcomingEvents),
		'registeredCount' => count($registeredEvents),
		'upcomingEvents' => $upcomingEvents,
		'registeredEvents' => $registeredEvents,
	]);
} finally {
	$conn->close();
}

?>
