<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/cvhvn/admin_permissions.php';

$adminPerms = getAdminPermissions($CVH, $user);

// Kiểm tra quyền truy cập
if (!$adminPerms->checkPageAccess('system_settings')) {
    header("Location: /admin");
    exit;
}

// Hiển thị thông báo thành công
if (isset($_GET['success']) && $_GET['success'] == '1') {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> Cập nhật quyền thành công!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}

// Hiển thị thông báo lỗi
if (isset($_GET['error'])) {
    $errorMessage = '';
    switch ($_GET['error']) {
        case '1':
            $errorMessage = 'Có lỗi xảy ra khi cập nhật quyền!';
            break;
        case '2':
            $errorMessage = 'Không thể cập nhật quyền của chính mình!';
            break;
        case '3':
            $errorMessage = 'Chỉ Super Admin mới có thể cập nhật quyền!';
            break;
        default:
            $errorMessage = 'Có lỗi xảy ra!';
    }
    
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> ' . $errorMessage . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}

// Lấy danh sách admin với quyền mới nhất
$admins = [];
$query = "SELECT id, username, is_admin, is_super_admin, 
          perm_home, perm_users_manager, perm_ip_manager, perm_recharge_manager, perm_post_manager, 
          perm_notification_manager, perm_giftcode_manager, perm_shop_manager, 
          perm_download_manager, perm_website_manager, perm_logout_all, 
          perm_ddos_check, perm_file_manager, perm_system_settings, perm_buff_item_manager 
          FROM account WHERE is_admin = 1 ORDER BY id DESC";
$result = $CVH->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Tạo user data với quyền mới
        $userData = [
            'id' => $row['id'],
            'username' => $row['username'],
            'is_admin' => $row['is_admin'],
            'is_super_admin' => $row['is_super_admin'],
            'perm_home' => $row['perm_home'],
            'perm_users_manager' => $row['perm_users_manager'],
            'perm_ip_manager' => $row['perm_ip_manager'],
            'perm_vnd_manager' => $row['perm_vnd_manager'],
            'perm_recharge_manager' => $row['perm_recharge_manager'],
            'perm_post_manager' => $row['perm_post_manager'],
            'perm_notification_manager' => $row['perm_notification_manager'],
            'perm_giftcode_manager' => $row['perm_giftcode_manager'],
            'perm_shop_manager' => $row['perm_shop_manager'],
            'perm_download_manager' => $row['perm_download_manager'],
            'perm_website_manager' => $row['perm_website_manager'],
            'perm_logout_all' => $row['perm_logout_all'],
            'perm_ddos_check' => $row['perm_ddos_check'],
            'perm_file_manager' => $row['perm_file_manager'],
            'perm_system_settings' => $row['perm_system_settings'],
            'perm_buff_item_manager' => $row['perm_buff_item_manager']
        ];
        
        $adminPermsTemp = getAdminPermissions($CVH, $userData);
        $row['permissions'] = $adminPermsTemp->getUserPermissions();
        
        // Debug: Log permissions
        error_log("Admin {$row['id']} ({$row['username']}) permissions: " . json_encode($row['permissions']));
        
        $admins[] = $row;
    }
}

$allPermissions = $adminPerms->getAllPermissions();
?>

