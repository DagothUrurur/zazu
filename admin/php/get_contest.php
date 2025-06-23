<?php
require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/contest.php';

session_start();
header('Content-Type: application/json');

// Включим полное логгирование ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/get_contest_errors.log');

// Логируем начало запроса
file_put_contents(__DIR__ . '/get_contest_debug.log', "\n" . date('Y-m-d H:i:s') . " - New request\n", FILE_APPEND);

try {
    // Проверка сессии и прав
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Сессия не инициализирована");
    }
    
    if ($_SESSION['user_role'] !== 'admin') {
        throw new Exception("Недостаточно прав для выполнения операции");
    }

    // Получаем ID конкурса
    $contestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    file_put_contents(__DIR__ . '/get_contest_debug.log', "Contest ID: $contestId\n", FILE_APPEND);
    
    if ($contestId <= 0) {
        throw new Exception("Неверный ID конкурса");
    }

    // Создаем экземпляр Contest
    $contestManager = new Contest($conn);
    file_put_contents(__DIR__ . '/get_contest_debug.log', "Contest manager created\n", FILE_APPEND);

    // Получаем данные конкурса
    $contest = $contestManager->getContestById($contestId);
    file_put_contents(__DIR__ . '/get_contest_debug.log', "Contest data: " . print_r($contest, true) . "\n", FILE_APPEND);
    
    if (!$contest) {
        throw new Exception("Конкурс с ID $contestId не найден в базе данных");
    }

    // Формируем успешный ответ
    $response = [
        'status' => 'success',
        'data' => [
            'id' => $contest['id'],
            'title' => $contest['title'],
            'description' => $contest['description'],
            'start_date' => $contest['start_date'],
            'end_date' => $contest['end_date']
        ]
    ];
    
    file_put_contents(__DIR__ . '/get_contest_debug.log', "Response: " . json_encode($response) . "\n", FILE_APPEND);
    echo json_encode($response);

} catch (Exception $e) {
    $errorResponse = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ];
    
    file_put_contents(__DIR__ . '/get_contest_debug.log', "ERROR: " . json_encode($errorResponse) . "\n", FILE_APPEND);
    echo json_encode($errorResponse);
}