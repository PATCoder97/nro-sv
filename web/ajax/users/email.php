<?php
if (empty($_SERVER["HTTP_REFERER"])) {
    header("HTTP/1.0 403 Forbidden");
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/cvhvn/autoload.php";

$input_email = $_POST["email"];
$input_code = $_POST["code"];
$current_time = time();
$us = $user["username"];
$table = "account";

$query = "SELECT `email` FROM `account` WHERE `username` = '" . $us . "'";
$result = mysqli_query($CVH->connect_db(), $query);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $email_data = json_decode($row["email"], true);

    if ($email_data) {
        if ($email_data["verify"] == "false") {
            $stored_code = $email_data["code"];
            $timecode_expiry = strtotime($email_data["timecode"]);

            if ($stored_code == $input_code && $current_time <= $timecode_expiry) {
                if ($input_email) {
                    if ($CVH->check_email_exist($input_email)) {
                        $email_data["email"] = $input_email;
                        $email_data["verify"] = "true";
                        $updated_email_json = json_encode($email_data);
                        $update_query = "UPDATE `account` SET `email` = '" . mysqli_real_escape_string($CVH->connect_db(), $updated_email_json) . "' WHERE `username` = '" . $us . "'";
                        mysqli_query($CVH->connect_db(), $update_query);

                        $CVH->Ex(true, "Email đã được thêm và tài khoản đã được xác nhận!");
                    } else {
                        $CVH->Ex(false, "Email đã tồn tại ở tài khoản khác!");
                    }
                } else {
                    $CVH->Ex(false, "Vui lòng nhập email!");
                }
            } elseif ($current_time > $timecode_expiry) {
                $CVH->Ex(false, "Mã kích hoạt đã hết hạn, vui lòng yêu cầu mã mới!");
            } else {
                $CVH->Ex(false, "Mã kích hoạt không đúng, vui lòng kiểm tra lại!");
            }
        } else {
            $CVH->Ex(false, "Tài khoản đã được xác nhận trước đó!");
        }
    } else {
        $CVH->Ex(false, "Lỗi khi xử lý dữ liệu JSON!");
    }
} else {
    $CVH->Ex(false, "Không tìm thấy người dùng!");
}
?>
