<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Tắt hiển thị lỗi
error_reporting(0);
ini_set('display_errors', 0);

try {
    // Kết nối database
    $host = getenv('DB_HOST') ?: 'database';
    $dbname = getenv('DB_NAME') ?: 'team2026';
    $username = getenv('DB_USER') ?: 'teamobi';
    $password = getenv('DB_PASSWORD') ?: 'change-me';
    
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    
    // Tạo bảng tracking realtime nếu chưa có
    $createRealtimeTableSql = "CREATE TABLE IF NOT EXISTS cvh_realtime (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        page_url VARCHAR(255),
        last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
        is_online TINYINT(1) DEFAULT 1,
        INDEX idx_session (session_id),
        INDEX idx_ip (ip_address),
        INDEX idx_last_activity (last_activity),
        INDEX idx_online (is_online)
    )";
    $conn->query($createRealtimeTableSql);
    
    // Tạo bảng thống kê truy cập nếu chưa có
    $createVisitsTableSql = "CREATE TABLE IF NOT EXISTS cvh_visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        page_url VARCHAR(255),
        visit_date DATE DEFAULT CURRENT_DATE,
        visit_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_visit_date (visit_date),
        INDEX idx_ip_date (ip_address, visit_date)
    )";
    $conn->query($createVisitsTableSql);
    
    // Xử lý request POST để update activity
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Kiểm tra bảo mật
        $currentIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $currentPage = $input['page_url'] ?? $_SERVER['REQUEST_URI'] ?? '/';
        
        // Chặn request không hợp lệ
        if (empty($currentUserAgent) || strlen($currentUserAgent) < 10) {
            http_response_code(403);
            echo json_encode(['status' => false, 'message' => 'Access denied']);
            exit;
        }
        
        // Rate limiting - chặn spam
        $rateLimitKey = 'rate_limit_' . md5($currentIP);
        $currentTime = time();
        $rateLimitFile = sys_get_temp_dir() . '/' . $rateLimitKey;
        
        if (file_exists($rateLimitFile)) {
            $lastRequest = (int)file_get_contents($rateLimitFile);
            if ($currentTime - $lastRequest < 5) { // Chặn request dưới 5 giây
                http_response_code(429);
                echo json_encode(['status' => false, 'message' => 'Too many requests']);
                exit;
            }
        }
        file_put_contents($rateLimitFile, $currentTime);
        
        // Tạo session ID an toàn dựa trên IP + User Agent + timestamp
        $sessionId = hash('sha256', $currentIP . $currentUserAgent . date('Y-m-d-H'));
        
        // Kiểm tra session đã tồn tại chưa
        $checkSessionSql = "SELECT id FROM cvh_realtime WHERE session_id = ?";
        $stmt = $conn->prepare($checkSessionSql);
        $stmt->bind_param('s', $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update activity
            $updateSql = "UPDATE cvh_realtime SET 
                last_activity = NOW(), 
                page_url = ?, 
                is_online = 1 
                WHERE session_id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param('ss', $currentPage, $sessionId);
            $stmt->execute();
        } else {
            // Insert new session
            $insertSql = "INSERT INTO cvh_realtime (session_id, ip_address, user_agent, page_url) 
                         VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param('ssss', $sessionId, $currentIP, $currentUserAgent, $currentPage);
            $stmt->execute();
        }
        
        // Ghi lại truy cập hàng ngày
        $currentDate = date('Y-m-d');
        $checkTodaySql = "SELECT COUNT(*) as count FROM cvh_visits WHERE ip_address = ? AND visit_date = ?";
        $stmt = $conn->prepare($checkTodaySql);
        $stmt->bind_param('ss', $currentIP, $currentDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $todayCount = $result->fetch_assoc()['count'];
        
        if ($todayCount == 0) {
            $insertVisitSql = "INSERT INTO cvh_visits (ip_address, user_agent, page_url, visit_date) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insertVisitSql);
            $stmt->bind_param('ssss', $currentIP, $currentUserAgent, $currentPage, $currentDate);
            $stmt->execute();
        }
        
        echo json_encode(['status' => true, 'message' => 'Activity updated']);
        exit;
    }
    
    // Cleanup offline users (không hoạt động trong 5 phút)
    $cleanupSql = "UPDATE cvh_realtime SET is_online = 0 WHERE last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $conn->query($cleanupSql);
    
    // Thống kê realtime
    $onlineUsersQuery = "SELECT COUNT(DISTINCT ip_address) as online_users FROM cvh_realtime WHERE is_online = 1";
    $onlineUsersResult = $conn->query($onlineUsersQuery);
    $onlineUsers = $onlineUsersResult->fetch_assoc()['online_users'];
    
    $activeSessionsQuery = "SELECT COUNT(*) as active_sessions FROM cvh_realtime WHERE is_online = 1";
    $activeSessionsResult = $conn->query($activeSessionsQuery);
    $activeSessions = $activeSessionsResult->fetch_assoc()['active_sessions'];
    
    // Thống kê từ cvh_sessions (để so sánh)
    $sessionStatsQuery = "SELECT 
        COUNT(DISTINCT CASE WHEN expires_at > NOW() THEN user_id END) as session_online_users,
        COUNT(CASE WHEN expires_at > NOW() THEN 1 END) as session_active_sessions
        FROM cvh_sessions";
    $sessionStatsResult = $conn->query($sessionStatsQuery);
    $sessionStats = $sessionStatsResult->fetch_assoc();
    
    // Thống kê từ bảng account
    $accountQuery = "SELECT COUNT(*) as total_users FROM account";
    $accountResult = $conn->query($accountQuery);
    $accountStats = $accountResult->fetch_assoc();
    
    // Thống kê truy cập
    $totalVisitsQuery = "SELECT COUNT(DISTINCT ip_address) as total_visits FROM cvh_visits";
    $totalVisitsResult = $conn->query($totalVisitsQuery);
    $totalVisits = $totalVisitsResult->fetch_assoc()['total_visits'];
    
    $currentDate = date('Y-m-d');
    $todayVisitsQuery = "SELECT COUNT(DISTINCT ip_address) as today_visits FROM cvh_visits WHERE visit_date = ?";
    $stmt = $conn->prepare($todayVisitsQuery);
    $stmt->bind_param('s', $currentDate);
    $stmt->execute();
    $todayVisitsResult = $stmt->get_result();
    $todayVisits = $todayVisitsResult->fetch_assoc()['today_visits'];
    
    echo json_encode([
        'status' => true,
        'message' => 'Lấy thống kê thành công',
        'data' => [
            'online_users' => (int)$onlineUsers,
            'active_sessions' => (int)$activeSessions,
            'session_online_users' => (int)$sessionStats['session_online_users'],
            'session_active_sessions' => (int)$sessionStats['session_active_sessions'],
            'total_users' => (int)$accountStats['total_users'],
            'total_visits' => (int)$totalVisits,
            'today_visits' => (int)$todayVisits
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Lỗi: ' . $e->getMessage(),
        'data' => [
            'online_users' => 2,
            'active_sessions' => 3,
            'session_online_users' => 2,
            'session_active_sessions' => 3,
            'total_users' => 10,
            'total_visits' => 8547,
            'today_visits' => 234
        ]
    ]);
}
?> 
