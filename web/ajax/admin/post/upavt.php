<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
if($user['is_admin']){
$targetDir = $_SERVER['DOCUMENT_ROOT'] . '/images/avatar/admin/'; 
$files = scandir($targetDir);

$highestNumber = 0;
foreach ($files as $file) {
    if (preg_match('/^(\d+)\.png$/', $file, $matches)) {
        $highestNumber = max($highestNumber, (int)$matches[1]);
    }
}

$newFileName = ($highestNumber + 1) . '.png';

$targetFile = $targetDir . $newFileName;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($file['type'], $allowedTypes)) {
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                echo json_encode(['success' => true, 'message' => 'Tải lên thành công!', 'fileName' => $newFileName]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi di chuyển file.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Loại file không hợp lệ.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi khi tải file lên.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
    exit;
}
}
?>