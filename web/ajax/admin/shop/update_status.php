<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

// Kiểm tra quyền super admin
if (empty($user['is_super_admin'])) {
    echo json_encode(['status' => false, 'message' => 'Chỉ Super Admin mới có quyền thay đổi trạng thái!']);
    exit;
}

if (!$user['is_admin']) {
    header("Location: /");
    exit;
} else {
        $active = isset($_POST['active']) ? intval($_POST['active']) : 0;
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (isset($id)) {
            mysqli_query($CVH->connect_db(), "UPDATE cvh_sell_item SET active = $active WHERE id = $id");
        } else {
        }
}
?>
