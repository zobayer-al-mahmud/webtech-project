<?php
require_once __DIR__ . '/_api_common.php';

apiRequireRole('admin');
apiRequirePost();

$eventId = (int)($_POST['event_id'] ?? 0);
if ($eventId <= 0) {
	apiJson(['ok' => false, 'error' => 'Invalid event_id'], 400);
}

$conn = apiDbOrFail();
try {
	$stmt = $conn->prepare("UPDATE events SET status = 'approved' WHERE event_id = ? AND status = 'pending'");
	$stmt->bind_param('i', $eventId);
	$stmt->execute();
	$updated = $stmt->affected_rows > 0;
	$stmt->close();

	apiJson(['ok' => true, 'updated' => $updated]);
} finally {
	$conn->close();
}

?>
