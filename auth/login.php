<?php
session_start();
if(isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === "user"){
    header("Location: ../account/index.php");
    exit;
    } else{
    header("Location: ../admin/admin.php");
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в Тень | Oblivion Scriptorium</title>
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
        <div class="silhouette silhouette-2"></div>
    </div>

    <!-- Основной контент -->
    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="../img/logo-sm.png" alt="Лого">
                <h1>Вход в Тень</h1>
                <p>Только избранные могут пройти</p>
            </div>

            <?php if(isset($_GET['error'])): ?>
                <div class="auth-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="auth.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="login"><i class="fas fa-user-secret"></i> Тайное имя</label>
                    <input type="text" id="login" name="login" required placeholder="Введите ваш логин">
                    <div class="input-underline"></div>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Знак причастия</label>
                    <input type="password" id="password" name="password" required placeholder="Введите ваш пароль">
                    <div class="input-underline"></div>
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-door-open"></i> Переступить Порог
                </button>

                <div class="auth-links">
                    <a href="register.php">Нет печати? <span>Примите клятву</span></a>
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