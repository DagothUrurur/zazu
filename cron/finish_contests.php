<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/contest.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$contestManager = new Contest($conn);

// Находим конкурсы, у которых время истекло, но победитель еще не выбран
$query = "SELECT * FROM contests WHERE end_date <= NOW() AND is_active = 1 AND winner_artwork_id IS NULL";
$result = $conn->query($query);

while ($contest = $result->fetch_assoc()) {
    // Находим работу с максимальным количеством лайков
    $winner_query = "SELECT a.id 
                    FROM artworks a 
                    WHERE a.contest_id = {$contest['id']} AND a.status = 'approved'
                    ORDER BY (SELECT COUNT(*) FROM likes WHERE artwork_id = a.id) DESC 
                    LIMIT 1";
    $winner_result = $conn->query($winner_query);
    
    if ($winner_result->num_rows > 0) {
        $winner = $winner_result->fetch_assoc();
        $contestManager->setWinner($contest['id'], $winner['id']);
    } else {
        // Если нет работ, просто деактивируем конкурс
        $conn->query("UPDATE contests SET is_active = 0 WHERE id = {$contest['id']}");
    }
}

$conn->close();
?>