<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'].'/cvhvn/autoload.php';

// Kiểm tra quyền
if(!$user['is_admin'] || (!$user['is_super_admin'] && empty($user['perm_buff_item_manager']))){
    echo json_encode(['status'=>false,'message'=>'Không có quyền buff item']);
    exit;
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

$conn = $CVH->connect_db();

// Kiểm tra bảng item_template có tồn tại không
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'item_template'");
if(mysqli_num_rows($checkTable) == 0) {
    echo json_encode(['status'=>false,'message'=>'Bảng item_template không tồn tại']);
    exit;
}

$results = array();

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $search_lower = strtolower($search);
    
    // 1. Tìm chính xác ID (độ ưu tiên cao nhất)
    $sql_exact_id = "SELECT *, 1 as priority FROM `item_template` WHERE `id` = '$search'";
    $query = mysqli_query($conn, $sql_exact_id);
    while ($row = mysqli_fetch_assoc($query)) {
        $results[] = $row;
    }
    
    // 2. Tìm ID bắt đầu bằng từ khóa
    $sql_start_id = "SELECT *, 2 as priority FROM `item_template` WHERE `id` LIKE '$search%' AND `id` != '$search'";
    $query = mysqli_query($conn, $sql_start_id);
    while ($row = mysqli_fetch_assoc($query)) {
        $results[] = $row;
    }
    
    // 3. Tìm tên chính xác (không phân biệt hoa thường)
    $sql_exact_name = "SELECT *, 3 as priority FROM `item_template` WHERE LOWER(`NAME`) = '$search_lower'";
    $query = mysqli_query($conn, $sql_exact_name);
    while ($row = mysqli_fetch_assoc($query)) {
        $results[] = $row;
    }
    
    // 4. Tìm tên bắt đầu bằng từ khóa
    $sql_start_name = "SELECT *, 4 as priority FROM `item_template` WHERE LOWER(`NAME`) LIKE '$search_lower%' AND LOWER(`NAME`) != '$search_lower'";
    $query = mysqli_query($conn, $sql_start_name);
    while ($row = mysqli_fetch_assoc($query)) {
        $results[] = $row;
    }
    
    // 5. Tìm tên chứa từ khóa
    $sql_contains = "SELECT *, 5 as priority FROM `item_template` WHERE LOWER(`NAME`) LIKE '%$search_lower%' AND LOWER(`NAME`) NOT LIKE '$search_lower%'";
    $query = mysqli_query($conn, $sql_contains);
    while ($row = mysqli_fetch_assoc($query)) {
        $results[] = $row;
    }
    
    // 6. Tìm ID chứa từ khóa
    $sql_id_contains = "SELECT *, 6 as priority FROM `item_template` WHERE `id` LIKE '%$search%' AND `id` NOT LIKE '$search%'";
    $query = mysqli_query($conn, $sql_id_contains);
    while ($row = mysqli_fetch_assoc($query)) {
        $results[] = $row;
    }
    
    // Loại bỏ trùng lặp và sắp xếp theo độ ưu tiên
    $unique_results = array();
    $seen_ids = array();
    foreach ($results as $row) {
        if (!in_array($row['id'], $seen_ids)) {
            $unique_results[] = $row;
            $seen_ids[] = $row['id'];
        }
    }
    
    // Sắp xếp theo độ ưu tiên và ID
    usort($unique_results, function($a, $b) {
        if ($a['priority'] != $b['priority']) {
            return $a['priority'] - $b['priority'];
        }
        return $a['id'] - $b['id'];
    });
    
    // Giới hạn kết quả
    $unique_results = array_slice($unique_results, 0, 30);
    
} else {
    // Hiển thị 30 item đầu tiên nếu không có từ khóa
    $sql = "SELECT *, 0 as priority FROM `item_template` ORDER BY `id` ASC LIMIT 30";
    $query = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
        $unique_results[] = $row;
    }
}

// Chuyển đổi thành JSON format
$data = [];
if (!empty($unique_results)) {
    foreach ($unique_results as $row) {
        $name = htmlspecialchars($row['NAME']);
        $id = $row['id'];
        
        // Highlight từ khóa tìm kiếm
        if (!empty($search)) {
            $name = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', $name);
            $id = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', $id);
        }
        
        $data[] = [
            'id' => $row['id'],
            'name' => $row['NAME'],
            'type' => $row['type'] ?? 'N/A',
            'display_name' => $name,
            'display_id' => $id
        ];
    }
}

echo json_encode(['status'=>true, 'data'=>$data]);

mysqli_close($conn);
?>
