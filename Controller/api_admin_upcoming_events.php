<?php
require_once __DIR__ . '/_api_common.php';

apiRequireRole('admin');

$conn = apiDbOrFail();
try {
	$events = [];
	$stmt = $conn->prepare(
		"SELECT e.event_id, e.title, e.event_date, e.event_time, e.location, e.capacity,
		        u.user_id AS organizer_id, u.full_name AS organizer_name, u.username AS organizer_username,
		        u.email AS organizer_email, u.phone AS organizer_phone
		 FROM events e
		 JOIN users u ON u.user_id = e.organizer_id
		 WHERE e.status = 'approved' AND e.event_date >= CURDATE()
		 ORDER BY e.event_date ASC, e.event_time ASC"
	);
	$stmt->execute();
	$res = $stmt->get_result();
	while ($row = $res->fetch_assoc()) {
		$events[] = $row;
	}
	$stmt->close();

	apiJson(['ok' => true, 'upcomingEvents' => $events]);
} finally {
	$conn->close();
}

?>
