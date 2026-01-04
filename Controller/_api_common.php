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

function apiRequireRole(string $role): void {
	$currentRole = (string)($_SESSION['user_role'] ?? '');
	if (($role !== '') && $currentRole !== $role) {
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

?>
