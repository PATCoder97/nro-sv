<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'].'/cvhvn/autoload.php';

if(!$user['is_admin']){ echo json_encode(['status'=>false,'message'=>'No permission']); exit; }

mysqli_query($CVH->connect_db(), "CREATE TABLE IF NOT EXISTS `system_status` ( `id` INT PRIMARY KEY, `status` TINYINT(1) NOT NULL DEFAULT 0, `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP )");

// Bảng điều khiển server
mysqli_query($CVH->connect_db(), "CREATE TABLE IF NOT EXISTS `server_control` ( `id` TINYINT PRIMARY KEY, `start_request` TINYINT(1) NOT NULL DEFAULT 0, `stop_request` TINYINT(1) NOT NULL DEFAULT 0, `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP )");
mysqli_query($CVH->connect_db(), "INSERT IGNORE INTO `server_control`(id) VALUES(1)");

$rs = mysqli_query($CVH->connect_db(), "SELECT * FROM `system_status` WHERE id=1");
if(mysqli_num_rows($rs)==0){ mysqli_query($CVH->connect_db(), "INSERT INTO `system_status`(id,status) VALUES(1,0)"); $status=0; $updated=null; }
else { $row = mysqli_fetch_assoc($rs); $status=intval($row['status']); $updated=$row['updated_at']; }

$ctrl = mysqli_fetch_assoc(mysqli_query($CVH->connect_db(), "SELECT start_request, stop_request, updated_at FROM `server_control` WHERE id=1"));

echo json_encode(['status'=>true,'data'=>['status'=>$status,'updated_at'=>$updated,'start_request'=>intval($ctrl['start_request']??0),'stop_request'=>intval($ctrl['stop_request']??0),'ctrl_updated_at'=>$ctrl['updated_at']??null]]);
