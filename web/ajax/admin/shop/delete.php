<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

// Kiểm tra quyền super admin
if (!$user['is_super_admin'] ?? false) {
    echo json_encode(['status' => false, 'message' => 'Chỉ Super Admin mới có quyền xóa item!']);
    exit;
}

if (!$user['is_admin']) {
    header("Location: /");
    exit;
} else {
    if ($_POST['type'] == 'Del_Gift') {
        $id = abs($_POST['id']);
        if (isset($id)) {
            mysqli_query($CVH->connect_db(), "DELETE FROM `cvh_sell_item` WHERE `id`='" . $id . "'");
            $CVH->Ex(true, "Xóa sản phẩm thành công!");
        } else {
            $CVH->Ex(true, "Sản phẩm không tồn tại!");
        }
    }
}
?>