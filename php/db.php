<?php
$servername = 'localhost';
$name = "root";
$password = "";
$dbname = "Oblivion";
$conn = new mysqli($servername, $name, $password, $dbname);
// Проверяем соединение
if ($conn->connect_error) {
    die("Ошибка подключения к MySQL: " . $conn->connect_error);
}

// Устанавливаем кодировку
$conn->set_charset("utf8mb4");
?>