<?php
// Webhook endpoint cho Sepay - Theo tài liệu chính thức
header('Content-Type: application/json');

// Tạo thư mục logs nếu chưa có
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

// Function ghi log
function writeLog($message) {
    $logFile = 'logs/sepay_webhook.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Ghi log bắt đầu
writeLog("=== SEPAY WEBHOOK RECEIVED ===");
writeLog("Method: " . $_SERVER['REQUEST_METHOD']);
writeLog("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

// Kiểm tra method phải là POST
if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    writeLog('ERROR: Invalid method');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Đọc và xác thực Authorization header: "Apikey <API_KEY>"
function getAuthorizationHeaderValue() {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    if (!empty($headers)) {
        foreach ($headers as $k => $v) {
            if (strcasecmp($k, 'Authorization') === 0) {
                return trim($v);
            }
            // Hỗ trợ các header phổ biến khác
            if (strcasecmp($k, 'X-Api-Key') === 0 || strcasecmp($k, 'X-API-KEY') === 0 || strcasecmp($k, 'Api-Key') === 0 || strcasecmp($k, 'X-SEPAY-APIKEY') === 0) {
                return trim($v);
            }
        }
    }
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['HTTP_AUTHORIZATION']);
    }
    if (!empty($_SERVER['Authorization'])) {
        return trim($_SERVER['Authorization']);
    }
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    }
    // Fallback query/body param
    if (isset($_GET['apikey'])) return trim($_GET['apikey']);
    if (isset($_POST['apikey'])) return trim($_POST['apikey']);
    return '';
}

$authHeader = getAuthorizationHeaderValue();
writeLog('Authorization: ' . ($authHeader ?: 'not set'));

// Đọc raw input
$raw_input = file_get_contents('php://input');
writeLog("Raw Input: " . $raw_input);

// Parse JSON data
$data = json_decode($raw_input);

if(!is_object($data)) {
    writeLog("ERROR: No data or invalid JSON");
    echo json_encode(['success'=>FALSE, 'message' => 'No data']);
    exit;
}

