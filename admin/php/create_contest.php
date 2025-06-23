<?php

require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/contest.php';

session_start();

if ($_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен']);
    exit;
}

$contestManager = new Contest($conn);

$title = $_POST['title'];
$description = $_POST['description'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$admin_id = $_SESSION['user_id'];

if ($contestManager->createContest($title, $description, $start_date, $end_date, $admin_id)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при создании конкурса']);
}
?>