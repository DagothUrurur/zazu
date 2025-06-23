<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

$artwork_id = (int)($_GET['id'] ?? 0);

$artwork = $conn->query("
    SELECT a.*, u.login as author 
    FROM artworks a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.id = $artwork_id
")->fetch_assoc();

if(!$artwork) {
    http_response_code(404);
    exit;
}

$like_count = $conn->query("SELECT COUNT(*) as cnt FROM likes WHERE artwork_id = $artwork_id")->fetch_assoc()['cnt'];
$is_liked = isset($_SESSION['user_id']) ? 
    $conn->query("SELECT id FROM likes WHERE artwork_id = $artwork_id AND user_id = {$_SESSION['user_id']}")->num_rows > 0 : 
    false;
$artwork['user_has_voted'] = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT 1 FROM votes WHERE artwork_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $artwork_id, $_SESSION['user_id']);
    $stmt->execute();
    $artwork['user_has_voted'] = $stmt->get_result()->num_rows > 0;
}
echo json_encode([
    'id' => $artwork['id'],
    'title' => $artwork['title'],
    'author' => $artwork['author'],
    'image_path' => $artwork['image_path'],
    'views' => $artwork['views'],
    'like_count' => $like_count,
    'is_liked' => $is_liked
]);
?>