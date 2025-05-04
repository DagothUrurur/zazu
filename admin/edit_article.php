<?php
session_start();
require_once('../php/db.php');



$article = null;
$images = [];

// Редактирование существующей статьи
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result->fetch_assoc();
    
    // Получаем изображения для галереи
    $img_stmt = $conn->prepare("SELECT * FROM article_images WHERE article_id = ? ORDER BY sort_order");
    $img_stmt->bind_param("i", $id);
    $img_stmt->execute();
    $images = $img_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $text = $conn->real_escape_string($_POST['text']);
    $category = $conn->real_escape_string($_POST['category']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Обработка загрузки главного изображения
    $img_path = $article['img'] ?? '';
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../img/articles/';
        $file_name = uniqid() . '_' . basename($_FILES['img']['name']);
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['img']['tmp_name'], $target_path)) {
            $img_path = 'img/articles/' . $file_name;
            // Удаляем старое изображение, если оно есть
            if (isset($article['img']) && file_exists('../' . $article['img'])) {
                unlink('../' . $article['img']);
            }
        }
    }
    
    if ($article) {
        // Обновление существующей статьи
        $stmt = $conn->prepare("UPDATE articles SET name = ?, description = ?, img = ?, text = ?, category = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssssssi", $name, $description, $img_path, $text, $category, $status, $article['id']);
    } else {
        // Создание новой статьи
        $stmt = $conn->prepare("INSERT INTO articles (name, description, img, text, category, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssssss", $name, $description, $img_path, $text, $category, $status);
    }
    
    if ($stmt->execute()) {
        $article_id = $article ? $article['id'] : $stmt->insert_id;
        
        // Обработка галереи изображений
        if (!empty($_POST['gallery'])) {
            // Удаляем старые изображения
            $conn->query("DELETE FROM article_images WHERE article_id = $article_id");
            
            foreach ($_POST['gallery'] as $image) {
                if (!empty($image['path'])) {
                    $stmt_img = $conn->prepare("INSERT INTO article_images (article_id, image_path, caption, sort_order) VALUES (?, ?, ?, ?)");
                    $stmt_img->bind_param("issi", $article_id, $image['path'], $image['caption'], $image['order']);
                    $stmt_img->execute();
                }
            }
        }
        
        // Обработка загрузки новых изображений для галереи
        if (!empty($_FILES['gallery_images'])) {
            $upload_dir = '../img/gallery/';
            
            foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = uniqid() . '_' . basename($_FILES['gallery_images']['name'][$key]);
                    $target_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($tmp_name, $target_path)) {
                        $img_path = 'img/gallery/' . $file_name;
                        $caption = $_POST['gallery_captions'][$key] ?? '';
                        
                        $stmt_img = $conn->prepare("INSERT INTO article_images (article_id, image_path, caption, sort_order) VALUES (?, ?, ?, ?)");
                        $order = $conn->query("SELECT IFNULL(MAX(sort_order), 0) + 1 FROM article_images WHERE article_id = $article_id")->fetch_row()[0];
                        $stmt_img->bind_param("issi", $article_id, $img_path, $caption, $order);
                        $stmt_img->execute();
                    }
                }
            }
        }
        
        $_SESSION['message'] = 'Статья успешно ' . ($article ? 'обновлена' : 'создана');
        header("Location: admin.php");
        exit;
    } else {
        $error = "Ошибка при сохранении статьи: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Темные Архивы | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>
<body class="admin-body">
<!-- Туманный фон -->
<div class="admin-fog-container">
        <div class="admin-fog-layer admin-fog-layer-1"></div>
        <div class="admin-fog-layer admin-fog-layer-2"></div>
    </div>

    <!-- Силуэты -->
    <div class="admin-silhouettes">
        <div class="admin-silhouette admin-silhouette-1"></div>
        <div class="admin-silhouette admin-silhouette-2"></div>
    </div>

    <main class="admin-main">
        <header class="admin-header">
            <div class="admin-header-left">
                <button class="admin-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="admin-page-title">
                    <?= $article ? 'Редактирование статьи' : 'Новая статья'; ?>
                </h1>
            </div>
            <div class="admin-header-right">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="admin-message">
                        <?= $_SESSION['message']; ?>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="admin-error">
                        <?= $error; ?>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <div class="admin-content">
            <form method="POST" enctype="multipart/form-data" class="admin-article-form">
                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="name">Название статьи</label>
                        <input type="text" id="name" name="name" required 
                               value="<?= htmlspecialchars($article['name'] ?? ''); ?>">
                    </div>
                    
                    <div class="admin-form-group">
                        <label for="category">Категория</label>
                        <select id="category" name="category" required>
                            <option value="software" <?= ($article['category'] ?? '') === 'software' ? 'selected' : ''; ?>>Графические редакторы</option>
                            <option value="brushes" <?= ($article['category'] ?? '') === 'brushes' ? 'selected' : ''; ?>>Кисти</option>
                            <option value="tutorials" <?= ($article['category'] ?? '') === 'tutorials' ? 'selected' : ''; ?>>Туториалы</option>
                            <option value="theory" <?= ($article['category'] ?? '') === 'theory' ? 'selected' : ''; ?>>Теория</option>
                        </select>
                    </div>
                    
                    <div class="admin-form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status" required>
                            <option value="published" <?= ($article['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                            <option value="draft" <?= ($article['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                            <option value="archived" <?= ($article['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>В архиве</option>
                        </select>
                    </div>
                </div>
                
                <div class="admin-form-group">
                    <label for="description">Краткое описание</label>
                    <textarea id="description" name="description" rows="3" required><?= htmlspecialchars($article['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="admin-form-group">
                    <label>Главное изображение</label>
                    <?php if (!empty($article['img'])): ?>
                        <div class="admin-current-image">
                            <img src="../<?= $article['img']; ?>" alt="Текущее изображение">
                            <span>Текущее изображение: <?= basename($article['img']); ?></span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="img" accept="image/*">
                </div>
                
                <div class="admin-form-group">
                    <label for="text">Текст статьи</label>
                    <textarea id="text" name="text"><?= htmlspecialchars($article['text'] ?? ''); ?></textarea>
                    <script>
                        CKEDITOR.replace('text', {
                            height: 400,
                            toolbar: [
                                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
                                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote'] },
                                { name: 'links', items: ['Link', 'Unlink'] },
                                { name: 'insert', items: ['Image', 'Table'] },
                                { name: 'styles', items: ['Styles', 'Format'] },
                                { name: 'document', items: ['Source'] }
                            ]
                        });
                    </script>
                </div>
                
                <!-- Галерея изображений -->
                <div class="admin-form-group">
                    <label>Галерея изображений</label>
                    <div id="galleryContainer" class="admin-gallery-container">
                        <?php foreach ($images as $image): ?>
                            <div class="admin-gallery-item">
                                <div class="admin-gallery-preview">
                                    <img src="../<?= $image['image_path']; ?>" alt="Изображение галереи">
                                    <button type="button" class="admin-gallery-remove" onclick="removeGalleryItem(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="gallery[<?= $image['id']; ?>][path]" value="<?= $image['image_path']; ?>">
                                <input type="text" name="gallery[<?= $image['id']; ?>][caption]" placeholder="Подпись" 
                                       value="<?= htmlspecialchars($image['caption']); ?>">
                                <input type="number" name="gallery[<?= $image['id']; ?>][order]" placeholder="Порядок" 
                                       value="<?= $image['sort_order']; ?>" min="1">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="admin-gallery-upload">
                        <label>Добавить изображения в галерею:</label>
                        <input type="file" name="gallery_images[]" multiple accept="image/*" id="galleryUpload">
                        <div id="galleryUploadPreview"></div>
                    </div>
                </div>
                
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-primary-btn">
                        <i class="fas fa-save"></i> Сохранить
                    </button>
                    <a href="admin.php" class="admin-btn admin-cancel-btn">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script src="../js/admin_article.js"></script>
</body>
</html>