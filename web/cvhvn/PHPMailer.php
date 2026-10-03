<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/vendor/PHPMailer/src/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/vendor/PHPMailer/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/vendor/PHPMailer/src/SMTP.php';

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Cấu hình máy chủ SMTP
        $mail->isSMTP();                                  // Sử dụng SMTP
        $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth   = true;                         // Bật xác thực SMTP
        $mail->Username   = getenv('SMTP_USERNAME') ?: '';
        $mail->Password   = getenv('SMTP_PASSWORD') ?: '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Bật mã hóa TLS
        $mail->Port       = intval(getenv('SMTP_PORT') ?: 465);
        $mail->CharSet = 'UTF-8';
        // Thiết lập người gửi và người nhận
        $mail->setFrom(getenv('SMTP_FROM') ?: $mail->Username, getenv('SMTP_FROM_NAME') ?: 'Teamobi 2026');
        $mail->addAddress($to);                             // Địa chỉ người nhận

        // Nội dung email
        $mail->isHTML(true);                                 // Định dạng HTML cho email
        $mail->Subject = $subject;                           // Chủ đề email
        $mail->Body    = $body;                              // Nội dung email (HTML)
        $mail->AltBody = strip_tags($body);                  // Nội dung dạng text cho email clients không hỗ trợ HTML

        // Gửi email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// sendMail('cvhvn@example.com', 'Tiêu đề email', '<h1>Nội dung email HTML</h1>');
?>
