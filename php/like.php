<?php
session_start();
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'auth']);
    exit;
}

if(!isset($_POST['artwork_id']) || !is_numeric($_POST['artwork_id'])) {
    echo json_encode(['error' => 'invalid_id', 'debug' => $_POST]);
    exit;
}

$artwork_id = (int)$_POST['artwork_id'];
$user_id = (int)$_SESSION['user_id'];

// Проверяем существование artwork
$artworkExists = $conn->query("SELECT id FROM artworks WHERE id = $artwork_id")->num_rows > 0;
if(!$artworkExists) {
    echo json_encode(['error' => 'artwork_not_found']);
    exit;
}

// Проверяем, лайкнул ли уже пользователь
$is_liked = $conn->query("SELECT id FROM likes WHERE artwork_id = $artwork_id AND user_id = $user_id")->num_rows > 0;

// Начинаем транзакцию
$conn->begin_transaction();

try {
    if($is_liked) {
        $conn->query("DELETE FROM likes WHERE artwork_id = $artwork_id AND user_id = $user_id");
    } else {
        $conn->query("INSERT INTO likes (artwork_id, user_id) VALUES ($artwork_id, $user_id)");
    }

    // Получаем новое количество лайков (из таблицы likes)
    $count = $conn->query("SELECT COUNT(*) FROM likes WHERE artwork_id = $artwork_id")->fetch_row()[0];
    
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'count' => $count,
        'action' => $is_liked ? 'removed' : 'added'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => 'database_error', 'message' => $e->getMessage()]);
}
?>