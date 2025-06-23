<?php
require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/contest.php';

session_start();
header('Content-Type: application/json');

if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен']);
    exit;
}

try {
    $contestManager = new Contest($conn);
    $contest_id = intval($_POST['id'] ?? 0);

    if ($contest_id <= 0) {
        throw new Exception("Неверный ID конкурса");
    }

    $conn->begin_transaction();


    $stmt = $conn->prepare("UPDATE artworks SET contest_id = NULL WHERE contest_id = ?");
    $stmt->bind_param("i", $contest_id);
    $stmt->execute();


    $stmt = $conn->prepare("DELETE FROM contests WHERE id = ?");
    $stmt->bind_param("i", $contest_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Ошибка при удалении конкурса");
    }

    $conn->commit();
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>