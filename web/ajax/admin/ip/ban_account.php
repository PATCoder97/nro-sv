<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

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

$account_id = intval($_POST['account_id'] ?? 0);
$action = trim($_POST['action'] ?? '');

if ($account_id <= 0) {
    echo json_encode(['status' => false, 'message' => 'ID tài khoản không hợp lệ!']);
    exit();
}

if (!in_array($action, ['ban', 'unban'])) {
    echo json_encode(['status' => false, 'message' => 'Hành động không hợp lệ!']);
    exit();
}

// Kiểm tra tài khoản có tồn tại không
$check_query = mysqli_query($CVH->connect_db(), "
    SELECT username, ban 
    FROM account 
    WHERE id = $account_id
");

if (mysqli_num_rows($check_query) == 0) {
    echo json_encode(['status' => false, 'message' => 'Không tìm thấy tài khoản!']);
    exit();
}

$account = mysqli_fetch_assoc($check_query);

// Thực hiện ban/unban
$ban_value = ($action == 'ban') ? 1 : 0;
$ban_text = ($action == 'ban') ? 'ban' : 'unban';

$update_query = mysqli_query($CVH->connect_db(), "
    UPDATE account 
    SET ban = $ban_value 
    WHERE id = $account_id
");

if ($update_query) {
    // Log hành động
    $log_query = mysqli_query($CVH->connect_db(), "
        INSERT INTO admin_log (admin_id, admin_username, action, target, details, created_at) 
        VALUES (
            " . intval($user['id']) . ", 
            '" . mysqli_real_escape_string($CVH->connect_db(), $user['username']) . "', 
            'account_" . $ban_text . "', 
            'Account: " . mysqli_real_escape_string($CVH->connect_db(), $account['username']) . "', 
            'Đã " . $ban_text . " tài khoản " . $account['username'] . "', 
            NOW()
        )
    ");
    
    echo json_encode([
        'status' => true, 
        'message' => 'Đã ' . $ban_text . ' thành công tài khoản ' . $account['username']
    ]);
} else {
    echo json_encode(['status' => false, 'message' => 'Có lỗi xảy ra khi ' . $ban_text . ' tài khoản!']);
}
?>
