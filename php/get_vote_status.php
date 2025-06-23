<?php
require_once __DIR__ . '/db.php';

session_start();
header('Content-Type: application/json');

if (!isset($_GET['artwork_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Artwork ID not provided']);
    exit;
}

$artwork_id = intval($_GET['artwork_id']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

try {
    // Получаем общее количество голосов
    $stmt = $conn->prepare("SELECT COUNT(*) as vote_count FROM votes WHERE artwork_id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $count = $result['vote_count'];

    // Проверяем, голосовал ли пользователь
    $hasVoted = false;
    if ($user_id) {
        $stmt = $conn->prepare("SELECT 1 FROM votes WHERE artwork_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $artwork_id, $user_id);
        $stmt->execute();
        $hasVoted = $stmt->get_result()->num_rows > 0;
    }

    echo json_encode([
        'status' => 'success',
        'count' => $count,
        'hasVoted' => $hasVoted
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}