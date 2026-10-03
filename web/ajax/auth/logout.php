<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Phương thức không hợp lệ!']);
    exit();
}

// Xóa session server-side nếu có
if (!empty($_COOKIE['session_token'])) {
    $sessionToken = $_COOKIE['session_token'];
    $conn = $CVH->connect_db();
    $stmt = $conn->prepare("DELETE FROM cvh_sessions WHERE token = ?");
    if ($stmt) {
        $stmt->bind_param('s', $sessionToken);
        $stmt->execute();
        $stmt->close();
    }

    // Hết hạn cookie session_token (HttpOnly nên không thể xoá bằng JS)
    setcookie('session_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Xóa cookie token cũ nếu còn
setcookie('token', '', time() - 3600, '/');

echo json_encode(['status' => true, 'message' => 'Đăng xuất thành công!']);
exit();
?>

