<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'].'/cvhvn/autoload.php';

if(!$user['is_admin'] || (!$user['is_super_admin'] && empty($user['perm_maintenance_manager']))){ echo json_encode(['status'=>false,'message'=>'No permission']); exit; }
if (!isset($_POST['csrf_token']) || !$CVH->validateCSRFToken($user['id'], $_POST['csrf_token'])) { echo json_encode(['status'=>false,'message'=>'Token không hợp lệ']); exit; }
$status = isset($_POST['status']) ? (intval($_POST['status']) ? 1 : 0) : 0;

$conn = $CVH->connect_db();
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `system_status` ( `id` INT PRIMARY KEY, `status` TINYINT(1) NOT NULL DEFAULT 0, `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP )");

$exists = mysqli_query($conn, "SELECT id FROM `system_status` WHERE id=1");
if(mysqli_num_rows($exists)==0){
    $ok = mysqli_query($conn, "INSERT INTO `system_status`(id,status) VALUES(1,$status)");
} else {
    $ok = mysqli_query($conn, "UPDATE `system_status` SET status=$status WHERE id=1");
}

echo json_encode(['status'=>$ok?true:false,'message'=>$ok?('Đã '.($status? 'bật':'tắt').' bảo trì'):'Cập nhật thất bại']);
