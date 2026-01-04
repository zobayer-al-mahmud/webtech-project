<?php
require_once __DIR__ . '/_api_common.php';

apiRequireRole('admin');

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

	$rows = [];

	if ($hasSnapshot) {
		$sql = $hasStatus
			? "SELECT r.registration_id, r.created_at, r.status,
			          e.event_id, e.title AS event_title,
			          o.user_id AS organizer_id, o.full_name AS organizer_name, o.username AS organizer_username, o.email AS organizer_email, o.phone AS organizer_phone,
			          r.student_id,
			          r.student_full_name AS student_name,
			          r.student_username AS student_username,
			          r.student_email AS student_email,
			          r.student_phone AS student_phone
			   FROM event_registrations r
			   JOIN events e ON e.event_id = r.event_id
			   JOIN users o ON o.user_id = e.organizer_id
			   WHERE e.status = 'approved' AND e.event_date >= CURDATE()
			   ORDER BY r.created_at DESC"
			: "SELECT r.registration_id, r.created_at, 'registered' AS status,
			          e.event_id, e.title AS event_title,
			          o.user_id AS organizer_id, o.full_name AS organizer_name, o.username AS organizer_username, o.email AS organizer_email, o.phone AS organizer_phone,
			          r.student_id,
			          r.student_full_name AS student_name,
			          r.student_username AS student_username,
			          r.student_email AS student_email,
			          r.student_phone AS student_phone
			   FROM event_registrations r
			   JOIN events e ON e.event_id = r.event_id
			   JOIN users o ON o.user_id = e.organizer_id
			   WHERE e.status = 'approved' AND e.event_date >= CURDATE()
			   ORDER BY r.created_at DESC";
		$res = $conn->query($sql);
		while ($res && ($row = $res->fetch_assoc())) {
			$rows[] = $row;
		}
		apiJson(['ok' => true, 'participants' => $rows]);
	}

	// Fallback: no snapshot columns, join users for student info
	$sql = $hasStatus
		? "SELECT r.registration_id, r.created_at, r.status,
		          e.event_id, e.title AS event_title,
		          o.user_id AS organizer_id, o.full_name AS organizer_name, o.username AS organizer_username, o.email AS organizer_email, o.phone AS organizer_phone,
		          s.user_id AS student_id, s.full_name AS student_name, s.username AS student_username, s.email AS student_email, s.phone AS student_phone
		   FROM event_registrations r
		   JOIN events e ON e.event_id = r.event_id
		   JOIN users o ON o.user_id = e.organizer_id
		   JOIN users s ON s.user_id = r.student_id
		   WHERE e.status = 'approved' AND e.event_date >= CURDATE()
		   ORDER BY r.created_at DESC"
		: "SELECT r.registration_id, r.created_at, 'registered' AS status,
		          e.event_id, e.title AS event_title,
		          o.user_id AS organizer_id, o.full_name AS organizer_name, o.username AS organizer_username, o.email AS organizer_email, o.phone AS organizer_phone,
		          s.user_id AS student_id, s.full_name AS student_name, s.username AS student_username, s.email AS student_email, s.phone AS student_phone
		   FROM event_registrations r
		   JOIN events e ON e.event_id = r.event_id
		   JOIN users o ON o.user_id = e.organizer_id
		   JOIN users s ON s.user_id = r.student_id
		   WHERE e.status = 'approved' AND e.event_date >= CURDATE()
		   ORDER BY r.created_at DESC";

	$res = $conn->query($sql);
	while ($res && ($row = $res->fetch_assoc())) {
		$rows[] = $row;
	}

	apiJson(['ok' => true, 'participants' => $rows]);
} finally {
	$conn->close();
}

?>
