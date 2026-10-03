<?php
if (ob_get_level()) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');

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

// Kiểm tra quyền VND Manager
if (!$user['is_super_admin'] && empty($user['perm_vnd_manager'])) {
    echo json_encode(['status' => false, 'message' => 'Bạn không có quyền quản lý VND']);
    exit();
}

// Kiểm tra CSRF token
if (!isset($_POST['csrf_token']) || !$CVH->validateCSRFToken($user['id'], $_POST['csrf_token'])) {
    echo json_encode(['status' => false, 'message' => 'Token không hợp lệ!']);
    exit();
}

$account_id = intval($_POST['account_id'] ?? 0);
$action_type = trim($_POST['action_type'] ?? '');
$amount = intval($_POST['amount'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

if ($account_id <= 0) {
    echo json_encode(['status' => false, 'message' => 'ID tài khoản không hợp lệ!']);
    exit();
}

if (!in_array($action_type, ['buff', 'deduct', 'set'])) {
    echo json_encode(['status' => false, 'message' => 'Loại thao tác không hợp lệ!']);
    exit();
}

if ($amount <= 0) {
    echo json_encode(['status' => false, 'message' => 'Số tiền phải lớn hơn 0!']);
    exit();
}

if (empty($reason)) {
    echo json_encode(['status' => false, 'message' => 'Vui lòng nhập lý do thao tác!']);
    exit();
}

// Kiểm tra tài khoản có tồn tại không
$check_query = mysqli_query($CVH->connect_db(), "
    SELECT id, username, vnd 
    FROM account 
    WHERE id = $account_id
");

if (mysqli_num_rows($check_query) == 0) {
    echo json_encode(['status' => false, 'message' => 'Không tìm thấy tài khoản!']);
    exit();
}

$account = mysqli_fetch_assoc($check_query);
$current_vnd = intval($account['vnd']);
$username = $account['username'];

// Tính toán VND mới
$new_vnd = 0;
$operation_text = '';

switch ($action_type) {
    case 'buff':
        $new_vnd = $current_vnd + $amount;
        $operation_text = "buff $amount VND";
        break;
    case 'deduct':
        $new_vnd = max(0, $current_vnd - $amount);
        $operation_text = "trừ $amount VND";
        break;
    case 'set':
        $new_vnd = $amount;
        $operation_text = "đặt thành $amount VND";
        break;
}

// Thực hiện cập nhật VND
$update_sql = "
    UPDATE account 
    SET vnd = $new_vnd 
    WHERE id = $account_id
";
$update_query = mysqli_query($CVH->connect_db(), $update_sql);

if ($update_query) {
    // Log hành động (chỉ khi tồn tại bảng admin_log)
    $has_admin_log = false;
    $checkLog = mysqli_query($CVH->connect_db(), "SHOW TABLES LIKE 'admin_log'");
    if ($checkLog && mysqli_num_rows($checkLog) > 0) { $has_admin_log = true; }

    if ($has_admin_log) {
        $ipAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $details = "Đã $operation_text cho tài khoản $username (ID: $account_id). VND: $current_vnd → $new_vnd. Lý do: $reason";
        $sqlLog = "INSERT INTO admin_log (admin_id, admin_username, action, target, details, ip_address, user_agent, created_at)
                   VALUES (" . intval($user['id']) . ", '" . mysqli_real_escape_string($CVH->connect_db(), $user['username']) . "',
                           'vnd_update', 'Account ID: $account_id',
                           '" . mysqli_real_escape_string($CVH->connect_db(), $details) . "',
                           '" . mysqli_real_escape_string($CVH->connect_db(), $ipAddr) . "',
                           '" . mysqli_real_escape_string($CVH->connect_db(), $ua) . "', NOW())";
        mysqli_query($CVH->connect_db(), $sqlLog); // Không để lỗi log phá vỡ JSON
    }
    
    $message = "Đã $operation_text cho tài khoản <strong>$username</strong><br>";
    $message .= "VND: <strong>" . number_format($current_vnd) . "</strong> → <strong>" . number_format($new_vnd) . "</strong><br>";
    $message .= "Lý do: $reason";
    
    echo json_encode([
        'status' => true, 
        'message' => $message,
        'old_vnd' => $current_vnd,
        'new_vnd' => $new_vnd,
        'change' => $new_vnd - $current_vnd
    ]);
} else {
    echo json_encode([
        'status' => false, 
        'message' => 'Có lỗi xảy ra khi cập nhật VND!',
        'sql' => $update_sql,
        'mysql_error' => mysqli_error($CVH->connect_db())
    ]);
}
?>
