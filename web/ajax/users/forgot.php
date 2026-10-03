<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
$recaptcha_secret = $config['recaptcha_secret'] ?? '';
$recaptcha_response = $_POST['g-recaptcha-response'];

if ($recaptcha_secret !== '') {
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . urlencode($recaptcha_secret) . "&response=" . urlencode($recaptcha_response));
    $captcha_success = json_decode($verify);
}
if ($recaptcha_secret !== '' && (empty($captcha_success) || !$captcha_success->success)) {
    $CVH->Ex(false, "Vui lòng xác thực captcha!");
    exit();
}
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

$username = $_POST["username"];
$email = $_POST["email"];

 if($CVH->check_username_email($username, $email)){
   $MKNE = $CVH->TaoMK(12);
   sendMail($email, 'Quên Mật Khẩu', '
   <body style="background-color:#eaf4fb; font-family:Arial,sans-serif; margin:0; padding:0;">
     <div style="max-width:500px; margin:40px auto; background:#fff; border-radius:10px; box-shadow:0 4px 16px rgba(79,140,255,0.10); overflow:hidden;">
       <div style="background:#4f8cff; padding:32px 0 16px 0; text-align:center;">
         <img src="https://files.catbox.moe/l2ixwq.png" alt="Logo" style="height:60px; margin-bottom:12px;">
         <h2 style="margin:0; font-size:26px; color:#fff; letter-spacing:1px;">NGỌC RỒNG VENUS</h2>
       </div>
       <div style="padding:32px 24px 24px 24px;">
         <p style="font-size:16px; color:#222;">Xin chào <b>'.$username.'</b>,</p>
         <p style="font-size:15px; color:#222; margin-bottom:24px;">
           Bạn vừa yêu cầu <b>cấp lại mật khẩu</b> cho tài khoản của mình.<br>
           Dưới đây là <b>mật khẩu mới</b> mà hệ thống đã tạo cho bạn:
         </p>
         <div style="background:#e3f0ff; border-radius:8px; padding:22px; text-align:center; margin-bottom:24px;">
           <span style="font-size:30px; font-weight:bold; letter-spacing:2px; color:#1976d2;">'.$MKNE.'</span>
         </div>
         <p style="font-size:15px; color:#222;">
           Vui lòng đăng nhập bằng mật khẩu mới này và <b>đổi lại mật khẩu</b> để đảm bảo an toàn cho tài khoản.<br>
           Nếu bạn không yêu cầu cấp lại mật khẩu, hãy bỏ qua email này.
         </p>
         <hr style="margin:32px 0 16px 0; border:none; border-top:1px solid #e3f0ff;">
         <p style="font-size:13px; color:#4f8cff; text-align:center;">
           Chúc bạn một ngày tốt lành!<br>
           <b>Ngọc Rồng Venus</b><br>
           <span style="color:#888;">venus.meliodas.info.vn</span>
         </p>
       </div>
     </div>
   </body>
   ');
    $data = array(
        "password" => $MKNE
    );
    $CVH->update('account', $data, "username = '" . $username . "'");

    $CVH->Ex(true, "Mật khẩu mới đã được gửi đến email của bạn!");
}else{
    $CVH->Ex(false, "Thông tin chưa chính xác vui lòng liên hệ admin!");
}
?>
