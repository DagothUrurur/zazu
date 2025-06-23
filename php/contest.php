<?php
require_once __DIR__ . '/db.php';

class Contest {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Создать новый конкурс
    public function createContest($title, $description, $start_date, $end_date, $admin_id) {
        $stmt = $this->conn->prepare("INSERT INTO contests (title, description, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $title, $description, $start_date, $end_date, $admin_id);
        return $stmt->execute();
    }
    public function updateContestStatus($contestId) {
    $now = date('Y-m-d H:i:s');
    $this->conn->query("UPDATE contests SET is_active = 0 WHERE end_date < '$now'");
    $this->conn->query("UPDATE contests SET is_active = 1 WHERE start_date <= '$now' AND end_date >= '$now'");
}
Public function updateContest($id, $title, $description, $startDate, $endDate) {
    $stmt = $this->conn->prepare("UPDATE contests SET 
        title = ?, 
        description = ?, 
        start_date = ?, 
        end_date = ? 
        WHERE id = ?");
    
    $stmt->bind_param("ssssi", 
        $title, 
        $description, 
        $startDate, 
        $endDate, 
        $id);
    
    return $stmt->execute();
}
    // Получить текущий активный конкурс
public function getActiveContest() {
    $now = date('Y-m-d H:i:s');
    $query = "SELECT *, 
              CASE 
                WHEN start_date > '$now' THEN 'pending'
                WHEN end_date < '$now' THEN 'finished'
                ELSE 'active'
              END as status
              FROM contests 
              WHERE is_active = 1
              ORDER BY start_date DESC 
              LIMIT 1";
    
    return $this->conn->query($query)->fetch_assoc();
}
public function getContestArtworksWithVotes($contest_id) {
    $query = "SELECT a.*, u.login as author, 
             COUNT(v.id) as votes_count,
             (SELECT COUNT(*) FROM likes WHERE artwork_id = a.id) as likes_count
             FROM artworks a 
             JOIN users u ON a.user_id = u.id 
             LEFT JOIN votes v ON a.id = v.artwork_id
             WHERE a.contest_id = ? AND a.status = 'approved'
             GROUP BY a.id
             ORDER BY votes_count DESC, likes_count DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $contest_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $artworks = [];
    while ($row = $result->fetch_assoc()) {
        $artworks[] = $row;
    }
    
    return $artworks;
}    
    // Получить последний завершенный конкурс (для отображения победителя)
    public function getLastFinishedContest() {
        $query = "SELECT * FROM contests WHERE end_date < NOW() AND winner_artwork_id IS NOT NULL ORDER BY end_date DESC LIMIT 1";
        $result = $this->conn->query($query);
        return $result->fetch_assoc();
    }
    
    // Установить победителя конкурса
    public function setWinner($contest_id, $artwork_id) {
        $stmt = $this->conn->prepare("UPDATE contests SET winner_artwork_id = ?, is_active = 0 WHERE id = ?");
        $stmt->bind_param("ii", $artwork_id, $contest_id);
        return $stmt->execute();
    }
    
    // Получить все работы для конкурса
    public function getContestArtworks($contest_id) {
        $query = "SELECT a.*, u.login as author, 
                 (SELECT COUNT(*) FROM likes WHERE artwork_id = a.id) as likes_count
                 FROM artworks a 
                 JOIN users u ON a.user_id = u.id 
                 WHERE a.contest_id = ? AND a.status = 'approved'
                 ORDER BY likes_count DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $contest_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Получить все конкурсы (для админки)
    public function getAllContests() {
        $query = "SELECT c.*, 
                 (SELECT COUNT(*) FROM artworks WHERE contest_id = c.id) as artworks_count,
                 u.login as creator
                 FROM contests c
                 JOIN users u ON c.created_by = u.id
                 ORDER BY c.start_date DESC";
        return $this->conn->query($query);
    }
 public function closeExpiredContests() {
    $now = date('Y-m-d H:i:s');
    $this->conn->query("UPDATE contests SET is_active = 0 WHERE end_date < '$now'");
}   
    // Получить информацию о конкурсе по ID
public function getContestById($id) {
    try {
        $stmt = $this->conn->prepare("SELECT id, title, description, 
                                    DATE_FORMAT(start_date, '%Y-%m-%d %H:%i:%s') as start_date,
                                    DATE_FORMAT(end_date, '%Y-%m-%d %H:%i:%s') as end_date
                                    FROM contests WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Ошибка подготовки запроса: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Ошибка выполнения запроса: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Ошибка получения результата: " . $stmt->error);
        }
        
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Ошибка в getContestById: " . $e->getMessage());
        return false;
    }
}
public function determineWinner($contestId) {
    // Находим работу с максимальным количеством голосов
    $winner = $this->conn->query("
        SELECT a.id, COUNT(v.id) as votes_count
        FROM artworks a
        LEFT JOIN votes v ON a.id = v.artwork_id
        WHERE a.contest_id = $contestId
        GROUP BY a.id
        ORDER BY votes_count DESC
        LIMIT 1
    ")->fetch_assoc();

    if ($winner) {
        $this->conn->query("UPDATE contests SET winner_artwork_id = {$winner['id']} WHERE id = $contestId");
        return true;
    }
    
    return false;
}
}
?>