<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'].'/cvhvn/autoload.php';

if(!$user['is_admin'] || (!$user['is_super_admin'] && empty($user['perm_maintenance_manager']))){ echo json_encode(['status'=>false,'message'=>'No permission']); exit; }
if (!isset($_POST['csrf_token']) || !$CVH->validateCSRFToken($user['id'], $_POST['csrf_token'])) { echo json_encode(['status'=>false,'message'=>'Token không hợp lệ']); exit; }
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

$conn = $CVH->connect_db();
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `server_control` ( `id` TINYINT PRIMARY KEY, `start_request` TINYINT(1) NOT NULL DEFAULT 0, `stop_request` TINYINT(1) NOT NULL DEFAULT 0, `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP )");
mysqli_query($conn, "INSERT IGNORE INTO `server_control`(id) VALUES(1)");

// Tự động reset cờ về 0 sau 1 phút
$oneMinuteAgo = date('Y-m-d H:i:s', strtotime('-1 minute'));
mysqli_query($conn, "UPDATE `server_control` SET start_request=0, stop_request=0 WHERE updated_at < '$oneMinuteAgo'");

if ($action === 'start') {
    $ok = mysqli_query($conn, "UPDATE `server_control` SET start_request=1 WHERE id=1");
    echo json_encode(['status'=>$ok?true:false,'message'=>$ok?'Đã gửi yêu cầu khởi động (tự động reset sau 1 phút)':'Gửi yêu cầu thất bại']);
} elseif ($action === 'stop') {
    $ok = mysqli_query($conn, "UPDATE `server_control` SET stop_request=1 WHERE id=1");
    echo json_encode(['status'=>$ok?true:false,'message'=>$ok?'Đã gửi yêu cầu dừng (tự động reset sau 1 phút)':'Gửi yêu cầu thất bại']);
} else {
    echo json_encode(['status'=>false,'message'=>'Hành động không hợp lệ']);
}
