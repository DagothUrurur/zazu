<?php
require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/contest.php';

session_start();
header('Content-Type: application/json');

if ($_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Доступ запрещён']);
    exit;
}

$contestId = intval($_GET['contest_id'] ?? 0);

try {
    $contestManager = new Contest($conn);
    $artworks = $contestManager->getContestArtworksWithVotes($contestId);
    
    if (empty($artworks)) {
        throw new Exception("Нет работ для этого конкурса");
    }
    
    // Находим максимальное количество голосов
    $maxVotes = max(array_column($artworks, 'votes_count'));
    
    // Фильтруем работы с максимальным количеством голосов
    $topArtworks = array_filter($artworks, function($artwork) use ($maxVotes) {
        return $artwork['votes_count'] == $maxVotes;
    });
    
    // Если только одна работа с максимальным количеством голосов - сразу возвращаем её
    if (count($topArtworks) === 1) {
        $response = [
            'status' => 'success',
            'data' => [
                'auto_winner' => true,
                'artworks' => $topArtworks
            ]
        ];
    } else {
        // Если несколько работ с одинаковым количеством голосов - показываем все для ручного выбора
        $response = [
            'status' => 'success',
            'data' => [
                'auto_winner' => false,
                'artworks' => $topArtworks
            ]
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}