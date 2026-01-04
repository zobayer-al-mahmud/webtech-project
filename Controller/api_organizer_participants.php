<?php
require_once __DIR__ . '/_api_common.php';

apiRequireRole('organizer');

$organizerId = (int)($_SESSION['user_id'] ?? 0);

$conn = apiDbOrFail();
try {
	$hasStatus = false;
	$col = $conn->query("SHOW COLUMNS FROM event_registrations LIKE 'status'");
	if ($col && $col->num_rows > 0) {
		$hasStatus = true;
	}

	$rows = [];

	$sql = $hasStatus
		? "SELECT r.registration_id, r.created_at, r.status,
		          e.event_id, e.title AS event_title,
		          s.user_id AS student_id, s.full_name, s.username, s.email, s.phone
		   FROM event_registrations r
		   JOIN events e ON e.event_id = r.event_id
		   JOIN users s ON s.user_id = r.student_id
		   WHERE e.organizer_id = ?
		   ORDER BY r.created_at DESC"
		: "SELECT r.registration_id, r.created_at, 'registered' AS status,
		          e.event_id, e.title AS event_title,
		          s.user_id AS student_id, s.full_name, s.username, s.email, s.phone
		   FROM event_registrations r
		   JOIN events e ON e.event_id = r.event_id
		   JOIN users s ON s.user_id = r.student_id
		   WHERE e.organizer_id = ?
		   ORDER BY r.created_at DESC";

	$stmt = $conn->prepare($sql);
	$stmt->bind_param('i', $organizerId);
	$stmt->execute();
	$res = $stmt->get_result();
	while ($row = $res->fetch_assoc()) {
		$rows[] = $row;
	}
	$stmt->close();

	apiJson(['ok' => true, 'participants' => $rows]);
} finally {
	$conn->close();
}

?>
