<?php
require_once __DIR__ . '/_api_common.php';

apiRequireRole('admin');

$conn = apiDbOrFail();

try {
	$totalUsers = 0;
	$totalEvents = 0;
	$pendingEventsCount = 0;
	$pendingEvents = [];

	$res = $conn->query('SELECT COUNT(*) AS c FROM users');
	if ($res) {
		$row = $res->fetch_assoc();
		$totalUsers = (int)($row['c'] ?? 0);
	}

	$res = $conn->query('SELECT COUNT(*) AS c FROM events');
	if ($res) {
		$row = $res->fetch_assoc();
		$totalEvents = (int)($row['c'] ?? 0);
	}

	$res = $conn->query("SELECT COUNT(*) AS c FROM events WHERE status = 'pending'");
	if ($res) {
		$row = $res->fetch_assoc();
		$pendingEventsCount = (int)($row['c'] ?? 0);
	}

	$stmt = $conn->prepare(
		"SELECT e.event_id, e.title, e.event_date, e.event_time, e.location, e.capacity,
		        u.username AS organizer_username, u.full_name AS organizer_name,
		        u.email AS organizer_email, u.phone AS organizer_phone
		 FROM events e
		 JOIN users u ON u.user_id = e.organizer_id
		 WHERE e.status = 'pending'
		 ORDER BY e.created_at DESC"
	);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$pendingEvents[] = $row;
	}
	$stmt->close();

	apiJson([
		'ok' => true,
		'totalUsers' => $totalUsers,
		'totalEvents' => $totalEvents,
		'pendingApprovals' => $pendingEventsCount,
		'pendingEvents' => $pendingEvents,
	]);
} finally {
	$conn->close();
}

?>
