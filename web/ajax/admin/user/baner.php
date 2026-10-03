<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($user) || empty($user['is_admin'])) {
    $CVH->Ex(false, "Bạn không có quyền thực hiện thao tác này!");
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $CVH->Ex(false, "Phiên làm việc không hợp lệ, vui lòng tải lại trang!");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = filter_input(INPUT_POST, 'status', FILTER_VALIDATE_INT);
if (!$id || !in_array($status, [0, 1], true)) {
    $CVH->Ex(false, "Dữ liệu không hợp lệ!");
    exit;
}

$conn = $CVH->connect_db();
$stmt = $conn->prepare('UPDATE account SET ban = ? WHERE id = ?');
$stmt->bind_param('ii', $status, $id);
$success = $stmt->execute() && $stmt->affected_rows >= 0;
$stmt->close();

$CVH->Ex($success, $success ? "Đã cập nhật trạng thái tài khoản." : "Không thể cập nhật trạng thái tài khoản!");
