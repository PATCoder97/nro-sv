<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
if (empty($user) || empty($user['is_admin'])) {
    $CVH->Ex(false, "Bạn không có quyền thực hiện thao tác này!");
    exit();
}
$type = abs((int) ($_GET['type'] ?? 0));
if ($type == '1') {
    $type = $_POST['type'];
    $username = $_POST['username'];
    if ($type == 'active') {

        if ($CVH->check_user_register($username)) {
            $CVH->Ex(true, "Kích hoạt tài khoản " . $username . " thành công!");
            $table = 'account';
            $data = array(
                "active" => 1
            );
            $where = 'username = "' . $username . '"';
            $CVH->update($table, $data, $where);
        } else {
            $CVH->Ex(false, "Tài khoản không tồn tại!");
        }

    } else if ($type == "unactive") {

        if ($CVH->check_user_register($username)) {
            $CVH->Ex(true, "Hủy kích hoạt tài khoản " . $username . " thành công!");
            $table = 'account';
            $data = array(
                "active" => 0
            );
            $where = 'username = "' . $username . '"';
            $CVH->update($table, $data, $where);
        } else {
            $CVH->Ex(false, "Tài khoản không tồn tại!");
        }
    } else {
        $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");
    }

} else if ($type == '2') {
    $money = abs($_POST['money']);
    $username = $_POST['username'];
    if ($CVH->check_user_register($username)) {
        $CVH->Ex(true, "Cộng " . number_format($money) . " cho tài khoản " . $username . " thành công");
        $huhu = $CVH->get_account_by_username($username);
        $table = 'account';
        $data = array(
            "vnd" =>  $huhu["vnd"] + $money
        );
        $where = 'username = "' . $username . '"';
        $CVH->update($table, $data, $where);
    } else {
        $CVH->Ex(false, "Tài khoản không tồn tại!");
    }

} else if ($type == '3') {

    $type = $_POST['type'];
    $username = $_POST['username'];
    if ($type == 'band') {

        if ($CVH->check_user_register($username)) {
            $CVH->Ex(true, "Khóa tài khoản " . $username . " thành công!");
            $table = 'account';
            $data = array(
                "ban" => 1
            );
            $where = 'username = "' . $username . '"';
            $CVH->update($table, $data, $where);
        } else {
            $CVH->Ex(false, "Tài khoản không tồn tại!");
        }

    } else if ($type == "unband") {

        if ($CVH->check_user_register($username)) {
            $CVH->Ex(true, "Mở khóa hoạt tài khoản " . $username . " thành công!");
            $table = 'account';
            $data = array(
                "ban" => 0
            );
            $where = 'username = "' . $username . '"';
            $CVH->update($table, $data, $where);
        } else {
            $CVH->Ex(false, "Tài khoản không tồn tại!");
        }
    } else {
        $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");
    }

} else {

    $CVH->Ex(false, "Lỗi rồi địt mẹ mày!");

}
