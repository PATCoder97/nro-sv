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
$inputCode = trim((string) ($_POST['code'] ?? ''));
if (!$email || !preg_match('/^\d{6}$/', $inputCode)) {
    $CVH->Ex(false, "Email hoặc mã xác thực không hợp lệ!");
    exit();
}

$emailData = json_decode((string) ($user['email'] ?? ''), true);
if (!is_array($emailData)) {
    $CVH->Ex(false, "Vui lòng yêu cầu gửi mã xác thực trước!");
    exit();
}
if (($emailData['verify'] ?? '') === 'true') {
    $CVH->Ex(false, "Tài khoản đã xác thực email trước đó!");
    exit();
}
if (!hash_equals((string) ($emailData['email'] ?? ''), (string) $email)) {
    $CVH->Ex(false, "Email không khớp với địa chỉ đã nhận mã!");
    exit();
}
if (!hash_equals((string) ($emailData['code'] ?? ''), $inputCode)) {
    $CVH->Ex(false, "Mã xác thực không đúng!");
    exit();
}
if (time() > strtotime((string) ($emailData['timecode'] ?? ''))) {
    $CVH->Ex(false, "Mã xác thực đã hết hạn, vui lòng yêu cầu mã mới!");
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

$emailData['verify'] = 'true';
unset($emailData['code'], $emailData['timecode']);
$emailJson = json_encode($emailData, JSON_UNESCAPED_UNICODE);
$update = $conn->prepare('UPDATE account SET email = ? WHERE id = ?');
$update->bind_param('si', $emailJson, $userId);
$update->execute();
$update->close();

$CVH->Ex(true, "Email đã được liên kết và xác thực thành công!");
?>
