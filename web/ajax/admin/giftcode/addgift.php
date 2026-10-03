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
if (empty($user) || empty($user['is_admin'])) {
    $CVH->Ex(false, "Bạn không có quyền thực hiện thao tác này!");
    exit();
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $CVH->Ex(false, "CSRF token không hợp lệ!");
    exit();
}

$code = trim((string) ($_POST['code'] ?? ''));
$itemId = filter_var($_POST['item'] ?? null, FILTER_VALIDATE_INT);
$count = filter_var($_POST['count'] ?? null, FILTER_VALIDATE_INT);
$quantity = filter_var($_POST['soluong'] ?? 1, FILTER_VALIDATE_INT);
$expires = trim((string) ($_POST['hsd'] ?? ''));

if ($code === '' || $itemId === false || $count === false || $quantity === false || $expires === '') {
    $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");
    exit();
}
if ($count < 1 || $quantity < 1) {
    $CVH->Ex(false, "Số lượt nhập và số lượng vật phẩm phải lớn hơn 0!");
    exit();
}
if (!preg_match('/^[A-Za-z0-9_-]{3,100}$/', $code) && $code !== 'rd') {
    $CVH->Ex(false, "Mã giftcode chỉ được chứa chữ, số, dấu gạch ngang hoặc gạch dưới!");
    exit();
}

$expiresDate = DateTime::createFromFormat('!Y-m-d', $expires);
if (!$expiresDate || $expiresDate->format('Y-m-d') !== $expires) {
    $CVH->Ex(false, "Ngày hết hạn không hợp lệ!");
    exit();
}
$expiresDate->setTime(23, 59, 59);
if ($expiresDate->getTimestamp() <= time()) {
    $CVH->Ex(false, "Ngày hết hạn phải sau thời điểm hiện tại!");
    exit();
}

$conn = $CVH->connect_db();
$shopStmt = $conn->prepare(
    "SELECT id FROM cvh_sell_item WHERE item = ? AND active = 1 LIMIT 1"
);
$shopStmt->bind_param('i', $itemId);
$shopStmt->execute();
$shopResult = $shopStmt->get_result();
$isSelling = $shopResult && $shopResult->num_rows > 0;
$shopStmt->close();

if ($isSelling) {
    $CVH->Ex(false, "Vật phẩm ID {$itemId} đang bán trong shop. Không thể thêm vào giftcode!");
    exit();
}

if ($code === 'rd') {
    $code = rand_string(6);
}

$options = [];
$optionIds = isset($_POST['option_id']) && is_array($_POST['option_id']) ? $_POST['option_id'] : [];
$optionParams = isset($_POST['param_option']) && is_array($_POST['param_option']) ? $_POST['param_option'] : [];
foreach ($optionIds as $index => $optionId) {
    if ($optionId === '' || !is_numeric($optionId)) {
        continue;
    }
    $options[] = [
        'id' => abs((int) $optionId),
        'param' => abs((int) ($optionParams[$index] ?? 0)),
    ];
}
if (empty($options)) {
    $options[] = ['id' => 30, 'param' => 0];
}

$detail = json_encode([[
    'id' => (int) $itemId,
    'quantity' => (int) $quantity,
    'options' => $options,
]], JSON_UNESCAPED_UNICODE);
$expiresSql = $expiresDate->format('Y-m-d H:i:s');

try {
    $duplicateStmt = $conn->prepare("SELECT id FROM giftcode WHERE code = ? LIMIT 1");
    $duplicateStmt->bind_param('s', $code);
    $duplicateStmt->execute();
    $duplicateResult = $duplicateStmt->get_result();
    $alreadyExists = $duplicateResult && $duplicateResult->num_rows > 0;
    $duplicateStmt->close();

    if ($alreadyExists) {
        $CVH->Ex(false, "Giftcode {$code} đã tồn tại!");
        exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO giftcode (code, count_left, detail, expired) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param('siss', $code, $count, $detail, $expiresSql);
    $stmt->execute();
    $stmt->close();

    $CVH->Ex(true, "Thêm giftcode {$code} thành công! Khởi động lại game server để tải code mới.");
} catch (Throwable $error) {
    error_log('Tạo giftcode thất bại: ' . $error->getMessage());
    $CVH->Ex(false, "Không thể tạo giftcode lúc này, vui lòng kiểm tra log máy chủ!");
}
?>
