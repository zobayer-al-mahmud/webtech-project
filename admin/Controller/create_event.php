<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';

apiRequireRole('organizer');
apiRequirePost();

$organizerId = (int)($_SESSION['user_id'] ?? 0);

$title = trim((string)($_POST['title'] ?? ''));
$eventDate = trim((string)($_POST['event_date'] ?? ''));
$eventTime = trim((string)($_POST['event_time'] ?? ''));
$location = trim((string)($_POST['location'] ?? ''));
$capacity = (int)($_POST['capacity'] ?? 0);

if ($title === '' || $eventDate === '' || $eventTime === '' || $location === '' || $capacity <= 0) {
	apiJson(['ok' => false, 'error' => 'All fields are required and capacity must be > 0'], 400);
}

$conn = apiDbOrFail();
try {
	$stmt = $conn->prepare(
		"INSERT INTO events (organizer_id, title, event_date, event_time, location, capacity, status)
		 VALUES (?, ?, ?, ?, ?, ?, 'pending')"
	);
	$stmt->bind_param('issssi', $organizerId, $title, $eventDate, $eventTime, $location, $capacity);
	$ok = $stmt->execute();
	$eventId = (int)$stmt->insert_id;
	$stmt->close();

	apiJson(['ok' => $ok, 'event_id' => $eventId, 'status' => 'pending']);
} finally {
	$conn->close();
}

?>
