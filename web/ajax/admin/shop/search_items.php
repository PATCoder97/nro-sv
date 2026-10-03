<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if (!$user['is_admin']) {
    echo "Bạn không có quyền truy cập!";
    exit();
}

$type = $_POST['type'] ?? '';
$search = trim($_POST['search'] ?? '');

if ($type == 'items') {
    // Tìm kiếm vật phẩm với độ ưu tiên
    $results = array();
    
    if (!empty($search)) {
        $search = mysqli_real_escape_string($CVH->connect_db(), $search);
        $search_lower = strtolower($search);
        
        // 1. Tìm chính xác ID (độ ưu tiên cao nhất)
        $sql_exact_id = "SELECT *, 1 as priority FROM `item_template` WHERE `id` = '$search'";
        $query = $CVH->query($sql_exact_id);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 2. Tìm ID bắt đầu bằng từ khóa
        $sql_start_id = "SELECT *, 2 as priority FROM `item_template` WHERE `id` LIKE '$search%' AND `id` != '$search'";
        $query = $CVH->query($sql_start_id);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 3. Tìm tên chính xác (không phân biệt hoa thường)
        $sql_exact_name = "SELECT *, 3 as priority FROM `item_template` WHERE LOWER(`NAME`) = '$search_lower'";
        $query = $CVH->query($sql_exact_name);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 4. Tìm tên bắt đầu bằng từ khóa
        $sql_start_name = "SELECT *, 4 as priority FROM `item_template` WHERE LOWER(`NAME`) LIKE '$search_lower%' AND LOWER(`NAME`) != '$search_lower'";
        $query = $CVH->query($sql_start_name);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 5. Tìm tên chứa từ khóa
        $sql_contains = "SELECT *, 5 as priority FROM `item_template` WHERE LOWER(`NAME`) LIKE '%$search_lower%' AND LOWER(`NAME`) NOT LIKE '$search_lower%'";
        $query = $CVH->query($sql_contains);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 6. Tìm ID chứa từ khóa
        $sql_id_contains = "SELECT *, 6 as priority FROM `item_template` WHERE `id` LIKE '%$search%' AND `id` NOT LIKE '$search%'";
        $query = $CVH->query($sql_id_contains);
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
        $query = $CVH->query($sql);
        while ($row = mysqli_fetch_assoc($query)) {
            $unique_results[] = $row;
        }
    }
    
    // Hiển thị kết quả
    if (!empty($unique_results)) {
        foreach ($unique_results as $row) {
            $name = htmlspecialchars($row['NAME']);
            $id = $row['id'];
            
            // Highlight từ khóa tìm kiếm
            if (!empty($search)) {
                $name = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', $name);
                $id = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', $id);
            }
            
            echo '<tr>';
            echo '<td>' . $id . '</td>';
            echo '<td>' . $name . '</td>';
            echo '<td><button type="button" class="btn btn-sm btn-success" onclick="selectItem(' . $row['id'] . ', \'' . addslashes($row['NAME']) . '\')">Chọn</button></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3" class="text-center">Không tìm thấy vật phẩm nào</td></tr>';
    }
    
} elseif ($type == 'options') {
    // Tìm kiếm options với độ ưu tiên
    $results = array();
    
    if (!empty($search)) {
        $search = mysqli_real_escape_string($CVH->connect_db(), $search);
        $search_lower = strtolower($search);
        
        // 1. Tìm chính xác ID (độ ưu tiên cao nhất)
        $sql_exact_id = "SELECT *, 1 as priority FROM `item_option_template` WHERE `id` = '$search'";
        $query = $CVH->query($sql_exact_id);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 2. Tìm ID bắt đầu bằng từ khóa
        $sql_start_id = "SELECT *, 2 as priority FROM `item_option_template` WHERE `id` LIKE '$search%' AND `id` != '$search'";
        $query = $CVH->query($sql_start_id);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 3. Tìm tên chính xác (không phân biệt hoa thường)
        $sql_exact_name = "SELECT *, 3 as priority FROM `item_option_template` WHERE LOWER(`NAME`) = '$search_lower'";
        $query = $CVH->query($sql_exact_name);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 4. Tìm tên bắt đầu bằng từ khóa
        $sql_start_name = "SELECT *, 4 as priority FROM `item_option_template` WHERE LOWER(`NAME`) LIKE '$search_lower%' AND LOWER(`NAME`) != '$search_lower'";
        $query = $CVH->query($sql_start_name);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 5. Tìm tên chứa từ khóa
        $sql_contains = "SELECT *, 5 as priority FROM `item_option_template` WHERE LOWER(`NAME`) LIKE '%$search_lower%' AND LOWER(`NAME`) NOT LIKE '$search_lower%'";
        $query = $CVH->query($sql_contains);
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
        
        // 6. Tìm ID chứa từ khóa
        $sql_id_contains = "SELECT *, 6 as priority FROM `item_option_template` WHERE `id` LIKE '%$search%' AND `id` NOT LIKE '$search%'";
        $query = $CVH->query($sql_id_contains);
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
        // Hiển thị 30 option đầu tiên nếu không có từ khóa
        $sql = "SELECT *, 0 as priority FROM `item_option_template` ORDER BY `id` ASC LIMIT 30";
        $query = $CVH->query($sql);
        while ($row = mysqli_fetch_assoc($query)) {
            $unique_results[] = $row;
        }
    }
    
    // Hiển thị kết quả
    if (!empty($unique_results)) {
        foreach ($unique_results as $row) {
            $name = htmlspecialchars($row['NAME']);
            $id = $row['id'];
            
            // Highlight từ khóa tìm kiếm
            if (!empty($search)) {
                $name = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', $name);
                $id = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', $id);
            }
            
            echo '<tr>';
            echo '<td>' . $id . '</td>';
            echo '<td>' . $name . '</td>';
            echo '<td><button type="button" class="btn btn-sm btn-success" onclick="selectOption(' . $row['id'] . ', \'' . addslashes($row['NAME']) . '\')">Chọn</button></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3" class="text-center">Không tìm thấy option nào</td></tr>';
    }
} else {
    echo "Loại tìm kiếm không hợp lệ!";
}
?> 