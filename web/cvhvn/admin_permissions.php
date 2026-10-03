<?php
/**
 * Admin Permissions Helper
 * Quản lý phân quyền chi tiết cho admin panel
 */

class AdminPermissions {
    private $CVH;
    private $user;
    
    // Định nghĩa các quyền admin
    const PERMISSIONS = [
        'home' => 'Trang chủ',
        'users_manager' => 'Quản lý thành viên',
        'ip_manager' => 'Quản lý IP',
        'item_template_manager' => 'Quản lý Item Template',
        'maintenance_manager' => 'Bảo trì server',
    'vnd_manager' => 'Quản lý VND',
        'recharge_manager' => 'Quản lý thẻ nạp',
        'post_manager' => 'Quản lý bài viết',
        'notification_manager' => 'Quản lý thông báo',
        'giftcode_manager' => 'Quản lý giftcode',
        'shop_manager' => 'Quản lý shop',
        'shop_log' => 'Xem log shop',
        'download_manager' => 'Quản lý link tải',
        'website_manager' => 'Quản lý website',
        'logout_all' => 'Logout all user',
        'ddos_check' => 'DDoS Check',
        'file_manager' => 'Quản lý file',
        'system_settings' => 'Cài đặt hệ thống',
        'buff_item_manager' => 'Quản lý Buff Item'
    ];
    
    public function __construct($CVH, $user) {
        $this->CVH = $CVH;
        $this->user = $user;
    }
    
    /**
     * Kiểm tra user có phải admin không
     */
    public function isAdmin() {
        return !empty($this->user) && !empty($this->user['is_admin']);
    }
    
