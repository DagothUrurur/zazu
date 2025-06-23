<?php
require_once('db.php');
session_start();
// Получаем категорию из URL (если есть)
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Подготовка SQL запроса
if ($category === 'all') {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE status = 'published' ORDER BY created_at DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE category = ? AND status = 'published' ORDER BY created_at DESC");
    $stmt->bind_param("s", $category);
}

$stmt->execute();
$articles = $stmt->get_result();
?>>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Архивы | Oblivion Scriptorium</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Основные стили -->
    <link rel="stylesheet" href="../style.css">
    <!-- Стили для страницы архивов -->
    <link rel="stylesheet" href="../css/archives.css">
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
                        <a href="../index.php" class="nav-link">Главная</a>
                        <a href="archiv.php" class="nav-link active">Архивы</a>
                        <a href="../gallery.php" class="nav-link">Галерея</a>
                        <a href="confession.html" class="nav-link">Исповедь</a>
                        <a href="../auth/login.php" class="auth-btn"><?php echo $_SESSION["user_role"] ? "Убежище" : "Войти в Тень"; ?>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Хлебные крошки -->
    <section class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Главная</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Архивы</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Основной контент -->
    <main class="archives-main">
        <div class="container">
            <!-- Заголовок -->
            <div class="row">
                <div class="col-12">
                    <h1 class="archives-title">
                        <span class="title-icon"><i class="fas fa-archive"></i></span>
                        Запретные Архивы
                    </h1>
                    <p class="archives-subtitle">Здесь хранятся знания, которые могли бы остаться забытыми</p>
                </div>
            </div>

            <!-- Фильтры -->
            <div class="row filters-row">
                <div class="col-md-8">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Искать в архивах...">
                        <button class="search-btn">Найти</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sort-box">
                        <label for="sort">Сортировать:</label>
                        <select id="sort" class="sort-select">
                            <option value="newest">Сначала новые</option>
                            <option value="oldest">Сначала старые</option>
                            <option value="popular">По популярности</option>
                            <option value="blood">По кровавости</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Категории -->
            <div class="row categories-row">
                <div class="col-12">
                    <div class="category-tabs">
                        <button class="category-tab active" data-category="all">Все</button>
                        <button class="category-tab" data-category="software">Графические редакторы</button>
                        <button class="category-tab" data-category="brushes">Кисти</button>
                        <button class="category-tab" data-category="tutorials">Туториалы</button>
                        <button class="category-tab" data-category="theory">Теория</button>
                    </div>
                </div>
            </div>

            <!-- Сетка статей -->
            <div class="row archives-grid">
                <?php while($article = $articles->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4" data-category="<?php echo $article['category']; ?>">
                    <div class="archive-card">
                        <div class="card-badge"><?php 
                            switch($article['category']) {
                                case 'software': echo 'Граф. редактор'; break;
                                case 'brushes': echo 'Кисти'; break;
                                case 'tutorials': echo 'Туториал'; break;
                                case 'theory': echo 'Теория'; break;
                            }
                        ?></div>
                        <div class="card-image-container">
                        <img src="<?php echo '/'.ltrim($article['img'], '/'); ?>" alt="<?php echo htmlspecialchars($article['name']); ?>" class="card-image">
                            <div class="image-overlay"></div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($article['name']); ?></h3>
                            <p class="card-excerpt"><?php echo htmlspecialchars($article['description']); ?></p>
                            <div class="card-meta">
                                <span class="meta-date"><i class="far fa-calendar-alt"></i> <?php echo date('d.m.Y', strtotime($article['created_at'])); ?></span>
                                <span class="meta-views"><i class="fas fa-eye"></i> <?php echo $article['views']; ?></span>
                            </div>
                            <a href="article.php?id=<?php echo $article['id']; ?>" class="card-link">Читать <i class="fas fa-chevron-right"></i></a>
                        </div>
                        <div class="card-stain"></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

             
            <!-- Пагинация -->
            <div class="row">
                <div class="col-12">
                    <nav class="archive-pagination">
                        <ul class="pagination">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
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
    <script src="../js/archives.js"></script>
</body>
</html>