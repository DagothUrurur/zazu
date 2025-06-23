<?php
session_start();
require_once '../php/db.php'; // Ваш файл с подключением MySQLi

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Обработка смены аватара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'avatar') {
    if (empty($_FILES['avatar']['name'])) {
        $message = '<div class="alert alert-danger">Файл не выбран</div>';
    } else {
        // Проверка типа файла
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['avatar']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $message = '<div class="alert alert-danger">Только JPG, PNG или GIF разрешены.</div>';
        } else {
            // Проверка размера (макс. 5MB)
            $max_size = 5 * 1024 * 1024;
            if ($_FILES['avatar']['size'] > $max_size) {
                $message = '<div class="alert alert-danger">Файл слишком большой. Максимум 5MB.</div>';
            } else {
                // Создаем папку для аватаров, если её нет
                $upload_dir = '../uploads/avatars/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Генерируем уникальное имя файла
                $file_ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_ext;
                $file_path = $upload_dir . $file_name;

                // Пытаемся сохранить файл
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $file_path)) {
                    // Получаем старый аватар
                    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->bind_result($old_avatar);
                    $stmt->fetch();
                    $stmt->close();

                    // Обновляем аватар в БД
                    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $stmt->bind_param("si", $file_name, $user_id);
                    $stmt->execute();

                    // Удаляем старый аватар (если это не дефолтный)
                    if ($old_avatar && $old_avatar !== 'default-avatar.jpg') {
                        @unlink($upload_dir . $old_avatar);
                    }

                    $message = '<div class="alert alert-success">Аватар успешно обновлён</div>';
                } else {
                    $message = '<div class="alert alert-danger">Ошибка загрузки файла.</div>';
                }
            }
        }
    }
}

// Обработка обновления профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'profile') {
    $bio = trim($_POST['bio'] ?? '');
    
    $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
    $stmt->bind_param("si", $bio, $user_id);
    $stmt->execute();
    
    $message = '<div class="alert alert-success">Профиль успешно обновлён</div>';
}

// Получаем данные пользователя
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки | Oblivion Scriptorium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        <a href="/index.php" class="nav-link">Главная</a>
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
                            
                            <div class="profile-stats">
                                <div class="stat-item">
                                    <span class="stat-number">0</span>
                                    <span class="stat-label">Работ</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">0</span>
                                    <span class="stat-label">Лайков</span>
                                </div>
                            </div>
                        </div>
                        
                        <nav class="account-menu">
                            <a href="/account/" class="menu-item">
                                <i class="fas fa-home"></i> Мои работы
                            </a>
                            <a href="/account/settings.php" class="menu-item active">
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
                            <i class="fas fa-cog"></i> Настройки Проклятия
                        </h1>
                        
                        <?= $message ?>
                        
                        <div class="settings-tabs">
                            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Профиль</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">Безопасность</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="settingsTabsContent">
                                <!-- Вкладка профиля -->
                                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                                    <form method="post" action="?action=profile">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Имя пользователя</label>
                                            <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['login']) ?>" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label for="bio" class="form-label">Биография</label>
                                            <textarea class="form-control" id="bio" name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                        </div>
                                        <div class="submit-actions">
                                            <button type="submit" class="submit-btn">Сохранить Изменения</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Вкладка безопасности -->
                                <div class="tab-pane fade" id="security" role="tabpanel">
                                    <form method="post" action="?action=password">
                                        <div class="mb-3">
                                            <label for="currentPassword" class="form-label">Текущий пароль</label>
                                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="newPassword" class="form-label">Новый пароль</label>
                                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirmPassword" class="form-label">Подтвердите пароль</label>
                                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                                        </div>
                                        <div class="submit-actions">
                                            <button type="submit" class="submit-btn">Изменить Пароль</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Модальное окно смены аватара -->
    <div class="modal fade" id="avatarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit"></i> Смена Аватара</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="?action=avatar" enctype="multipart/form-data">
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
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Кастомный JS -->
    <script src="/account/js/account.js"></script>
</body>
</html>