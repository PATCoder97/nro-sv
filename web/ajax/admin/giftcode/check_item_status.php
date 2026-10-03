<?php
// Không chặn khi thiếu Referer để tránh lỗi AJAX trên một số thiết bị
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
header('Content-Type: application/json; charset=utf-8');

if (empty($user) || empty($user['is_admin'])) {
    echo json_encode(["status" => false, "message" => "Bạn không có quyền!"]);
    exit();
}

$itemId = isset($_GET['item']) ? abs(intval($_GET['item'])) : 0;
if ($itemId <= 0) {
    echo json_encode(["status" => false, "message" => "Thiếu item id"]);
    exit();
}

$selling = false;
$shopId = null;
$q = mysqli_query($CVH->connect_db(), "SELECT id FROM `cvh_sell_item` WHERE `item` = {$itemId} AND `active` = 1 LIMIT 1");
if ($q && mysqli_num_rows($q) > 0) {
    $row = mysqli_fetch_assoc($q);
    $selling = true;
    $shopId = (int)$row['id'];
}

echo json_encode([
    "status" => true,
    "selling" => $selling,
    "shop_id" => $shopId,
    "message" => $selling ? "Vật phẩm đang bán trong shop (ID #{$shopId}). Không thể dùng cho giftcode." : "OK"
]);
exit();
?>


