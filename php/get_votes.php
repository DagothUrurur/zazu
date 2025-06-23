<?php
require_once __DIR__ . '/db.php';

session_start();
header('Content-Type: application/json');

if (!isset($_GET['artwork_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Не указана работа']);
    exit;
}

$artwork_id = intval($_GET['artwork_id']);
$user_id = $_SESSION['user_id'] ?? null;

try {
    // Получаем данные работы (включая votes_count)
    $artwork = $conn->query("
        SELECT a.votes_count 
        FROM artworks a 
        WHERE a.id = $artwork_id
    ")->fetch_assoc();
    
    if (!$artwork) {
        throw new Exception("Работа не найдена");
    }
    
    // Проверяем, голосовал ли текущий пользователь
    $hasVoted = false;
    if ($user_id) {
        $hasVoted = $conn->query("SELECT 1 FROM votes WHERE artwork_id = $artwork_id AND user_id = $user_id")->num_rows > 0;
    }
    
echo json_encode([
    'status' => 'success',
    'count' => $count,
    'hasVoted' => $hasVoted
]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}