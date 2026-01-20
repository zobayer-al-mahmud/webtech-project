<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';

apiRequireRole(['student', 'organizer', 'admin']);
$userId = (int)$_SESSION['user_id'];

$conn = apiDbOrFail();

try {
    // Get all clubs
    // Also check if current user has requested or joined
    $sql = "SELECT c.club_id, c.club_name, c.description, c.created_at,
            (SELECT status FROM club_memberships cm WHERE cm.club_id = c.club_id AND cm.student_id = ?) as membership_status
            FROM clubs c
            ORDER BY c.club_name ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $clubs = [];
    while ($row = $result->fetch_assoc()) {
        $clubs[] = $row;
    }

    apiJson(['ok' => true, 'clubs' => $clubs]);

} catch (Exception $e) {
    apiJson(['ok' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
