<?php
header('Content-Type: text/html; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

// Kiểm tra quyền admin
if (!$user['is_admin']) {
    echo "Bạn không có quyền truy cập!";
    exit();
}

// Kiểm tra quyền IP Manager
if (!$user['is_super_admin'] && empty($user['perm_ip_manager'])) {
    echo "Bạn không có quyền quản lý IP!";
    exit();
}

// Kiểm tra CSRF token
if (!isset($_POST['csrf_token']) || !$CVH->validateCSRFToken($user['id'], $_POST['csrf_token'])) {
    echo "Token không hợp lệ!";
    exit();
}

$ip = trim($_POST['ip'] ?? '');
if (empty($ip)) {
    echo "IP không hợp lệ!";
    exit();
}

// Lấy danh sách tài khoản theo IP
$query = mysqli_query($CVH->connect_db(), "
    SELECT 
        id,
        username,
        email,
        ban,
        last_time_login,
        create_time, update_time,
        ip_address
    FROM account 
    WHERE ip_address = '" . mysqli_real_escape_string($CVH->connect_db(), $ip) . "'
    ORDER BY last_time_login DESC
");

if (mysqli_num_rows($query) > 0) {
    echo '<div class="table-responsive">';
    echo '<table class="table table-hover">';
    echo '<thead class="table-dark">';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Username</th>';
    echo '<th>Email</th>';
    echo '<th>Trạng thái</th>';
    echo '<th>Đăng nhập cuối</th>';
    echo '<th>Ngày tạo</th>';
    echo '<th>Cập nhật cuối</th>';
    echo '<th>Thao tác</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    while ($row = mysqli_fetch_assoc($query)) {
        $status_class = $row['ban'] ? 'badge bg-danger' : 'badge bg-success';
        $status_text = $row['ban'] ? 'Bị Ban' : 'Hoạt động';
        // Xử lý last_time_login - có thể là timestamp hoặc datetime string
        $last_login_time = null;
        if ($row['last_time_login']) {
            if (is_numeric($row['last_time_login'])) {
                // Nếu là timestamp
                $last_login_time = $row['last_time_login'];
            } else {
                // Nếu là datetime string
                $timestamp = strtotime($row['last_time_login']);
                if ($timestamp !== false) {
                    $last_login_time = $timestamp;
                }
            }
        }
        $last_login = $last_login_time ? $CVH->time_ago($last_login_time) : 'Chưa đăng nhập';
        $created_at = $row['create_time'] ? date('d/m/Y H:i', strtotime($row['create_time'])) : '-';
        $updated_at = $row['update_time'] ? date('d/m/Y H:i', strtotime($row['update_time'])) : '-';
        
        echo '<tr>';
        echo '<td>' . $row['id'] . '</td>';
        echo '<td><strong>' . htmlspecialchars($row['username']) . '</strong></td>';
        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
        echo '<td><span class="' . $status_class . '">' . $status_text . '</span></td>';
        echo '<td><small class="text-muted">' . $last_login . '</small></td>';
        echo '<td><small class="text-muted">' . $created_at . '</small></td>';
        echo '<td><small class="text-muted">' . $updated_at . '</small></td>';
        echo '<td>';
        if ($row['ban']) {
            echo '<button type="button" class="btn btn-sm btn-success" onclick="unbanAccount(' . $row['id'] . ')">';
            echo '<i class="fas fa-check"></i> Unban';
            echo '</button>';
        } else {
            echo '<button type="button" class="btn btn-sm btn-danger" onclick="banAccount(' . $row['id'] . ')">';
            echo '<i class="fas fa-ban"></i> Ban';
            echo '</button>';
        }
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else {
    echo '<div class="alert alert-info">Không có tài khoản nào sử dụng IP này.</div>';
}
?>

<script>
// Ban tài khoản
function banAccount(accountId) {
    Swal.fire({
        title: 'Xác nhận Ban tài khoản',
        text: 'Bạn có chắc muốn ban tài khoản này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ban',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/ajax/admin/ip/ban_account.php',
                type: 'POST',
                data: { 
                    account_id: accountId, 
                    action: 'ban',
                    csrf_token: '<?php echo htmlspecialchars($_POST['csrf_token']); ?>' 
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire('Thành công', response.message, 'success').then(() => {
                            showAccounts('<?php echo htmlspecialchars($ip); ?>');
                        });
                    } else {
                        Swal.fire('Lỗi', response.message, 'error');
                    }
                }
            });
        }
    });
}

// Unban tài khoản
function unbanAccount(accountId) {
    Swal.fire({
        title: 'Xác nhận Unban tài khoản',
        text: 'Bạn có chắc muốn unban tài khoản này?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Unban',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/ajax/admin/ip/ban_account.php',
                type: 'POST',
                data: { 
                    account_id: accountId, 
                    action: 'unban',
                    csrf_token: '<?php echo htmlspecialchars($_POST['csrf_token']); ?>' 
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire('Thành công', response.message, 'success').then(() => {
                            showAccounts('<?php echo htmlspecialchars($ip); ?>');
                        });
                    } else {
                        Swal.fire('Lỗi', response.message, 'error');
                    }
                }
            });
        }
    });
}
</script>
