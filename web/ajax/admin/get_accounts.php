<?php
header('Content-Type: application/json; charset=utf-8');

// Tắt hiển thị lỗi để tránh HTML
error_reporting(0);
ini_set('display_errors', 0);

// Kết nối database trực tiếp
try {
    $host = getenv('DB_HOST') ?: 'database';
    $dbname = getenv('DB_NAME') ?: 'team2026';
    $username = getenv('DB_USER') ?: 'teamobi';
    $password = getenv('DB_PASSWORD') ?: 'change-me';
    
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => 'Lỗi kết nối database: ' . $e->getMessage()]);
    exit();
}

try {
    // Tạo bảng cvh_sessions nếu chưa tồn tại
    $createTableSql = "CREATE TABLE IF NOT EXISTS `cvh_sessions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `token` varchar(128) NOT NULL,
        `expires_at` datetime NOT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `token` (`token`),
        KEY `user_id` (`user_id`),
        KEY `expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($createTableSql);
    
    // Lấy tham số phân trang
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20; // Giới hạn 20 phiên mỗi trang
    $offset = ($page - 1) * $limit;
    
    // Truy vấn bảng cvh_sessions và join với account để lấy thông tin user
    $query = "SELECT s.id, s.user_id, s.token, s.expires_at, s.created_at, 
                     a.username, a.is_admin, a.ban
              FROM cvh_sessions s 
              LEFT JOIN account a ON s.user_id = a.id 
              WHERE s.expires_at > NOW() 
              ORDER BY s.created_at DESC 
              LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Lỗi truy vấn database: " . $conn->error);
    }
    
    $sessionsData = [];
    while ($row = $result->fetch_assoc()) {
        // Tính thời gian còn lại của session
        $expiresAt = new DateTime($row['expires_at']);
        $now = new DateTime();
        $timeLeft = $now->diff($expiresAt);
        
        $timeLeftStr = '';
        if ($timeLeft->days > 0) {
            $timeLeftStr = $timeLeft->days . ' ngày ' . $timeLeft->h . ' giờ';
        } elseif ($timeLeft->h > 0) {
            $timeLeftStr = $timeLeft->h . ' giờ ' . $timeLeft->i . ' phút';
        } else {
            $timeLeftStr = $timeLeft->i . ' phút';
        }
        
        // Kiểm tra trạng thái session
        $status = 'active';
        $statusText = 'Hoạt động';
        $statusColor = 'success';
        
        if ($row['ban'] == '1') {
            $status = 'banned';
            $statusText = 'Bị cấm';
            $statusColor = 'danger';
        } elseif ($expiresAt < $now) {
            $status = 'expired';
            $statusText = 'Hết hạn';
            $statusColor = 'warning';
        }
        
        $sessionsData[] = [
            'session_id' => (int)$row['id'],
            'user_id' => (int)$row['user_id'],
            'username' => htmlspecialchars($row['username'] ?? 'Unknown'),
            'is_admin' => (bool)$row['is_admin'],
            'status' => $status,
            'status_text' => $statusText,
            'status_color' => $statusColor,
            'token_preview' => substr($row['token'], 0, 20) . '...',
            'created_at' => $row['created_at'],
            'expires_at' => $row['expires_at'],
            'time_left' => $timeLeftStr,
            'is_expired' => $expiresAt < $now
        ];
    }
    
    // Thống kê chi tiết
    $statsQuery = "SELECT 
        COUNT(*) as total_sessions,
        COUNT(CASE WHEN expires_at > NOW() THEN 1 END) as active_sessions,
        COUNT(CASE WHEN expires_at <= NOW() THEN 1 END) as expired_sessions,
        COUNT(DISTINCT user_id) as unique_users,
        COUNT(DISTINCT CASE WHEN expires_at > NOW() THEN user_id END) as online_users,
        COUNT(CASE WHEN expires_at > NOW() AND a.is_admin = 1 THEN 1 END) as admin_sessions
        FROM cvh_sessions s
        LEFT JOIN account a ON s.user_id = a.id";
    
    $statsResult = $conn->query($statsQuery);
    if (!$statsResult) {
        throw new Exception("Lỗi truy vấn thống kê: " . $conn->error);
    }
    $stats = $statsResult->fetch_assoc();
    
    // Tính tổng số phiên hoạt động để phân trang
    $totalActiveQuery = "SELECT COUNT(*) as total FROM cvh_sessions s 
                        LEFT JOIN account a ON s.user_id = a.id 
                        WHERE s.expires_at > NOW()";
    $totalActiveResult = $conn->query($totalActiveQuery);
    $totalActive = $totalActiveResult->fetch_assoc()['total'];
    
    $totalPages = ceil($totalActive / $limit);
    
    echo json_encode([
        'status' => true,
        'message' => 'Lấy danh sách phiên đăng nhập thành công',
        'sessions' => $sessionsData,
        'total' => count($sessionsData),
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'limit' => $limit,
            'total_records' => $totalActive
        ],
        'stats' => [
            'total_sessions' => (int)$stats['total_sessions'],
            'active_sessions' => (int)$stats['active_sessions'],
            'expired_sessions' => (int)$stats['expired_sessions'],
            'unique_users' => (int)$stats['unique_users'],
            'online_users' => (int)$stats['online_users'],
            'admin_sessions' => (int)$stats['admin_sessions']
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}
?> 
