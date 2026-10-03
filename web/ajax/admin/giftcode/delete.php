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
if (empty($user) || empty($user['is_admin'])) {
    $CVH->Ex(false, "Bạn không có quyền thực hiện thao tác này!");
    exit();
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $CVH->Ex(false, "CSRF token không hợp lệ!");
    exit();
}
{
    if ($_POST['type'] == 'Del_Gift') {
        $id = abs((int) ($_POST['id'] ?? 0));
        if ($id > 0) {
            $conn = $CVH->connect_db();
            $stmt = $conn->prepare('DELETE FROM giftcode WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $deleted = $stmt->affected_rows > 0;
            $stmt->close();
            $CVH->Ex($deleted, $deleted ? "Xóa giftcode thành công!" : "Giftcode không tồn tại!");
        } else {
            $CVH->Ex(false, "Giftcode không hợp lệ!");
        }
    }
}
?>
