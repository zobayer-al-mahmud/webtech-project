<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';

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

	$search = trim((string)($_GET['search'] ?? ''));
	$searchParam = '%' . $search . '%';

	// Upcoming
	$sqlUpcoming = "SELECT event_id, title, event_date, event_time, location, capacity
		 FROM events
		 WHERE organizer_id = ? AND status = 'approved' AND event_date >= CURDATE()";
	if ($search !== '') {
		$sqlUpcoming .= " AND title LIKE ?";
	}
	$sqlUpcoming .= " ORDER BY event_date ASC, event_time ASC";

	$stmt = $conn->prepare($sqlUpcoming);
	if ($search !== '') {
		$stmt->bind_param('is', $organizerId, $searchParam);
	} else {
		$stmt->bind_param('i', $organizerId);
	}
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$upcomingEvents[] = $row;
	}
	$stmt->close();

	// Pending
	$sqlPending = "SELECT event_id, title, event_date, event_time, location, capacity
		 FROM events
		 WHERE organizer_id = ? AND status = 'pending'";
	if ($search !== '') {
		$sqlPending .= " AND title LIKE ?";
	}
	$sqlPending .= " ORDER BY created_at DESC";

	$stmt = $conn->prepare($sqlPending);
	if ($search !== '') {
		$stmt->bind_param('is', $organizerId, $searchParam);
	} else {
		$stmt->bind_param('i', $organizerId);
	}
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$pendingEvents[] = $row;
	}
	$stmt->close();

	// Club Members Stats
	$totalClubMembers = 0;
	$stmt = $conn->prepare("
		SELECT COUNT(*) AS c 
		FROM club_memberships cm 
		JOIN clubs c ON cm.club_id = c.club_id 
		WHERE c.organizer_id = ? AND cm.status = 'approved'
	");
	$stmt->bind_param('i', $organizerId);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	$totalClubMembers = (int)($row['c'] ?? 0);
	$stmt->close();

    // Pending Club Requests
    $pendingClubRequests = 0;
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS c 
        FROM club_memberships cm 
        JOIN clubs c ON cm.club_id = c.club_id 
        WHERE c.organizer_id = ? AND cm.status = 'pending'
    ");
    $stmt->bind_param('i', $organizerId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $pendingClubRequests = (int)($row['c'] ?? 0);
    $stmt->close();

	// Club Members List
	$clubMembers = [];
	$sqlMembers = "SELECT u.full_name, u.email, u.phone, c.club_name, cm.created_at as joined_at
		 FROM club_memberships cm
		 JOIN clubs c ON cm.club_id = c.club_id
		 JOIN users u ON cm.student_id = u.user_id
		 WHERE c.organizer_id = ? AND cm.status = 'approved'";
	
	if ($search !== '') {
		$sqlMembers .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR c.club_name LIKE ?)";
	}
	$sqlMembers .= " ORDER BY cm.created_at DESC";
	
	$stmt = $conn->prepare($sqlMembers);
	if ($search !== '') {
		$stmt->bind_param('isss', $organizerId, $searchParam, $searchParam, $searchParam);
	} else {
		$stmt->bind_param('i', $organizerId);
	}
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$clubMembers[] = $row;
	}
	$stmt->close();


	apiJson([
		'ok' => true,
		'totalCreated' => $totalCreated,
		'upcomingApproved' => $upcomingApprovedCount,
		'upcomingEvents' => $upcomingEvents,
		'pendingEvents' => $pendingEvents,
		'totalClubMembers' => $totalClubMembers,
        'pendingClubRequests' => $pendingClubRequests,
		'clubMembers' => $clubMembers
	]);
} finally {
	$conn->close();
}

?>
