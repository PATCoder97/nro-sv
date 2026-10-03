<?php
require_once '../../cvhvn/autoload.php';
// Luôn trả về JSON
header('Content-Type: application/json; charset=utf-8');

// Function lấy cấu trúc mã thanh toán
function getPaymentCodeConfig($conn) {
    // Kiểm tra bảng payment_code_templates nếu chưa có thì tạo
    $check_table = "SHOW TABLES LIKE 'payment_code_templates'";
    $result = $conn->query($check_table);
    
    if ($result->num_rows == 0) {
        // Tạo bảng payment_code_templates
        $create_table = "CREATE TABLE `payment_code_templates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `prefix` varchar(10) NOT NULL DEFAULT 'venus',
            `suffix_min` int(11) NOT NULL DEFAULT 1,
            `suffix_max` int(11) NOT NULL DEFAULT 3,
            `suffix_type` enum('number','text','mixed') NOT NULL DEFAULT 'number',
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `is_default` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->query($create_table);
        
        // Thêm template mặc định
        $insert_default = "INSERT INTO `payment_code_templates` (`name`, `prefix`, `suffix_min`, `suffix_max`, `suffix_type`, `is_active`, `is_default`) 
                          VALUES ('Mẫu mặc định', 'venus', 1, 3, 'number', 1, 1)";
        $conn->query($insert_default);
    }
    
    // Lấy template đang hoạt động
    $sql = "SELECT * FROM payment_code_templates WHERE is_active = 1 ORDER BY is_default DESC, id ASC LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    // Trả về cấu hình mặc định nếu không có template nào
    return [
        'prefix' => 'venus',
        'suffix_min' => 1,
        'suffix_max' => 3,
        'suffix_type' => 'number'
    ];
}

// Function tạo mã thanh toán
function generatePaymentCode($config, $user_id, $amount, $conn) {
    $prefix = $config['prefix'] ?? 'venus';
    // Mã người dùng rút gọn để liên kết player (base36, in hoa)
    $userPart = strtoupper(base_convert(max(1, intval($user_id)), 10, 36));

    // Tạo suffix ngẫu nhiên
    $suffix_length = rand($config['suffix_min'], $config['suffix_max']);
    $suffix = '';

    switch ($config['suffix_type']) {
        case 'number':
            $suffix = str_pad(rand(0, pow(10, $suffix_length) - 1), $suffix_length, '0', STR_PAD_LEFT);
            break;
        case 'text':
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < $suffix_length; $i++) {
                $suffix .= $chars[rand(0, strlen($chars) - 1)];
            }
            break;
        case 'mixed':
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            for ($i = 0; $i < $suffix_length; $i++) {
                $suffix .= $chars[rand(0, strlen($chars) - 1)];
            }
            break;
        default:
            $suffix = str_pad(rand(0, pow(10, $suffix_length) - 1), $suffix_length, '0', STR_PAD_LEFT);
    }

    // Ghép mã: PREFIX + userPart + suffix
    $code = $prefix . $userPart . $suffix;

    // Đảm bảo duy nhất trong cvh_recharge.tranid
    $safe = false;
    $tries = 0;
    while (!$safe && $tries < 5) {
        $check = $conn->prepare("SELECT id FROM cvh_recharge WHERE tranid = ? LIMIT 1");
        if ($check) {
            $check->bind_param("s", $code);
            $check->execute();
            $res = $check->get_result();
            if ($res && $res->num_rows === 0) {
                $safe = true;
            } else {
                // Tạo lại suffix nếu trùng
                $suffix = '';
                for ($i = 0; $i < $suffix_length; $i++) {
                    $suffix .= substr('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', rand(0, 35), 1);
                }
                $code = $prefix . $userPart . $suffix;
            }
            $check->close();
        } else {
            // Nếu không prepare được, chấp nhận code hiện tại
            $safe = true;
        }
        $tries++;
    }

    return $code;
}

// Kiểm tra đăng nhập bằng $user từ autoload
if (!isset($user) || empty($user['id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$user_id = intval($user['id']);

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
    exit;
}

// Lấy dữ liệu từ form
$amount = floatval($_POST['amount'] ?? 0);
$bank_account = $_POST['bank_account'] ?? '';
$custom_tranid = $_POST['custom_tranid'] ?? '';

// Validate dữ liệu
if ($amount < 10000) {
    echo json_encode(['success' => false, 'message' => 'Số tiền tối thiểu là 10,000 VNĐ']);
    exit;
}

if ($amount > 100000000) {
    echo json_encode(['success' => false, 'message' => 'Số tiền tối đa là 100,000,000 VNĐ']);
    exit;
}

if (empty($bank_account)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ngân hàng']);
    exit;
}

try {
    // Kết nối database
    $conn = new mysqli($GLOBALS['config']['db_host'], $GLOBALS['config']['db_user'], $GLOBALS['config']['db_pass'], $GLOBALS['config']['db_name']);
    
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
        exit;
    }
    
    // Tạo bảng map mã thanh toán ↔ user nếu chưa có
    $conn->query("CREATE TABLE IF NOT EXISTS `bank_payment_codes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `code` varchar(50) NOT NULL,
        `is_used` tinyint(1) NOT NULL DEFAULT 0,
        `used_at` datetime DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `code_unique` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Lấy cấu trúc mã thanh toán từ database hoặc sử dụng mặc định
    $payment_code_config = getPaymentCodeConfig($conn);
    
    // Tạo transaction ID theo cấu trúc hoặc sử dụng custom_tranid
    if (!empty($custom_tranid)) {
        $transaction_id = $custom_tranid;
    } else {
        $transaction_id = generatePaymentCode($payment_code_config, $user_id, $amount, $conn);
    }
    
    // Lưu mã thanh toán ↔ user (không dùng cvh_recharge cho nạp ATM)
    $stmt = $conn->prepare("INSERT INTO bank_payment_codes (user_id, code, is_used) VALUES (?, ?, 0)");
    $stmt->bind_param("is", $user_id, $transaction_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Tạo mã thanh toán thành công!',
            'data' => [
                'transaction_id' => $transaction_id
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi tạo giao dịch']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 