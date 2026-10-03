<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if ($_POST['item'] || $_POST['item'] === '0' && $_POST['price'] || $_POST['price'] === '0' && $_POST['slot']) {
	$gender = isset($_POST['gender']) ? intval($_POST['gender']) : 0;
    if (isset($user['is_admin'])) {
        $itemz = ($_POST['item']);
        $price = ($_POST['price']);
        $slot = ($_POST['slot']);
        $option_id = ($_POST['option_id']);
        $param_option = ($_POST['param_option']);
        
        // Luôn đảm bảo có option mặc định
        $data = array();
        
        // Thêm option mặc định trước
        $default_option = array(
            "id" => "30",
            "param" => "0"
        );
        $data[] = $default_option;
        
        // Thêm các option từ form nếu có
        if (isset($_POST["option_id"]) && count($_POST["option_id"]) > 0) {
            for ($i = 0; $i < count($_POST["option_id"]); $i++) {
                if (trim($_POST["option_id"][$i]) != '') {
                    $id = isset($_POST["option_id"][$i]) ? $_POST["option_id"][$i] : 30;
                    $param = isset($_POST["param_option"][$i]) ? $_POST["param_option"][$i] : 0;
                    $item = array(
                        "id" => $id,
                        "param" => $param
                    );
                    $data[] = $item;
                }
            }
        }
        
        $option = json_encode($data);

        $table = "cvh_sell_item";
        $data = array(
            "id" => null,
            "item" => $itemz,
            "slot" => $slot,
            "price" => $price,
            "options" => $option,
			 "gender" => $gender,
            "active" => 1,
            "users_buy" => "[]",
            "time" => time()
        );
        $insert_result = $CVH->insert($table, $data);
        
        if ($insert_result) {
            // Lấy ID của item vừa thêm
            $new_item_id = $CVH->lastInsertId();
        } else {
            $CVH->Ex(false, "Lỗi khi thêm item vào shop!");
            exit;
        }
        
        // Lấy thông tin item từ item_template
        $item_info = $CVH->get_row("SELECT name, icon_id FROM item_template WHERE id = " . intval($itemz));
        $item_name = $item_info ? $item_info['name'] : "Item ID: " . $itemz;
        $icon_id = $item_info ? $item_info['icon_id'] : 0;
        
        // Ghi log vào bảng shop_log
        $log_data = array(
            "admin_id" => $user['id'] ?? 0,
            "admin_username" => $user['username'] ?? 'Unknown',
            "item_id" => $new_item_id, // ID của item trong bảng cvh_sell_item
            "item_name" => $item_name,
            "planet" => $user['gender'] ?? 0,
            "price" => $price,
            "max_buy" => 0, // Sẽ được cập nhật sau
            "options" => $option,
            "icon_id" => $icon_id,
            "created_at" => date('Y-m-d H:i:s'),
            "status" => "active"
        );
        
        $CVH->insert("shop_log", $log_data);
        
        $CVH->Ex(true, "Thêm sản phẩm thành công!");
    } else {
        $CVH->Ex(false, "Địt mẹ mày cút ngay!");
    }

} else {
    $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");
}
?>