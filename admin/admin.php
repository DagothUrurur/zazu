<?php
session_start();
require_once('../php/db.php');

// Проверка прав администратора
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Получаем статистику для дашборда
$stats = [
    'articles' => $conn->query("SELECT COUNT(*) FROM articles")->fetch_row()[0],
    'pending_artworks' => $conn->query("SELECT COUNT(*) FROM artworks WHERE status = 'pending'")->fetch_row()[0],
    'published_artworks' => $conn->query("SELECT COUNT(*) FROM artworks WHERE status = 'approved'")->fetch_row()[0],
    'users' => $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0]
];

// Определяем активный раздел
$active_page = $_GET['page'] ?? 'dashboard';

// Обработка действий
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'delete':
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $_SESSION['message'] = 'Статья успешно удалена';
                header('Location: admin.php?page=articles');
                exit;
            }
            break;
            
        case 'delete_artwork':
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                
                // Сначала получаем информацию о работе для удаления файла
                $stmt = $conn->prepare("SELECT image_path FROM artworks WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $artwork = $result->fetch_assoc();
                
                if ($artwork) {
                    // Удаляем файл изображения
                    $file_path = '../uploads/artworks/' . $artwork['image_path'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                    
                    // Удаляем запись из БД
                    $stmt = $conn->prepare("DELETE FROM artworks WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    
                    $_SESSION['message'] = 'Работа успешно удалена';
                } else {
                    $_SESSION['message'] = 'Работа не найдена';
                }
                
                header('Location: admin.php?page=gallery');
                exit;
            }
            break;
    }
}

// Получение списка статей (всегда, для первоначального отображения)
$query = "SELECT * FROM articles ORDER BY created_at DESC";
$articles = $conn->query($query);

