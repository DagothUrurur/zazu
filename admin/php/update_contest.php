<?php
require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/contest.php';

session_start();
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        throw new Exception("Доступ запрещён");
    }

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $contestId = intval($input['id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $startDate = $input['start_date'] ?? '';
    $endDate = $input['end_date'] ?? '';
    if ($contestId <= 0) {
        throw new Exception("Неверный ID конкурса");
    }
    
    if (empty($title) || empty($startDate) || empty($endDate)) {
        throw new Exception("Все обязательные поля должны быть заполнены");
    }

    $startDateTime = date('Y-m-d H:i:s', strtotime($startDate));
    $endDateTime = date('Y-m-d H:i:s', strtotime($endDate));
    
    if (!$startDateTime || !$endDateTime) {
        throw new Exception("Неверный формат даты");
    }

    if (strtotime($endDateTime) <= strtotime($startDateTime)) {
        throw new Exception("Дата окончания должна быть позже даты начала");
    }

    $currentContest = $conn->query("SELECT * FROM contests WHERE id = $contestId")->fetch_assoc();
    if (!$currentContest) {
        throw new Exception("Конкурс не найден");
    }

    $stmt = $conn->prepare("UPDATE contests SET 
        title = ?, 
        description = ?, 
        start_date = ?, 
        end_date = ? 
        WHERE id = ?");
    
    $stmt->bind_param("ssssi", 
        $title, 
        $description, 
        $startDateTime, 
        $endDateTime, 
        $contestId);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception("Ошибка при обновлении конкурса");
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}