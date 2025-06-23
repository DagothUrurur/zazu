<?php
require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/contest.php';

session_start();

if ($_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен']);
    exit;
}

$contestManager = new Contest($conn);
$contest_id = $_POST['contest_id'];
$artwork_id = $_POST['artwork_id'];

if ($contestManager->setWinner($contest_id, $artwork_id)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при выборе победителя']);
}
?>