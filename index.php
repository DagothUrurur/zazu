
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oblivion Scriptorium | Digital Art Academy</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;700&family=IM+Fell+English+SC&display=swap" rel="stylesheet">
    <!-- Кастомные стили -->
    <link rel="stylesheet" href="style.css">
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
                        <a href="../php/archiv.php" class="nav-link">Архивы</a>
                        <a href="gallery.html" class="nav-link">Галерея</a>
                        <a href="#" class="nav-link">Исповедь</a>
                        <a href="auth/login.php" class="auth-btn">Войти в Тень</a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Главный баннер -->
    <section class="main-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center banner-content">
                    <h1 class="banner-title">Ты — лишь инструмент в руках Тьмы</h1>
                    <p class="banner-text">Здесь учат не рисовать. Здесь учат вырезать куски реальности и вшивать их в холст. Добро пожаловать в последнюю академию потерянных искусств.</p>
                    <button type = "submit" class="cta-btn">Принести Клятву <i class="fas fa-hand-holding-water blood-drip"></i></button>
                </div>
            </div>
        </div>
    </section>

    <!-- Блок "С чего начать" -->
    <section class="start-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5"><span class="title-border">Первые Шаги в Тьму</span></h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="start-card">
                        <div class="card-icon">
                            <i class="fas fa-skull"></i>
                            <div class="icon-halo"></div>
                        </div>
                        <h3>Выбор оружия</h3>
                        <p>Photoshop, Procreate, Krita — какое проклятие вы выберете?</p>
                        <a href="#" class="card-link">Изучить проклятья <i class="fas fa-long-arrow-alt-right"></i></a>
                        <div class="card-stain stain-1"></div>
                    </div>
                </div>
                <!-- Ещё 2 карточки -->
            </div>
        </div>
    </section>

    <!-- Популярные статьи -->
    <section class="popular-articles py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5"><span class="title-border">Проклятые Свитки</span></h2>
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="article-card">
                        <div class="article-badge">Топ недели</div>
                        <div class="article-img-container">
                            <img src="img/article1.jpg" alt="Статья" class="article-img">
                            <div class="img-overlay"></div>
                        </div>
                        <div class="article-content">
                            <h3>Тени, которые шепчут</h3>
                            <p>Как заставить тени рассказывать ваши секреты.</p>
                            <div class="article-meta">
                                <span class="blood-rating"><i class="fas fa-tint"></i> Кровь: 92%</span>
                                <a href="#" class="read-more">Читать <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                        <div class="card-stain stain-2"></div>
                    </div>
                </div>
                <!-- Ещё 2 статьи -->
            </div>
        </div>
    </section>

    <!-- Лучшие работы недели -->
    <section class="top-artworks py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5"><span class="title-border">Искажённые Видения</span></h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="artwork-card">
                        <div class="artwork-img-container">
                            <img src="img/artwork1.jpg" alt="Работа" class="artwork-img">
                            <div class="artwork-overlay">
                                <h3>"Кричащий пиксель"</h3>
                                <p>Автор: @shadow_painter</p>
                                <button class="vote-btn"><i class="fas fa-vote-yea"></i> Приговорить</button>
                            </div>
                            <div class="artwork-glitch"></div>
                        </div>
                    </div>
                </div>
                <!-- Ещё 2 работы -->
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
                        <li><a href="#">Главная</a></li>
                        <li><a href="#">Архивы</a></li>
                        <li><a href="#">Галерея</a></li>
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

    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Кастомный JS -->
    <script src="script.js"></script>
</body>
</html>