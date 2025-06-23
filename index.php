<?php
require_once 'php/db.php';
session_start();
// Получаем 3 случайные статьи для "Первые шаги в тьму"
$random_articles = $conn->query("SELECT * FROM articles WHERE status='published' ORDER BY RAND() LIMIT 3");

// Получаем 3 самые просматриваемые статьи для "Проклятые свитки"
$popular_articles = $conn->query("SELECT * FROM articles WHERE status='published' ORDER BY views DESC LIMIT 3");

// Получаем 3 случайные работы из галереи для "Искаженные видения"
$random_artworks = $conn->query("SELECT a.*, u.login as author FROM artworks a JOIN users u ON a.user_id = u.id WHERE a.status='approved' ORDER BY RAND() LIMIT 3");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Oblivion Scriptorium | Digital Art Academy</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;700&family=IM+Fell+English+SC&display=swap" rel="stylesheet">
    <!-- Кастомные стили -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/gallery.css">
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

    <!-- Главный баннер -->
    <section class="main-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center banner-content">
                    <h1 class="banner-title">Ты — лишь инструмент в руках Тьмы</h1>
                    <p class="banner-text">Здесь учат не рисовать. Здесь учат вырезать куски реальности и вшивать их в холст. Добро пожаловать в последнюю академию потерянных искусств.</p>
                    <button type="submit" class="cta-btn">Принести Клятву <i class="fas fa-hand-holding-water blood-drip"></i></button>
                </div>
            </div>
        </div>
    </section>

    <!-- Блок "С чего начать" -->
    <section class="start-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5"><span class="title-border">Первые Шаги в Тьму</span></h2>
            <div class="row">
                <?php while ($article = $random_articles->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="start-card">
                        <div class="card-icon">
                            <i class="fas fa-skull"></i>
                            <div class="icon-halo"></div>
                        </div>
                        <h3><?= htmlspecialchars($article['name']) ?></h3>
                        <p><?= htmlspecialchars(mb_substr($article['description'], 0, 100)) ?>...</p>
                        <a href="/php/article.php?id=<?= $article['id'] ?>" class="card-link">Читать <i class="fas fa-long-arrow-alt-right"></i></a>
                        <div class="card-stain stain-1"></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <!-- Лучшие работы недели -->
    <section class="top-artworks py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5"><span class="title-border">Искажённые Видения</span></h2>
            <div class="row">
                <?php while ($artwork = $random_artworks->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="artwork-card">
                        <div class="artwork-img-container">
                            <img src="/uploads/artworks/<?= $artwork['image_path'] ?>" alt="<?= htmlspecialchars($artwork['title']) ?>" class="artwork-img">
                            <div class="artwork-overlay">
                                <h3>"<?= htmlspecialchars($artwork['title']) ?>"</h3>
                                <p>Автор: <?= htmlspecialchars($artwork['author']) ?></p>
                                <a href="/[gallery]/view.php?id=<?= $artwork['id'] ?>" class="vote-btn"><i class="fas fa-eye"></i> Смотреть</a>
                            </div>
                            <div class="artwork-glitch"></div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Мотивационный блок -->
    <section class="motivation-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto text-center">
                    <div class="quote-container">
                        <h2 class="quote">«Каждая линия — это шрам на реальности. Каждый цвет — крик забытого бога. Рисуй, пока твои глаза не потекут чернилами.»</h2>
                        <p class="quote-author">— Последняя запись в журнале мастера Вальтера</p>
                        <div class="quote-stain"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                        <li><a href="index.php">Главная</a></li>
                        <li><a href="../php/archiv.php">Архивы</a></li>
                        <li><a href="gallery.html">Галерея</a></li>
                        <li><a href="#">Исповедь</a></li>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Кастомный JS -->
    <script src="script.js"></script>
</body>
</html>