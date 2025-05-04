<?php
session_start();
require_once('../php/db.php');

$login = $_POST['login'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Валидация
if($password !== $confirm_password) {
    header("Location: register.php?error=Пароли не совпадают");
    exit;
}

if(strlen($password) < 6) {
    header("Location: register.php?error=Пароль должен быть не менее 6 символов");
    exit;
}

// Проверка на существующего пользователя
$check_stmt = $conn->prepare("SELECT id FROM `users` WHERE login = ? OR email = ?");
$check_stmt->bind_param("ss", $login, $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if($check_result->num_rows > 0) {
    header("Location: register.php?error=Пользователь с таким логином или email уже существует");
    exit;
}

// Хеширование пароля (в реальном проекте обязательно!)
// $hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Создание пользователя
$stmt = $conn->prepare("INSERT INTO users (login, email, password, role) VALUES (?, ?, ?, 'user')");
$stmt->bind_param("sss", $login, $email, $password);

if($stmt->execute()) {
    $user_id = $stmt->insert_id;
    
    // Автоматический вход после регистрации
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_login'] = $login;
    $_SESSION['user_role'] = 'user';
    
    header("Location: ../index.php");
    exit;
} else {
    header("Location: register.php?error=Ошибка при создании пользователя");
    exit;
}
?>