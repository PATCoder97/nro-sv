<?php
// Không chặn khi thiếu Referer vì một số trình duyệt/thiết lập sẽ ẩn Referer
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

// Lấy thông tin item từ item_template (bao gồm icon_id)
$info = $CVH->get_row("SELECT id, NAME, gender, icon_id FROM `item_template` WHERE `id` = '{$itemId}' LIMIT 1");
if (!$info) {
    echo json_encode(["status" => false, "message" => "Không tìm thấy vật phẩm"]);
    exit();
}

// Map icon theo icon_id như add shop
$iconId = isset($info['icon_id']) ? (int)$info['icon_id'] : 0;
$iconUrl = getItemIcon($iconId);

// Kiểm tra đang bán trong shop
$selling = false; $shopId = null;
$q = mysqli_query($CVH->connect_db(), "SELECT id FROM `cvh_sell_item` WHERE `item` = {$itemId} AND `active` = 1 LIMIT 1");
if ($q && mysqli_num_rows($q) > 0) {
    $r = mysqli_fetch_assoc($q);
    $selling = true; $shopId = (int)$r['id'];
}

echo json_encode([
    'status' => true,
    'data' => [
        'id' => (int)$info['id'],
        'name' => $info['NAME'] ?? ('Item #' . $itemId),
        'gender' => isset($info['gender']) ? (int)$info['gender'] : null,
        'icon_id' => $iconId,
        'icon' => $iconUrl,
    ],
    'selling' => $selling,
    'shop_id' => $shopId,
    'message' => $selling ? ("Vật phẩm đang bán trong shop (ID #{$shopId}). Không thể dùng cho giftcode.") : 'OK'
]);
exit();
?>


