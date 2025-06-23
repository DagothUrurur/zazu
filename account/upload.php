<?php
session_start();
require_once '../php/db.php';
require_once '../php/contest.php'; // ДОБАВЛЯЕМ ПОДКЛЮЧЕНИЕ КЛАССА КОНКУРСОВ

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Доступ запрещен']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['error' => 'Неправильный метод запроса']));
}

// 1. ПРОВЕРКА ФАЙЛА (ОСТАВЛЯЕМ БЕЗ ИЗМЕНЕНИЙ)
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die(json_encode(['error' => 'Ошибка загрузки файла']));
}

$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['image']['type'], $allowed_types)) {
    die(json_encode(['error' => 'Только JPG, PNG или GIF разрешены']));
}

$max_size = 10 * 1024 * 1024;
if ($_FILES['image']['size'] > $max_size) {
    die(json_encode(['error' => 'Файл слишком большой. Максимум 10MB']));
}

$upload_dir = '../uploads/artworks/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$file_name = uniqid() . '.' . $file_ext;
$file_path = $upload_dir . $file_name;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
    die(json_encode(['error' => 'Ошибка при сохранении файла']));
}

// 2. ОБРАБОТКА ДАННЫХ ФОРМЫ (ОБНОВЛЯЕМ ЭТОТ БЛОК)
$title = $_POST['title'] ?? 'Без названия';
$description = $_POST['description'] ?? '';
$user_id = $_SESSION['user_id'];

// Определяем участие в конкурсе
$contestManager = new Contest($conn);
$contest_id = null;
$is_contest = 0;

if (isset($_POST['join_contest']) && $_POST['join_contest'] == 'on') {
    $activeContest = $contestManager->getActiveContest();
    if ($activeContest) {
        $contest_id = $activeContest['id'];
        $is_contest = 1;
    }
}

// 3. СОХРАНЕНИЕ В БАЗУ (ПОЛНОСТЬЮ ЗАМЕНЯЕМ ЭТОТ БЛОК)
$stmt = $conn->prepare("
    INSERT INTO artworks 
    (user_id, title, description, image_path, is_contest, contest_id, status) 
    VALUES (?, ?, ?, ?, ?, ?, 'pending')
");
$stmt->bind_param("isssii", $user_id, $title, $description, $file_name, $is_contest, $contest_id);

if ($stmt->execute()) {
    $response = [
        'success' => true,
        'message' => $is_contest 
            ? 'Работа загружена и участвует в конкурсе!' 
            : 'Работа загружена в галерею!',
        'is_contest' => $is_contest
    ];
    
    // Уведомление администратору (опционально)
    if ($is_contest) {
        $admin_msg = "Новая конкурсная работа: " . $title;
        // Здесь можно добавить отправку email или запись в лог
    }
    
    echo json_encode($response);
} else {
    unlink($file_path);
    die(json_encode(['error' => 'Ошибка базы данных']));
}
?>