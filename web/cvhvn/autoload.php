<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/PHPMailer.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/function.php";
session_start();

$CVH = new System;

// Cleanup expired CSRF tokens
$CVH->cleanupExpiredCSRFTokens();
$setting = $CVH->setting(1);

// Ưu tiên xác thực theo session_token (an toàn hơn). Nếu không có thì fallback token cũ để tương thích
$user = null;
if (!empty($_COOKIE['session_token'])) {
    $sessionToken = $_COOKIE['session_token'];
    $conn = $CVH->connect_db();
    $stmt = $conn->prepare("SELECT a.* FROM cvh_sessions s JOIN account a ON a.id = s.user_id WHERE s.token = ? AND s.expires_at > NOW() LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $sessionToken);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

if (!$user && isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
    $userChecked = $CVH->Check($token);
    if ($userChecked) {
        $expected = $CVH->Token($userChecked['username'], $userChecked['password']);
        $legacyRevoked = intval($userChecked['legacy_token_revoked'] ?? 0) === 1;
        if (hash_equals($expected, $token) && !$legacyRevoked) {
            $user = $userChecked;
        } else {
            unset($_COOKIE['token']);
            setcookie('token', '', time() - (7 * 24 * 60 * 60), '/');
        }
    }
}

if ($user) {
    // Set session user_id để đảm bảo session hoạt động
    $_SESSION['user_id'] = $user['id'];
    
    if ($CVH->player($user['id'])) {
        $player = $CVH->player($user['id']);
    } else {
        $player = null;
    }
    // Tự tạo mã thanh toán cố định cho tài khoản nếu chưa có
    try {
        $conn_auto = $CVH->connect_db();
        // Tạo bảng nếu chưa tồn tại
        $conn_auto->query("CREATE TABLE IF NOT EXISTS `bank_payment_codes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `code` varchar(50) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_user` (`user_id`),
            UNIQUE KEY `uq_code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $check_stmt = $conn_auto->prepare('SELECT code FROM bank_payment_codes WHERE user_id = ? LIMIT 1');
        if ($check_stmt) {
            $uid = intval($user['id']);
            $check_stmt->bind_param('i', $uid);
            $check_stmt->execute();
            $res = $check_stmt->get_result();
            if (!$res || $res->num_rows === 0) {
                // Tạo mã ngẫu nhiên, duy nhất: VENUS + 6-8 ký tự chữ/số
                $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
                $code = '';
                for ($tries = 0; $tries < 5 && $code === ''; $tries++) {
                    $len = 6;
                    $rand = '';
                    for ($i = 0; $i < $len; $i++) { $rand .= $chars[random_int(0, strlen($chars) - 1)]; }
                    $candidate = 'venus' . $rand;
                    $ck = $conn_auto->prepare('SELECT id FROM bank_payment_codes WHERE code = ? LIMIT 1');
                    if ($ck) {
                        $ck->bind_param('s', $candidate);
                        $ck->execute();
                        $eres = $ck->get_result();
                        $ck->close();
                        if (!$eres || $eres->num_rows === 0) { $code = $candidate; }
                    }
                }
                if ($code !== '') {
                    $ins = $conn_auto->prepare('INSERT INTO bank_payment_codes (user_id, code) VALUES (?, ?)');
                    if ($ins) { $ins->bind_param('is', $uid, $code); $ins->execute(); $ins->close(); }
                }
            }
            $check_stmt->close();
        }
    } catch (Exception $e) {
        // Bỏ qua, không làm gián đoạn autoload
    }
} else {
    $player = null;
}

function RandomString($data)
{
    $String = $data[array_rand($data)];
    return $String;
}

function FULL_URL($path)
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . "://" . $host . $path;
}


function getCurrentURL()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];

    $url = $protocol . "://" . $host . $uri;

    return $url;
}

function checkGender($genderCode)
{
    switch ($genderCode) {
        case 0:
            return "Trái Đất";
        case 1:
            return "Namec";
        case 2:
            return "Xayda";
        case 3:
            return "Null";
        default:
            return "Không xác định";
    }
}

function checkAmount($amount_real)
{
    switch ($amount_real) {
        case -1:
            return "Chưa cập nhật";
        default:
            return number_format($amount_real) . "đ";
    }
}

function getStatus($stt)
{
    switch ($stt) {
        case 0:
            return '<span class="badge bg-primary badge-sm">Chờ Duyệt</span>';
        case 1:
            return '<span class="badge bg-success badge-sm">Thành Công</span>';
        case 2:
            return '<span class="badge bg-danger badge-sm">Thẻ Sai</span>';
        case 3:
            return '<span class="badge bg-warning badge-sm">Sai Mệnh Giá</span>';
        default:
            return '<span class="badge bg-secondary badge-sm">Chưa Xác Định</span>';
    }
}

function getBand($stt)
{
    switch ($stt) {
        case 0:
            return '<span class="badge bg-success">Đang mở</span>';
        case 1:
            return '<span class="badge bg-danger">Đã khóa</span>';
        default:
            return '<span class="badge bg-secondary">Chưa Xác Định</span>';
    }
}


function getGender($var)
{
    switch ($var) {
        case 0:
            return "Trái Đất";
        case 1:
            return "Namếc";
        case 2:
            return "Xayda";
        default:
            return 'All';
    }
}


function getActive($stt)
{
    switch ($stt) {
        case 0:
            return '<span class="badge bg-danger">Chưa kích hoạt</span>';
        case 1:
            return '<span class="badge bg-success">Đã kích hoạt</span>';
        default:
            return '<span class="badge bg-secondary">Chưa Xác Định</span>';
    }
}

function fNumber($value)
{
    if ($value > 1000000000) {
        return number_format($value / 1000000000, 1, '.', '') . 'tỷ';
    } elseif ($value > 1000000) {
        return number_format($value / 1000000, 1, '.', '') . 'triệu';
    } elseif ($value >= 1000) {
        return number_format($value / 1000, 1, '.', '') . 'k';
    } else {
        return number_format($value, 0, '.', ',');
    }
}

function sendTele($message)
{
    global $config;
    $tele_token = $config['telegram_bot_token'] ?? '';
    $tele_chatid = $config['telegram_chat_id'] ?? '';
    if ($tele_token === '' || $tele_chatid === '') {
        return false;
    }

    $data = http_build_query([
        'chat_id' => $tele_chatid,
        'text' => $message,
    ]);

    $url = 'https://api.telegram.org/bot' . $tele_token . '/sendMessage';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Win) AppleWebKit/1000.0 (KHTML, like Gecko) Chrome/65.663 Safari/1000.01');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if ($data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function templateTele($content)
{
    return "🔔 THÔNG BÁO\n📝 Nội dung: " . $content . "\n🕒 Thời gian: " . date('d/m/Y H:i:s');
}


function loc($string)
{
    $text = array("lồn", "cặc", "địt", "dm", "dmm", "đm", "mẹ", "bố", "cha", "như lồn", "như cc", "game cc", "cc", "ngu");
    $ketqua = str_replace($text, str_repeat('*', strlen($text[0])), $string);

    return $ketqua;
}

function extractImageSrc($input)
{
    preg_match_all('/<img[^>]+>/i', $input, $matches);

    $imageSrcs = array();
    foreach ($matches[0] as $imgTag) {
        preg_match('/src="([^"]+)"/i', $imgTag, $src);
        if (isset($src[1])) {
            $imageSrcs[] = $src[1];
        }
    }

    return $imageSrcs;
}

function getAdminPosts($chose)
{
    switch ($chose) {
        default:
            $if_admin = array(
                "name" => "ADMIN",
                "avatar" => "/images/avatar/admin/".$chose.".png"
            );
            return json_encode($if_admin);
    }
}

function rand_string($length = 10) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function Time_Mes($createdAt) {
    $currentTime = time();
    $messageTime = $createdAt;
    $timeDiff = $currentTime - $messageTime;
    if ($timeDiff < 60) {
        return 'Vừa xong';
    } elseif ($timeDiff < 3600) {
        return 'Hôm nay ' . date('H:i', $messageTime);
    } elseif ($timeDiff < 86400) {
        return 'Hôm qua ' . date('H:i', $messageTime);
    } elseif ($timeDiff < 259200) {
        return date('d/m/Y H:i', $messageTime);
    } else {
        return date('d/m/Y H:i', $messageTime);
    }
}
?>
