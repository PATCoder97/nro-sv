<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

// Chỉ cho phép POST + Admin + CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Phương thức không hợp lệ!']);
    exit();
}
if (empty($user) || empty($user['is_admin'])) {
    echo json_encode(['status' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này!']);
    exit();
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    echo json_encode(['status' => false, 'message' => 'CSRF token không hợp lệ!']);
    exit();
}

$conn = $CVH->connect_db();

// Đảm bảo bảng session tồn tại
$conn->query("CREATE TABLE IF NOT EXISTS cvh_sessions (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, token VARCHAR(128) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)");

$effected = 0;

// Logout tất cả người dùng
if (!empty($_POST['all']) && $_POST['all'] == '1') {
    $conn->query("DELETE FROM cvh_sessions");
    // Thêm cột nếu chưa có và bật cờ thu hồi token cũ cho tất cả tài khoản
    @$conn->query("ALTER TABLE account ADD COLUMN IF NOT EXISTS legacy_token_revoked TINYINT(1) NOT NULL DEFAULT 0");
    $conn->query("UPDATE account SET legacy_token_revoked = 1");
    echo json_encode(['status' => true, 'message' => 'Đã đăng xuất tất cả phiên của mọi tài khoản và vô hiệu hoá token cũ!']);
    exit();
}

// Xử lý đăng xuất phiên cụ thể
if (!empty($_POST['session_id'])) {
    $sessionId = intval($_POST['session_id']);
    
    // Xóa session cụ thể
    $stmt = $conn->prepare("DELETE FROM cvh_sessions WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $sessionId);
        $stmt->execute();
        $effected = $stmt->affected_rows;
        $stmt->close();
        
        if ($effected > 0) {
            echo json_encode(['status' => true, 'message' => "Đã đăng xuất phiên ID: $sessionId"]);
        } else {
            echo json_encode(['status' => false, 'message' => "Không tìm thấy phiên ID: $sessionId"]);
        }
    } else {
        echo json_encode(['status' => false, 'message' => 'Lỗi khi xóa phiên']);
    }
    exit();
}

// Ngược lại: đăng xuất toàn bộ phiên của 1 người dùng (id hoặc username)
$targetUserId = null;
if (!empty($_POST['user_id'])) {
    $targetUserId = intval($_POST['user_id']);
} elseif (!empty($_POST['username'])) {
    $uname = $_POST['username'];
    $stmtFind = $conn->prepare("SELECT id FROM account WHERE username = ? LIMIT 1");
    if ($stmtFind) {
        $stmtFind->bind_param('s', $uname);
        $stmtFind->execute();
        $res = $stmtFind->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $targetUserId = intval($row['id']);
        }
        $stmtFind->close();
    }
}

if (!$targetUserId) {
    echo json_encode(['status' => false, 'message' => 'Thiếu user_id/username hợp lệ!']);
    exit();
}

// Xoá toàn bộ phiên hiện tại của user
$stmt = $conn->prepare("DELETE FROM cvh_sessions WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $targetUserId);
    $stmt->execute();
    $effected = $stmt->affected_rows;
    $stmt->close();
}

// Vô hiệu hoá token cũ cho user
@$conn->query("ALTER TABLE account ADD COLUMN IF NOT EXISTS legacy_token_revoked TINYINT(1) NOT NULL DEFAULT 0");
$stmt2 = $conn->prepare("UPDATE account SET legacy_token_revoked = 1 WHERE id = ?");
if ($stmt2) {
    $stmt2->bind_param('i', $targetUserId);
    $stmt2->execute();
    $stmt2->close();
}

echo json_encode(['status' => true, 'message' => 'Đã đăng xuất tất cả phiên của người dùng!', 'data' => ['sessions_removed' => $effected]]);
exit();
?>

