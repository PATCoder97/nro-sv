<?php
/**
 * Setup Admin Permissions System
 * Chạy file này một lần để tạo cấu trúc phân quyền
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/admin_permissions.php';

// Chỉ cho phép admin chạy
if (!$user || !$user['is_admin']) {
    die('Chỉ admin mới có thể chạy file này!');
}

$adminPerms = getAdminPermissions($CVH, $user);
$conn = $CVH->connect_db();

echo "<h2>Setup Admin Permissions System</h2>";

try {
    // 1. Tạo các cột quyền
    echo "<h3>1. Tạo các cột quyền trong bảng account...</h3>";
    $adminPerms->createPermissionColumns($conn);
    echo "✅ Đã tạo các cột quyền thành công!<br>";
    
    // 2. Tạo bảng admin_activity_log nếu chưa có
    echo "<h3>2. Tạo bảng admin_activity_log...</h3>";
    $createLogTable = "CREATE TABLE IF NOT EXISTS `admin_activity_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `admin_id` int(11) NOT NULL,
        `admin_username` varchar(50) NOT NULL,
        `action` varchar(100) NOT NULL,
        `details` text,
        `ip_address` varchar(45),
        `user_agent` text,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `admin_id` (`admin_id`),
        KEY `action` (`action`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($createLogTable);
    echo "✅ Đã tạo bảng admin_activity_log thành công!<br>";
    
    // 3. Tạo bảng admin_roles nếu chưa có
    echo "<h3>3. Tạo bảng admin_roles...</h3>";
    $createRolesTable = "CREATE TABLE IF NOT EXISTS `admin_roles` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `description` text,
        `permissions` json,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($createRolesTable);
    echo "✅ Đã tạo bảng admin_roles thành công!<br>";
    
    // 4. Tạo các role mặc định
    echo "<h3>4. Tạo các role mặc định...</h3>";
    
    $roles = [
        [
            'name' => 'Super Admin',
            'description' => 'Quản trị viên cao cấp - có tất cả quyền',
            'permissions' => json_encode(array_keys(AdminPermissions::PERMISSIONS))
        ],
        [
            'name' => 'Content Manager',
            'description' => 'Quản lý nội dung - quản lý bài viết, thông báo',
            'permissions' => json_encode(['home', 'post_manager', 'notification_manager', 'download_manager'])
        ],
        [
            'name' => 'User Manager',
            'description' => 'Quản lý người dùng - quản lý thành viên, giftcode',
            'permissions' => json_encode(['home', 'users_manager', 'giftcode_manager', 'logout_all'])
        ],
        [
            'name' => 'Financial Manager',
            'description' => 'Quản lý tài chính - quản lý thẻ nạp, shop',
            'permissions' => json_encode(['home', 'recharge_manager', 'shop_manager'])
        ],
        [
            'name' => 'System Manager',
            'description' => 'Quản lý hệ thống - cài đặt, DDoS check',
            'permissions' => json_encode(['home', 'website_manager', 'ddos_check', 'system_settings'])
        ]
    ];
    
    foreach ($roles as $role) {
        $stmt = $conn->prepare("INSERT IGNORE INTO admin_roles (name, description, permissions) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $role['name'], $role['description'], $role['permissions']);
        $stmt->execute();
        echo "✅ Đã tạo role: {$role['name']}<br>";
    }
    
    // 5. Cập nhật quyền cho admin hiện tại (nếu chưa có)
    echo "<h3>5. Cập nhật quyền cho admin hiện tại...</h3>";
    
    // Kiểm tra xem admin hiện tại có phải super admin không
    $currentAdminId = $user['id'];
    $checkSuperAdmin = $conn->prepare("SELECT is_super_admin FROM account WHERE id = ?");
    $checkSuperAdmin->bind_param('i', $currentAdminId);
    $checkSuperAdmin->execute();
    $result = $checkSuperAdmin->get_result();
    $adminData = $result->fetch_assoc();
    
    if (!$adminData['is_super_admin']) {
        // Cấp quyền super admin cho admin hiện tại
        $updateSuperAdmin = $conn->prepare("UPDATE account SET is_super_admin = 1 WHERE id = ?");
        $updateSuperAdmin->bind_param('i', $currentAdminId);
        $updateSuperAdmin->execute();
        
        // Cấp tất cả quyền
        $adminPerms->updateUserPermissions($currentAdminId, array_keys(AdminPermissions::PERMISSIONS));
        
        echo "✅ Đã cấp quyền Super Admin cho tài khoản hiện tại!<br>";
    } else {
        echo "ℹ️ Tài khoản hiện tại đã là Super Admin!<br>";
    }
    
    // 6. Hiển thị thông tin cấu trúc
    echo "<h3>6. Thông tin cấu trúc phân quyền:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Cột</th><th>Mô tả</th><th>Giá trị</th></tr>";
    
    foreach (AdminPermissions::PERMISSIONS as $key => $name) {
        $column = 'perm_' . $key;
        echo "<tr>";
        echo "<td>$column</td>";
        echo "<td>$name</td>";
        echo "<td>0/1 (TINYINT)</td>";
        echo "</tr>";
    }
    
    echo "<tr><td>is_super_admin</td><td>Super Admin</td><td>0/1 (TINYINT)</td></tr>";
    echo "</table>";
    
    echo "<h3>✅ Setup hoàn tất!</h3>";
    echo "<p>Hệ thống phân quyền đã được cài đặt thành công. Bạn có thể:</p>";
    echo "<ul>";
    echo "<li>Sử dụng AdminPermissions class để kiểm tra quyền</li>";
    echo "<li>Tạo các admin khác với quyền hạn cụ thể</li>";
    echo "<li>Quản lý quyền thông qua admin panel</li>";
    echo "</ul>";
    
    echo "<p><strong>Lưu ý:</strong> Xóa file này sau khi chạy xong để bảo mật!</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Lỗi: " . $e->getMessage() . "</h3>";
}
?>
