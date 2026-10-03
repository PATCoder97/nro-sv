<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $CVH->Ex(false, "Phương thức không hợp lệ!");
    exit();
}
if (!$user) {
    $CVH->Ex(false, "Bạn chưa đăng nhập!");
    exit();
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $CVH->Ex(false, "CSRF token không hợp lệ!");
    exit();
}

$oldPassword = $CVH->rewrite($CVH->FormatString($_POST['mkcu'] ?? ''));
$newPassword = $CVH->rewrite($CVH->FormatString($_POST['mkmoi'] ?? ''));
$repeatedPassword = $CVH->rewrite($CVH->FormatString($_POST['remkmoi'] ?? ''));

if ($oldPassword === '' || $newPassword === '' || $repeatedPassword === '') {
    $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");
    exit();
}
if (!hash_equals((string) $user['password'], $oldPassword)) {
    $CVH->Ex(false, "Mật khẩu cũ không đúng!");
    exit();
}
if (!hash_equals($newPassword, $repeatedPassword)) {
    $CVH->Ex(false, "Hai mật khẩu mới chưa giống nhau!");
    exit();
}
if (!$CVH->LimitString($newPassword, 4, 9)) {
    $CVH->Ex(false, "Mật khẩu bắt buộc phải từ 4 tới 9 ký tự!");
    exit();
}

$conn = $CVH->connect_db();
try {
    $conn->begin_transaction();
    $userId = (int) $user['id'];

    $update = $conn->prepare('UPDATE account SET password = ?, legacy_token_revoked = 1 WHERE id = ?');
    $update->bind_param('si', $newPassword, $userId);
    $update->execute();
    $update->close();

    $revoke = $conn->prepare('DELETE FROM cvh_sessions WHERE user_id = ?');
    $revoke->bind_param('i', $userId);
    $revoke->execute();
    $revoke->close();

    $conn->commit();

    setcookie('session_token', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    setcookie('token', '', time() - 3600, '/');
    $CVH->Ex(true, "Đổi mật khẩu thành công, vui lòng đăng nhập lại!");
} catch (Throwable $error) {
    $conn->rollback();
    error_log('Đổi mật khẩu thất bại: ' . $error->getMessage());
    $CVH->Ex(false, "Không thể đổi mật khẩu lúc này, vui lòng thử lại!");
}
?>
