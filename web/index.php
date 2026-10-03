<?php
$database_file = $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/database.php';
if (!file_exists($database_file)) {
    header('Location: /install');
    exit;
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

$request = isset($_GET['request']) ? $_GET['request'] : '';

// Chặn các trang yêu cầu đăng nhập trước khi output HTML
if ($request === 'nap-tien' && empty($user)) {
    header('Location: /');
    exit;
}

// Xử lý callback riêng biệt (không cần layout)
if ($request === 'callback') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/pages/callback.php';
    exit;
}

// Xử lý webhook riêng biệt (không cần layout)
if ($request === 'sepay-webhook') {
    require_once 'sepay-webhook.php';
    exit;
}

// Xử lý API riêng biệt (không cần layout)
if ($request === 'api-girl') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/pages/api-gaixinh.php';
    exit;
}

if ($request === 'api-sexy') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/pages/api-sexy.php';
    exit;
}

// Xử lý upload tools (không cần layout)
if ($request === 'upload-images') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/upload_images.php';
    exit;
}

if ($request === 'import-urls') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/import_urls.php';
    exit;
}

if ($request === 'export-urls') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/export_urls.php';
    exit;
}

// Xử lý AJAX endpoints (không cần layout)
if ($request === 'upload-ajax') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/upload_ajax.php';
    exit;
}

if ($request === 'get-upload-stats') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/get_upload_stats.php';
    exit;
}

if ($request === 'test-upload') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/test_upload.php';
    exit;
}

if ($request === 'test-simple') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/test_simple.php';
    exit;
}

if ($request === 'debug-upload') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/debug_upload.php';
    exit;
}

if ($request === 'setup-shop-log') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/cvhvn/setup_shop_log.php';
    exit;
}

if ($request === 'setup-shop-log-permission') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/cvhvn/setup_shop_log_permission.php';
    exit;
}

if ($request === 'debug-shop-status') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/debug_shop_status.php';
    exit;
}

if ($request === 'fix-shop-log-mapping') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/fix_shop_log_mapping.php';
    exit;
}

if ($request === 'setup-buff-system') {
    require_once $_SERVER["DOCUMENT_ROOT"].'/cvhvn/setup_buff_system.php';
    exit;
}






require_once $_SERVER["DOCUMENT_ROOT"].'/theme/head.php'; 
?>
<main id="app" class="app app-boxed-layout rounded rounded-4 cvh-margin">
    <?php
	require_once $_SERVER["DOCUMENT_ROOT"].'/theme/header.php';
	
    switch ($request) {
		case '':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/home.php';
			break;
		case 'home':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/home.php';
			break;
		case 'gioi-thieu':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/about.php';
			break;
		case 'dien-dan':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/blog.php';
			break;
		case 'dang-nhap':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/login.php';
			break;
		case 'dang-ky':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/register.php';
			break;
		case 'nap-tien':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/recharge.php';
			break;
		case 'quen-mat-khau':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/forgot-password.php';
			break;
		case 'topic':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/topic.php';
			break;
		case 'chat':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/live-chat.php';
			break;
		case 'shop':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/shop.php';
			break;
		case 'lich-su-mua':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/history_shop.php';
			break;
		case 'hdsd-mod':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/hdsdmod.php';
			break;
		case 'receiver':
			require_once __DIR__ . '/receiver.php';
			break;
		case 'sepay-auto-bank':
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/sepay-auto-bank.php';
			break;
		default:
			http_response_code(404);
			require_once $_SERVER["DOCUMENT_ROOT"].'/pages/404.php';
			break;
	}
	?>
</main>
<?php 
require_once $_SERVER["DOCUMENT_ROOT"].'/theme/footer.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/theme/setting.php'; 
require_once $_SERVER["DOCUMENT_ROOT"].'/theme/end.php'; 
?>