<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if ($user) {
    if (isset($_POST['username']) !== $user['username']) {
        if ($user['active'] == 0) {
            if ($user['vnd'] >= $setting['amount_mtv']) {
                $table = 'account';
                $data = array(
                    "active" => 1
                );
                $where = 'username = "' . $user['username'] . '"';
                $CVH->update($table, $data, $where);
                $table = 'account';
                $data = 'vnd';
                $sodiem = $setting['amount_mtv'];
                $where = 'username = "' . $user['username'] . '"';
                $result = $CVH->tru($table, $data, $sodiem, $where);
                $CVH->Ex(true, "Bạn đã kích hoạt tài khoản thành công!");
            } else {
                $CVH->Ex(false, "Bạn không đủ " .number_format($setting['amount_mtv']). "đ để kích hoạt tài khoản!");
            }
        } else {
            $CVH->Ex(false, "Có lỗi xảy ra, vui lòng thử lại sau!");
        }
    } else {
        $CVH->Ex(false, "Có lỗi xảy ra, vui lòng thử lại sau!");
    }
} else {
    $CVH->Ex(false, "Bạn chưa đăng nhập vui lòng đăng nhập để thực hiện thao tác này!");
}