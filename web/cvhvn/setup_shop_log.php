<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

try {
    // Tạo bảng shop_log
    $sql = "CREATE TABLE IF NOT EXISTS `shop_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `admin_id` int(11) NOT NULL COMMENT 'ID của admin thực hiện',
        `admin_username` varchar(50) NOT NULL COMMENT 'Username của admin',
        `item_id` int(11) NOT NULL COMMENT 'ID của item được thêm',
        `item_name` varchar(255) NOT NULL COMMENT 'Tên item',
        `planet` varchar(50) NOT NULL COMMENT 'Hành tinh',
        `price` int(11) NOT NULL COMMENT 'Giá tiền',
        `max_buy` int(11) NOT NULL COMMENT 'Số lượt mua tối đa',
        `options` text NOT NULL COMMENT 'JSON options của item',
        `icon_id` int(11) DEFAULT NULL COMMENT 'ID icon của item',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian thêm',
        `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT 'Trạng thái item',
        PRIMARY KEY (`id`),
        KEY `admin_id` (`admin_id`),
        KEY `item_id` (`item_id`),
        KEY `created_at` (`created_at`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log theo dõi việc thêm item vào shop';";
    
    $CVH->query($sql);
    
    echo "✅ Bảng shop_log đã được tạo thành công!<br>";
    echo "📊 Bảng này sẽ theo dõi:<br>";
    echo "- Admin nào thêm item gì<br>";
    echo "- Chỉ số và options của item<br>";
    echo "- Thời gian thêm<br>";
    echo "- Trạng thái item<br><br>";
    
    // Kiểm tra xem có dữ liệu cũ không để migrate
    $check_old = $CVH->get_row("SELECT COUNT(*) as count FROM cvh_sell_item WHERE 1");
    $old_count = $check_old['count'];
    
    if ($old_count > 0) {
        echo "🔍 Phát hiện {$old_count} item cũ trong bảng shop<br>";
        echo "💡 Bạn có muốn migrate dữ liệu cũ vào bảng log không?<br>";
        echo "<a href='?migrate=1' class='btn btn-primary'>Migrate dữ liệu cũ</a><br><br>";
    }
    
    if (isset($_GET['migrate']) && $_GET['migrate'] == 1) {
        echo "🔄 Đang migrate dữ liệu cũ...<br>";
        
        $items = $CVH->get_list("SELECT * FROM cvh_sell_item WHERE 1");
        $migrated = 0;
        
        foreach ($items as $item) {
            // Lấy thông tin admin (giả sử admin đầu tiên)
            $admin = $CVH->get_row("SELECT id, username FROM account WHERE is_admin = 1 LIMIT 1");
            $admin_data = $admin;
            
            if ($admin_data) {
                // Lấy thông tin item từ item_template
                $item_info = $CVH->get_row("SELECT name, icon_id FROM item_template WHERE id = " . intval($item['item']));
                $item_name = $item_info ? $item_info['name'] : "Item ID: " . $item['item'];
                $icon_id = $item_info ? $item_info['icon_id'] : 0;
                
                $CVH->insert("shop_log", [
                    "admin_id" => $admin_data['id'],
                    "admin_username" => $admin_data['username'],
                    "item_id" => $item['id'],
                    "item_name" => $item_name,
                    "planet" => $admin_data['gender'] ?? 0,
                    "price" => $item['price'],
                    "max_buy" => 0,
                    "options" => $item['options'],
                    "icon_id" => $icon_id,
                    "created_at" => date('Y-m-d H:i:s'),
                    "status" => "active"
                ]);
                
                $migrated++;
            }
        }
        
        echo "✅ Đã migrate {$migrated} item thành công!<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
?>
