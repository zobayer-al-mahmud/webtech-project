<?php
require_once __DIR__ . '/../../Auth/Controller/common.php';

apiRequireRole('organizer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$eventId = (int)($input['event_id'] ?? 0);

if ($eventId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Invalid event ID']);
    exit;
}

$organizerId = (int)$_SESSION['user_id'];
$conn = apiDbOrFail();

try {
    $conn->begin_transaction();

    // Verify ownership and valid status
    $stmt = $conn->prepare("SELECT status FROM events WHERE event_id = ? AND organizer_id = ?");
    $stmt->bind_param('ii', $eventId, $organizerId);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        throw new Exception('Event not found or access denied');
    }

    $event = $res->fetch_assoc();
    if ($event['status'] === 'cancelled') {
        throw new Exception('Event is already cancelled');
    }

    // Update event status
    $updateEvent = $conn->prepare("UPDATE events SET status = 'cancelled' WHERE event_id = ?");
    $updateEvent->bind_param('i', $eventId);
    if (!$updateEvent->execute()) {
        throw new Exception('Failed to cancel event');
    }

    // Update registrations status
    $updateReg = $conn->prepare("UPDATE event_registrations SET status = 'cancelled' WHERE event_id = ?");
    $updateReg->bind_param('i', $eventId);
    if (!$updateReg->execute()) {
        throw new Exception('Failed to cancel registrations');
    }

    $conn->commit();
    apiJson(['ok' => true]);

} catch (Exception $e) {
    if ($conn->in_transaction) {
        $conn->rollback();
    }
    apiJson(['ok' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
