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

// Kiểm tra bảng player có tồn tại không
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'player'");
if(!$checkTable) {
    echo json_encode(['status'=>false,'message'=>'Lỗi kiểm tra bảng: ' . mysqli_error($conn)]);
    exit;
}

$results = array();

if(mysqli_num_rows($checkTable) > 0) {
    // Tìm kiếm trong bảng player
    if (!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $search_lower = strtolower($search);
        
        // 1. Tìm chính xác ID (độ ưu tiên cao nhất)
        if(is_numeric($search)) {
            $sql_exact_id = "SELECT *, 1 as priority FROM `player` WHERE `id` = '$search'";
            $query = mysqli_query($conn, $sql_exact_id);
            while ($row = mysqli_fetch_assoc($query)) {
                $results[] = $row;
            }
        }
        
        // 2. Tìm tên chính xác (không phân biệt hoa thường)
        $sql_exact_name = "SELECT *, 2 as priority FROM `player` WHERE LOWER(`name`) = '$search_lower'";
        $query = mysqli_query($conn, $sql_exact_name);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 3. Tìm tên bắt đầu bằng từ khóa
        $sql_start_name = "SELECT *, 3 as priority FROM `player` WHERE LOWER(`name`) LIKE '$search_lower%' AND LOWER(`name`) != '$search_lower'";
        $query = mysqli_query($conn, $sql_start_name);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 4. Tìm tên chứa từ khóa
        $sql_contains = "SELECT *, 4 as priority FROM `player` WHERE LOWER(`name`) LIKE '%$search_lower%' AND LOWER(`name`) NOT LIKE '$search_lower%'";
        $query = mysqli_query($conn, $sql_contains);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 5. Tìm ID chứa từ khóa (nếu search là số)
        if(is_numeric($search)) {
            $sql_id_contains = "SELECT *, 5 as priority FROM `player` WHERE `id` LIKE '%$search%' AND `id` != '$search'";
            $query = mysqli_query($conn, $sql_id_contains);
            while ($row = mysqli_fetch_assoc($query)) {
                $results[] = $row;
            }
        }
        
    } else {
        // Hiển thị 30 player đầu tiên nếu không có từ khóa
        $sql = "SELECT *, 0 as priority FROM `player` ORDER BY `name` ASC LIMIT 30";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
    }
    
} else {
    // Fallback sang bảng account
    if (!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $search_lower = strtolower($search);
        
        // 1. Tìm chính xác ID (độ ưu tiên cao nhất)
        if(is_numeric($search)) {
            $sql_exact_id = "SELECT id, username as name, level, 1 as priority FROM `account` WHERE `id` = '$search' AND is_admin = 0";
            $query = mysqli_query($conn, $sql_exact_id);
            while ($row = mysqli_fetch_assoc($query)) {
                $results[] = $row;
            }
        }
        
        // 2. Tìm tên chính xác (không phân biệt hoa thường)
        $sql_exact_name = "SELECT id, username as name, level, 2 as priority FROM `account` WHERE LOWER(`username`) = '$search_lower' AND is_admin = 0";
        $query = mysqli_query($conn, $sql_exact_name);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 3. Tìm tên bắt đầu bằng từ khóa
        $sql_start_name = "SELECT id, username as name, level, 3 as priority FROM `account` WHERE LOWER(`username`) LIKE '$search_lower%' AND LOWER(`username`) != '$search_lower' AND is_admin = 0";
        $query = mysqli_query($conn, $sql_start_name);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 4. Tìm tên chứa từ khóa
        $sql_contains = "SELECT id, username as name, level, 4 as priority FROM `account` WHERE LOWER(`username`) LIKE '%$search_lower%' AND LOWER(`username`) NOT LIKE '$search_lower%' AND is_admin = 0";
        $query = mysqli_query($conn, $sql_contains);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 5. Tìm ID chứa từ khóa (nếu search là số)
        if(is_numeric($search)) {
            $sql_id_contains = "SELECT id, username as name, level, 5 as priority FROM `account` WHERE `id` LIKE '%$search%' AND `id` != '$search' AND is_admin = 0";
            $query = mysqli_query($conn, $sql_id_contains);
            while ($row = mysqli_fetch_assoc($query)) {
                $results[] = $row;
            }
        }
        
    } else {
        // Hiển thị 30 account đầu tiên nếu không có từ khóa
        $sql = "SELECT id, username as name, level, 0 as priority FROM `account` WHERE is_admin = 0 ORDER BY `username` ASC LIMIT 30";
        $query = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
    }
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

// Sắp xếp theo độ ưu tiên và tên
usort($unique_results, function($a, $b) {
    if ($a['priority'] != $b['priority']) {
        return $a['priority'] - $b['priority'];
    }
    return strcasecmp($a['name'], $b['name']);
});

// Giới hạn kết quả
$unique_results = array_slice($unique_results, 0, 30);

// Chuyển đổi thành JSON format
$data = [];
if (!empty($unique_results)) {
    foreach ($unique_results as $row) {
        $name = htmlspecialchars($row['name']);
        $id = $row['id'];
        
        // Highlight từ khóa tìm kiếm
        if (!empty($search)) {
            $name = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', $name);
            $id = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', $id);
        }
        
        $data[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'level' => $row['level'] ?? 'N/A',
            'display_name' => $name,
            'display_id' => $id
        ];
    }
}

echo json_encode(['status'=>true, 'data'=>$data]);

mysqli_close($conn);
?>
