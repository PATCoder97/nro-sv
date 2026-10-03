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
if (!$user) {
    $CVH->Ex(false, "Bạn chưa đăng nhập!");
    exit();
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $CVH->Ex(false, "CSRF token không hợp lệ!");
    exit();
}

$email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
if (!$email) {
    $CVH->Ex(false, "Địa chỉ email không hợp lệ!");
    exit();
}

$conn = $CVH->connect_db();
$userId = (int) $user['id'];
$duplicate = $conn->prepare(
    "SELECT id FROM account
     WHERE id <> ? AND JSON_UNQUOTE(JSON_EXTRACT(email, '$.email')) = ?
     LIMIT 1"
);
$duplicate->bind_param('is', $userId, $email);
$duplicate->execute();
$duplicateResult = $duplicate->get_result();
$emailInUse = $duplicateResult && $duplicateResult->num_rows > 0;
$duplicate->close();

if ($emailInUse) {
    $CVH->Ex(false, "Email đã được dùng cho tài khoản khác!");
    exit();
}

$verificationCode = (string) random_int(100000, 999999);
$expiresAt = time() + 120;
$siteHost = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'Teamobi 2026', ENT_QUOTES, 'UTF-8');
$username = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
$body = "<h2>Xác thực email</h2>"
    . "<p>Xin chào <strong>{$username}</strong>, mã xác thực của bạn là:</p>"
    . "<p style=\"font-size:30px;font-weight:bold;letter-spacing:4px\">{$verificationCode}</p>"
    . "<p>Mã có hiệu lực trong 2 phút.</p><p>{$siteHost}</p>";

if (!sendMail($email, 'Mã xác thực email', $body)) {
    $CVH->Ex(false, "Không thể gửi email. Vui lòng kiểm tra cấu hình SMTP!");
    exit();
}

$emailData = json_encode([
    'email' => $email,
    'verify' => 'false',
    'code' => $verificationCode,
    'timecode' => date('Y-m-d H:i:s', $expiresAt),
], JSON_UNESCAPED_UNICODE);
$update = $conn->prepare('UPDATE account SET email = ? WHERE id = ?');
$update->bind_param('si', $emailData, $userId);
$update->execute();
$update->close();

$CVH->Ex(true, "Mã xác thực đã được gửi đến email của bạn!");
?>
