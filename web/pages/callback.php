<?php
// Tránh load duplicate class System
if (!class_exists('System')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
} else {
    // Nếu class đã tồn tại nhưng $CVH chưa được khởi tạo
    if (!isset($CVH)) {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/database.php";
        $CVH = new System;
    }
}

// Đọc GET parameters từ gachthefast
$callback_data = $_GET;
unset($callback_data['request']); // Loại bỏ parameter 'request'

// Ghi log tất cả callback nhận được
$log_data = "=== CALLBACK RECEIVED [GET VERSION] ===\n";
$log_data .= "Time: " . date('Y-m-d H:i:s') . "\n";
$log_data .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$log_data .= "Callback Data: " . print_r($callback_data, true) . "\n";
$log_data .= "GET params: " . print_r($_GET, true) . "\n";
file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/callback_log.txt", $log_data, FILE_APPEND | LOCK_EX);

// Kiểm tra nếu có GET parameters hợp lệ
if ($callback_data && isset($callback_data['status']) && isset($callback_data['request_id'])) {
    $tranid = $callback_data['request_id'];
    $status = $callback_data['status'];
    $am_real = $callback_data['amount'] ?? 0;
    
    // Verify callback signature theo tài liệu (md5(partner_key + code + serial))
    // Lấy partner_key từ cấu hình (autoload đã include cvhvn/config.php)
    $partner_key = isset($GLOBALS['config']['partner_key']) ? (string)$GLOBALS['config']['partner_key'] : '';
    $code = $callback_data['code'] ?? '';
    $serial = $callback_data['serial'] ?? '';
    $callback_sign = $callback_data['callback_sign'] ?? '';
    
    $expected_sign = md5($partner_key . $code . $serial);
    
    if ($callback_sign !== $expected_sign) {
        $log_error = "SIGNATURE VERIFICATION FAILED!\n";
        $log_error .= "Expected: " . $expected_sign . "\n";
        $log_error .= "Received: " . $callback_sign . "\n";
        $log_error .= "Code: " . $code . "\n";
        $log_error .= "Serial: " . $serial . "\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/callback_log.txt", $log_error, FILE_APPEND | LOCK_EX);
        
        // Vẫn tiếp tục xử lý nhưng ghi log cảnh báo
        sendTele(templateTele("⚠️ CALLBACK SIGNATURE MISMATCH: " . $tranid));
    }

    $conn = $CVH->connect_db();
    
    // Kiểm tra kết nối database
    if (!$conn) {
        $log_db_error = "DATABASE CONNECTION FAILED!\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/callback_log.txt", $log_db_error, FILE_APPEND | LOCK_EX);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'DATABASE_ERROR']);
        exit;
    }

    // Đảm bảo bảng có đủ cột để lưu thêm thông tin từ callback
    @mysqli_query($conn, "ALTER TABLE `cvh_recharge` ADD COLUMN IF NOT EXISTS `declared_value` INT NULL");
    @mysqli_query($conn, "ALTER TABLE `cvh_recharge` ADD COLUMN IF NOT EXISTS `value` INT NULL");
    @mysqli_query($conn, "ALTER TABLE `cvh_recharge` ADD COLUMN IF NOT EXISTS `card_value` INT NULL");
    @mysqli_query($conn, "ALTER TABLE `cvh_recharge` ADD COLUMN IF NOT EXISTS `telco` VARCHAR(32) NULL");
    @mysqli_query($conn, "ALTER TABLE `cvh_recharge` ADD COLUMN IF NOT EXISTS `trans_id` VARCHAR(64) NULL");
    @mysqli_query($conn, "ALTER TABLE `cvh_recharge` ADD COLUMN IF NOT EXISTS `message` VARCHAR(255) NULL");
    @mysqli_query($conn, "ALTER TABLE `cvh_recharge` ADD COLUMN IF NOT EXISTS `code` VARCHAR(64) NULL");
    @mysqli_query($conn, "ALTER TABLE `cvh_recharge` ADD COLUMN IF NOT EXISTS `serial` VARCHAR(64) NULL");

    $partner_trans_id = $callback_data['trans_id'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM `cvh_recharge` WHERE `tranid` = ?");
    $stmt->bind_param("s", $tranid);
    $stmt->execute();
    $result = $stmt->get_result();
    $get = $result->fetch_assoc();

    // Nếu không tìm thấy theo tranid (request_id), thử tìm theo trans_id (ID của đối tác) nếu có
    if (($result->num_rows ?? 0) === 0 && $partner_trans_id !== '') {
        $stmt->close();
        $stmt = $conn->prepare("SELECT * FROM `cvh_recharge` WHERE `trans_id` = ? LIMIT 1");
        $stmt->bind_param("s", $partner_trans_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $get = $result->fetch_assoc();
        if ($get && isset($get['tranid'])) {
            $tranid = $get['tranid']; // đồng bộ lại tranid nội bộ
        }
    }

    if ($result->num_rows > 0) {
        $account_id = $get['account_id'];
        $monney = $get['amount'];

        if ($status == 1) {
            $update = $conn->prepare("UPDATE `account` SET `vnd` = `vnd` + ?, `tongnap` = `tongnap` + ? WHERE `id` = ?");
            $update->bind_param("ddi", $monney, $monney, $account_id);
            $update->execute();
            $update->close();

            $declared_value_i = isset($callback_data['declared_value']) ? (int)$callback_data['declared_value'] : 0;
            $value_i = isset($callback_data['value']) ? (int)$callback_data['value'] : 0;
            $card_value_i = isset($callback_data['card_value']) ? (int)$callback_data['card_value'] : 0;
            $telco_safe = $callback_data['telco'] ?? '';
            $trans_id_safe = $callback_data['trans_id'] ?? '';
            $message_safe = $callback_data['message'] ?? '';

            $update2 = $conn->prepare("UPDATE `cvh_recharge` SET `status`='1', `amount_real`=?, `declared_value`=?, `value`=?, `card_value`=?, `telco`=?, `trans_id`=?, `message`=?, `code`=?, `serial`=? WHERE `tranid`=?");
            $update2->bind_param("iiiissssss", $am_real, $declared_value_i, $value_i, $card_value_i, $telco_safe, $trans_id_safe, $message_safe, $code, $serial, $tranid);
            $update_result = $update2->execute();
            $affected_rows = $update2->affected_rows;
            $update2->close();
            
            // Log kết quả update
            $log_update = "UPDATE RESULT: Success=$update_result, Affected Rows=$affected_rows\n";
            $log_update .= "SQL: UPDATE cvh_recharge SET status='1', amount_real=$am_real WHERE tranid='$tranid'\n";
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/callback_log.txt", $log_update, FILE_APPEND | LOCK_EX);
            
            // Log successful processing
            $log_success = "SUCCESS: Transaction $tranid processed successfully\n";
            $log_success .= "Account ID: $account_id, Amount: $monney, Real Amount: $am_real\n";
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/callback_log.txt", $log_success, FILE_APPEND | LOCK_EX);
            
            // Gửi thông báo Telegram khi nạp thẻ thành công
            $account_info = $CVH->get_row("SELECT username FROM account WHERE id = " . $account_id);
            if ($account_info) {
                $telco = $telco_safe ?: 'Unknown';
                $message_text = $message_safe ?: 'Thành công';
                $trans_id = $trans_id_safe;
                $declared_value = $declared_value_i ?: $monney;
                $value = $value_i ?: $am_real;
                
                $telegram_msg = "✅ NẠP THẺ THÀNH CÔNG!\n";
                $telegram_msg .= "👤 Người dùng: " . $account_info["username"] . "\n";
                $telegram_msg .= "💰 Số tiền nhận: " . number_format($am_real) . "đ\n";
                $telegram_msg .= "🎫 Mệnh giá khai báo: " . number_format($declared_value) . "đ\n";
                $telegram_msg .= "💎 Giá trị thực: " . number_format($value) . "đ\n";
                $telegram_msg .= "📱 Loại thẻ: " . $telco . "\n";
                $telegram_msg .= "🔢 Mã GD: " . $tranid;
                if ($trans_id) {
                    $telegram_msg .= "\n🏦 Mã GD API: " . $trans_id;
                }
                $telegram_msg .= "\n✉️ Trạng thái: " . $message_text;
                
                sendTele(templateTele($telegram_msg));
            }
        } else {
            $telco_safe = $callback_data['telco'] ?? '';
            $trans_id_safe = $callback_data['trans_id'] ?? '';
            $message_safe = $callback_data['message'] ?? '';
            $declared_value_i = isset($callback_data['declared_value']) ? (int)$callback_data['declared_value'] : 0;
            $value_i = isset($callback_data['value']) ? (int)$callback_data['value'] : 0;
            $card_value_i = isset($callback_data['card_value']) ? (int)$callback_data['card_value'] : 0;

            $update2 = $conn->prepare("UPDATE `cvh_recharge` SET `status`='1', `amount_real`=0, `declared_value`=?, `value`=?, `card_value`=?, `telco`=?, `trans_id`=?, `message`=?, `code`=?, `serial`=? WHERE `tranid` = ?");
            $update2->bind_param("iiissssss", $declared_value_i, $value_i, $card_value_i, $telco_safe, $trans_id_safe, $message_safe, $code, $serial, $tranid);
            $update2->execute();
            $update2->close();
            
            // Log failed processing
            $log_fail = "FAILED: Transaction $tranid failed with status $status\n";
            $log_fail .= "Account ID: $account_id, Message: " . ($callback_data['message'] ?? 'No message') . "\n";
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/callback_log.txt", $log_fail, FILE_APPEND | LOCK_EX);
            
            // Gửi thông báo Telegram khi nạp thẻ thất bại
            $account_info = $CVH->get_row("SELECT username FROM account WHERE id = " . $account_id);
            if ($account_info) {
                $telco = $callback_data['telco'] ?? 'Unknown';
                $message_text = $callback_data['message'] ?? 'Thất bại';
                $trans_id = $callback_data['trans_id'] ?? '';
                $declared_value = $callback_data['declared_value'] ?? $monney;
                
                $telegram_msg = "❌ NẠP THẺ THẤT BẠI!\n";
                $telegram_msg .= "👤 Người dùng: " . $account_info["username"] . "\n";
                $telegram_msg .= "💰 Số tiền: 0đ (không được cộng)\n";
                $telegram_msg .= "🎫 Mệnh giá khai báo: " . number_format($declared_value) . "đ\n";
                $telegram_msg .= "📱 Loại thẻ: " . $telco . "\n";
                $telegram_msg .= "🔢 Mã GD: " . $tranid;
                if ($trans_id) {
                    $telegram_msg .= "\n🏦 Mã GD API: " . $trans_id;
                }
                $telegram_msg .= "\n✉️ Lý do: " . $message_text;
                $telegram_msg .= "\n🔥 Status API: " . $status;
                
                sendTele(templateTele($telegram_msg));
            }
        }
        
        // Response cho gachthefast (GET callback trả text)
        header('Content-Type: text/plain; charset=utf-8');
        echo 'OK';
    } else {
        // Transaction không tồn tại
        $log_notfound = "ERROR: Transaction $tranid not found in database\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/callback_log.txt", $log_notfound, FILE_APPEND | LOCK_EX);
        
        sendTele(templateTele("❌ CALLBACK ERROR: Transaction $tranid not found"));
        header('Content-Type: text/plain; charset=utf-8');
        echo 'TRANSACTION_NOT_FOUND';
    }

    $stmt->close();
    $conn->close();
} else {
    // Thiếu GET parameters bắt buộc
    $log_invalid = "ERROR: Invalid callback - missing required GET parameters\n";
    $log_invalid .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
    $log_invalid .= "Expected: request_id, status, amount, code, serial, callback_sign\n";
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/callback_log.txt", $log_invalid, FILE_APPEND | LOCK_EX);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'INVALID_PARAMETERS';
}
?>