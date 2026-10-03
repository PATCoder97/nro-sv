<?php
if (ob_get_level()) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/autoload.php';

try {
    if(!$user['is_admin'] || (!$user['is_super_admin'] && empty($user['perm_item_template_manager']))){
        echo json_encode(['status'=>false,'message'=>'Không có quyền']); exit;
    }

    if (!isset($_POST['csrf_token']) || !$CVH->validateCSRFToken($user['id'], $_POST['csrf_token'])) {
        echo json_encode(['status'=>false,'message'=>'Token không hợp lệ']); exit;
    }

$fields = ['TYPE','gender','NAME','description','icon_id','part','is_up_to_up','power_require','gold','gem','head','body','leg'];
$data = [];
foreach($fields as $f){ $data[$f] = isset($_POST[$f]) ? trim($_POST[$f]) : null; }
$id = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : 0;

// Validate cơ bản: NAME & description có thể để trống
$data['TYPE'] = intval($data['TYPE']);
$data['gender'] = intval($data['gender']);
foreach(['icon_id','part','is_up_to_up','power_require','gold','gem','head','body','leg'] as $n){ $data[$n] = ($data[$n]!==null && $data[$n]!=='' ? intval($data[$n]) : 0); }

$conn = $CVH->connect_db();
if($id > 0){
    // update
    $sql = sprintf(
        "UPDATE `item_template` SET `TYPE`=%d, `gender`=%d, `NAME`='%s', `description`='%s', `icon_id`=%d, `part`=%d, `is_up_to_up`=%d, `power_require`=%d, `gold`=%d, `gem`=%d, `head`=%d, `body`=%d, `leg`=%d WHERE `id`=%d",
        $data['TYPE'], $data['gender'], mysqli_real_escape_string($conn, $data['NAME'] ?? ''), mysqli_real_escape_string($conn, $data['description'] ?? ''), $data['icon_id'], $data['part'], $data['is_up_to_up'], $data['power_require'], $data['gold'], $data['gem'], $data['head'], $data['body'], $data['leg'], $id
    );
    $ok = mysqli_query($conn,$sql);
    if(!$ok){ echo json_encode(['status'=>false,'message'=>'Lưu thất bại','sql'=>$sql,'mysql_error'=>mysqli_error($conn)]); }
    else { echo json_encode(['status'=>true,'message'=>'Đã cập nhật item #'.$id]); }
} else {
    // insert
    if ($id <= 0) {
        // Nếu không nhập ID hoặc bảng không auto_increment -> lấy MAX(id)+1
        $rs = mysqli_query($conn, "SELECT IFNULL(MAX(id),0)+1 AS next_id FROM `item_template`");
        $row = mysqli_fetch_assoc($rs);
        $id = intval($row['next_id']);
    }
    // chèn với id xác định
    $sql = sprintf(
        "INSERT INTO `item_template`(`id`,`TYPE`,`gender`,`NAME`,`description`,`icon_id`,`part`,`is_up_to_up`,`power_require`,`gold`,`gem`,`head`,`body`,`leg`) VALUES(%d,%d,%d,'%s','%s',%d,%d,%d,%d,%d,%d,%d,%d,%d)",
        $id, $data['TYPE'], $data['gender'], mysqli_real_escape_string($conn, $data['NAME'] ?? ''), mysqli_real_escape_string($conn, $data['description'] ?? ''), $data['icon_id'], $data['part'], $data['is_up_to_up'], $data['power_require'], $data['gold'], $data['gem'], $data['head'], $data['body'], $data['leg']
    );
    $ok = mysqli_query($conn,$sql);
    if(!$ok){ echo json_encode(['status'=>false,'message'=>'Thêm thất bại','sql'=>$sql,'mysql_error'=>mysqli_error($conn)]); }
    else { echo json_encode(['status'=>true,'message'=>'Đã thêm item mới']); }
}
} catch (Throwable $e) {
    echo json_encode(['status'=>false,'message'=>'Lỗi server','mysql_error'=>$e->getMessage()]);
}
