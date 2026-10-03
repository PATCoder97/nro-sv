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
if (!isset($_POST['username']) || !hash_equals($user['username'], (string) $_POST['username'])) {
    $CVH->Ex(false, "Tài khoản không hợp lệ!");
    exit();
}
if ((int) $user['active'] !== 0) {
    $CVH->Ex(false, "Tài khoản đã được kích hoạt!");
    exit();
}

$amount = max(0, (int) $setting['amount_mtv']);
$conn = $CVH->connect_db();
$stmt = $conn->prepare(
    'UPDATE account SET active = 1, vnd = vnd - ? WHERE id = ? AND active = 0 AND vnd >= ?'
);
$userId = (int) $user['id'];
$stmt->bind_param('iii', $amount, $userId, $amount);
$stmt->execute();
$activated = $stmt->affected_rows === 1;
$stmt->close();

if ($activated) {
    $CVH->Ex(true, "Bạn đã kích hoạt tài khoản thành công!");
} else {
    $CVH->Ex(false, "Bạn không đủ " . number_format($amount) . "đ để kích hoạt tài khoản!");
}
?>
