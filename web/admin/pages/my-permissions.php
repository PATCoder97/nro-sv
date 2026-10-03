<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/admin_permissions.php';

$adminPerms = getAdminPermissions($CVH, $user);

// Lấy quyền hiện tại
$currentPermissions = $adminPerms->getUserPermissions();
$allPermissions = $adminPerms->getAllPermissions();

// Tạo danh sách quyền có tên
$permissionList = [];
foreach ($currentPermissions as $perm) {
    if (isset($allPermissions[$perm])) {
        $permissionList[] = $allPermissions[$perm];
    }
}

// Kiểm tra Super Admin
$isSuperAdmin = $user['is_super_admin'] ?? false;
?>

<div id="content" class="app-content">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-user-shield"></i> Quyền hạn của tôi
                    </h4>
                    <p class="card-text">Xem quyền hạn hiện tại của tài khoản admin</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-info-circle"></i> Thông tin tài khoản
                                    </h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>ID:</strong></td>
                                            <td><?php echo $user['id']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Username:</strong></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Vai trò:</strong></td>
                                            <td>
                                                <?php if ($isSuperAdmin): ?>
                                                    <span class="badge bg-success">Super Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Admin</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-key"></i> Quyền hạn hiện tại
                                    </h5>
                                    <?php if ($isSuperAdmin): ?>
                                        <div class="alert alert-success">
                                            <i class="fas fa-crown"></i> <strong>Super Admin:</strong> Bạn có tất cả quyền hạn trong hệ thống
                                        </div>
                                    <?php elseif (empty($permissionList)): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> <strong>Chưa có quyền:</strong> Bạn chưa được cấp quyền hạn nào
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group">
                                            <?php foreach ($permissionList as $permName): ?>
                                                <div class="list-group-item list-group-item-success">
                                                    <i class="fas fa-check-circle text-success"></i> <?php echo $permName; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-list"></i> Tất cả quyền hạn có sẵn
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($allPermissions as $key => $name): ?>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           <?php echo in_array($key, $currentPermissions) ? 'checked' : ''; ?> 
                                                           disabled>
                                                    <label class="form-check-label">
                                                        <?php echo $name; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Lưu ý:</strong> Để thay đổi quyền hạn, vui lòng liên hệ Super Admin hoặc truy cập 
                                <a href="/admin?request=admin-permissions" class="alert-link">Quản lý phân quyền admin</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