<div id="content" class="app-content">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Quản lý phân quyền Admin</h4>
                    <p class="card-text">Quản lý quyền hạn cho các tài khoản admin</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Super Admin</th>
                                    <th>Quyền hiện tại</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?php echo $admin['id']; ?></td>
                                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <td>
                                        <?php if ($admin['is_super_admin']): ?>
                                            <span class="badge bg-success">Super Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Admin</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($admin['is_super_admin']) {
                                            echo '<span class="text-success">Tất cả quyền</span>';
                                        } else {
                                            $permNames = [];
                                            foreach ($admin['permissions'] as $perm) {
                                                if (isset($allPermissions[$perm])) {
                                                    $permNames[] = $allPermissions[$perm];
                                                }
                                            }
                                            echo !empty($permNames) ? implode(', ', $permNames) : '<span class="text-muted">Chưa có quyền</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($admin['id'] == $user['id']): ?>
                                            <span class="text-muted">Không thể sửa quyền của chính mình</span>
                                        <?php elseif (!($user['is_super_admin'] ?? false)): ?>
                                            <span class="text-muted">Chỉ Super Admin mới có thể sửa quyền</span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    data-user-id="<?php echo $admin['id']; ?>"
                                                    data-username="<?php echo htmlspecialchars($admin['username']); ?>"
                                                    data-permissions='<?php echo json_encode($admin['permissions']); ?>'
                                                    onclick="editPermissionsFromData(this)">
                                                <i class="fas fa-edit"></i> Sửa quyền
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal chỉnh sửa quyền -->
<div class="modal fade" id="editPermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa quyền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="permissionForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_permissions">
                    <input type="hidden" name="user_id" id="editUserId">
                    
                    <div class="mb-3">
                        <label class="form-label">Tài khoản:</label>
                        <input type="text" class="form-control" id="editUsername" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Chọn quyền:</label>
                        <div class="row">
                            <?php foreach ($allPermissions as $key => $name): ?>
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="permissions[]" value="<?php echo $key; ?>" 
                                           id="perm_<?php echo $key; ?>">
                                    <label class="form-check-label" for="perm_<?php echo $key; ?>">
                                        <?php echo $name; ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Cập nhật quyền</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal thành công -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Cập nhật thành công
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-check-circle fa-3x text-success"></i>
                </div>
                <h6 class="text-success">Đã cập nhật quyền thành công!</h6>
                <p class="text-muted" id="successMessage">
                    Quyền hạn đã được cập nhật cho tài khoản.
                </p>
                <div class="mt-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải lại trang...</span>
                    </div>
                    <p class="text-muted mt-2">Đang tải lại trang...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal lỗi -->
<div class="modal fade" id="errorModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h6 class="text-danger">Không thể cập nhật quyền!</h6>
                <p class="text-muted" id="errorMessage">
                    Đã xảy ra lỗi trong quá trình cập nhật quyền hạn.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                    <i class="fas fa-redo"></i> Thử lại
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function editPermissionsFromData(button) {
    const userId = button.getAttribute('data-user-id');
    const username = button.getAttribute('data-username');
    const permissionsJson = button.getAttribute('data-permissions');
    
            // Security: Removed debug log
    
    try {
        const permissions = JSON.parse(permissionsJson);
        editPermissions(userId, username, permissions);
    } catch (e) {
        console.error('Error parsing permissions JSON:', e);
        alert('Lỗi khi tải dữ liệu quyền!');
    }
}

function editPermissions(userId, username, permissions) {
    console.log('Editing permissions for user:', userId, username, permissions);
    
    document.getElementById('editUserId').value = userId;
    document.getElementById('editUsername').value = username;
    
    // Reset tất cả checkbox
    document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Check các quyền hiện có
    if (Array.isArray(permissions)) {
        permissions.forEach(perm => {
            const checkbox = document.getElementById('perm_' + perm);
            if (checkbox) {
                checkbox.checked = true;
                console.log('Checked permission:', perm);
            } else {
                console.log('Permission checkbox not found:', perm);
            }
        });
    }
    
    // Hiển thị modal
    const modal = new bootstrap.Modal(document.getElementById('editPermissionsModal'));
    modal.show();
}

// Xử lý form submit với AJAX
document.getElementById('permissionForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Ngăn form submit bình thường
    
    const submitBtn = document.getElementById('submitBtn');
    const userId = document.getElementById('editUserId').value;
    const username = document.getElementById('editUsername').value;
    const formData = new FormData(this);
    
    console.log('Submitting form for user:', userId, username);
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
    
    // Gửi request AJAX đến endpoint riêng
    fetch('/ajax/admin/update-permissions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Security: Removed debug log
        
        // Đóng modal edit
        const modal = bootstrap.Modal.getInstance(document.getElementById('editPermissionsModal'));
        if (modal) {
            modal.hide();
        }
        
        if (data.success) {
            // Hiển thị popup thành công
            showUpdateSuccessPopup(username);
            
            // Reload trang sau 2 giây
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            // Hiển thị popup lỗi với message cụ thể
            document.getElementById('errorMessage').innerHTML = data.message;
            showUpdateErrorPopup();
        }
        
        // Reset button
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Cập nhật quyền';
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Hiển thị popup lỗi
        showUpdateErrorPopup();
        
        // Reset button
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Cập nhật quyền';
    });
});

// Tự động ẩn alert sau 5 giây
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

// Hàm hiển thị popup thành công
function showUpdateSuccessPopup(username) {
    const message = `Quyền hạn đã được cập nhật thành công cho tài khoản <strong>${username}</strong>.`;
    document.getElementById('successMessage').innerHTML = message;
    
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
}

// Hàm hiển thị popup lỗi
function showUpdateErrorPopup() {
    const modal = new bootstrap.Modal(document.getElementById('errorModal'));
    modal.show();
}
</script>

