<?php
// Bảo đảm luôn trả JSON, không in HTML lỗi
ini_set('display_errors', 0);
error_reporting(0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once '../../cvhvn/autoload.php';
header('Content-Type: application/json; charset=utf-8');

// Yêu cầu đăng nhập
if (!$user || empty($user['id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

try {
    // Dùng kết nối chuẩn của hệ thống (đã cấu hình trong cvhvn/database.php)
    $conn = $CVH->connect_db();
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
        exit;
    }

    // Tạo bảng nếu chưa có (mỗi tài khoản 1 mã duy nhất)
    $conn->query("CREATE TABLE IF NOT EXISTS `bank_payment_codes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `code` varchar(50) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_user` (`user_id`),
        UNIQUE KEY `uq_code` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $userId = intval($user['id']);

    // Kiểm tra đã có mã chưa
    $stmt = $conn->prepare('SELECT code FROM bank_payment_codes WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($existingCode);
    if ($stmt->fetch()) {
        $stmt->close();
        echo json_encode(['success' => true, 'code' => $existingCode]);
        exit;
    }
    $stmt->close();

    // Tạo mã ngẫu nhiên duy nhất dạng 'venus' + 6-8 ký tự chữ/số (đúng cấu trúc Sepay)
    function genCode($length) {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $s = '';
        for ($i = 0; $i < $length; $i++) {
            // Fallback an toàn nếu random_int không có
            if (function_exists('random_int')) {
                $idx = random_int(0, strlen($chars) - 1);
            } else {
                $idx = mt_rand(0, strlen($chars) - 1);
            }
            $s .= $chars[$idx];
        }
        return 'venus' . $s;
    }

    $code = '';
    for ($i = 0; $i < 5; $i++) {
        $candidate = genCode(random_int(6, 8));
        $ck = $conn->prepare('SELECT 1 FROM bank_payment_codes WHERE code = ? LIMIT 1');
        $ck->bind_param('s', $candidate);
        $ck->execute();
        $ck->store_result();
        $num = $ck->num_rows;
        $ck->close();
        if ($num === 0) {
            $code = $candidate;
            break;
        }
    }

    if ($code === '') {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo mã, vui lòng thử lại']);
        exit;
    }

    $ins = $conn->prepare('INSERT INTO bank_payment_codes (user_id, code) VALUES (?, ?)');
    $ins->bind_param('is', $userId, $code);
    if ($ins->execute()) {
        echo json_encode(['success' => true, 'code' => $code]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể lưu mã']);
    }
    $ins->close();
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>

