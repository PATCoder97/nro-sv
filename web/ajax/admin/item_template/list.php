<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/autoload.php';

if(!$user['is_admin'] || (!$user['is_super_admin'] && empty($user['perm_item_template_manager']))){
    echo json_encode(['status'=>false,'message'=>'Không có quyền']); exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) && $_GET['type'] !== '' ? intval($_GET['type']) : null; // không mặc định filter TYPE
$minId = isset($_GET['min_id']) ? intval($_GET['min_id']) : 0; // mặc định từ 0
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 30;

$WHERE = "WHERE id >= $minId";
if ($type !== null) {
    $WHERE .= " AND TYPE = $type";
}
if($q !== ''){
    $esc = mysqli_real_escape_string($CVH->connect_db(), $q);
    if (ctype_digit($q)) {
        $id = intval($q);
        $WHERE .= " AND (id = $id OR NAME LIKE '%$esc%')";
    } else {
        $WHERE .= " AND (NAME LIKE '%$esc%' OR description LIKE '%$esc%')";
    }
}

$countSql = "SELECT COUNT(*) AS total FROM item_template $WHERE";
$countRes = mysqli_query($CVH->connect_db(), $countSql);
$total = 0; if ($countRes) { $row = mysqli_fetch_assoc($countRes); $total = intval($row['total']); }

$offset = ($page - 1) * $perPage;
$sql = "SELECT id, TYPE, gender, NAME, description, icon_id, part, is_up_to_up, power_require, gold, gem, head, body, leg FROM item_template $WHERE ORDER BY id ASC LIMIT $perPage OFFSET $offset";
$res = mysqli_query($CVH->connect_db(), $sql);
$items = [];
if($res){ while($row = mysqli_fetch_assoc($res)){ $items[] = $row; } }
echo json_encode(['status'=>true,'items'=>$items,'page'=>$page,'per_page'=>$perPage,'total'=>$total]);
