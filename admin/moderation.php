<?php
session_start();
require_once('../php/db.php');

// Проверка прав администратора
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Обработка действий модерации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artwork_id = intval($_POST['artwork_id']);
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE artworks SET status = 'approved', 
                               moderated_by = ?, moderated_at = NOW() 
                               WHERE id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE artworks SET status = 'rejected', 
                               moderated_by = ?, moderated_at = NOW() 
                               WHERE id = ?");
    }
    
    $stmt->bind_param("ii", $_SESSION['user_id'], $artwork_id);
    $stmt->execute();
    
    $_SESSION['message'] = 'Решение сохранено';
    exit;
}

// Получение работ для модерации
$query = "SELECT a.*, u.login as author 
          FROM artworks a 
          JOIN users u ON a.user_id = u.id 
          WHERE a.status = 'pending' 
          ORDER BY a.created_at DESC";
$artworks = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Темные Архивы | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>
<body class="admin-body">
<!-- Туманный фон -->
    <div class="admin-fog-container">
        <div class="admin-fog-layer admin-fog-layer-1"></div>
        <div class="admin-fog-layer admin-fog-layer-2"></div>
    </div>

    <!-- Силуэты -->
    <div class="admin-silhouettes">
        <div class="admin-silhouette admin-silhouette-1"></div>
        <div class="admin-silhouette admin-silhouette-2"></div>
    </div>->

    <div class="admin-content">
        <h1>Модерация работ</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="moderation-grid">
            <?php while ($artwork = $artworks->fetch_assoc()): ?>
                <div class="moderation-item">
    <img src="/uploads/artworks/<?= $artwork['image_path'] ?>" alt="<?= htmlspecialchars($artwork['title']) ?>">
    <h3><?= htmlspecialchars($artwork['title']) ?></h3>
    <p>Автор: <?= htmlspecialchars($artwork['author']) ?></p>
    <p><?= htmlspecialchars($artwork['description']) ?></p>
    
    <!-- Добавляем блок с информацией о типе загрузки -->
    <div class="moderation-type">
        <?php if ($artwork['is_contest']): ?>
            <span class="badge bg-warning text-dark"><i class="fas fa-trophy"></i> На конкурс</span>
        <?php else: ?>
            <span class="badge bg-info"><i class="fas fa-images"></i> В галерею</span>
        <?php endif; ?>
    </div>
    
    <form method="post">
        <input type="hidden" name="artwork_id" value="<?= $artwork['id'] ?>">
        <button type="submit" name="action" value="approve" class="btn btn-success">Одобрить</button>
        <button type="submit" name="action" value="reject" class="btn btn-danger">Отклонить</button>
    </form>
</div>
            <?php endwhile; ?>
            
            <?php if ($artworks->num_rows === 0): ?>
                <p>Нет работ для модерации</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>