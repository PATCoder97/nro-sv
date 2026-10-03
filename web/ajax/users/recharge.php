<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if ($user) {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $CVH->Ex(false, "Phiên làm việc không hợp lệ, vui lòng tải lại trang!");
        exit;
    }

    $player = $CVH->player($user['id']);

    $mathe = trim($_POST["code"] ?? '');
    $serial = trim($_POST["serial"] ?? '');
    $loaithe = trim($_POST["type"] ?? '');
    $menhgia = filter_var($_POST["amount"] ?? null, FILTER_VALIDATE_INT);


    if ($loaithe && $menhgia && $mathe && $serial) {

        $tranid = rand(100000, 999999);

        $huydepzaii = $CVH->post_card($tranid, $loaithe, $mathe, $serial, $menhgia, $config['partner_id'], $config['partner_key']);


        if (is_array($huydepzaii) && ($huydepzaii['status'] ?? null) == 99) {
            $table = "cvh_recharge";
            $data = array(
                "id" => null,
                "account_id" => $user['id'],
                "code" => $mathe,
                "serial" => $serial,
                "amount" => $menhgia,
                "type" => $loaithe,
                "amount_real" => '-1',
                "status" => 0,
                "tranid" => $tranid,
                "time" => date("H:i:s d/m/Y")
            );
            $CVH->insert($table, $data);
            
            // Gửi thông báo Telegram khi có giao dịch mới (đang chờ duyệt)
            $telegram_msg = "⏳ NẠP THẺ MỚI - ĐANG CHỜ DUYỆT!\n";
            $telegram_msg .= "👤 Người dùng: " . $user['username'] . "\n";
            $telegram_msg .= "💰 Số tiền: Đang xử lý...\n";
            $telegram_msg .= "🎫 Mệnh giá: " . number_format($menhgia) . "đ\n";
            $telegram_msg .= "📱 Loại thẻ: " . $loaithe . "\n";
            $telegram_msg .= "🔢 Mã GD: " . $tranid . "\n";
            $telegram_msg .= "🔑 Mã thẻ: " . $mathe . "\n";
            $telegram_msg .= "📟 Serial: " . $serial . "\n";
            $telegram_msg .= "⏰ Thời gian: " . date("H:i:s d/m/Y") . "\n";
            $telegram_msg .= "📊 Trạng thái: Đã gửi lên API, chờ callback";
            
            sendTele(templateTele($telegram_msg));

            $CVH->Ex(true, "Nạp thẻ thành công vui lòng chờ duyệt!");

        } else {
            
            // Gửi thông báo Telegram khi gửi thẻ thất bại (API reject)
            $telegram_msg = "🚫 GỬI THẺ THẤT BẠI!\n";
            $telegram_msg .= "👤 Người dùng: " . $user['username'] . "\n";
            $telegram_msg .= "💰 Số tiền: 0đ (không được xử lý)\n";
            $telegram_msg .= "🎫 Mệnh giá: " . number_format($menhgia) . "đ\n";
            $telegram_msg .= "📱 Loại thẻ: " . $loaithe . "\n";
            $telegram_msg .= "🔑 Mã thẻ: " . $mathe . "\n";
            $telegram_msg .= "📟 Serial: " . $serial . "\n";
            $telegram_msg .= "⏰ Thời gian: " . date("H:i:s d/m/Y") . "\n";
            $telegram_msg .= "❌ Lỗi API: " . ($huydepzaii['message'] ?? 'Unknown error') . "\n";
            $telegram_msg .= "🔥 Status API: " . ($huydepzaii['status'] ?? 'Unknown');
            
            sendTele(templateTele($telegram_msg));

            $CVH->Ex(false, $huydepzaii['message'] ?? 'Không thể kết nối dịch vụ nạp thẻ!');

        }

    } else {

        $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");

    }


} else {

    $CVH->Ex(false, "Bạn chưa đăng nhập vui lòng đăng nhập để thực hiện thao tác này!");

}
;
