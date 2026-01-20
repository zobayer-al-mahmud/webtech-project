<?php
require_once __DIR__ . '/../Model/DatabaseConnection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function apiJson($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

function apiRequireRole($role): void {
    $currentRole = (string)($_SESSION['user_role'] ?? '');
    $roles = is_array($role) ? $role : [$role];
    
    if (!empty($role) && !in_array($currentRole, $roles, true)) {
        apiJson(['ok' => false, 'error' => 'Forbidden'], 403);
    }
    if (empty($_SESSION['user_id'])) {
        apiJson(['ok' => false, 'error' => 'Unauthorized'], 401);
    }
}

function apiRequirePost(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        apiJson(['ok' => false, 'error' => 'Method not allowed'], 405);
    }
}

function apiDbOrFail(): mysqli {
    $db = new DatabaseConnection();
    $conn = $db->openConnection();
    if (!$conn) {
        apiJson(['ok' => false, 'error' => 'Database connection failed'], 500);
    }
    return $conn;
}

function redirect(string $page): void {
    header('Location: ' . $page);
    exit();
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['username']);
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'phone' => $_SESSION['phone'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
        'filepath' => $_SESSION['filepath'] ?? '',
    ];
}

function requireRoleOrRedirect(string $requiredRole): array {
    if (!isLoggedIn()) {
        redirect('../../Auth/View/login.php');
    }
    $user = getCurrentUser();
    if ($user['role'] !== $requiredRole) {
        redirect('../../Auth/View/index.php');
    }
    return $user;
}

function redirectToRoleDashboard(string $role): void {
    if ($role === 'admin') {
        redirect('../../admin/View/admin_dashboard.php');
    } elseif ($role === 'organizer') {
        redirect('../../organizer/View/club_organizer_dashboard.php');
    } else {
        redirect('../../student/View/student_dashboard.php');
    }
}
?>
