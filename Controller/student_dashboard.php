<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';

apiRequireRole('student');

$studentId = (int)($_SESSION['user_id'] ?? 0);

$conn = apiDbOrFail();
try {
	$upcomingEvents = [];
	$registeredEvents = [];

	$search = trim((string)($_GET['search'] ?? ''));
	$searchParam = '%' . $search . '%';

	// Upcoming
	$sql = "SELECT e.event_id, e.title, e.event_date, e.event_time, e.location, e.capacity,
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
			 WHERE e.status = 'approved' AND e.event_date >= CURDATE()";
	if ($search !== '') {
		$sql .= " AND (e.title LIKE ? OR u.username LIKE ?)";
	}
	$sql .= " ORDER BY e.event_date ASC, e.event_time ASC";
	
	$stmt = $conn->prepare($sql);
	if ($search !== '') {
		$stmt->bind_param('iiss', $studentId, $studentId, $searchParam, $searchParam);
	} else {
		$stmt->bind_param('ii', $studentId, $studentId);
	}

	$stmt->execute();
	$res = $stmt->get_result();
	while ($row = $res->fetch_assoc()) {
		$row['is_registered'] = (int)$row['is_registered'] === 1;
		$upcomingEvents[] = $row;
	}
	$stmt->close();

	// Registered
	$sql = "SELECT e.event_id, e.title, e.event_date, e.event_time, e.location,
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
			 WHERE lr.status = 'registered' AND e.status = 'approved' AND e.event_date >= CURDATE()";
	if ($search !== '') {
		$sql .= " AND (e.title LIKE ? OR u.username LIKE ?)";
	}
	$sql .= " ORDER BY e.event_date ASC, e.event_time ASC";

	$stmt = $conn->prepare($sql);
	if ($search !== '') {
		$stmt->bind_param('iiss', $studentId, $studentId, $searchParam, $searchParam);
	} else {
		$stmt->bind_param('ii', $studentId, $studentId);
	}
	$stmt->execute();
	$res = $stmt->get_result();
	while ($row = $res->fetch_assoc()) {
		$registeredEvents[] = $row;
	}
	$stmt->close();

	// Joined Clubs
	$joinedClubs = [];
	$sqlClubs = "SELECT c.club_id, c.club_name, c.description, cm.created_at as joined_at, u.full_name as organizer_name
				 FROM clubs c
				 JOIN club_memberships cm ON c.club_id = cm.club_id
				 JOIN users u ON c.organizer_id = u.user_id
				 WHERE cm.student_id = ? AND cm.status = 'approved'";
	
	if ($search !== '') {
		$sqlClubs .= " AND (c.club_name LIKE ? OR u.full_name LIKE ?)";
	}
	$sqlClubs .= " ORDER BY cm.created_at DESC";
	
	$stmt = $conn->prepare($sqlClubs);
	if ($search !== '') {
		$stmt->bind_param('iss', $studentId, $searchParam, $searchParam);
	} else {
		$stmt->bind_param('i', $studentId);
	}
	
	$stmt->execute();
	$res = $stmt->get_result();
	while ($row = $res->fetch_assoc()) {
		$joinedClubs[] = $row;
	}
	$stmt->close();

	apiJson([
		'ok' => true,
		'upcomingCount' => count($upcomingEvents),
		'registeredCount' => count($registeredEvents),
		'joinedClubsCount' => count($joinedClubs),
		'upcomingEvents' => $upcomingEvents,
		'registeredEvents' => $registeredEvents,
		'joinedClubs' => $joinedClubs,
	]);
} finally {
	$conn->close();
}

?>
