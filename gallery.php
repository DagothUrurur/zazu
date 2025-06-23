<?php
session_start();
// Подключаемся к БД
require_once __DIR__ . '/php/db.php';

// Проверяем соединение
if (!$conn || $conn->connect_error) {
    die("Ошибка подключения к базе данных: " . ($conn ? $conn->connect_error : "Соединение не установлено"));
}

// Функция для форматирования времени
function formatTime($seconds) {
    if ($seconds <= 0) return '00:00:00';
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Галерея | Oblivion Scriptorium</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Основные стили -->
    <link rel="stylesheet" href="style.css">
    <!-- Стили для галереи -->
    <link rel="stylesheet" href="css/gallery.css">
<script>
    // Передаем PHP переменные в JavaScript
    <?php if (isset($activeContest)): ?>
        const contestStart = <?= strtotime($activeContest['start_date']) ?>;
        const contestEnd = <?= strtotime($activeContest['end_date']) ?>;
        const serverNow = <?= time() ?>;
    <?php else: ?>
        const contestStart = 0;
        const contestEnd = 0;
        const serverNow = <?= time() ?>;
    <?php endif; ?>
</script>
</head>
<body>
    <!-- Туманный фон -->
    <div class="fog-container">
        <div class="fog-layer fog-layer-1"></div>
        <div class="fog-layer fog-layer-2"></div>
    </div>

    <!-- Силуэты на фоне -->
    <div class="silhouettes">
        <div class="silhouette silhouette-1"></div>
        <div class="silhouette silhouette-2"></div>
        <div class="silhouette silhouette-3"></div>
    </div>

<header class="header">
    <div class="container">
        <div class="row align-items-center py-3">
            <div class="col-md-4 logo">
                <img src="img/logo.png" alt="Лого" class="logo-img">
                <span class="logo-text">Oblivion Scriptorium</span>
            </div>
            <div class="col-md-8">
                <nav class="nav-menu">
                    <a href="index.php" class="nav-link">Главная</a>
                    <a href="/php/archiv.php" class="nav-link">Архивы</a>
                    <a href="gallery.php" class="nav-link active">Галерея</a>
                    <a href="../auth/login.php" class="auth-btn">
                        <?php echo (isset($_SESSION['user_id']) ? "Убежище" : "Войти в Тень"); ?>
                    </a>
                </nav>
                <div class="burger-menu">
                    <span class="burger-line"></span>
                    <span class="burger-line"></span>
                    <span class="burger-line"></span>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Мобильное меню -->
<div id="mobileMenuContainer" class="mobile-menu">
    <div class="mobile-menu-content">
        <a href="index.php" class="nav-link">Главная</a>
        <a href="/php/archiv.php" class="nav-link">Архивы</a>
        <a href="gallery.php" class="nav-link active">Галерея</a>
        <a href="../auth/login.php" class="auth-btn">
            <?php echo (isset($_SESSION['user_id']) ? "Убежище" : "Войти в Тень"); ?>
        </a>
    </div>
</div>

         <!-- Конкурс недели -->
<!-- Конкурс недели -->
<section class="weekly-contest mt-5">
    <div class="row">
        <div class="col-12">
            <div class="contest-header">
                <?php
                require_once __DIR__ . '/php/contest.php';
                $contestManager = new Contest($conn);
                
                // Получаем активный конкурс
                $activeContest = $contestManager->getActiveContest();
                
                // Если нет активного конкурса, проверяем последний завершенный
                if (!$activeContest) {
                    $lastContest = $contestManager->getLastFinishedContest();
                    if ($lastContest) {
                        // Показываем победителя последнего конкурса
                        $winner = $conn->query("SELECT a.*, u.login as author 
                                              FROM artworks a 
                                              JOIN users u ON a.user_id = u.id 
                                              WHERE a.id = {$lastContest['winner_artwork_id']}")->fetch_assoc();
                        ?>
                        <div class="d-flex justify-content-between align-items-center flex-wrap contest-header mb-3">
    <h2 class="contest-title mb-0">
        <i class="fas fa-trophy"></i> Конкурс "<?= htmlspecialchars($activeContest['title']) ?>"
        <span class="contest-badge">Голосуйте!</span>
    </h2>
<div class="contest-timer text-end mt-2 mt-md-0" id="contestTimer">
    <?php 
    if (isset($activeContest)): 
        $now = time();
        $start = strtotime($activeContest['start_date']);
        $end = strtotime($activeContest['end_date']);
    ?>
        <?php if ($now < $start): ?>
            <i class="fas fa-hourglass-start"></i> До начала: <span class="time-remaining"><?= formatTime($start - $now) ?></span>
        <?php elseif ($now < $end): ?>
            <i class="fas fa-hourglass-half"></i> До конца: <span class="time-remaining"><?= formatTime($end - $now) ?></span>
        <?php else: ?>
            <i class="fas fa-hourglass-end"></i> Конкурс завершён
            <?php if ($activeContest['winner_artwork_id']): ?>
                <span class="badge bg-success ms-2">Победитель выбран</span>
            <?php else: ?>
                <span class="badge bg-warning ms-2">Идёт подсчёт</span>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <i class="fas fa-hourglass-start"></i> Конкурс скоро начнётся
    <?php endif; ?>
</div>
</div>

                        </div>
                        </div>
                        </div>
                        
                        <div class="row contest-gallery">
                            <div class="col-md-4 offset-md-4 mb-4">
                                <div class="contest-artwork winner">
                                    <div class="artwork-image-container">
                                        <img src="/uploads/artworks/<?= $winner['image_path'] ?>" 
                                             alt="<?= htmlspecialchars($winner['title']) ?>" 
                                             class="artwork-image">
                                        <div class="contest-ribbon">ПОБЕДИТЕЛЬ</div>
                                        <div class="artwork-overlay">
                                            <div class="artwork-info">
                                                <h3>"<?= htmlspecialchars($winner['title']) ?>"</h3>
                                                <p>Автор: @<?= htmlspecialchars($winner['author']) ?></p>
                                                <div class="artwork-stats">
                                                    <span class="votes"><i class="fas fa-heart"></i> <?= $winner['likes_count'] ?></span>
                                                    <span class="views"><i class="fas fa-eye"></i> <?= $winner['views'] ?></span>
                                                    <span class="date"><i class="far fa-calendar-alt"></i> <?= date('d.m.Y', strtotime($winner['created_at'])) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="artwork-glitch"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12 text-center">
                                <p class="contest-message">Новый конкурс будет объявлен в ближайшее время</p>
                            </div>
                        </div>
                        <?php
                    } else {
                        // Нет ни активных, ни завершенных конкурсов
                        ?>
                        <h2 class="contest-title">
                            <i class="fas fa-trophy"></i> Конкурс "Проклятый Шедевр"
                        </h2>
                        <div class="contest-timer">
                            <i class="fas fa-hourglass-start"></i> Конкурс скоро начнётся
                        </div>
                        </div>
                        </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12 text-center">
                                <p class="contest-message">Следите за новостями о следующем конкурсе</p>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    // Есть активный конкурс
                    $timeLeft = strtotime($activeContest['end_date']) - time();
                    $daysLeft = floor($timeLeft / (60 * 60 * 24));
                    $hoursLeft = floor(($timeLeft % (60 * 60 * 24)) / (60 * 60));
                    $minutesLeft = floor(($timeLeft % (60 * 60)) / 60);
                    $secondsLeft = $timeLeft % 60;
                    ?>
                    <h2 class="contest-title">
                        <i class="fas fa-trophy"></i> Конкурс "<?= htmlspecialchars($activeContest['title']) ?>"
                        <span class="contest-badge">Голосуйте!</span>
                    </h2>
                    <div class="contest-timer text-end mt-2 mt-md-0" id="contestTimer">
    <?php 
    if (isset($activeContest)): 
        $now = time();
        $start = strtotime($activeContest['start_date']);
        $end = strtotime($activeContest['end_date']);
    ?>
        <?php if ($now < $start): ?>
            <i class="fas fa-hourglass-start"></i> До начала: <span class="time-remaining"><?= formatTime($start - $now) ?></span>
        <?php elseif ($now < $end): ?>
            <i class="fas fa-hourglass-half"></i> До конца: <span class="time-remaining"><?= formatTime($end - $now) ?></span>
        <?php else: ?>
            <i class="fas fa-hourglass-end"></i> Конкурс завершён
            <?php if ($activeContest['winner_artwork_id']): ?>
                <span class="badge bg-success ms-2">Победитель выбран</span>
            <?php else: ?>
                <span class="badge bg-warning ms-2">Идёт подсчёт</span>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <i class="fas fa-hourglass-start"></i> Конкурс скоро начнётся
    <?php endif; ?>
</div>

                     
                    </div>
                    </div>
                    </div>
                    
                    <div class="row contest-gallery">
                        <?php
                        // Если конкурс завершен и есть победитель - показываем только победителя
                        if ($now >= $end && $activeContest['winner_artwork_id']) {
                            $winner = $conn->query("SELECT a.*, u.login as author 
                                                  FROM artworks a 
                                                  JOIN users u ON a.user_id = u.id 
                                                  WHERE a.id = {$activeContest['winner_artwork_id']}")->fetch_assoc();
                            ?>
                            <div class="col-md-4 offset-md-4 mb-4">
                                <div class="contest-artwork winner">
                                    <div class="artwork-image-container">
                                        <img src="/uploads/artworks/<?= $winner['image_path'] ?>" 
                                             alt="<?= htmlspecialchars($winner['title']) ?>" 
                                             class="artwork-image">
                                        <div class="contest-ribbon">ПОБЕДИТЕЛЬ</div>
                                        <div class="artwork-overlay">
                                            <div class="artwork-info">
                                                <h3>"<?= htmlspecialchars($winner['title']) ?>"</h3>
                                                <p>Автор: @<?= htmlspecialchars($winner['author']) ?></p>
                                                <div class="artwork-stats">
                                                    <span class="votes"><i class="fas fa-heart"></i> <?= $winner['likes_count'] ?></span>
                                                    <span class="views"><i class="fas fa-eye"></i> <?= $winner['views'] ?></span>
                                                    <span class="date"><i class="far fa-calendar-alt"></i> <?= date('d.m.Y', strtotime($winner['created_at'])) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="artwork-glitch"></div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        } else {
                            // Показываем все работы конкурса
                            $contest_artworks = $contestManager->getContestArtworks($activeContest['id']);
                            while ($artwork = $contest_artworks->fetch_assoc()): 
                                $is_liked = isset($_SESSION['user_id']) ? 
                                    $conn->query("SELECT id FROM likes WHERE artwork_id = {$artwork['id']} AND user_id = {$_SESSION['user_id']}")->num_rows > 0 : 
                                    false;
                                $is_voted = isset($_SESSION['user_id']) ? 
        $conn->query("SELECT id FROM votes WHERE artwork_id = {$artwork['id']} AND user_id = {$_SESSION['user_id']}")->num_rows > 0 : 
        false;
    $votes_count = $conn->query("SELECT COUNT(*) FROM votes WHERE artwork_id = {$artwork['id']}")->fetch_row()[0];
                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="contest-artwork">
                                    <div class="artwork-image-container">
                                        <img src="/uploads/artworks/<?= $artwork['image_path'] ?>" 
                                             alt="<?= htmlspecialchars($artwork['title']) ?>" 
                                             class="artwork-image">
                                        <div class="contest-ribbon">КОНКУРС</div>
                                        <div class="artwork-overlay">
                                            <div class="artwork-info">
                                                <h3>"<?= htmlspecialchars($artwork['title']) ?>"</h3>
                                                <p>Автор: @<?= htmlspecialchars($artwork['author']) ?></p>
                                                <div class="artwork-stats">
                                                    <span class="votes"><i class="fas fa-heart"></i> <?= $artwork['likes_count'] ?></span>
                                                    <span class="views"><i class="fas fa-eye"></i> <?= $artwork['views'] ?></span>
                                                    <span class="date"><i class="far fa-calendar-alt"></i> <?= date('d.m.Y', strtotime($artwork['created_at'])) ?></span>
                                                </div>
                                            </div>
                                            <div class="artwork-actions">
                                                <?php if ($artwork['is_contest']): ?>
<button class="vote-btn <?= $is_voted ? 'voted' : '' ?>" 
        data-artwork-id="<?= $artwork['id'] ?>">
    <i class="<?= $is_voted ? 'fas' : 'far' ?> fa-medal"></i>
    <span class="vote-count"><?= $votes_count ?></span>
</button>
                                                <?php endif; ?>
                                                <button class="like-btn <?= $is_liked ? 'liked' : '' ?>" data-artwork-id="<?= $artwork['id'] ?>">
                                                    <i class="<?= $is_liked ? 'fas' : 'far' ?> fa-heart"></i>
                                                    <span class="like-count"><?= $artwork['likes_count'] ?></span>
                                                </button>
                                              <button class="view-btn" 
        data-bs-toggle="modal" 
        data-bs-target="#artworkModal" 
        data-id="<?= $artwork['id'] ?>" 
        data-contest="true">
    <i class="fas fa-expand"></i>
</button>
                                            </div>
                                        </div>
                                        <div class="artwork-glitch"></div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php } ?>
                    </div>
                    <div class="row">
    <div class="col-12">
        <div class="contest-description-box my-4">
            <?= nl2br(htmlspecialchars($activeContest['description'])) ?>
        </div>
    </div>
</div>
                    <?php if ($now < $end): ?>
                        <div class="row">
                            <div class="col-12 text-center">
                                <button class="submit-btn" data-bs-toggle="modal" data-bs-target="#submitModal">
                                    <i class="fas fa-upload"></i> Участвовать в конкурсе
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php } ?>
</section>

            <!-- Все работы -->
<section class="all-artworks mt-5">
    <div class="row">
        <div class="col-12">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-archive"></i> Архив Видений
                </h2>
                <div class="sort-box">
                    <label for="sort">Сортировать:</label>
                    <select id="sort" class="sort-select">
                        <option value="newest">Сначала новые</option>
                        <option value="popular">По популярности</option>
                        <option value="controversial">Самые спорные</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row artworks-grid">
        <?php
        // Пагинация
        $itemsPerPage = 6; // Количество работ на странице
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($currentPage < 1) $currentPage = 1;
        
        // Подсчет общего количества работ
        $totalItems = $conn->query("SELECT COUNT(*) FROM artworks WHERE status = 'approved'")->fetch_row()[0];
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        // Проверка, чтобы currentPage не был больше totalPages
        if ($currentPage > $totalPages && $totalPages > 0) {
            $currentPage = $totalPages;
        }
        
        // Сортировка
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
        $orderBy = 'a.created_at DESC';
        switch ($sort) {
            case 'popular':
                $orderBy = 'likes_count DESC, a.created_at DESC';
                break;
            case 'controversial':
                $orderBy = '(SELECT COUNT(*) FROM comments WHERE artwork_id = a.id) DESC, a.created_at DESC';
                break;
        }
        
        // Запрос работ с пагинацией
        $offset = ($currentPage - 1) * $itemsPerPage;
        $artworks_query = "SELECT a.*, u.login as author, 
                         (SELECT COUNT(*) FROM likes WHERE artwork_id = a.id) as likes_count
                         FROM artworks a 
                         JOIN users u ON a.user_id = u.id 
                         WHERE a.status = 'approved'
                         ORDER BY $orderBy
                         LIMIT $itemsPerPage OFFSET $offset";
        $artworks_result = $conn->query($artworks_query);
        
        if ($artworks_result->num_rows > 0) {
            while ($artwork = $artworks_result->fetch_assoc()):
                $is_liked = isset($_SESSION['user_id']) ? 
                    $conn->query("SELECT id FROM likes WHERE artwork_id = {$artwork['id']} AND user_id = {$_SESSION['user_id']}")->num_rows > 0 : 
                    false;
                $contest_badge = $artwork['is_contest'] ? '<span class="contest-tag"><i class="fas fa-trophy"></i> Конкурс</span>' : '';
        ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="artwork-card">
                <div class="artwork-image-container">
                    <img src="/uploads/artworks/<?= $artwork['image_path'] ?>" 
                         alt="<?= htmlspecialchars($artwork['title']) ?>" 
                         class="artwork-image">
                    <div class="artwork-overlay">
                        <div class="artwork-info">
                            <h3>"<?= htmlspecialchars($artwork['title']) ?>"</h3>
                            <p>Автор: @<?= htmlspecialchars($artwork['author']) ?></p>
                            <div class="artwork-stats">
                                <span class="views-count">
                                    <i class="fas fa-eye"></i> <?= $artwork['views'] ?>
                                </span>
                                <button class="like-btn <?= $is_liked ? 'liked' : '' ?>" data-artwork-id="<?= $artwork['id'] ?>">
                                    <i class="<?= $is_liked ? 'fas' : 'far' ?> fa-heart"></i>
                                    <span class="like-count"><?= $artwork['likes_count'] ?></span>
                                </button>
                                <span class="date"><i class="far fa-calendar-alt"></i> <?= date('d.m.Y', strtotime($artwork['created_at'])) ?></span>
                                <?= $contest_badge ?>
                            </div>
                        </div>
                        <div class="artwork-actions">
                            <button class="expand-btn" data-bs-toggle="modal" data-bs-target="#artworkModal" data-id="<?= $artwork['id'] ?>">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    <div class="artwork-glitch"></div>
                </div>
            </div>
        </div>
        <?php 
            endwhile;
        } else {
            echo '<div class="col-12 text-center py-5"><h4>Работы не найдены</h4></div>';
        }
        ?>
    </div>

    <!-- Пагинация -->
    <div class="row">
        <div class="col-12">
            <nav class="gallery-pagination">
                <ul class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $currentPage - 1 ?>&sort=<?= $sort ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link" aria-hidden="true">&laquo;</span>
                        </li>
                    <?php endif; ?>

                    <?php
                    // Показываем ограниченное количество страниц вокруг текущей
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    if ($startPage > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=1&sort='.$sort.'">1</a></li>';
                        if ($startPage > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&sort=<?= $sort ?>"><?= $i ?></a>
                        </li>
                    <?php endfor;
                    
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?page='.$totalPages.'&sort='.$sort.'">'.$totalPages.'</a></li>';
                    }
                    ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $currentPage + 1 ?>&sort=<?= $sort ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link" aria-hidden="true">&raquo;</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</section>
        </div>
    </main>

    <!-- Модальное окно работы -->
<div class="modal fade" id="artworkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="artworkModalTitle">Загрузка...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="artwork-display">
                            <img id="modalArtworkImage" src="" alt="Полноразмерная работа" class="main-artwork">
                            <div class="artwork-tools">
                                <span class="views-count me-3">
                                    <i class="fas fa-eye"></i> <span id="viewsCount">0</span>
                                </span>
                                
                                <!-- Кнопка лайка (обычная) -->
<button class="like-btn" id="modalLikeBtn">
    <i class="far fa-heart"></i>
    <span class="like-count">0</span>
</button>                  
                                <!-- Кнопка голосования (только для конкурсных работ) -->
        <button class="vote-btn d-none" id="modalVoteBtn">
            <i class="far fa-medal"></i>
            <span class="vote-count">0</span>
        </button>
                                
                                <button class="tool-btn"><i class="fas fa-share-alt"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="artwork-comments">
                            <?php if(isset($_SESSION['user_id'])): ?>
                            <div class="comment-form">
                                <textarea placeholder="Оставьте свой след..." class="comment-input" id="commentText"></textarea>
                                <button class="comment-submit">Отправить</button>
                            </div>
                            <?php endif; ?>
                            <div class="comments-list" id="commentsList">
                                <!-- Комментарии будут загружены через AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Модальное окно загрузки работы -->
   <div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload"></i> Участие в конкурсе
                    <span class="badge bg-primary ms-2">
                        <?= htmlspecialchars($activeContest['title'] ?? 'Без конкурса') ?>
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/account/upload.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="contest_id" value="<?= $activeContest['id'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label for="artworkTitle" class="form-label">Название работы</label>
                        <input type="text" class="form-control" id="artworkTitle" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="artworkDescription" class="form-label">Описание</label>
                        <textarea class="form-control" id="artworkDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="artworkImage" class="form-label">Изображение</label>
                        <input type="file" class="form-control" id="artworkImage" name="image" accept="image/*" required>
                        <div class="form-text">Максимальный размер: 5MB. Допустимые форматы: JPG, PNG, GIF</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="joinContest" name="join_contest" checked>
                        <label class="form-check-label" for="joinContest">
                            Участвовать в текущем конкурсе
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Отправить работу
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Футер -->
    <footer class="footer py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="footer-logo">
                        <img src="img/logo-sm.png" alt="Лого" class="footer-logo-img">
                        <p class="footer-text">Oblivion Scriptorium © 2024<br>Все права прокляты</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h3 class="footer-title">Навигация по руинам</h3>
                    <ul class="footer-nav">
                        <li><a href="index.html">Главная</a></li>
                        <li><a href="archiv.html">Архивы</a></li>
                        <li><a href="gallery.php">Галерея</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h3 class="footer-title">Крики в пустоту</h3>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-telegram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-vk"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-pinterest"></i></a>
                    </div>
                    <div class="footer-credits">
                        <p>Сайт создан в забытом склепе<br>при свечах и лунном свете</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Кастомный JS -->
    <script src="js/gallery.js"></script>
</div> 
</body>
</html>