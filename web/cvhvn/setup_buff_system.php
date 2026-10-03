<?php
require_once 'autoload.php';

// Tạo bảng buff_requests
$sql = "CREATE TABLE IF NOT EXISTS `buff_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `player_id` INT NOT NULL,
    `player_name` VARCHAR(50) NOT NULL,
    `item_id` INT NOT NULL,
    `options_string` VARCHAR(255) NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    `admin_name` VARCHAR(50) NOT NULL,
    `admin_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL,
    `result_message` TEXT NULL,
    INDEX `idx_status` (`status`),
    INDEX `idx_player_id` (`player_id`),
    INDEX `idx_player_name` (`player_name`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($CVH->connect_db(), $sql)) {
    echo "✅ Tạo bảng buff_requests thành công<br>";
} else {
    echo "❌ Lỗi tạo bảng buff_requests: " . mysqli_error($CVH->connect_db()) . "<br>";
}

// Thêm cột player_id nếu chưa có
$sql = "ALTER TABLE `buff_requests` ADD COLUMN IF NOT EXISTS `player_id` INT NOT NULL AFTER `id`";
if(mysqli_query($CVH->connect_db(), $sql)) {
    echo "✅ Thêm cột player_id vào bảng buff_requests thành công<br>";
} else {
    echo "❌ Lỗi thêm cột player_id: " . mysqli_error($CVH->connect_db()) . "<br>";
}

// Thêm index cho player_id nếu chưa có
$sql = "ALTER TABLE `buff_requests` ADD INDEX IF NOT EXISTS `idx_player_id` (`player_id`)";
if(mysqli_query($CVH->connect_db(), $sql)) {
    echo "✅ Thêm index cho player_id thành công<br>";
} else {
    echo "❌ Lỗi thêm index: " . mysqli_error($CVH->connect_db()) . "<br>";
}

// Thêm quyền buff_item_manager vào bảng account
$sql = "ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_buff_item_manager` TINYINT(1) NOT NULL DEFAULT 0";
if(mysqli_query($CVH->connect_db(), $sql)) {
    echo "✅ Thêm quyền perm_buff_item_manager vào bảng account thành công<br>";
} else {
    echo "❌ Lỗi thêm quyền: " . mysqli_error($CVH->connect_db()) . "<br>";
}

echo "<br>🎉 Setup hệ thống buff item hoàn tất!";
?>
