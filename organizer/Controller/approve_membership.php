<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';

apiRequireRole('organizer');
$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    apiJson(['ok' => false, 'error' => 'Method not allowed']);
}

$input = json_decode(file_get_contents('php://input'), true);
$membershipId = (int)($input['membership_id'] ?? 0);

if ($membershipId <= 0) {
    apiJson(['ok' => false, 'error' => 'Invalid ID']);
}

$conn = apiDbOrFail();

try {
    // Verify ownership of the club related to this membership
    $checkSql = "SELECT cm.membership_id 
                 FROM club_memberships cm
                 JOIN clubs c ON cm.club_id = c.club_id
                 WHERE cm.membership_id = ? AND c.organizer_id = ?";
                 
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param('ii', $membershipId, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Access denied or invalid ID');
    }
    $stmt->close();
    
    // Approve
    $upSql = "UPDATE club_memberships SET status = 'approved' WHERE membership_id = ?";
    $upStmt = $conn->prepare($upSql);
    $upStmt->bind_param('i', $membershipId);
    if ($upStmt->execute()) {
        apiJson(['ok' => true]);
    } else {
        throw new Exception('Failed to update');
    }

} catch (Exception $e) {
    apiJson(['ok' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
