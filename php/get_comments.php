<?php
require_once 'db.php';

$artwork_id = intval($_GET['artwork_id']);

$comments = $conn->query("
    SELECT c.*, u.login, u.avatar 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.artwork_id = $artwork_id 
    ORDER BY c.created_at DESC
");

if ($comments->num_rows > 0) {
    while ($comment = $comments->fetch_assoc()) {
        echo '
        <div class="comment">
            <div class="comment-author">
                <img src="'.($comment['avatar'] ? '/uploads/avatars/'.$comment['avatar'] : '/img/default-avatar.jpg').'" 
                     alt="'.htmlspecialchars($comment['login']).'" class="comment-avatar">
                <span class="comment-author-name">'.htmlspecialchars($comment['login']).'</span>
                <span class="comment-date">'.date('d.m.Y H:i', strtotime($comment['created_at'])).'</span>
            </div>
            <div class="comment-content">'.htmlspecialchars($comment['content']).'</div>
        </div>';
    }
} else {
    echo '<p class="no-comments">Пока нет комментариев. Будьте первым!</p>';
}
?>