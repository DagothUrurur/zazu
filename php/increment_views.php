<?php
require_once 'db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if($id > 0) {
        $conn->query("UPDATE artworks SET views = views + 1 WHERE id = $id");
    }
}
?>