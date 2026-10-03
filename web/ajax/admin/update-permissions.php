<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/admin_permissions.php';

// Đảm bảo không có output nào trước khi xử lý
ob_clean();

// Kiểm tra quyền admin
$adminPerms = getAdminPermissions($CVH, $user);
$adminPerms->requireAdmin();

// Chỉ xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Kiểm tra action
if (!isset($_POST['action']) || $_POST['action'] !== 'update_permissions') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

$userId = intval($_POST['user_id']);
$permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

// Debug: Log thông tin
error_log("AJAX: Updating permissions for user ID: $userId");
error_log("AJAX: Permissions: " . json_encode($permissions));
error_log("AJAX: Current user ID: " . $user['id']);
error_log("AJAX: Current user is_super_admin: " . ($user['is_super_admin'] ?? 'NULL'));

// Kiểm tra quyền cập nhật - Super Admin có thể sửa quyền của admin khác
if ($userId == $user['id']) {
    error_log("AJAX: Cannot update own permissions");
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật quyền của chính mình']);
    exit;
}

// Kiểm tra có phải Super Admin không
if (!($user['is_super_admin'] ?? false)) {
    error_log("AJAX: Only Super Admin can update permissions");
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Chỉ Super Admin mới có thể cập nhật quyền']);
    exit;
}

try {
    if ($adminPerms->updateUserPermissions($userId, $permissions)) {
        $adminPerms->logActivity('UPDATE_PERMISSIONS', "Cập nhật quyền cho user ID: $userId");
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Cập nhật quyền thành công']);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật quyền']);
        exit;
    }
} catch (Exception $e) {
    error_log("AJAX: Error updating permissions: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    exit;
}
?>
