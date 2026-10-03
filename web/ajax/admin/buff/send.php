<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'].'/cvhvn/autoload.php';

// Kiểm tra quyền
if(!$user['is_admin'] || (!$user['is_super_admin'] && empty($user['perm_buff_item_manager']))){
    echo json_encode(['status'=>false,'message'=>'Không có quyền buff item']);
    exit;
}

// Kiểm tra CSRF token
if (!isset($_POST['csrf_token']) || !$CVH->validateCSRFToken($user['id'], $_POST['csrf_token'])) {
    echo json_encode(['status'=>false,'message'=>'Token không hợp lệ']);
    exit;
}

// Lấy dữ liệu
$player_id = isset($_POST['player_id']) ? intval($_POST['player_id']) : 0;
$player_name = isset($_POST['player_name']) ? trim($_POST['player_name']) : '';
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$options_string = isset($_POST['options']) ? trim($_POST['options']) : '';
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Validation
if($player_id <= 0) {
    echo json_encode(['status'=>false,'message'=>'Player ID không hợp lệ']);
    exit;
}

if(empty($player_name)) {
    echo json_encode(['status'=>false,'message'=>'Vui lòng nhập tên người chơi']);
    exit;
}

if($item_id <= 0) {
    echo json_encode(['status'=>false,'message'=>'Item ID không hợp lệ']);
    exit;
}

if($quantity < 1 || $quantity > 100) {
    echo json_encode(['status'=>false,'message'=>'Số lượng phải từ 1-100']);
    exit;
}

// Validate options format
if(!empty($options_string)) {
    $options = explode(';', $options_string);
    foreach($options as $option) {
        $parts = explode('-', trim($option));
        if(count($parts) != 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
            echo json_encode(['status'=>false,'message'=>'Format options không đúng (vd: 50-20;30-1)']);
            exit;
        }
    }
}

$conn = $CVH->connect_db();

// Kiểm tra player có tồn tại trong database không (theo ID)
$playerExists = false;
$actualPlayerName = '';

// Thử tìm trong bảng player trước
$checkPlayer = mysqli_query($conn, "SHOW TABLES LIKE 'player'");
if(mysqli_num_rows($checkPlayer) > 0) {
    $stmt = mysqli_prepare($conn, "SELECT id, name FROM player WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $player_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if(mysqli_num_rows($result) > 0) {
        $playerData = mysqli_fetch_assoc($result);
        $playerExists = true;
        $actualPlayerName = $playerData['name'];
    }
}

// Nếu không tìm thấy trong player, thử tìm trong account
if(!$playerExists) {
    $stmt = mysqli_prepare($conn, "SELECT id, username FROM account WHERE id = ? AND is_admin = 0");
    mysqli_stmt_bind_param($stmt, 'i', $player_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if(mysqli_num_rows($result) > 0) {
        $playerData = mysqli_fetch_assoc($result);
        $playerExists = true;
        $actualPlayerName = $playerData['username'];
    }
}

if(!$playerExists) {
    echo json_encode(['status'=>false,'message'=>'Người chơi không tồn tại trong database']);
    exit;
}

// Sử dụng tên thực từ database thay vì tên nhập vào
$player_name = $actualPlayerName;

// Thêm yêu cầu buff vào database
$stmt = mysqli_prepare($conn, "
    INSERT INTO buff_requests (player_id, player_name, item_id, options_string, quantity, admin_name, admin_id, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
");

mysqli_stmt_bind_param($stmt, 'isisisi', $player_id, $player_name, $item_id, $options_string, $quantity, $user['username'], $user['id']);

if(mysqli_stmt_execute($stmt)) {
    $request_id = mysqli_insert_id($conn);
    
    // Log admin action
    try {
        $log_message = "Buff item: $player_name (ID: $player_id) - Item ID: $item_id - Quantity: $quantity";
        if(!empty($options_string)) {
            $log_message .= " - Options: $options_string";
        }
        
        $CVH->insert('admin_log', [
            'admin_id' => $user['id'],
            'admin_name' => $user['username'],
            'action' => 'buff_item',
            'details' => $log_message,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } catch(Exception $e) {
        // Log error silently
    }
    
    echo json_encode(['status'=>true,'message'=>'Đã gửi yêu cầu buff item thành công']);
} else {
    echo json_encode(['status'=>false,'message'=>'Lỗi database: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>
