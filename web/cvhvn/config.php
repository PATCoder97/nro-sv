<?php
/*
 * Tác giả: NRO VENUS *
 * Ngày tạo: 19/02/2023 *
 * Zalo: 0352.845.018 *
 * Website: WwW.CvH.Vn *
 */

$config = array(

   'Author' => 'Nro Venus',
   'Facebook' => 'Null',
   'Zalo' => 'Null',
   'KichHoat' => '10000',
   
   // Cấu hình gachthefast.com
   'partner_id' => getenv('CARD_PARTNER_ID') ?: '',
   'partner_key' => getenv('CARD_PARTNER_KEY') ?: '',
   'domain' => getenv('CARD_API_DOMAIN') ?: 'gachthefast.com',
   
   // Cấu hình Sepay API
   'sepay_api_key' => getenv('SEPAY_API_KEY') ?: '',
   'sepay_api_url' => getenv('SEPAY_API_URL') ?: 'https://my.sepay.vn/userapi',
   'sepay_webhook_secret' => getenv('SEPAY_WEBHOOK_SECRET') ?: '',

   // Thông báo Telegram (để trống để tắt)
   'telegram_bot_token' => getenv('TELEGRAM_BOT_TOKEN') ?: '',
   'telegram_chat_id' => getenv('TELEGRAM_CHAT_ID') ?: '',

   // Database dùng chung cho các API ảnh tùy chọn
   'db_host' => getenv('DB_HOST') ?: 'database',
   'db_user' => getenv('DB_USER') ?: 'teamobi',
   'db_pass' => getenv('DB_PASSWORD') ?: 'change-me',
   'db_name' => getenv('DB_NAME') ?: 'team2026',
   
   // reCAPTCHA (điền nếu muốn bật xác thực captcha ở trang đăng nhập)
   'recaptcha_site_key' => getenv('RECAPTCHA_SITE_KEY') ?: '',
   'recaptcha_secret' => getenv('RECAPTCHA_SECRET') ?: ''
);
?>
