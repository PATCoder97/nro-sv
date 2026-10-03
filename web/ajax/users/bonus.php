<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
if ($user) {

    if ($CVH->checkNhanqua($user['username'])) {
        $response = array(
            "status" => 'OK',
            "msg" => '
        <div class="text-center">
        <img width="70%" src="https://community.facer.io/uploads/default/original/3X/6/a/6aa18e24a142fcbbd317899375c0827a035b4b59.gif"/>
        <h5>HalloWeen Cùng Ngọc Rồng Kite</h5>
        <b>Bạn Đã Nhận Quà Trước Đó Rồi</b>
        <p>Chúc Các Bạn Có Một Mùa HalloWeen Vui Vẻ!</p>
        </div>'
        );
    } else {
        $response = array(
            "status" => 'OK',
            "msg" => '
        <div class="text-center">
        <img width="70%" src="https://community.facer.io/uploads/default/original/3X/6/a/6aa18e24a142fcbbd317899375c0827a035b4b59.gif"/>
        <h5>HalloWeen Cùng Ngọc Rồng Kite</h5>
        <b>Bạn Nhận Được Phần Quà Là <span style="color: #FF0000;">10.000đ</span></b>
        <p>Chúc Các Bạn Có Một Mùa HalloWeen Vui Vẻ!</p>
        </div>'
        );
        $CVH->addNhanqua($user['username']);
        $table = 'account';
        $data = array(
            "vnd" => 10000
        );
        $where = 'username = "' . $user['username'] . '"';
        $CVH->update($table, $data, $where);
    }
} else {
    $response = array(
        "status" => 'LOGIN',
        "msg" => '
        <div class="text-center">
        <b>Vui Lòng Đăng Nhập</b>
        </div>'
    );
}

$json_response = json_encode($response);
header('Content-Type: application/json');
echo $json_response;
?>