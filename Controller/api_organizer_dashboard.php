<?php
require_once __DIR__ . '/_api_common.php';

apiRequireRole('organizer');

$organizerId = (int)($_SESSION['user_id'] ?? 0);

$conn = apiDbOrFail();

try {
	$totalCreated = 0;
	$upcomingApprovedCount = 0;
	$upcomingEvents = [];
	$pendingEvents = [];

	$stmt = $conn->prepare('SELECT COUNT(*) AS c FROM events WHERE organizer_id = ?');
	$stmt->bind_param('i', $organizerId);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	$totalCreated = (int)($row['c'] ?? 0);
	$stmt->close();

	$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM events WHERE organizer_id = ? AND status = 'approved' AND event_date >= CURDATE()");
	$stmt->bind_param('i', $organizerId);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	$upcomingApprovedCount = (int)($row['c'] ?? 0);
	$stmt->close();

	$stmt = $conn->prepare(
		"SELECT event_id, title, event_date, event_time, location, capacity
		 FROM events
		 WHERE organizer_id = ? AND status = 'approved' AND event_date >= CURDATE()
		 ORDER BY event_date ASC, event_time ASC"
	);
	$stmt->bind_param('i', $organizerId);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$upcomingEvents[] = $row;
	}
	$stmt->close();

	$stmt = $conn->prepare(
		"SELECT event_id, title, event_date, event_time, location, capacity
		 FROM events
		 WHERE organizer_id = ? AND status = 'pending'
		 ORDER BY created_at DESC"
	);
	$stmt->bind_param('i', $organizerId);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$pendingEvents[] = $row;
	}
	$stmt->close();

	apiJson([
		'ok' => true,
		'totalCreated' => $totalCreated,
		'upcomingApproved' => $upcomingApprovedCount,
		'upcomingEvents' => $upcomingEvents,
		'pendingEvents' => $pendingEvents,
	]);
} finally {
	$conn->close();
}

?>
