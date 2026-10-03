<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

try {
    // Kiểm tra xem cột đã tồn tại chưa
    $check_column = $CVH->get_row("SHOW COLUMNS FROM account LIKE 'perm_shop_log'");
    
    if (!$check_column) {
        // Thêm cột perm_shop_log
        $sql = "ALTER TABLE account ADD COLUMN perm_shop_log TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Quyền xem log shop'";
        $CVH->query($sql);
        
        echo "✅ Đã thêm cột perm_shop_log vào bảng account!<br>";
        
        // Cấp quyền cho super admin
        $update_super = "UPDATE account SET perm_shop_log = 1 WHERE is_super_admin = 1";
        $CVH->query($update_super);
        
        echo "✅ Đã cấp quyền shop_log cho tất cả super admin!<br>";
        
        // Cấp quyền cho admin có quyền shop_manager
        $update_shop_admin = "UPDATE account SET perm_shop_log = 1 WHERE perm_shop_manager = 1";
        $CVH->query($update_shop_admin);
        
        echo "✅ Đã cấp quyền shop_log cho admin có quyền shop_manager!<br>";
        
    } else {
        echo "ℹ️ Cột perm_shop_log đã tồn tại trong bảng account!<br>";
    }
    
    echo "<br>🎉 Hoàn thành! Quyền 'Xem log shop' đã được thêm vào hệ thống phân quyền.<br>";
    echo "<br>📋 Hướng dẫn sử dụng:<br>";
    echo "- Super Admin: Tự động có quyền này<br>";
    echo "- Admin có quyền shop_manager: Tự động có quyền này<br>";
    echo "- Admin khác: Cần được cấp quyền thủ công trong trang 'Quản lý phân quyền admin'<br>";
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
?>
