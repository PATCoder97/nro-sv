<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

// Kiểm tra reCAPTCHA (chỉ khi được cấu hình)
if (!empty($config['recaptcha_secret']) && !empty($_POST['g-recaptcha-response'])) {
    $recaptcha_secret = $config['recaptcha_secret'];
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
    $captcha_success = json_decode($verify);
    if (empty($captcha_success) || empty($captcha_success->success)) {
        $CVH->Ex(false, "Vui lòng xác thực captcha!");
        exit();
    }
}
if (!empty($_POST['username']) && !empty($_POST['password'])) {

    $username = $CVH->antil_text($CVH->rewrite($CVH->FormatString($_POST['username'])));
    $password = $CVH->FormatString($_POST['password']);

    if (!$CVH->check_user($username, $password)) {

        $CVH->Ex(false, "Tài khoản hoặc mật khẩu không chính xác!");

    } else {

        // Lấy thông tin tài khoản
        $account = $CVH->get_account_by_username($username);

        if (!$CVH->player($account['id'])) {
            $CVH->Ex(false, "Vui lòng tạo nhân vật trước khi đăng nhập");
        } else if (!$account['ban'] == 0) {
            $CVH->Ex(false, "Tài khoản của bạn đang bị khóa");
        } else {
            // Tạo session token ngẫu nhiên, lưu DB và set cookie an toàn
            $sessionToken = bin2hex(random_bytes(32));
            $conn = $CVH->connect_db();
            // Tạo bảng nếu chưa có
            $conn->query("CREATE TABLE IF NOT EXISTS cvh_sessions (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, token VARCHAR(128) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)");

            $expiresAt = date('Y-m-d H:i:s', time() + 7*24*60*60);
            $stmt2 = $conn->prepare("INSERT INTO cvh_sessions(user_id, token, expires_at) VALUES (?, ?, ?)");
            if ($stmt2) { $stmt2->bind_param('iss', $account['id'], $sessionToken, $expiresAt); $stmt2->execute(); $stmt2->close(); }

            $cookieOptions = ['expires' => time() + 7*24*60*60, 'path' => '/', 'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', 'httponly' => true, 'samesite' => 'Lax'];
            setcookie('session_token', $sessionToken, $cookieOptions);

            $CVH->Ex(true, "Đăng nhập thành công!");
        }
    }

} else {

    $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");

}