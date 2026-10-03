<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
header('Content-Type: application/json; charset=utf-8');

// Kiểm tra quyền admin chặt chẽ hơn và chặn phương thức/CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $CVH->Ex(false, "Phương thức không hợp lệ!");
    exit();
}
if (empty($user) || empty($user['is_admin'])) {
    $CVH->Ex(false, "Bạn không có quyền thực hiện thao tác này!");
    exit();
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $CVH->Ex(false, "CSRF token không hợp lệ!");
    exit();
}

if ((isset($_POST['code']) && $_POST['code'] !== '')
    && (isset($_POST['item']) && ($_POST['item'] !== '' || $_POST['item'] === '0'))
    && (isset($_POST['count']) && $_POST['count'] !== '')
    && (isset($_POST['hsd']) && $_POST['hsd'] !== '')) {
    if (!empty($user['is_admin'])) {
        $code = ($_POST['code']);
        $itema = ($_POST['item']);
        $count = abs(intval($_POST['count']));
        $option_id = ($_POST['option_id']);
        $param_option = ($_POST['param_option']);
        $hsd = ($_POST['hsd']);
        // Chặn item đang bán trong shop không được thêm vào giftcode
        $itemIdInt = abs(intval($itema));
        $checkShop = mysqli_query($CVH->connect_db(), "SELECT id FROM `cvh_sell_item` WHERE `item` = {$itemIdInt} AND `active` = 1 LIMIT 1");
        if ($checkShop && mysqli_num_rows($checkShop) > 0) {
            $CVH->Ex(false, "Vật phẩm ID {$itemIdInt} đang bán trong shop. Không thể thêm vào giftcode!");
            exit();
        }
        if (count($_POST["option_id"]) > 0) {
            $data = array();
            for ($i = 0; $i < count($_POST["option_id"]); $i++) {
                if (trim($_POST["option_id"][$i] != '') || $_POST['option_id'] === '0' ) {
                    $id = isset($_POST["option_id"][$i])  ? $_POST["option_id"][$i] : 30;
                    $param = isset($_POST["param_option"][$i]) ? $_POST["param_option"][$i] : 0;
                    $item = array(
                        "id" => $id,
                        "param" => $param
                    );
                    $data[] = $item;
                }
            }
            $option = json_encode($data);
            if ($option == "[]") {
                $dataz = array(
                    "id" => 30,
                    "param" => 0
                );
                $option = json_encode([$dataz]);
            }
        }

        $soluong = isset($_POST["soluong"]) ? abs(intval($_POST["soluong"])) : 1;
        $itemz = array(
            "id" => $itema,
            "soluong" => $soluong
        );
        if ($code == 'rd') {
            $code = rand_string(6);
        }
        $itemz = json_encode([$itemz]);
        $table = "cvh_giftcode";
        $data = array(
            "id" => null,
            "code" => $code,
            "luot" => $count,
            "item" => $itemz,
            "option" => $option,
            "status" => true,
            "hsd" => $hsd,
            "time" => time()
        );
        $CVH->insert($table, $data);
        $CVH->Ex(true, "Thêm gift code " . $code . " thành công!");
    } else {
        $CVH->Ex(false, "Địt mẹ mày cút ngay!");
    }

} else {
    $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");
}
?>