    /**
     * Kiểm tra quyền admin và redirect nếu không có quyền
     */
    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header("Location: /");
            exit;
        }
    }
    
    /**
     * Kiểm tra quyền admin và trả về JSON error nếu không có quyền
     */
    public function requireAdminJson() {
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này']);
            exit;
        }
    }
    
    /**
     * Kiểm tra quyền cụ thể
     */
    public function hasPermission($permission) {
        if (!$this->isAdmin()) {
            return false;
        }
        
        // Super admin có tất cả quyền
        if (!empty($this->user['is_super_admin'])) {
            return true;
        }
        
        // Kiểm tra quyền cụ thể
        $permissions = $this->getUserPermissions();
        return in_array($permission, $permissions);
    }
    
    /**
     * Kiểm tra quyền và trả về JSON error nếu không có quyền
     */
    public function requirePermission($permission) {
        if (!$this->hasPermission($permission)) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập chức năng này']);
            exit;
        }
    }
    
    /**
     * Lấy danh sách quyền của user
     */
    public function getUserPermissions() {
        if (!$this->isAdmin()) {
            return [];
        }
        
        $permissions = [];
        
        // Kiểm tra từng quyền
        foreach (self::PERMISSIONS as $key => $name) {
            $column = 'perm_' . $key;
            if (!empty($this->user[$column])) {
                $permissions[] = $key;
            }
        }
        
        return $permissions;
    }
    
    /**
     * Lấy danh sách tất cả quyền
     */
    public function getAllPermissions() {
        return self::PERMISSIONS;
    }
    
    /**
     * Cập nhật quyền cho user
     */
    public function updateUserPermissions($userId, $permissions) {
        try {
            $conn = $this->CVH->connect_db();
            
            // Tạo các cột quyền nếu chưa có
            $this->createPermissionColumns($conn);
            
            // Cập nhật từng quyền
            foreach (self::PERMISSIONS as $key => $name) {
                $column = 'perm_' . $key;
                $value = in_array($key, $permissions) ? 1 : 0;
                
                $sql = "UPDATE account SET $column = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    error_log("Failed to prepare statement: " . $conn->error);
                    return false;
                }
                
                $stmt->bind_param('ii', $value, $userId);
                if (!$stmt->execute()) {
                    error_log("Failed to execute statement: " . $stmt->error);
                    $stmt->close();
                    return false;
                }
                
                $stmt->close();
            }
            
            // Log thành công
            error_log("Successfully updated permissions for user ID: $userId");
            return true;
            
        } catch (Exception $e) {
            error_log("Exception in updateUserPermissions: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tạo các cột quyền trong bảng account
     */
    public function createPermissionColumns($conn) {
        foreach (self::PERMISSIONS as $key => $name) {
            $column = 'perm_' . $key;
            $sql = "ALTER TABLE account ADD COLUMN IF NOT EXISTS $column TINYINT(1) NOT NULL DEFAULT 0";
            $conn->query($sql);
        }
        
        // Thêm cột super admin
        $conn->query("ALTER TABLE account ADD COLUMN IF NOT EXISTS is_super_admin TINYINT(1) NOT NULL DEFAULT 0");
    }
    
    /**
     * Kiểm tra CSRF token
     */
    public function validateCSRF($token) {
        if (isset($_SESSION['csrf_token']) && isset($token)) {
            return hash_equals($_SESSION['csrf_token'], $token);
        }
        return false;
    }
    
    /**
     * Tạo CSRF token mới
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Kiểm tra HTTP_REFERER
     */
    public function validateReferer() {
        return !empty($_SERVER['HTTP_REFERER']);
    }
    
    /**
     * Kiểm tra phương thức HTTP
     */
    public function validateMethod($method = 'POST') {
        return $_SERVER['REQUEST_METHOD'] === $method;
    }
    
    /**
     * Validate đường dẫn file an toàn
     */
    public function validateFilePath($filePath, $allowedRoot = '/images/') {
        if (empty($filePath) || strpos($filePath, $allowedRoot) !== 0) {
            return false;
        }
        
        $full = realpath($_SERVER['DOCUMENT_ROOT'] . $filePath);
        $root = realpath($_SERVER['DOCUMENT_ROOT'] . $allowedRoot);
        
        return $full && $root && strpos($full, $root) === 0;
    }
    
    /**
     * Log hoạt động admin
     */
    public function logActivity($action, $details = '') {
        if ($this->isAdmin()) {
            $username = $this->user['username'] ?? 'unknown';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $logMessage = sprintf(
                "[%s] Admin %s (%s) - %s: %s",
                date('Y-m-d H:i:s'),
                $username,
                $ip,
                $action,
                $details
            );
            
            error_log($logMessage);
        }
    }
    
    /**
     * Kiểm tra quyền truy cập file
     */
    public function canAccessFile($filePath) {
        if (!$this->validateFilePath($filePath)) {
            return false;
        }
        
        $fullPath = realpath($_SERVER['DOCUMENT_ROOT'] . $filePath);
        return file_exists($fullPath) && is_readable($fullPath);
    }
    
    /**
     * Kiểm tra quyền xóa file
     */
    public function canDeleteFile($filePath) {
        if (!$this->validateFilePath($filePath)) {
            return false;
        }
        
        $fullPath = realpath($_SERVER['DOCUMENT_ROOT'] . $filePath);
        return file_exists($fullPath) && is_writable($fullPath);
    }
    
    /**
     * Tạo menu admin dựa trên quyền
     */
    public function getAdminMenu() {
        $menu = [];
        
        foreach (self::PERMISSIONS as $key => $name) {
            if ($this->hasPermission($key)) {
                $menu[$key] = $name;
            }
        }
        
        return $menu;
    }
    
    /**
     * Kiểm tra quyền truy cập trang admin
     */
    public function checkPageAccess($page) {
        if (!$this->isAdmin()) {
            return false;
        }
        
        // Super admin có quyền truy cập tất cả
        if (!empty($this->user['is_super_admin'])) {
            return true;
        }
        
        // Kiểm tra quyền cụ thể cho trang
        return $this->hasPermission($page);
    }
}

/**
 * Helper function để tạo instance AdminPermissions
 */
function getAdminPermissions($CVH, $user) {
    return new AdminPermissions($CVH, $user);
}

/**
 * Helper function để kiểm tra quyền nhanh
 */
function hasAdminPermission($permission) {
    global $CVH, $user;
    $adminPerms = getAdminPermissions($CVH, $user);
    return $adminPerms->hasPermission($permission);
}
?>
