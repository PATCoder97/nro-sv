<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $CVH->Ex(false, "Phương thức không hợp lệ!");
    exit();
}

$recaptchaSecret = $config['recaptcha_secret'] ?? '';
if ($recaptchaSecret !== '') {
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $verify = @file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($recaptchaSecret)
        . '&response=' . urlencode($recaptchaResponse)
    );
    $captcha = @json_decode($verify);
    if (empty($captcha) || empty($captcha->success)) {
        $CVH->Ex(false, "Vui lòng xác thực captcha!");
        exit();
    }
}

$username = trim((string) ($_POST['username'] ?? ''));
$email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
if ($username === '' || !$email) {
    $CVH->Ex(false, "Thông tin chưa chính xác!");
    exit();
}

$conn = $CVH->connect_db();
$find = $conn->prepare(
    "SELECT id, username FROM account
     WHERE username = ?
       AND JSON_UNQUOTE(JSON_EXTRACT(email, '$.email')) = ?
       AND JSON_UNQUOTE(JSON_EXTRACT(email, '$.verify')) = 'true'
     LIMIT 1"
);
$find->bind_param('ss', $username, $email);
$find->execute();
$result = $find->get_result();
$account = $result ? $result->fetch_assoc() : null;
$find->close();

if (!$account) {
    $CVH->Ex(false, "Thông tin chưa chính xác!");
    exit();
}

$newPassword = $CVH->taoMK(8);
$safeUsername = htmlspecialchars($account['username'], ENT_QUOTES, 'UTF-8');
$safePassword = htmlspecialchars($newPassword, ENT_QUOTES, 'UTF-8');
$siteHost = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'Teamobi 2026', ENT_QUOTES, 'UTF-8');
$body = "<h2>Khôi phục mật khẩu</h2>"
    . "<p>Xin chào <strong>{$safeUsername}</strong>, mật khẩu mới của bạn là:</p>"
    . "<p style=\"font-size:28px;font-weight:bold;letter-spacing:2px\">{$safePassword}</p>"
    . "<p>Hãy đăng nhập và đổi lại mật khẩu ngay. Nếu bạn không yêu cầu thao tác này, hãy liên hệ quản trị viên.</p>"
    . "<p>{$siteHost}</p>";

if (!sendMail($email, 'Khôi phục mật khẩu', $body)) {
    $CVH->Ex(false, "Không thể gửi email. Vui lòng kiểm tra cấu hình SMTP!");
    exit();
}

try {
    $conn->begin_transaction();
    $accountId = (int) $account['id'];
    $update = $conn->prepare('UPDATE account SET password = ?, legacy_token_revoked = 1 WHERE id = ?');
    $update->bind_param('si', $newPassword, $accountId);
    $update->execute();
    $update->close();

    $revoke = $conn->prepare('DELETE FROM cvh_sessions WHERE user_id = ?');
    $revoke->bind_param('i', $accountId);
    $revoke->execute();
    $revoke->close();
    $conn->commit();

    $CVH->Ex(true, "Mật khẩu mới đã được gửi đến email của bạn!");
} catch (Throwable $error) {
    $conn->rollback();
    error_log('Khôi phục mật khẩu thất bại: ' . $error->getMessage());
    $CVH->Ex(false, "Không thể cập nhật mật khẩu lúc này, vui lòng liên hệ quản trị viên!");
}
?>
