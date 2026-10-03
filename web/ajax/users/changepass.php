<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if (!empty($_POST['mkcu']) && !empty($_POST['mkmoi']) && !empty($_POST['remkmoi'])) {

    $mkcu = $CVH->rewrite($CVH->FormatString($_POST['mkcu']));
    $mkmoi = $CVH->rewrite($CVH->FormatString($_POST['mkmoi']));
    $remkmoi = $CVH->rewrite($CVH->FormatString($_POST['remkmoi']));

    if ($user) {

        if ($mkmoi == $remkmoi) {

            if ($CVH->LimitString($mkmoi, 4, 9) == false) {
                $CVH->Ex(false, "Mật khẩu bắt buộc phải từ 4 tới 9 ký tự!");
            } else {
                if ($user['password'] === $mkcu) {
                    
                    $table = "account";
                    $data = array(
                        "password" => $mkmoi
                    );
                    $CVH->update($table, $data, 'username = "' . $user['username'] . '"');
                    
                    setcookie('token', '', time() - 3600, '/');
                    $CVH->Ex(true, "Đổi mật khẩu thành công vui lòng đăng nhập lại!");
                    
                } else {
                    $CVH->Ex(false, "Mật khẩu cũ không đúng!");
                }
            }

        } else {
            $CVH->Ex(false, "Hai mật khẩu mới bạn nhập chưa giống nhau!");
        }

    } else {
        $CVH->Ex(false, "Bạn chưa đăng nhập!");
    }

} else {
    $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");
}