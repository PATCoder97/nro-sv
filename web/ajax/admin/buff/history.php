<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'].'/cvhvn/autoload.php';

// Kiểm tra quyền
if(!$user['is_admin'] || (!$user['is_super_admin'] && empty($user['perm_buff_item_manager']))){
    echo json_encode(['status'=>false,'message'=>'Không có quyền xem buff item']);
    exit;
}

$conn = $CVH->connect_db();

// Lấy filter status
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build query
$sql = "SELECT id, player_name, item_id, options_string, quantity, status, admin_name, created_at, result_message 
        FROM buff_requests";

if(!empty($status_filter)) {
    $status_filter = mysqli_real_escape_string($conn, $status_filter);
    $sql .= " WHERE status = '$status_filter'";
}

$sql .= " ORDER BY created_at DESC LIMIT 50";

$result = mysqli_query($conn, $sql);

if($result) {
    $data = [];
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['id'],
            'player_name' => $row['player_name'],
            'item_id' => $row['item_id'],
            'options_string' => $row['options_string'],
            'quantity' => $row['quantity'],
            'status' => $row['status'],
            'admin_name' => $row['admin_name'],
            'created_at' => date('d/m/Y H:i:s', strtotime($row['created_at'])),
            'result_message' => $row['result_message']
        ];
    }
    
    echo json_encode(['status'=>true, 'data'=>$data]);
} else {
    echo json_encode(['status'=>false, 'message'=>'Lỗi database: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>
