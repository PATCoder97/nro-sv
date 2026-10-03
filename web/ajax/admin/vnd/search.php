<?php
header('Content-Type: application/json; charset=utf-8');

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
    if (!isset($CVH) || !isset($user)) {
        throw new Exception('Autoload failed');
    }
} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => 'Lỗi hệ thống']);
    exit;
}

// Quyền
if (!$user['is_admin'] || (!$user['is_super_admin'] && empty($user['perm_vnd_manager']))) {
    echo json_encode(['status' => false, 'message' => 'Không có quyền']);
    exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = isset($_GET['limit']) ? max(1, min(50, intval($_GET['limit']))) : 20;

if ($q === '') {
    echo json_encode(['status' => true, 'items' => [], 'total' => 0]);
    exit;
}

$esc = mysqli_real_escape_string($CVH->connect_db(), $q);
$sql = "
    SELECT id, username, email, vnd, last_time_login, create_time
    FROM account
    WHERE username LIKE '%$esc%' OR email LIKE '%$esc%' OR id = '$esc'
    ORDER BY vnd DESC, id DESC
    LIMIT $limit
";
$res = mysqli_query($CVH->connect_db(), $sql);

$items = [];
while ($row = mysqli_fetch_assoc($res)) {
    $items[] = [
        'id' => (int)$row['id'],
        'username' => $row['username'],
        'email' => $row['email'],
        'vnd' => (int)$row['vnd'],
        'last_time_login' => $row['last_time_login'],
        'create_time' => $row['create_time']
    ];
}

echo json_encode(['status' => true, 'items' => $items, 'total' => count($items)]);
