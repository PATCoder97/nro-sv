<?php
// Bảo mật: Kiểm tra referer
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

// Bảo mật: Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    echo "Method not allowed";
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

// Bảo mật: Kiểm tra session và user
if (!$user) {
    $CVH->Ex(false, "Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại!");
    exit();
}

// Bảo mật: Rate limiting - giới hạn 1 request/giây
$rate_limit_key = 'buy_rate_limit_' . $user['id'];
$current_time = time();
if (isset($_SESSION[$rate_limit_key]) && ($current_time - $_SESSION[$rate_limit_key]) < 1) {
    $CVH->Ex(false, "Vui lòng chờ 1 giây trước khi thực hiện giao dịch tiếp theo!");
    exit();
}
$_SESSION[$rate_limit_key] = $current_time;

// Bảo mật: CSRF Protection using database
if (!isset($_POST['csrf_token']) || !$CVH->validateCSRFToken($user['id'], $_POST['csrf_token'])) {
    $CVH->Ex(false, "Token bảo mật không hợp lệ!");
    exit();
}

// Bảo mật: Validate input
$id = isset($_POST['Tempid']) ? intval($_POST['Tempid']) : 0;
if ($id <= 0) {
    $CVH->Ex(false, "ID sản phẩm không hợp lệ!");
    exit();
}

// Bảo mật: Kiểm tra item có tồn tại và active không
$row = $CVH->get_row("SELECT * FROM `cvh_sell_item` WHERE `id` = $id AND `active` = 1");
if (!$row) {
    $CVH->Ex(false, "Sản phẩm không tồn tại hoặc đã bị vô hiệu hóa!");
    exit();
}

// Bảo mật: Kiểm tra slot còn lại
if ($row['slot'] < 1) {
    $CVH->Ex(false, "Sản phẩm đã hết số lượt mua!");
    exit();
}

// Bảo mật: Kiểm tra tiền trong tài khoản
if ($user['vnd'] < $row['price']) {
    $CVH->Ex(false, "Tài khoản của bạn không đủ " . number_format($row["price"]) . "đ vui lòng nạp thêm tiền để thực hiện giao dịch!");
    exit();
}

// Bảo mật: Kiểm tra user không bị khóa
if (isset($user['status']) && $user['status'] != 1) {
    $CVH->Ex(false, "Tài khoản của bạn đã bị khóa!");
    exit();
}

// Bảo mật: Transaction để tránh race condition
mysqli_begin_transaction($CVH->connect_db());

try {
    // Kiểm tra lại slot và tiền sau khi bắt đầu transaction
    $current_item = $CVH->get_row("SELECT slot FROM `cvh_sell_item` WHERE `id` = $id FOR UPDATE");
    $current_user = $CVH->get_row("SELECT vnd FROM `account` WHERE `id` = " . $user['id'] . " FOR UPDATE");
    
    if (!$current_item || $current_item['slot'] < 1) {
        throw new Exception("Sản phẩm đã hết số lượt mua!");
    }
    
    if (!$current_user || $current_user['vnd'] < $row['price']) {
        throw new Exception("Tài khoản không đủ tiền!");
    }
    
    // Thực hiện giao dịch
    $player = $CVH->player($user['id']);
    
    // Ghi log mua hàng
    $CVH->addBuy($id, $player['id'], time(), 0);
    
    // Cập nhật slot
    $data = array("slot" => $row['slot'] - 1);
    $CVH->update("cvh_sell_item", $data, 'id = ' . $id);
    
    // Trừ tiền thành viên
    $table = 'account';
    $where = 'username = "' . mysqli_real_escape_string($CVH->connect_db(), $user['username']) . '"';
    $CVH->tru($table, "vnd", $row['price'], $where);
    
    // Commit transaction
    mysqli_commit($CVH->connect_db());
    
    // Ghi log bảo mật
    error_log("PURCHASE: User {$user['username']} (ID: {$user['id']}) purchased item ID: $id for " . number_format($row['price']) . " VND at " . date('Y-m-d H:i:s'));
    
    $CVH->Ex(true, "Mua vật phẩm thành công vui lòng vào game để nhận!");
    
} catch (Exception $e) {
    // Rollback nếu có lỗi
    mysqli_rollback($CVH->connect_db());
    $CVH->Ex(false, $e->getMessage());
}
?>