// Функция для определения заголовка страницы
function getPageTitle($page) {
    $titles = [
        'dashboard' => 'Дашборд',
        'articles' => 'Статьи',
        'gallery' => 'Галерея',
        'moderation' => 'Модерация работ'
    ];
    return $titles[$page] ?? 'Админ-панель';
}
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
    <link rel="stylesheet" href="../style.css">
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
    </div>

    <!-- Главный контейнер -->
    <div class="admin-container">
        <!-- Боковая панель -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <img src="../img/logo-sm.png" alt="Лого" class="admin-logo-img">
                <span class="admin-logo-text">Темные Архивы</span>
            </div>

            <nav class="admin-nav">
                <div class="admin-nav-section">
                    <h4 class="admin-nav-title">Главное</h4>
                    <a href="?page=dashboard" class="admin-nav-link <?= $active_page === 'dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> Дашборд
                    </a>
                </div>

                <div class="admin-nav-section">
                    <h4 class="admin-nav-title">Контент</h4>
                    <a href="?page=articles" class="admin-nav-link <?= $active_page === 'articles' ? 'active' : '' ?>">
                        <i class="fas fa-scroll"></i> Статьи
                    </a>
                    <a href="?page=gallery" class="admin-nav-link <?= $active_page === 'gallery' ? 'active' : '' ?>">
                        <i class="fas fa-images"></i> Галерея
                    </a>
                    <a href="?page=moderation" class="admin-nav-link <?= $active_page === 'moderation' ? 'active' : '' ?>">
                        <i class="fas fa-gavel"></i> Модерация
                        <?php if ($stats['pending_artworks'] > 0): ?>
                            <span class="admin-nav-badge"><?= $stats['pending_artworks'] ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="#" class="admin-nav-link">
                        <i class="fas fa-tags"></i> Категории
                    </a>
                </div>

                <div class="admin-nav-section">
                    <h4 class="admin-nav-title">Коммуникация</h4>
                    <a href="#" class="admin-nav-link">
                        <i class="fas fa-envelope"></i> Сообщения
                        <span class="admin-nav-badge">12</span>
                    </a>
                    <a href="#" class="admin-nav-link">
                        <i class="fas fa-comment-alt"></i> Комментарии
                        <span class="admin-nav-badge">3</span>
                    </a>
                </div>

                <div class="admin-nav-section">
                    <h4 class="admin-nav-title">Система</h4>
                    <a href="#" class="admin-nav-link">
                        <i class="fas fa-users"></i> Пользователи
                    </a>
                    <a href="#" class="admin-nav-link">
                        <i class="fas fa-cog"></i> Настройки
                    </a>
                </div>
            </nav>

            <div class="admin-sidebar-footer">
                <div class="admin-user">
                    <img src="../img/admin-avatar.jpg" alt="Аватар" class="admin-user-avatar">
                    <div class="admin-user-info">
                        <span class="admin-user-name">Архивариус</span>
                        <span class="admin-user-role">Верховный Страж</span>
                    </div>
                </div>
                <a href="#" class="admin-logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Выйти
                </a>
            </div>
        </aside>

        <!-- Основное содержимое -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="admin-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="admin-page-title">
                        <?= getPageTitle($active_page) ?>
                    </h1>
                </div>
                <div class="admin-header-right">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="admin-message">
                            <?= $_SESSION['message'] ?>
                            <?php unset($_SESSION['message']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="admin-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Поиск в архивах..." id="searchInput">
                    </div>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($active_page === 'dashboard'): ?>
                    <!-- Дашборд -->
                    <div class="admin-stats-grid">
                        <div class="admin-stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-scroll"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $stats['articles'] ?></h3>
                                <p>Статей</p>
                            </div>
                            <a href="?page=articles" class="stat-link">Просмотреть</a>
                        </div>

                        <div class="admin-stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-gavel"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $stats['pending_artworks'] ?></h3>
                                <p>На модерации</p>
                            </div>
                            <a href="?page=moderation" class="stat-link">Проверить</a>
                        </div>

                        <div class="admin-stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-images"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $stats['published_artworks'] ?></h3>
                                <p>Одобренных работ</p>
                            </div>
                            <a href="?page=gallery" class="stat-link">Перейти</a>
                        </div>

                        <div class="admin-stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $stats['users'] ?></h3>
                                <p>Пользователей</p>
                            </div>
                            <a href="?page=users" class="stat-link">Управление</a>
                        </div>
                    </div>

                    <div class="admin-welcome">
                        <h2>Добро пожаловать, Архивариус</h2>
                        <p>Сегодня <?= date('d.m.Y') ?>. У вас <?= $stats['pending_artworks'] ?> работ на модерации.</p>
                    </div>

                <?php elseif ($active_page === 'articles'): ?>
                    <!-- Раздел статей -->
                    <div class="admin-quick-actions">
                        <a href="edit_article.php" class="admin-quick-btn admin-primary-btn">
                            <i class="fas fa-plus"></i> Новая статья
                        </a>
                    </div>

                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Категория</th>
                                    <th>Статус</th>
                                    <th>Просмотры</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($article = $articles->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $article['id'] ?></td>
                                    <td>
                                        <div class="admin-article-preview">
                                            <img src="<?= $article['img'] ?>" alt="Превью" class="admin-article-thumb">
                                            <span><?= htmlspecialchars($article['name']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $categoryBadges = [
                                            'software' => 'admin-software-badge',
                                            'brushes' => 'admin-brushes-badge',
                                            'tutorials' => 'admin-tutorials-badge',
                                            'theory' => 'admin-theory-badge'
                                        ];
                                        $categoryNames = [
                                            'software' => 'Редакторы',
                                            'brushes' => 'Кисти',
                                            'tutorials' => 'Туториалы',
                                            'theory' => 'Теория'
                                        ];
                                        ?>
                                        <span class="admin-badge <?= $categoryBadges[$article['category']] ?>">
                                            <?= $categoryNames[$article['category']] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusBadges = [
                                            'published' => 'admin-published-badge',
                                            'draft' => 'admin-draft-badge',
                                            'archived' => 'admin-archived-badge'
                                        ];
                                        $statusNames = [
                                            'published' => 'Опубликовано',
                                            'draft' => 'Черновик',
                                            'archived' => 'В архиве'
                                        ];
                                        ?>
                                        <span class="admin-badge <?= $statusBadges[$article['status']] ?>">
                                            <?= $statusNames[$article['status']] ?>
                                        </span>
                                    </td>
                                    <td><?= $article['views'] ?></td>
                                    <td><?= date('d.m.Y', strtotime($article['created_at'])) ?></td>
                                    <td>
                                        <div class="admin-actions">
                                            <a href="edit_article.php?id=<?= $article['id'] ?>" class="admin-action-btn admin-edit-btn" title="Редактировать">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <a href="admin.php?page=articles&action=delete&id=<?= $article['id'] ?>" class="admin-action-btn admin-delete-btn" title="Удалить" onclick="return confirm('Вы уверены?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="../php/article.php?id=<?= $article['id'] ?>" target="_blank" class="admin-action-btn admin-eye-btn" title="Просмотреть">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($active_page === 'gallery'): ?>
                    <!-- Раздел галереи -->
                    <div class="admin-table-container">
                        <h2>Управление галереей</h2>
                        
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                            <?php unset($_SESSION['message']); ?>
                        <?php endif; ?>
                        
                        <!-- Получаем все работы из галереи -->
                        <?php
                        $gallery_query = "SELECT a.*, u.login as author 
                                         FROM artworks a 
                                         JOIN users u ON a.user_id = u.id 
                                         ORDER BY a.created_at DESC";
                        $gallery_result = $conn->query($gallery_query);
                        ?>
                        
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Изображение</th>
                                    <th>Название</th>
                                    <th>Автор</th>
                                    <th>Тип</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($artwork = $gallery_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $artwork['id'] ?></td>
                                    <td>
                                        <img src="/uploads/artworks/<?= $artwork['image_path'] ?>" 
                                             alt="<?= htmlspecialchars($artwork['title']) ?>" 
                                             class="admin-gallery-thumb">
                                    </td>
                                    <td><?= htmlspecialchars($artwork['title']) ?></td>
                                    <td><?= htmlspecialchars($artwork['author']) ?></td>
                                    <td>
                                        <?php if ($artwork['is_contest']): ?>
                                            <span class="badge bg-warning">Конкурс</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Галерея</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_badges = [
                                            'pending' => 'bg-secondary',
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger'
                                        ];
                                        $status_text = [
                                            'pending' => 'На модерации',
                                            'approved' => 'Одобрено',
                                            'rejected' => 'Отклонено'
                                        ];
                                        ?>
                                        <span class="badge <?= $status_badges[$artwork['status']] ?>">
                                            <?= $status_text[$artwork['status']] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d.m.Y', strtotime($artwork['created_at'])) ?></td>
                                    <td>
                                        <div class="admin-actions">
                                            <a href="admin.php?page=gallery&action=delete_artwork&id=<?= $artwork['id'] ?>" 
                                               class="admin-action-btn admin-delete-btn" 
                                               title="Удалить" 
                                               onclick="return confirm('Вы уверены, что хотите удалить эту работу?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php if ($artwork['status'] == 'pending'): ?>
                                                <a href="admin.php?page=moderation&approve=<?= $artwork['id'] ?>" 
                                                   class="admin-action-btn admin-edit-btn" 
                                                   title="Модерация">
                                                    <i class="fas fa-gavel"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($active_page === 'moderation'): ?>
                    <!-- Раздел модерации -->
                    <?php include 'moderation.php'; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Модальное окно редактирования -->
    <div class="admin-modal" id="editArticleModal">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h3>Редактирование статьи</h3>
                <button class="admin-modal-close">&times;</button>
            </div>
            <div class="admin-modal-body">
                <!-- Форма редактирования будет здесь -->
            </div>
            <div class="admin-modal-footer">
                <button class="admin-btn admin-cancel-btn">Отменить</button>
                <button class="admin-btn admin-save-btn">Сохранить изменения</button>
            </div>
        </div>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>