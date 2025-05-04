<?php
session_start();
require_once('../php/db.php');

$login = $_POST['login'];
$password = $_POST['password'];

// Подготовленный запрос для защиты от SQL-инъекций
$stmt = $conn->prepare("SELECT id, login, role FROM `users` WHERE login = ? AND password = ?");
$stmt->bind_param("ss", $login, $password);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Устанавливаем сессию
    $_SESSION['user_id'] = $row["id"];
    $_SESSION['user_login'] = $row["login"];
    $_SESSION['user_role'] = $row["role"];
    
    // Перенаправляем в зависимости от роли
    if ($_SESSION["user_role"] === "admin") {
        header("Location: ../admin/admin.php");
    } else {
        header("Location: ../account/index.php");
    }
    exit;
} else {
    header("Location: login.php?error=Неверные имя пользователя или пароль");
    exit;
}
?>