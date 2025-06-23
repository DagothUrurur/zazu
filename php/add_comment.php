<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Необходимо авторизоваться']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Неверный метод запроса']);
    exit;
}

$artwork_id = intval($_POST['artwork_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($artwork_id <= 0) {
    echo json_encode(['error' => 'Неверный ID работы']);
    exit;
}

if (empty($content)) {
    echo json_encode(['error' => 'Комментарий не может быть пустым']);
    exit;
}

// Проверяем существование работы
$check = $conn->prepare("SELECT id FROM artworks WHERE id = ?");
$check->bind_param("i", $artwork_id);
$check->execute();
if (!$check->get_result()->num_rows) {
    echo json_encode(['error' => 'Работа не найдена']);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("INSERT INTO comments (artwork_id, user_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $artwork_id, $user_id, $content);

if ($stmt->execute()) {
    // Получаем данные нового комментария
    $comment_id = $stmt->insert_id;
    $comment = $conn->query("
        SELECT c.*, u.login, u.avatar 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.id = $comment_id
    ")->fetch_assoc();
    
    echo json_encode([
        'status' => 'success',
        'comment' => [
            'id' => $comment['id'],
            'content' => htmlspecialchars($comment['content']),
            'created_at' => date('d.m.Y H:i', strtotime($comment['created_at'])),
            'author' => $comment['login'],
            'avatar' => $comment['avatar'] ? '/uploads/avatars/'.$comment['avatar'] : '/img/default-avatar.jpg'
        ]
    ]);
} else {
    echo json_encode(['error' => 'Ошибка при сохранении комментария: '.$conn->error]);
}
?>