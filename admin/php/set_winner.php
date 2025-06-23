<?php
require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/contest.php';

session_start();
header('Content-Type: application/json');

// Проверка прав администратора
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Доступ запрещён']);
    exit;
}

// Получаем данные из POST
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$contestId = intval($input['contest_id'] ?? 0);
$artworkId = intval($input['artwork_id'] ?? 0);

// Валидация входных данных
if ($contestId <= 0 || $artworkId <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Неверные параметры запроса']);
    exit;
}

try {
    $conn->begin_transaction();

    // 1. Проверяем что конкурс завершен
    $contest = $conn->query("SELECT * FROM contests WHERE id = $contestId AND end_date < NOW()")->fetch_assoc();
    if (!$contest) {
        throw new Exception("Конкурс не найден или еще не завершен");
    }

    // 2. Проверяем что победитель еще не выбран
    if ($contest['winner_artwork_id']) {
        throw new Exception("Победитель уже выбран для этого конкурса");
    }

    // 3. Проверяем что работа принадлежит конкурсу
    $stmt = $conn->prepare("SELECT id FROM artworks WHERE id = ? AND contest_id = ?");
    $stmt->bind_param("ii", $artworkId, $contestId);
    $stmt->execute();
    
    if (!$stmt->get_result()->num_rows) {
        throw new Exception("Работа не принадлежит указанному конкурсу");
    }
    
    // 4. Назначаем победителя
    $stmt = $conn->prepare("UPDATE contests SET winner_artwork_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $artworkId, $contestId);
    
    if (!$stmt->execute()) {
        throw new Exception("Ошибка при обновлении данных конкурса");
    }
    
    $conn->commit();
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}