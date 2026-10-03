<?php
$database_file = $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/database.php';
if (!file_exists($database_file)) {
    header('Location: /install');
    exit;
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/admin_permissions.php";

// Kiểm tra quyền admin
$adminPerms = getAdminPermissions($CVH, $user);
$adminPerms->requireAdmin();

require_once $_SERVER["DOCUMENT_ROOT"].'/admin/theme/head.php'; 

$request = isset($_GET['request']) ? $_GET['request'] : 'home';

?>
<div id="app" class="app">
    <?php
	require_once $_SERVER["DOCUMENT_ROOT"].'/admin/theme/header.php';
    switch ($request) {
        case 'home':
        case '/':
            if ($adminPerms->checkPageAccess('home')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/home.php';
            } else {
                echo '<script>showPermissionDenied("Trang chủ");</script>';
            }
            break;
        
        case 'users-manager':
            if ($adminPerms->checkPageAccess('users_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/users-manager.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Quản lý thành viên"); });</script>';
            }
            break;

        case 'ddos-check':
            if ($adminPerms->checkPageAccess('ddos_check')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/ddos/ddos_monitor_panel.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("DDoS Check"); });</script>';
            }
            break;

        case 'recharge':
            if ($adminPerms->checkPageAccess('recharge_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/recharge.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Quản lý thẻ nạp"); });</script>';
            }
            break;

        case 'manager-post':
            if ($adminPerms->checkPageAccess('post_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/manager-post.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Quản lý bài viết"); });</script>';
            }
            break;

        case 'admin-post':
            if ($adminPerms->checkPageAccess('post_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/poster/admin-post.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Đăng bài viết"); });</script>';
            }
            break;

        case 'giftcode':
            if ($adminPerms->checkPageAccess('giftcode_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/giftcode.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Quản lý giftcode"); });</script>';
            }
            break;

        case 'setting':
            if ($adminPerms->checkPageAccess('system_settings')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/setting.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Cài đặt hệ thống"); });</script>';
            }
            break;

        case 'edit-poster':
            if ($adminPerms->checkPageAccess('post_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/poster/edit-post.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Chỉnh sửa bài viết"); });</script>';
            }
            break;

        case 'download-manager':
            if ($adminPerms->checkPageAccess('download_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/download/download-manager.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Quản lý link tải"); });</script>';
            }
            break;

        case 'edit-download':
            if ($adminPerms->checkPageAccess('download_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/download/edit.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Chỉnh sửa link tải"); });</script>';
            }
            break;

        case 'add-shop':
            if ($adminPerms->checkPageAccess('shop_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/add-shop.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Thêm shop"); });</script>';
            }
            break;
            
        case 'shop-log':
            if ($adminPerms->checkPageAccess('shop_log')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/shop-log.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Xem log shop"); });</script>';
            }
            break;
			
		case 'logout-all':
            if ($adminPerms->checkPageAccess('logout_all')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/logout_all.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Logout tất cả user"); });</script>';
            }
            break;

        case 'admin-permissions':
            if ($adminPerms->checkPageAccess('system_settings')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/admin-permissions.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Quản lý phân quyền admin"); });</script>';
            }
            break;

        case 'my-permissions':
            // Không cần kiểm tra quyền - tất cả admin đều có thể xem quyền của mình
            require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/my-permissions.php';
            break;

        case 'ip-manager':
            if ($adminPerms->checkPageAccess('ip_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/ip-manager.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Quản lý IP"); });</script>';
            }
            break;

        case 'buff-item-manager':
            if ($adminPerms->checkPageAccess('buff_item_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/buff-item-manager.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Quản lý Buff Item"); });</script>';
            }
            break;

        case 'item-template':
            if ($adminPerms->checkPageAccess('item_template_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/item-template.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Quản lý Item Template"); });</script>';
            }
            break;

        case 'maintenance':
            if ($adminPerms->checkPageAccess('maintenance_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/maintenance.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Bảo trì server"); });</script>';
            }
            break;

        case 'vnd-manager':
            if ($adminPerms->checkPageAccess('vnd_manager')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/vnd-manager.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Quản lý VND"); });</script>';
            }
            break;

        default:
			http_response_code(404);
			if ($adminPerms->checkPageAccess('home')) {
                require_once $_SERVER["DOCUMENT_ROOT"].'/admin/pages/home.php';
            } else {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showPermissionDenied("Trang này"); });</script>';
            }
			break;
	}
    ?>
</div>

<!-- Modal Permission Denied -->
<div class="modal fade" id="permissionDeniedModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Không có quyền truy cập
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-lock fa-3x text-danger"></i>
                </div>
                <h6 class="text-danger">Bạn không có quyền truy cập chức năng này!</h6>
                <p class="text-muted" id="permissionDeniedMessage">
                    Vui lòng liên hệ Super Admin để được cấp quyền.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="window.location.href='/admin'">
                    <i class="fas fa-home"></i> Về trang chủ
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showPermissionDenied(functionName) {
    // Security: Removed debug log
    
    // Lấy quyền hiện tại của user
    let currentPermissions = [];
    <?php 
    $currentPerms = $adminPerms->getUserPermissions();
    $allPerms = $adminPerms->getAllPermissions();
    $permNames = [];
    foreach ($currentPerms as $perm) {
        if (isset($allPerms[$perm])) {
            $permNames[] = $allPerms[$perm];
        }
    }
    echo "currentPermissions = " . json_encode($permNames) . ";";
    ?>
    
    let permissionText = currentPermissions.length > 0 ? 
        'Quyền hiện tại: <strong>' + currentPermissions.join(', ') + '</strong>' : 
        'Bạn chưa có quyền nào được cấp';
    
    const message = `Bạn không có quyền truy cập: <strong>${functionName}</strong><br><br>${permissionText}<br><br>Vui lòng liên hệ Super Admin để được cấp quyền.`;
    document.getElementById('permissionDeniedMessage').innerHTML = message;
    
    // Kiểm tra Bootstrap có sẵn không
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modalElement = document.getElementById('permissionDeniedModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
            // KHÔNG auto redirect nữa - để user tự đóng
        } else {
            console.error('Modal element not found');
            alert('Bạn không có quyền truy cập: ' + functionName);
        }
    } else {
        console.error('Bootstrap Modal not available');
        alert('Bạn không có quyền truy cập: ' + functionName);
    }
}

// Xử lý khi modal được đóng - KHÔNG auto redirect
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('permissionDeniedModal');
    if (modalElement) {
        // Khi modal đóng, KHÔNG làm gì cả - để user tự điều hướng
        modalElement.addEventListener('hidden.bs.modal', function () {
            console.log('Modal closed - no auto redirect');
        });
    }
});
</script>

<?php 
require_once $_SERVER["DOCUMENT_ROOT"].'/admin/theme/footer.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/admin/theme/end.php'; 
?>