try {
    // Include autoload để kết nối database chính
    require_once 'cvhvn/autoload.php';
    $conn = $CVH->connect_db();

    // Xác thực API key nếu có cấu hình
    $configuredKey = isset($config['sepay_api_key']) ? trim((string)$config['sepay_api_key']) : '';
    if (!empty($configuredKey)) {
        // Hỗ trợ dạng 'Apikey KEY' hoặc chỉ 'KEY'
        $provided = trim($authHeader);
        if (stripos($provided, 'Apikey ') === 0) {
            $providedKey = trim(substr($provided, 7));
        } else {
            $providedKey = $provided;
        }
        // Chuẩn hóa: bỏ ký tự không phải chữ/số và upper-case
        $configuredKeyNorm = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $configuredKey));
        $providedKeyNorm   = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $providedKey));

        writeLog('ConfiguredKey(len=' . strlen($configuredKey) . ') norm(len=' . strlen($configuredKeyNorm) . '): ' . $configuredKeyNorm);
        writeLog('ProvidedKey(len=' . strlen($providedKey) . ') norm(len=' . strlen($providedKeyNorm) . '): ' . $providedKeyNorm);

        if (!hash_equals($configuredKeyNorm, $providedKeyNorm)) {
            writeLog('ERROR: Invalid API key');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
    }
    
    // Khởi tạo các biến theo tài liệu Sepay
    $gateway = $data->gateway ?? '';
    $transaction_date = $data->transactionDate ?? '';
    $account_number = $data->accountNumber ?? '';
    $sub_account = $data->subAccount ?? '';
    
    $transfer_type = $data->transferType ?? '';
    $transfer_amount = floatval($data->transferAmount ?? 0);
    $accumulated = floatval($data->accumulated ?? 0);
    
    $code = $data->code ?? '';
    $transaction_content = $data->content ?? '';
    $reference_number = $data->referenceCode ?? '';
    $body = $data->description ?? '';
    
    $amount_in = 0;
    $amount_out = 0;
    
    // Kiểm tra giao dịch tiền vào hay tiền ra
    if($transfer_type == "in")
        $amount_in = $transfer_amount;
    else if($transfer_type == "out")
        $amount_out = $transfer_amount;
    
    writeLog("Extracted Data:");
    writeLog("- ID: " . ($data->id ?? ''));
    writeLog("- Gateway: $gateway");
    writeLog("- Transfer Type: $transfer_type");
    writeLog("- Amount In: $amount_in");
    writeLog("- Transaction Content: $transaction_content");
    writeLog("- Reference Number: $reference_number");
    
    // Đảm bảo tồn tại bảng bank_payment_codes (tránh lỗi khi webhook đến trước khi tạo mã)
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

    // Insert vào bảng tb_transactions (theo tài liệu Sepay)
    $sql = "INSERT INTO tb_transactions (gateway, transaction_date, account_number, sub_account, amount_in, amount_out, accumulated, code, transaction_content, reference_number, body) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssddsssss", $gateway, $transaction_date, $account_number, $sub_account, $amount_in, $amount_out, $accumulated, $code, $transaction_content, $reference_number, $body);
    
    if ($stmt->execute()) {
        writeLog("SUCCESS: Inserted into tb_transactions");
        $transactionRowId = $conn->insert_id;

        // Đảm bảo có cột 'credited' để đánh dấu đã cộng tiền
        $colCheck = $conn->query("SHOW COLUMNS FROM tb_transactions LIKE 'credited'");
        if ($colCheck && $colCheck->num_rows === 0) {
            $conn->query("ALTER TABLE tb_transactions ADD COLUMN credited TINYINT(1) NOT NULL DEFAULT 0");
            writeLog("INFO: Added 'credited' column to tb_transactions");
        }
        
        // Chỉ xử lý nếu là tiền vào
        if ($amount_in > 0) {
            // Ưu tiên dùng trường 'code' do SePay nhận diện theo cấu trúc; fallback trích từ content/description
            $lookup_code = '';
            $pattern = '/(venus[a-z0-9]{2,12})/i'; // linh hoạt độ dài hậu tố 2-12
            if (!empty($code)) {
                if (preg_match($pattern, $code, $m)) { $lookup_code = $m[1]; }
            }
            if (empty($lookup_code) && !empty($transaction_content)) {
                if (preg_match($pattern, $transaction_content, $m)) { $lookup_code = $m[1]; }
            }
            if (empty($lookup_code) && !empty($body)) {
                if (preg_match($pattern, $body, $m)) { $lookup_code = $m[1]; }
            }
            writeLog('Lookup code resolved: ' . ($lookup_code ?: 'not found'));
            
            // So khớp không phân biệt hoa/thường để an toàn
            $find_sql = "SELECT user_id FROM bank_payment_codes WHERE LOWER(code) = LOWER(?) LIMIT 1";
            $find_stmt = $conn->prepare($find_sql);
            if ($find_stmt) {
                $find_stmt->bind_param("s", $lookup_code);
                $find_stmt->execute();
                $result = $find_stmt->get_result();
            } else {
                writeLog("WARNING: bank_payment_codes table not ready or prepare failed; using fallback parser");
                $result = false;
            }

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $user_id = intval($row['user_id']);

                // Cộng tiền
                $balance_sql = "UPDATE account SET vnd = vnd + ?, tongnap = tongnap + ? WHERE id = ?";
                $balance_stmt = $conn->prepare($balance_sql);
                $balance_stmt->bind_param("ddi", $amount_in, $amount_in, $user_id);
                if ($balance_stmt->execute()) {
                    writeLog("SUCCESS: Updated user balance by bank webhook");

                    // Mã cố định theo tài khoản: không đánh dấu used, chỉ log credited

                    // Đánh dấu đã xử lý giao dịch này
                    $conn->query("UPDATE tb_transactions SET credited = 1 WHERE id = " . intval($transactionRowId));

                    // Thông báo Telegram
                    $user_info = $conn->query("SELECT username FROM account WHERE id = $user_id")->fetch_assoc();
                    $username = $user_info['username'] ?? 'Unknown';
                    $telegram_message = "🎉 Nạp ATM thành công!\n" .
                        "👤 User: $username\n" .
                        "💰 Số tiền: " . number_format($amount_in) . " VNĐ\n" .
                        "🏦 Ngân hàng: $gateway\n" .
                        "📝 Mã: " . $lookup_code . "\n" .
                        "⏰ Thời gian: " . date('Y-m-d H:i:s');
                    if (isset($config['telegram_bot_token']) && isset($config['telegram_chat_id'])) {
                        $telegram_url = "https://api.telegram.org/bot{$config['telegram_bot_token']}/sendMessage";
                        $telegram_data = [
                            'chat_id' => $config['telegram_chat_id'],
                            'text' => $telegram_message,
                            'parse_mode' => 'HTML'
                        ];
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $telegram_url);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $telegram_data);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_exec($ch);
                        curl_close($ch);
                        writeLog("SUCCESS: Sent Telegram notification (bank)");
                    }
                } else {
                    writeLog("ERROR: Failed to update user balance (bank)");
                }
            } else {
                // Fallback: parse user id từ nội dung (ví dụ VENUS407, VENUS 407)
                if (preg_match('/VENUS\s*([0-9]+)/i', $transaction_content, $m)) {
                    $user_id = intval($m[1]);
                    $userCheck = $conn->query("SELECT id, username FROM account WHERE id = " . $user_id);
                    if ($userCheck && $userCheck->num_rows === 1) {
                        $balance_sql = "UPDATE account SET vnd = vnd + ?, tongnap = tongnap + ? WHERE id = ?";
                        $balance_stmt = $conn->prepare($balance_sql);
                        $balance_stmt->bind_param("ddi", $amount_in, $amount_in, $user_id);
                        if ($balance_stmt->execute()) {
                            writeLog("SUCCESS: Credited by parsed VENUS user id: $user_id");
                            $conn->query("UPDATE tb_transactions SET credited = 1 WHERE id = " . intval($transactionRowId));
                        }
                    } else {
                        writeLog("WARNING: Parsed user id not found: $user_id from content: $transaction_content");
                    }
                } else {
                    writeLog("WARNING: No payment code found or already used for content: $transaction_content");
                }
            }

            if ($find_stmt) { $find_stmt->close(); }
        }
        
        echo json_encode(['success'=>TRUE]);
    } else {
        writeLog("ERROR: Failed to insert into tb_transactions: " . $stmt->error);
        echo json_encode(['success'=>FALSE, 'message' => 'Can not insert record to mysql: ' . $stmt->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    writeLog("ERROR: " . $e->getMessage());
    // Trả về success=true để SePay không retry, nhưng vẫn log lỗi
    echo json_encode(['success'=>TRUE]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

writeLog("=== WEBHOOK PROCESSING COMPLETED ===");
?> 