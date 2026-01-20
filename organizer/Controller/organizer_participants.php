<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';

apiRequireRole('organizer');

$organizerId = (int)($_SESSION['user_id'] ?? 0);

$conn = apiDbOrFail();
try {
	$rows = [];

	$search = trim((string)($_GET['search'] ?? ''));
	$searchParam = '%' . $search . '%';

	$sql = "SELECT r.registration_id, r.created_at, r.status,
		          e.event_id, e.title AS event_title,
		          s.user_id AS student_id, s.full_name, s.username, s.email, s.phone
		   FROM event_registrations r
		   JOIN events e ON e.event_id = r.event_id
		   JOIN users s ON s.user_id = r.student_id
		   WHERE e.organizer_id = ?";

	if ($search !== '') {
		$sql .= " AND (e.title LIKE ? OR s.full_name LIKE ? OR s.username LIKE ?)";
	}
	$sql .= " ORDER BY r.created_at DESC";

	$stmt = $conn->prepare($sql);
	if ($search !== '') {
		$stmt->bind_param('isss', $organizerId, $searchParam, $searchParam, $searchParam);
	} else {
		$stmt->bind_param('i', $organizerId);
	}
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
