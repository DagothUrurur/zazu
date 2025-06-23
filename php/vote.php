<?php
require_once __DIR__ . '/db.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Требуется авторизация']);
    exit;
}

if (!isset($_POST['artwork_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Не указана работа']);
    exit;
}

$artwork_id = intval($_POST['artwork_id']);
$user_id = $_SESSION['user_id'];

$conn->begin_transaction();

try {
    // Проверяем существование работы
    $stmt = $conn->prepare("SELECT id FROM artworks WHERE id = ? AND status = 'approved'");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    
    if (!$stmt->get_result()->num_rows) {
        throw new Exception("Работа не найдена или не одобрена");
    }

    // Проверяем, не голосовал ли уже
    $hasVoted = $conn->query("SELECT id FROM votes WHERE artwork_id = $artwork_id AND user_id = $user_id")->num_rows > 0;
    
    if ($hasVoted) {
        // Удаляем голос
        $conn->query("DELETE FROM votes WHERE artwork_id = $artwork_id AND user_id = $user_id");
        $action = 'removed';
    } else {
        // Добавляем голос
        $conn->query("INSERT INTO votes (artwork_id, user_id) VALUES ($artwork_id, $user_id)");
        $action = 'added';
    }
    
    // Получаем количество голосов из таблицы votes
    $count = $conn->query("SELECT COUNT(*) FROM votes WHERE artwork_id = $artwork_id")->fetch_row()[0];
    
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'action' => $action,
        'count' => $count,
        'hasVoted' => $action === 'added'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>