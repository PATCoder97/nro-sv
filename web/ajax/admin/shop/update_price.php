<?php
require_once $_SERVER["DOCUMENT_ROOT"].'/cvhvn/autoload.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/cvhvn/admin_permissions.php';

// Kiểm tra quyền admin
if (!$user || !$user['is_admin']) {
    echo json_encode(['status' => false, 'message' => 'Không có quyền truy cập!']);
    exit;
}

// Kiểm tra quyền super admin
if (empty($user['is_super_admin'])) {
    echo json_encode(['status' => false, 'message' => 'Chỉ Super Admin mới có quyền sửa giá!']);
    exit;
}

// Kiểm tra quyền shop
$adminPermissions = new AdminPermissions($CVH, $user);
if (!$adminPermissions->hasPermission('shop_manager')) {
    echo json_encode(['status' => false, 'message' => 'Không có quyền quản lý shop!']);
    exit;
}

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Method không hợp lệ!']);
    exit;
}

// Lấy dữ liệu từ request
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$price = isset($_POST['price']) ? intval($_POST['price']) : 0;

// Validate dữ liệu
if ($item_id <= 0) {
    echo json_encode(['status' => false, 'message' => 'ID item không hợp lệ!']);
    exit;
}

if ($price < 0) {
    echo json_encode(['status' => false, 'message' => 'Giá tiền không hợp lệ!']);
    exit;
}

try {
    // Kiểm tra item có tồn tại không
    $item = $CVH->get_row("SELECT id, price FROM cvh_sell_item WHERE id = '$item_id'");
    if (!$item) {
        echo json_encode(['status' => false, 'message' => 'Item không tồn tại!']);
        exit;
    }
    
    // Cập nhật giá tiền - sử dụng SQL trực tiếp để tránh lỗi
    $escaped_price = mysqli_real_escape_string($CVH->connect_db(), $price);
    $escaped_item_id = mysqli_real_escape_string($CVH->connect_db(), $item_id);
    $sql = "UPDATE cvh_sell_item SET price = '$escaped_price' WHERE id = '$escaped_item_id'";
    $result = mysqli_query($CVH->connect_db(), $sql);
    
    if ($result) {
        // Không log vào shop_log vì bảng này chỉ dành cho việc thêm item mới
        $old_price = $item['price'];
        
        echo json_encode([
            'status' => true, 
            'message' => 'Cập nhật giá tiền thành công!',
            'old_price' => number_format($old_price),
            'new_price' => number_format($price)
        ]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Cập nhật thất bại!']);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
?>
