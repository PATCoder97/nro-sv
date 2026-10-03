<?php
// Tắt output buffering để tránh HTML output
if (ob_get_level()) {
    ob_end_clean();
}
header('Content-Type: application/json');

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
    
    // Kiểm tra xem autoload có thành công không
    if (!isset($CVH) || !isset($user)) {
        throw new Exception('Autoload failed - CVH or user not found');
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    exit();
}

// Kiểm tra quyền admin
if (!$user['is_admin']) {
    echo json_encode(['status' => false, 'message' => 'Bạn không có quyền truy cập!']);
    exit();
}

// Kiểm tra quyền IP Manager
if (!$user['is_super_admin'] && empty($user['perm_ip_manager'])) {
    echo json_encode(['status' => false, 'message' => 'Bạn không có quyền quản lý IP']);
    exit();
}

// Kiểm tra CSRF token
if (!isset($_POST['csrf_token']) || !$CVH->validateCSRFToken($user['id'], $_POST['csrf_token'])) {
    echo json_encode(['status' => false, 'message' => 'Token không hợp lệ!']);
    exit();
}

$ip = trim($_POST['ip'] ?? '');
$action = trim($_POST['action'] ?? '');

if (empty($ip)) {
    echo json_encode(['status' => false, 'message' => 'IP không hợp lệ!']);
    exit();
}

if (!in_array($action, ['ban', 'unban'])) {
    echo json_encode(['status' => false, 'message' => 'Hành động không hợp lệ!']);
    exit();
}

// Kiểm tra IP có tồn tại không
$check_query = mysqli_query($CVH->connect_db(), "
    SELECT COUNT(*) as count 
    FROM account 
    WHERE ip_address = '" . mysqli_real_escape_string($CVH->connect_db(), $ip) . "'
");

$check_result = mysqli_fetch_assoc($check_query);
if ($check_result['count'] == 0) {
    echo json_encode([
        'status' => false, 
        'message' => 'Không tìm thấy tài khoản nào sử dụng IP ' . $ip . '! Có thể IP này chưa được lưu trong database hoặc tài khoản chưa đăng nhập.'
    ]);
    exit();
}

// Thực hiện ban/unban
$ban_value = ($action == 'ban') ? 1 : 0;
$ban_text = ($action == 'ban') ? 'ban' : 'unban';

$update_query = mysqli_query($CVH->connect_db(), "
    UPDATE account 
    SET ban = $ban_value 
    WHERE ip_address = '" . mysqli_real_escape_string($CVH->connect_db(), $ip) . "'
");

if ($update_query) {
    // Đếm số tài khoản thực tế bị ảnh hưởng
    $count_query = mysqli_query($CVH->connect_db(), "
        SELECT COUNT(*) as count 
        FROM account 
        WHERE ip_address = '" . mysqli_real_escape_string($CVH->connect_db(), $ip) . "'
    ");
    $count_result = mysqli_fetch_assoc($count_query);
    $total_accounts = $count_result['count'];
    
    // Đếm số tài khoản với trạng thái ban mới
    $ban_count_query = mysqli_query($CVH->connect_db(), "
        SELECT COUNT(*) as count 
        FROM account 
        WHERE ip_address = '" . mysqli_real_escape_string($CVH->connect_db(), $ip) . "' 
        AND ban = $ban_value
    ");
    $ban_count_result = mysqli_fetch_assoc($ban_count_query);
    $affected_rows = $ban_count_result['count'];
    
    // Log hành động (nếu bảng admin_log tồn tại)
    try {
        $log_query = mysqli_query($CVH->connect_db(), "
            INSERT INTO admin_log (admin_id, admin_username, action, target, details, created_at) 
            VALUES (
                " . intval($user['id']) . ", 
                '" . mysqli_real_escape_string($CVH->connect_db(), $user['username']) . "', 
                'ip_" . $ban_text . "', 
                'IP: " . mysqli_real_escape_string($CVH->connect_db(), $ip) . "', 
                'Đã " . $ban_text . " " . $affected_rows . " tài khoản sử dụng IP " . $ip . "', 
                NOW()
            )
        ");
    } catch (Exception $e) {
        // Bỏ qua lỗi log nếu bảng không tồn tại
        // Không ảnh hưởng đến kết quả chính
    }
    
    // Tạo thông báo chi tiết
    if ($total_accounts == 0) {
        $message = 'Không tìm thấy tài khoản nào sử dụng IP ' . $ip;
    } else {
        $message = 'Đã ' . $ban_text . ' thành công ' . $affected_rows . ' tài khoản sử dụng IP ' . $ip;
        
        // Thêm thông tin tổng quan
        if ($total_accounts > $affected_rows) {
            $remaining = $total_accounts - $affected_rows;
            $opposite_status = ($ban_value == 1) ? 'active' : 'banned';
            $message .= '<br><small class="text-muted">Tổng cộng: ' . $total_accounts . ' tài khoản (' . $remaining . ' ' . $opposite_status . ')</small>';
        }
        
        // Thêm thông tin chi tiết nếu có tài khoản
        if ($affected_rows > 0) {
            $accounts_query = mysqli_query($CVH->connect_db(), "
                SELECT username 
                FROM account 
                WHERE ip_address = '" . mysqli_real_escape_string($CVH->connect_db(), $ip) . "'
                AND ban = $ban_value
                LIMIT 5
            ");
            
            $usernames = [];
            while ($account = mysqli_fetch_assoc($accounts_query)) {
                $usernames[] = $account['username'];
            }
            
            if (!empty($usernames)) {
                $message .= '<br><small class="text-muted">Tài khoản: ' . implode(', ', $usernames);
                if ($affected_rows > 5) {
                    $message .= ' và ' . ($affected_rows - 5) . ' tài khoản khác';
                }
                $message .= '</small>';
            }
        }
    }
    
    $response = [
        'status' => true, 
        'message' => $message,
        'affected_rows' => $affected_rows
    ];
    

    
    echo json_encode($response);
} else {
    echo json_encode(['status' => false, 'message' => 'Có lỗi xảy ra khi ' . $ban_text . ' IP!']);
}
?>
