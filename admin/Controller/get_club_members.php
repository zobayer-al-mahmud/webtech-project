<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';

apiRequireRole('organizer');
$userId = (int)$_SESSION['user_id'];

$conn = apiDbOrFail();

try {
    // Get organizer's club(s) (assuming 1 for now but loop works)
    $clubSql = "SELECT club_id, club_name FROM clubs WHERE organizer_id = ?";
    $stmt = $conn->prepare($clubSql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $clubsRes = $stmt->get_result();
    $clubsData = [];
    
    while ($club = $clubsRes->fetch_assoc()) {
        $clubId = $club['club_id'];
        
        // Get members
        $memSql = "SELECT cm.membership_id, cm.student_id, cm.status, cm.request_reason, cm.created_at,
                          u.full_name, u.username, u.email
                   FROM club_memberships cm
                   JOIN users u ON cm.student_id = u.user_id
                   WHERE cm.club_id = ?
                   ORDER BY FIELD(cm.status, 'pending', 'approved', 'rejected'), cm.created_at DESC";
                   
        $mStmt = $conn->prepare($memSql);
        $mStmt->bind_param('i', $clubId);
        $mStmt->execute();
        $mRes = $mStmt->get_result();
        
        $members = [];
        while ($m = $mRes->fetch_assoc()) {
            $members[] = $m;
        }
        $mStmt->close();
        
        $clubsData[] = [
            'club_info' => $club,
            'members' => $members
        ];
    }
    
    apiJson(['ok' => true, 'data' => $clubsData]);

} catch (Exception $e) {
    apiJson(['ok' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
