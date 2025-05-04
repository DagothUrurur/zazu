<?php
session_start();
require_once '../php/db.php'; // Ваш текущий файл с mysqli

if (!isset($_SESSION['user_id'])) {
    die('Доступ запрещен.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Неправильный метод запроса.');
}

// Проверяем загружен ли файл
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die('Ошибка загрузки файла.');
}

// Проверяем тип файла
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['image']['type'], $allowed_types)) {
    die('Только JPG, PNG или GIF разрешены.');
}

// Проверяем размер (макс. 10MB)
$max_size = 10 * 1024 * 1024;
if ($_FILES['image']['size'] > $max_size) {
    die('Файл слишком большой. Максимум 10MB.');
}

// Создаем папку для загрузки, если её нет
$upload_dir = '../uploads/artworks/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Генерируем уникальное имя файла
$file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$file_name = uniqid() . '.' . $file_ext;
$file_path = $upload_dir . $file_name;

// Пытаемся сохранить файл
if (!move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
    die('Ошибка при сохранении файла.');
}

// Сохраняем данные в БД
$title = $_POST['title'] ?? 'Без названия';
$description = $_POST['description'] ?? '';
$is_contest = isset($_POST['is_contest']) ? 1 : 0;
$user_id = $_SESSION['user_id'];

// ЗАМЕНЯЕМ ЭТОТ БЛОК НА НОВЫЙ ЗАПРОС С СТАТУСОМ МОДЕРАЦИИ
$stmt = $conn->prepare("
    INSERT INTO artworks (user_id, title, description, image_path, is_contest, status) 
    VALUES (?, ?, ?, ?, ?, 'pending')
");
$stmt->bind_param("isssi", $user_id, $title, $description, $file_name, $is_contest);
// КОНЕЦ ЗАМЕНЫ

$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Отправляем уведомление администратору (дополнительно потом доделаю бибабоба)
    $admin_email = 'admin@example.com'; // Замените на реальный email
    $subject = 'Новая работа на модерацию';
    $message = "Пользователь ID: $user_id загрузил новую работу.\n";
    $message .= "Название: $title\n";
    $message .= "Описание: $description\n";
    $message .= "Ссылка на модерацию: http://вашсайт.ru/admin/moderation.php";
    mail($admin_email, $subject, $message);

    echo json_encode(['success' => true, 'message' => 'Работа успешно загружена и отправлена на модерацию!']);
    header('Location: ./account.php');
} else {
    // Удаляем файл, если запись в БД не удалась
    unlink($file_path);
    die('Ошибка при сохранении в базу данных.');
}
?>