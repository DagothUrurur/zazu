<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Галерея | Oblivion Scriptorium</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Основные стили -->
    <link rel="stylesheet" href="style.css">
    <!-- Стили для галереи -->
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
                        <a href="confession.html" class="nav-link">Исповедь</a>
                        <a href="/auth/login.php" class="auth-btn">Войти в Тень</a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Основной контент -->
    <main class="gallery-main">
        <div class="container">
            <!-- Заголовок -->
            <div class="row">
                <div class="col-12">
                    <h1 class="gallery-title">
                        <span class="title-icon"><i class="fas fa-images"></i></span>
                        Искажённые Видения
                    </h1>
                    <p class="gallery-subtitle">Где тень становится искусством, а искусство — проклятием</p>
                </div>
            </div>

            <!-- Конкурс недели -->
            <section class="weekly-contest mt-5">
                <div class="row">
                    <div class="col-12">
                        <div class="contest-header">
                            <h2 class="contest-title">
                                <i class="fas fa-trophy"></i> Конкурс "Проклятый Шедевр"
                                <span class="contest-badge">Голосуйте!</span>
                            </h2>
                            <div class="contest-timer">
                                <i class="fas fa-hourglass-half"></i> До конца: 3 дня 12:45:21
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row contest-gallery">
                    <?php
                    require_once 'php/db.php';
                    
                    // Получаем конкурсные работы (одобренные администратором)
                    $contest_query = "SELECT a.*, u.login as author 
                                    FROM artworks a 
                                    JOIN users u ON a.user_id = u.id 
                                    WHERE a.status = 'approved' AND a.is_contest = 1
                                    ORDER BY a.created_at DESC 
                                    LIMIT 3";
                    $contest_result = $conn->query($contest_query);
                    
                    if ($contest_result->num_rows > 0) {
                        while ($artwork = $contest_result->fetch_assoc()) {
                            echo '
                            <div class="col-md-4 mb-4">
                                <div class="contest-artwork">
                                    <div class="artwork-image-container">
                                        <img src="/uploads/artworks/'.$artwork['image_path'].'" alt="'.htmlspecialchars($artwork['title']).'" class="artwork-image">
                                        <div class="artwork-overlay">
                                            <div class="artwork-info">
                                                <h3>"'.htmlspecialchars($artwork['title']).'"</h3>
                                                <p>Автор: @'.htmlspecialchars($artwork['author']).'</p>
                                                <div class="artwork-stats">
                                                    <span class="votes"><i class="fas fa-heart"></i> '.$artwork['views'].'</span>
                                                    <span class="date"><i class="far fa-calendar-alt"></i> '.date('d.m.Y', strtotime($artwork['created_at'])).'</span>
                                                </div>
                                            </div>
                                            <div class="artwork-actions">
                                                <button class="vote-btn"><i class="fas fa-vote-yea"></i> Голосовать</button>
                                                <button class="view-btn" data-bs-toggle="modal" data-bs-target="#artworkModal" data-id="'.$artwork['id'].'">
                                                    <i class="fas fa-expand"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="artwork-glitch"></div>
                                    </div>
                                </div>
                            </div>';
                        }
                    } else {
                        echo '<div class="col-12"><p>Пока нет работ для конкурса</p></div>';
                    }
                    ?>
                </div>

                <div class="row">
                    <div class="col-12 text-center">
                        <button class="submit-btn" data-bs-toggle="modal" data-bs-target="#submitModal">
                            <i class="fas fa-upload"></i> Принести Жертву Искусству
                        </button>
                    </div>
                </div>
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
                    // Получаем все одобренные работы
                    $artworks_query = "SELECT a.*, u.login as author 
                                     FROM artworks a 
                                     JOIN users u ON a.user_id = u.id 
                                     WHERE a.status = 'approved'
                                     ORDER BY a.created_at DESC 
                                     LIMIT 6";
                    $artworks_result = $conn->query($artworks_query);
                    
                    if ($artworks_result->num_rows > 0) {
                        while ($artwork = $artworks_result->fetch_assoc()) {
                            $contest_badge = $artwork['is_contest'] ? '<span class="contest-tag"><i class="fas fa-trophy"></i> Конкурс</span>' : '';
                            
                            echo '
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="artwork-card">
                                    <div class="artwork-image-container">
                                        <img src="/uploads/artworks/'.$artwork['image_path'].'" alt="'.htmlspecialchars($artwork['title']).'" class="artwork-image">
                                        <div class="artwork-overlay">
                                            <div class="artwork-info">
                                                <h3>"'.htmlspecialchars($artwork['title']).'"</h3>
                                                <p>Автор: @'.htmlspecialchars($artwork['author']).'</p>
                                                <div class="artwork-stats">
                                                    <span class="likes"><i class="fas fa-heart"></i> '.$artwork['views'].'</span>
                                                    <span class="date"><i class="far fa-calendar-alt"></i> '.date('d.m.Y', strtotime($artwork['created_at'])).'</span>
                                                    '.$contest_badge.'
                                                </div>
                                            </div>
                                            <div class="artwork-actions">
                                                <button class="like-btn"><i class="far fa-heart"></i></button>
                                                <button class="expand-btn" data-bs-toggle="modal" data-bs-target="#artworkModal" data-id="'.$artwork['id'].'">
                                                    <i class="fas fa-expand"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="artwork-glitch"></div>
                                    </div>
                                </div>
                            </div>';
                        }
                    } else {
                        echo '<div class="col-12"><p>Пока нет работ в галерее</p></div>';
                    }
                    ?>
                </div>

                <!-- Пагинация -->
                <div class="row">
                    <div class="col-12">
                        <nav class="gallery-pagination">
                            <ul class="pagination">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
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
                                    <button class="tool-btn"><i class="fas fa-heart"></i> <span id="likeCount">0</span></button>
                                    <button class="tool-btn"><i class="fas fa-bookmark"></i></button>
                                    <button class="tool-btn"><i class="fas fa-share-alt"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="artwork-comments">
                                <div class="comment-form">
                                    <textarea placeholder="Оставьте свой след..." class="comment-input"></textarea>
                                    <button class="comment-submit">Отправить</button>
                                </div>
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
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload"></i> Принести Жертву Искусству</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="submit-form" action="/account/upload.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="artworkTitle" class="form-label">Название Видения</label>
                            <input type="text" class="form-control" id="artworkTitle" name="title" placeholder="Как назовёте свой кошмар?" required>
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Кастомный JS -->
    <script src="js/gallery.js"></script>
    
    <script>
    // Обработка модального окна с деталями работы
    $(document).ready(function() {
        $('#artworkModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var artworkId = button.data('id');
            var modal = $(this);
            
            // Загрузка данных о работе
            $.get('/php/get_artwork.php?id=' + artworkId, function(data) {
                var artwork = JSON.parse(data);
                modal.find('#artworkModalTitle').html('"' + artwork.title + '" <span class="author">@' + artwork.author + '</span>');
                modal.find('#modalArtworkImage').attr('src', '/uploads/artworks/' + artwork.image_path);
                modal.find('#likeCount').text(artwork.views);
                
                // Увеличиваем счетчик просмотров
                $.post('/php/increment_views.php', {id: artworkId});
            });
            
            // Загрузка комментариев
            $.get('/php/get_comments.php?artwork_id=' + artworkId, function(data) {
                $('#commentsList').html(data);
            });
        });
    });
    </script>
</body>
</html>