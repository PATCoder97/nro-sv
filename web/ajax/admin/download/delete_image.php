<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/admin_permissions.php';

header('Content-Type: application/json; charset=utf-8');

// Khởi tạo AdminPermissions
$adminPerms = getAdminPermissions($CVH, $user);

try {
    // Kiểm tra HTTP_REFERER
    if (!$adminPerms->validateReferer()) {
        header('HTTP/1.0 403 Forbidden');
        echo "Forbidden: You don't have permission to access this resource.";
        exit();
    }

    // Kiểm tra quyền admin và quyền file_manager
    $adminPerms->requireAdminJson();
    $adminPerms->requirePermission('file_manager');

    // Kiểm tra phương thức POST
    if (!$adminPerms->validateMethod('POST')) {
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
        exit;
    }

    // Kiểm tra CSRF token (nếu có)
    if (isset($_POST['csrf_token']) && !$adminPerms->validateCSRF($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'CSRF token không hợp lệ']);
        exit;
    }

    // Lấy và validate đường dẫn file
    $file = isset($_POST['file']) ? trim($_POST['file']) : '';
    if (!$adminPerms->validateFilePath($file)) {
        echo json_encode(['success' => false, 'message' => 'Đường dẫn không hợp lệ']);
        exit;
    }

    // Kiểm tra quyền xóa file
    if (!$adminPerms->canDeleteFile($file)) {
        echo json_encode(['success' => false, 'message' => 'Không có quyền xóa file hoặc file không tồn tại']);
        exit;
    }

    // Thực hiện xóa file
    $fullPath = realpath($_SERVER['DOCUMENT_ROOT'] . $file);
    if (@unlink($fullPath)) {
        // Log hoạt động
        $adminPerms->logActivity('DELETE_IMAGE', "Đã xóa file: {$file}");
        
        echo json_encode(['success' => true, 'message' => 'Xóa file thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Xóa thất bại']);
    }
    
} catch (Throwable $e) {
    // Log lỗi chi tiết
    $adminPerms->logActivity('ERROR', "Lỗi trong delete_image.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
}
?>


