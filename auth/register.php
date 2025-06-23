<?php
session_start();
if(isset($_SESSION['users_id'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клятва Тени | Oblivion Scriptorium</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Основные стили -->
    <link rel="stylesheet" href="../style.css">
    <!-- Стили для авторизации -->
    <link rel="stylesheet" href="styles/auth.css">
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
        <div class="silhouette silhouette-3"></div>
    </div>

    <!-- Основной контент -->
    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="../img/logo-sm.png" alt="Лого">
                <h1>Клятва Тени</h1>
                <p>Станьте частью забытого знания</p>
            </div>

            <?php if(isset($_GET['error'])): ?>
                <div class="auth-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="register_handler.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="login"><i class="fas fa-user-secret"></i> Изберите тайное имя</label>
                    <input type="text" id="login" name="login" required placeholder="Придумайте логин">
                    <div class="input-underline"></div>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Знак связи</label>
                    <input type="email" id="email" name="email" required placeholder="Введите ваш email">
                    <div class="input-underline"></div>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Создайте знак причастия</label>
                    <input type="password" id="password" name="password" required placeholder="Придумайте пароль">
                    <div class="input-underline"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Повторите знак</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Повторите пароль">
                    <div class="input-underline"></div>
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-hand-holding-water"></i> Принести Клятву
                </button>

                <div class="auth-links">
                    <a href="login.php">Уже приняли клятву? <span>Войдите в Тень</span></a>
                    <a href="../index.php">Вернуться в Тьму</a>
                </div>
            </form>
        </div>
    </main>

    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Кастомный JS -->
    <script src="../js/auth.js"></script>
</body>
</html>