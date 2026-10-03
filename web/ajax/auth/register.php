<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

// Kiểm tra reCAPTCHA nếu được cấu hình
if (!empty($config['recaptcha_secret'])) {
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    if (empty($recaptcha_response)) {
        $CVH->Ex(false, "Vui lòng xác thực captcha!");
        exit();
    }
    $verify = @file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret=" . $config['recaptcha_secret'] . "&response=" . $recaptcha_response
    );
    $captcha_success = @json_decode($verify);
    if (empty($captcha_success) || empty($captcha_success->success)) {
        $CVH->Ex(false, "Captcha không hợp lệ!");
        exit();
    }
}

// Chặn truy cập trực tiếp không hợp lệ (tuỳ chọn)
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['repassword'])) {

    $username = $CVH->rewrite($CVH->FormatString($_POST['username']));
    $password = $CVH->rewrite($CVH->FormatString($_POST['password']));
    $repassword = $CVH->rewrite($CVH->FormatString($_POST['repassword']));

    if ($password == $repassword) {

        if ($CVH->LimitString($username, 4, 9) == false) {

            $CVH->Ex(false, "Tên tài khoản bắt buộc phải từ 4 tới 9 ký tự!");

        } else if ($CVH->LimitString($password, 4, 9) == false) {

            $CVH->Ex(false, "Mật khẩu bắt buộc phải từ 4 tới 9 ký tự!");

        } else if ($CVH->check_user_register($username) == true) {

            $CVH->Ex(false, "Tài khoản đã tồn tại trên hệ thống!");

        } else if ($CVH->check_user_register($username) == false) {

            $token = $CVH->Token($username, $password);
            $table = "account";
            $data = array(
                "id" => null,
                "username" => $username,
                "password" => $password,
                "email" => '',
                "token" => $token,
                "xsrf_token" => '',
                "newpass" => ''
            );
            $CVH->insert($table, $data);

            $CVH->Ex(true, "Đăng ký thành công!");

        }

    } else {

        $CVH->Ex(false, "Hai mật khẩu bạn nhập chưa giống nhau!");

    }


} else {

    $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");

}
