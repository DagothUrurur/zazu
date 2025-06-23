<?php
require_once('db.php');
session_start();
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Увеличиваем счетчик просмотров
$conn->query("UPDATE articles SET views = views + 1 WHERE id = $article_id");

// Получаем статью
$stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();
//проверка
if(!$article) {
    header("HTTP/1.0 404 Not Found");
    include('404.php'); // Создайте этот файл для страницы 404
    exit;
}
// Если статья не найдена - редирект
if(!$article) {
    header("Location: archiv.php");
    exit;
}

// Получаем изображения для галереи
$gallery_stmt = $conn->prepare("SELECT * FROM article_images WHERE article_id = ? ORDER BY sort_order");
$gallery_stmt->bind_param("i", $article_id);
$gallery_stmt->execute();
$gallery = $gallery_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['name']); ?> | Oblivion Scriptorium</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Основные стили -->
    <link rel="stylesheet" href="../style.css">
    <!-- Стили для статьи -->
    <link rel="stylesheet" href="../css/article.css">
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


    <!-- Основное содержимое статьи -->
    <main class="article-main">
        <div class="container">
            <!-- Заглавное изображение -->
            <div class="row">
                <div class="col-12">
                    <div class="article-hero">
                    <img src="<?php echo '/'.ltrim($article['img'], '/'); ?>" alt="<?php echo htmlspecialchars($article['name']); ?>" class="hero-image">
                        <div class="image-overlay"></div>
                    </div>
                </div>
            </div>

            <!-- Заголовок и описание -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="article-title"><?php echo htmlspecialchars($article['name']); ?></h1>
                    <p class="article-description"><?php echo htmlspecialchars($article['description']); ?></p>
                    <div class="article-meta">
                        <span class="meta-date"><i class="far fa-calendar-alt"></i> <?php echo date('d.m.Y', strtotime($article['created_at'])); ?></span>
                        <span class="meta-views"><i class="fas fa-eye"></i> <?php echo $article['views']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Текст статьи -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="article-content">
                        <?php echo $article['text']; ?>
                    </div>
                </div>
            </div>

            <!-- Галерея изображений -->
            <?php if($gallery->num_rows > 0): ?>
            <div class="row">
                <div class="col-12">
                    <h2 class="gallery-title">Галерея Тьмы</h2>
                    <div class="article-gallery">
                        <?php while($image = $gallery->fetch_assoc()): ?>
                        <div class="gallery-item">
                        <img src="<?php echo '/'.ltrim($image['image_path'], '/'); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>" class="gallery-image">
                            <div class="image-caption"><?php echo htmlspecialchars($image['caption']); ?></div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

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
    <script src="../js/article.js"></script>
</body>
</html>