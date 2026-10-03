<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

$email = $_POST["email"];
$current_time = time();

if ($user) {
 if(!$CVH->check_email_exist($email)){
    $rand = rand(100000, 999999);
    $timecode_expiry = $current_time + 120;

    sendMail($email, 'Mã Xác Thực Email', '
    <body style="background-color:#eaf4fb; font-family:Arial,sans-serif; margin:0; padding:0;">
      <div style="max-width:500px; margin:40px auto; background:#fff; border-radius:10px; box-shadow:0 4px 16px rgba(79,140,255,0.10); overflow:hidden;">
        <div style="background:#4f8cff; padding:32px 0 16px 0; text-align:center;">
          <img src="https://files.catbox.moe/l2ixwq.png" alt="Logo" style="height:60px; margin-bottom:12px;">
          <h2 style="margin:0; font-size:26px; color:#fff; letter-spacing:1px;">NGỌC RỒNG VENUS</h2>
        </div>
        <div style="padding:32px 24px 24px 24px;">
          <p style="font-size:16px; color:#222;">Xin chào <b>'.$user["username"].'</b>,</p>
          <p style="font-size:15px; color:#222; margin-bottom:24px;">
            Đây là <b>mã xác thực tài khoản</b> gồm 6 chữ số mà hệ thống đã gửi cho bạn.<br>
            Vui lòng nhập mã này vào website để xác thực email.
          </p>
          <div style="background:#e3f0ff; border-radius:8px; padding:22px; text-align:center; margin-bottom:24px;">
            <span style="font-size:32px; font-weight:bold; letter-spacing:4px; color:#1976d2;">'.$rand.'</span>
          </div>
          <p style="font-size:15px; color:#222;">
            <b>Lưu ý:</b> Mã này có giá trị trong <b>2 phút</b> kể từ khi nhận được email.<br>
            Nếu bạn không thực hiện yêu cầu này, hãy bỏ qua email này.
          </p>
          <hr style="margin:32px 0 16px 0; border:none; border-top:1px solid #e3f0ff;">
          <p style="font-size:13px; color:#4f8cff; text-align:center;">
            Trân trọng,<br>
            <b>Ngọc Rồng Venus</b><br>
            <span style="color:#888;">venus.meliodas.info.vn</span>
          </p>
        </div>
      </div>
    </body>
    ');
    $us = $user["username"];
    $table = "account";
    
    $data = array(
        "email" => json_encode(array(
            "email" => $email,
            "verify" => "false",
            "code" => $rand,
            "timecode" => date('Y-m-d H:i:s', $timecode_expiry)
        ))
    );

    $CVH->update($table, $data, "username = '" . $us . "'");

    $CVH->Ex(true, "Mã xác thực đã được gửi đến email của bạn!");
}else{
    $CVH->Ex(false, "Email đã tồn tại ở tài khoản khác!");
}
} else {
    $CVH->Ex(false, "Bạn chưa đăng nhập, vui lòng đăng nhập để thực hiện thao tác này!");
}
?>