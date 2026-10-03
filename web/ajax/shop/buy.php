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
    $CVH->Ex(false, "Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại!");
    exit();
}
if ((int) ($user['ban'] ?? 0) !== 0) {
    $CVH->Ex(false, "Tài khoản của bạn đang bị khóa!");
    exit();
}

$rateLimitKey = 'buy_rate_limit_' . $user['id'];
$currentTime = time();
if (isset($_SESSION[$rateLimitKey]) && ($currentTime - $_SESSION[$rateLimitKey]) < 1) {
    $CVH->Ex(false, "Vui lòng chờ 1 giây trước giao dịch tiếp theo!");
    exit();
}
$_SESSION[$rateLimitKey] = $currentTime;

if (!isset($_POST['csrf_token']) || !$CVH->validateCSRFToken($user['id'], $_POST['csrf_token'])) {
    $CVH->Ex(false, "Token bảo mật không hợp lệ!");
    exit();
}

$itemId = (int) ($_POST['Tempid'] ?? 0);
if ($itemId <= 0) {
    $CVH->Ex(false, "ID sản phẩm không hợp lệ!");
    exit();
}

$player = $CVH->player($user['id']);
if (!$player) {
    $CVH->Ex(false, "Vui lòng tạo nhân vật trước khi mua vật phẩm!");
    exit();
}

$conn = $CVH->connect_db();
try {
    $conn->begin_transaction();

    $itemStmt = $conn->prepare(
        'SELECT slot, price, users_buy FROM cvh_sell_item WHERE id = ? AND active = 1 FOR UPDATE'
    );
    $itemStmt->bind_param('i', $itemId);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();
    $item = $itemResult ? $itemResult->fetch_assoc() : null;
    $itemStmt->close();

    if (!$item) {
        throw new RuntimeException("Sản phẩm không tồn tại hoặc đã bị vô hiệu hóa!");
    }
    if ((int) $item['slot'] < 1) {
        throw new RuntimeException("Sản phẩm đã hết số lượng!");
    }

    $userId = (int) $user['id'];
    $userStmt = $conn->prepare('SELECT vnd FROM account WHERE id = ? FOR UPDATE');
    $userStmt->bind_param('i', $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $lockedUser = $userResult ? $userResult->fetch_assoc() : null;
    $userStmt->close();

    $price = max(0, (int) $item['price']);
    if (!$lockedUser || (int) $lockedUser['vnd'] < $price) {
        throw new RuntimeException("Tài khoản không đủ " . number_format($price) . "đ!");
    }

    $buyers = json_decode((string) $item['users_buy'], true);
    if (!is_array($buyers)) {
        $buyers = [];
    }
    array_unshift($buyers, [
        'uid' => (int) $player['id'],
        'status' => 0,
        'time' => time(),
    ]);
    $buyersJson = json_encode($buyers, JSON_UNESCAPED_UNICODE);

    $itemUpdate = $conn->prepare(
        'UPDATE cvh_sell_item SET slot = slot - 1, users_buy = ? WHERE id = ? AND slot > 0'
    );
    $itemUpdate->bind_param('si', $buyersJson, $itemId);
    $itemUpdate->execute();
    if ($itemUpdate->affected_rows !== 1) {
        $itemUpdate->close();
        throw new RuntimeException("Sản phẩm vừa hết số lượng, vui lòng thử lại!");
    }
    $itemUpdate->close();

    $balanceUpdate = $conn->prepare('UPDATE account SET vnd = vnd - ? WHERE id = ? AND vnd >= ?');
    $balanceUpdate->bind_param('iii', $price, $userId, $price);
    $balanceUpdate->execute();
    if ($balanceUpdate->affected_rows !== 1) {
        $balanceUpdate->close();
        throw new RuntimeException("Số dư đã thay đổi, vui lòng thử lại!");
    }
    $balanceUpdate->close();

    $conn->commit();
    error_log("PURCHASE: User {$user['username']} (#{$userId}) purchased shop item #{$itemId} for {$price} VND");
    $CVH->Ex(true, "Mua vật phẩm thành công, vui lòng vào game để nhận!");
} catch (Throwable $error) {
    $conn->rollback();
    $CVH->Ex(false, $error instanceof RuntimeException
        ? $error->getMessage()
        : "Không thể hoàn tất giao dịch, vui lòng thử lại!");
}
?>
