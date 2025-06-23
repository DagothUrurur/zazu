<?php
session_start();
require_once '../php/db.php'; // Подключаем ваш файл с mysqli

// Проверка авторизации (если нужно)
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем данные пользователя
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Получаем работы пользователя
$artworks_query = $conn->prepare("SELECT * FROM artworks WHERE user_id = ? ORDER BY created_at DESC LIMIT 6");
$artworks_query->bind_param("i", $user_id);
$artworks_query->execute();
$artworks_result = $artworks_query->get_result();
$artworks = $artworks_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет | Oblivion Scriptorium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <!-- Основные стили -->
     <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="css/account.css">
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
    </div>

    <!-- Хэдер -->
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
                        <a href="/gallery.php" class="nav-link active">Галерея</a>
                        <a href="confession.html" class="nav-link">Исповедь</a>
                        <a href="auth/login.php" class="auth-btn"><?php echo $_SESSION["user_role"] ? "Убежище" : "Войти в Тень"; ?></a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Основной контент -->
    <main class="account-main">
        <div class="container">
            <div class="row">
                <!-- Боковая панель -->
                <div class="col-md-4">
                    <div class="account-sidebar">
                        <div class="profile-card">
                            <div class="avatar-container">
                                <img src="/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Аватар" class="profile-avatar">
                                <div class="avatar-overlay">
                                    <button class="change-avatar-btn" data-bs-toggle="modal" data-bs-target="#avatarModal">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                            </div>
                            <h2 class="profile-username"><?= htmlspecialchars($user['login']) ?></h2>
                            <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                            
                            <?php if (!empty($user['bio'])): ?>
                                <div class="profile-bio">
                                    <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="profile-stats">
                                <div class="stat-item">
                                    <span class="stat-number"><?= count($artworks) ?></span>
                                    <span class="stat-label">Работ</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">0</span> <!-- Замените на реальные данные -->
                                    <span class="stat-label">Лайков</span>
                                </div>
                            </div>
                        </div>
                        
                        <nav class="account-menu">
                            <a href="/account/" class="menu-item active">
                                <i class="fas fa-home"></i> Мои работы
                            </a>
                            <a href="/account/settings.php" class="menu-item">
                                <i class="fas fa-cog"></i> Настройки
                            </a>
                            <a href="/gallery.php" class="menu-item">
                                <i class="fas fa-images"></i> Галерея
                            </a>
                            <a href="../php/destroy.php" class="menu-item">
                                <i class="fas fa-sign-out-alt"></i> Выйти
                            </a>
                        </nav>
                    </div>
                </div>
                
                <!-- Основное содержимое -->
                <div class="col-md-8">
                    <div class="account-content">
                        <h1 class="account-title">
                            <i class="fas fa-user-secret"></i> Твоё Проклятое Убежище
                        </h1>
                        
                        <!-- Кнопки действий -->
                        <div class="action-buttons">
                            <button class="upload-btn" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload"></i> Загрузить Видение
                            </button>
                            <button class="contest-btn" data-bs-toggle="modal" data-bs-target="#uploadModal" data-contest="true">
                                <i class="fas fa-trophy"></i> На Конкурс
                            </button>
                        </div>
                        
                        <!-- Галерея работ -->
                        <section class="user-gallery">
                            <h2 class="section-title">
                                <i class="fas fa-archive"></i> Твои Искажённые Творения
                            </h2>
                            
                            <?php if (empty($artworks)): ?>
                                <div class="empty-gallery">
                                    <i class="fas fa-ghost"></i>
                                    <p>Тут пусто... Загрузи своё первое видение</p>
                                </div>
                            <?php else: ?>
                                <div class="row artworks-row">
                                    <?php foreach ($artworks as $artwork): ?>
                                        <div class="col-md-6 mb-4">
                                            <div class="user-artwork">
                                                <img src="/uploads/artworks/<?= htmlspecialchars($artwork['image_path']) ?>" alt="<?= htmlspecialchars($artwork['title']) ?>" class="artwork-thumbnail">
                                                <div class="artwork-info">
                                                    <h3><?= htmlspecialchars($artwork['title']) ?></h3>
                                                    <div class="artwork-meta">
                                                        <span><i class="far fa-calendar"></i> <?= date('d.m.Y', strtotime($artwork['created_at'])) ?></span>
                                                        <span><i class="far fa-eye"></i> <?= $artwork['views'] ?></span>
                                                        <?php if ($artwork['is_contest']): ?>
                                                            <span class="contest-tag"><i class="fas fa-trophy"></i> Конкурс</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="artwork-actions">
                                                        <a href="/gallery/view.php?id=<?= $artwork['id'] ?>" class="view-btn"><i class="fas fa-expand"></i></a>
                                                        <button class="delete-btn" data-id="<?= $artwork['id'] ?>"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Модальное окно загрузки работы -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload"></i> Загрузить Видение</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" action="/account/upload.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="artworkTitle" class="form-label">Название Видения</label>
                            <input type="text" class="form-control" id="artworkTitle" name="title" required placeholder="Как назовёте свой кошмар?">
                        </div>
                        <div class="mb-3">
                            <label for="artworkDescription" class="form-label">Описание</label>
                            <textarea class="form-control" id="artworkDescription" name="description" rows="3" placeholder="Что скрывается за этим образом?"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="artworkFile" class="form-label">Файл Видения</label>
                            <input class="form-control" type="file" id="artworkFile" name="image" accept="image/*" required>
                            <div class="form-text">Только PNG или JPG. Максимум 10MB.</div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="contestCheck" name="is_contest">
                            <label class="form-check-label" for="contestCheck">Участвовать в текущем конкурсе</label>
                        </div>
                        <div class="submit-actions">
                            <button type="submit" class="submit-btn">Отправить в Бездну</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно смены аватара -->
    <div class="modal fade" id="avatarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit"></i> Смена Аватара</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="avatarForm" action="/account/settings.php?action=avatar" method="post" enctype="multipart/form-data">
                        <div class="current-avatar">
                            <img src="/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Текущий аватар" class="current-avatar-img">
                        </div>
                        <div class="mb-3">
                            <label for="avatarFile" class="form-label">Выберите новый аватар</label>
                            <input class="form-control" type="file" id="avatarFile" name="avatar" accept="image/*" required>
                        </div>
                        <div class="submit-actions">
                            <button type="submit" class="submit-btn">Сохранить Изменения</button>
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
                        <li><a href="gallery.html">Галерея</a></li>
                        <li><a href="confession.html">Исповедь</a></li>
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
    <!-- Кастомный JS -->
    <script src="/account/js/account.js"></script>
</body>
</html>