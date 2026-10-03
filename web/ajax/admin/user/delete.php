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
if (($_POST['type'] ?? '') !== 'Del_Mem' || !$id) {
    $CVH->Ex(false, "Tài khoản không hợp lệ!");
    exit;
}

$conn = $CVH->connect_db();
try {
    $conn->begin_transaction();

    $stmt = $conn->prepare('DELETE FROM player WHERE account_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare('DELETE FROM account WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $deleted = $stmt->affected_rows;
    $stmt->close();

    if ($deleted !== 1) {
        throw new RuntimeException('Không tìm thấy tài khoản');
    }

    $conn->commit();
    $CVH->Ex(true, "Xóa tài khoản và nhân vật thành công!");
} catch (Throwable $e) {
    $conn->rollback();
    $CVH->Ex(false, "Không thể xóa tài khoản!");
}
