<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';

apiRequireRole('student');
$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    apiJson(['ok' => false, 'error' => 'Method not allowed']);
}

$input = json_decode(file_get_contents('php://input'), true);
$clubId = (int)($input['club_id'] ?? 0);
$reason = trim($input['reason'] ?? '');

if ($clubId <= 0) {
    apiJson(['ok' => false, 'error' => 'Invalid club ID']);
}

$conn = apiDbOrFail();

try {
    // Check if already member
    $check = $conn->prepare("SELECT membership_id FROM club_memberships WHERE club_id = ? AND student_id = ?");
    $check->bind_param('ii', $clubId, $userId);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        apiJson(['ok' => false, 'error' => 'Already requested or joined']);
    }
    
    $stmt = $conn->prepare("INSERT INTO club_memberships (club_id, student_id, status, request_reason) VALUES (?, ?, 'pending', ?)");
    $stmt->bind_param('iis', $clubId, $userId, $reason);
    
    if ($stmt->execute()) {
        apiJson(['ok' => true]);
    } else {
        throw new Exception('Failed to create request');
    }

} catch (Exception $e) {
    apiJson(['ok' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
