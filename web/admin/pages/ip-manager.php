    <?php
    // Kiểm tra quyền truy cập
    if (!$user['is_admin']) {
        echo '<div class="alert alert-danger">Bạn không có quyền truy cập trang này!</div>';
        exit;
    }

    // Kiểm tra quyền IP Manager
    if (!$user['is_super_admin'] && empty($user['perm_ip_manager'])) {
        echo '<div class="alert alert-danger">Bạn không có quyền quản lý IP!</div>';
        exit;
    }

    $kmess = 20;
    $page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
    $start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);

    // Lấy danh sách IP và thống kê tài khoản
    $result = mysqli_query($CVH->connect_db(), "
        SELECT 
            ip_address,
            COUNT(*) as account_count,
            SUM(CASE WHEN ban = 1 THEN 1 ELSE 0 END) as banned_count,
            SUM(CASE WHEN ban = 0 THEN 1 ELSE 0 END) as active_count,
            MAX(last_time_login) as last_activity,
            GROUP_CONCAT(DISTINCT username ORDER BY username SEPARATOR ', ') as usernames
        FROM account 
        WHERE ip_address IS NOT NULL AND ip_address != ''
        GROUP BY ip_address 
        ORDER BY account_count DESC, last_activity DESC
        LIMIT $start, $kmess
    ");

    $total_query = mysqli_query($CVH->connect_db(), "
        SELECT COUNT(DISTINCT ip_address) as total 
        FROM account 
        WHERE ip_address IS NOT NULL AND ip_address != ''
    ");
    $total = mysqli_fetch_assoc($total_query)['total'];

    // Tạo CSRF token
    $csrf_token = $CVH->getCSRFToken($user['id']);
    ?>

    <div id="content" class="app-content">
        <div class="row">
            <div class="col-xl-12 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            Quản lý IP - Ban/Unban & Thống kê tài khoản
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Thống kê tổng quan -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title">Tổng IP</h6>
                                                <h3 class="mb-0"><?php echo number_format($total); ?></h3>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-network-wired fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title">IP Hoạt động</h6>
                                                <h3 class="mb-0" id="active-ip-count">-</h3>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-check-circle fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title">IP Bị Ban</h6>
                                                <h3 class="mb-0" id="banned-ip-count">-</h3>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-ban fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title">Tài khoản đa IP</h6>
                                                <h3 class="mb-0" id="multi-ip-count">-</h3>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-users fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bảng danh sách IP -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>IP Address</th>
                                        <th>Số tài khoản</th>
                                        <th>Trạng thái</th>
                                        <th>Hoạt động cuối</th>
                                        <th>Danh sách tài khoản</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $active_ips = 0;
                                    $banned_ips = 0;
                                    $multi_account_ips = 0;

                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $ip = $row['ip_address'];
                                            $account_count = $row['account_count'];
                                            $banned_count = $row['banned_count'];
                                            $active_count = $row['active_count'];
                                            $last_activity = $row['last_activity'];
                                            $usernames = $row['usernames'];

                                            // Đếm thống kê
                                            if ($banned_count > 0) {
                                                $banned_ips++;
                                            } else {
                                                $active_ips++;
                                            }
                                            if ($account_count > 1) {
                                                $multi_account_ips++;
                                            }

                                            // Xác định trạng thái
                                            $status_class = '';
                                            $status_text = '';
                                            if ($banned_count == $account_count) {
                                                $status_class = 'badge bg-danger';
                                                $status_text = 'Bị Ban';
                                            } elseif ($banned_count > 0) {
                                                $status_class = 'badge bg-warning';
                                                $status_text = 'Một phần bị ban';
                                            } else {
                                                $status_class = 'badge bg-success';
                                                $status_text = 'Hoạt động';
                                            }

                                            // Format thời gian
                                            // Xử lý last_activity - có thể là timestamp hoặc datetime string
            $last_activity_time = null;
            if ($last_activity) {
                if (is_numeric($last_activity)) {
                    // Nếu là timestamp
                    $last_activity_time = $last_activity;
                } else {
                    // Nếu là datetime string
                    $timestamp = strtotime($last_activity);
                    if ($timestamp !== false) {
                        $last_activity_time = $timestamp;
                    }
                }
            }
            $time_ago = $last_activity_time ? $CVH->time_ago($last_activity_time) : 'Chưa đăng nhập';
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($ip); ?></strong>
                                            <?php if ($account_count > 1): ?>
                                                <span class="badge bg-info ms-1"><?php echo $account_count; ?> tài khoản</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary me-1"><?php echo $account_count; ?></span>
                                                <?php if ($banned_count > 0): ?>
                                                    <span class="badge bg-danger me-1"><?php echo $banned_count; ?> ban</span>
                                                <?php endif; ?>
                                                <?php if ($active_count > 0): ?>
                                                    <span class="badge bg-success"><?php echo $active_count; ?> active</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo $time_ago; ?></small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="showAccounts('<?php echo htmlspecialchars($ip); ?>')">
                                                <i class="fas fa-eye"></i> Xem tài khoản
                                            </button>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <?php if ($banned_count == 0): ?>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="banIP('<?php echo htmlspecialchars($ip); ?>')">
                                                        <i class="fas fa-ban"></i> Ban IP
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="unbanIP('<?php echo htmlspecialchars($ip); ?>')">
                                                        <i class="fas fa-check"></i> Unban IP
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        onclick="showIPDetails('<?php echo htmlspecialchars($ip); ?>')">
                                                    <i class="fas fa-info-circle"></i> Chi tiết
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="6" class="text-center">Không có dữ liệu IP nào</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Phân trang -->
                        <?php if ($total > $kmess): ?>
                        <div class="d-flex justify-content-center mt-3">
                            <?php echo $CVH->phantrang('ip-manager?', $start, $total, $kmess); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal chi tiết tài khoản theo IP -->
    <div class="modal fade" id="accountsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Danh sách tài khoản theo IP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="accountsList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal chi tiết IP -->
    <div class="modal fade" id="ipDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết IP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="ipDetails"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Cập nhật thống kê
    $(document).ready(function() {
        $('#active-ip-count').text('<?php echo $active_ips; ?>');
        $('#banned-ip-count').text('<?php echo $banned_ips; ?>');
        $('#multi-ip-count').text('<?php echo $multi_account_ips; ?>');
    });

    // Hiển thị danh sách tài khoản theo IP
    function showAccounts(ip) {
        // Tạm thời disable anti-debug để tránh popup lỗi
        window.adminActionInProgress = true;
        
        $('#accountsList').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>');
        $('#accountsModal').modal('show');
        
        $.ajax({
            url: '/ajax/admin/ip/get_accounts.php',
            type: 'POST',
            data: { ip: ip, csrf_token: '<?php echo $csrf_token; ?>' },
            success: function(response) {
                $('#accountsList').html(response);
                // Re-enable anti-debug
                window.adminActionInProgress = false;
            },
            error: function() {
                $('#accountsList').html('<div class="alert alert-danger">Lỗi tải dữ liệu</div>');
                // Re-enable anti-debug
                window.adminActionInProgress = false;
            }
        });
    }

    // Hiển thị chi tiết IP
    function showIPDetails(ip) {
        // Tạm thời disable anti-debug để tránh popup lỗi
        window.adminActionInProgress = true;
        
        $('#ipDetails').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>');
        $('#ipDetailsModal').modal('show');
        
        $.ajax({
            url: '/ajax/admin/ip/get_details.php',
            type: 'POST',
            data: { ip: ip, csrf_token: '<?php echo $csrf_token; ?>' },
            success: function(response) {
                $('#ipDetails').html(response);
                // Re-enable anti-debug
                window.adminActionInProgress = false;
            },
            error: function() {
                $('#ipDetails').html('<div class="alert alert-danger">Lỗi tải dữ liệu</div>');
                // Re-enable anti-debug
                window.adminActionInProgress = false;
            }
        });
    }

    // Ban IP
    function banIP(ip) {
        // Tạm thời disable anti-debug để tránh popup lỗi
        window.adminActionInProgress = true;
        
        Swal.fire({
            title: 'Xác nhận Ban IP',
            text: `Bạn có chắc muốn ban IP ${ip}? Tất cả tài khoản sử dụng IP này sẽ bị khóa.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ban IP',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/ajax/admin/ip/ban.php',
                    type: 'POST',
                    data: { 
                        ip: ip, 
                        action: 'ban',
                        csrf_token: '<?php echo $csrf_token; ?>' 
                    },
                    dataType: 'text', // Thay đổi thành text để xử lý thủ công
                    success: function(response) {
                        // Kiểm tra response rỗng
                        if (!response || response.trim() === '') {
                            Swal.fire('Lỗi', 'Server trả về response rỗng, vui lòng thử lại!', 'error');
                            window.adminActionInProgress = false;
                            return;
                        }
                        
                        try {
                            // Thử parse JSON
                            var jsonResponse = JSON.parse(response);
                            
                            if (jsonResponse.status) {
                                Swal.fire('Thành công', jsonResponse.message, 'success').then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Lỗi', jsonResponse.message || 'Có lỗi xảy ra', 'error');
                            }
                        } catch (e) {
                            // Nếu response có chứa HTML error, hiển thị thông báo chung
                            if (response.includes('<br />') || response.includes('<b>')) {
                                Swal.fire('Lỗi', 'Có lỗi xảy ra trên server, vui lòng thử lại sau!', 'error');
                            } else {
                                Swal.fire('Lỗi', 'Có lỗi xảy ra khi xử lý phản hồi từ server', 'error');
                            }
                        }
                        // Re-enable anti-debug
                        window.adminActionInProgress = false;
                    },
                    error: function(xhr, status, error) {
                        // Kiểm tra status code
                        if (xhr.status === 403) {
                            Swal.fire('Lỗi', 'Bạn không có quyền thực hiện thao tác này!', 'error');
                        } else if (xhr.status === 404) {
                            Swal.fire('Lỗi', 'Không tìm thấy API endpoint!', 'error');
                        } else if (xhr.status === 500) {
                            Swal.fire('Lỗi', 'Lỗi server, vui lòng thử lại sau!', 'error');
                        } else {
                            Swal.fire('Lỗi', 'Có lỗi xảy ra, vui lòng thử lại!', 'error');
                        }
                        // Re-enable anti-debug
                        window.adminActionInProgress = false;
                    }
                });
            } else {
                // Re-enable anti-debug nếu user cancel
                window.adminActionInProgress = false;
            }
        });
    }

    // Unban IP
    function unbanIP(ip) {
        // Tạm thời disable anti-debug để tránh popup lỗi
        window.adminActionInProgress = true;
        
        Swal.fire({
            title: 'Xác nhận Unban IP',
            text: `Bạn có chắc muốn unban IP ${ip}? Tất cả tài khoản sử dụng IP này sẽ được mở khóa.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Unban IP',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/ajax/admin/ip/ban.php',
                    type: 'POST',
                    data: { 
                        ip: ip, 
                        action: 'unban',
                        csrf_token: '<?php echo $csrf_token; ?>' 
                    },
                    dataType: 'text', // Thay đổi thành text để xử lý thủ công
                    success: function(response) {
                        // Kiểm tra response rỗng
                        if (!response || response.trim() === '') {
                            Swal.fire('Lỗi', 'Server trả về response rỗng, vui lòng thử lại!', 'error');
                            window.adminActionInProgress = false;
                            return;
                        }
                        
                        try {
                            // Thử parse JSON
                            var jsonResponse = JSON.parse(response);
                            
                            if (jsonResponse.status) {
                                Swal.fire('Thành công', jsonResponse.message, 'success').then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Lỗi', jsonResponse.message || 'Có lỗi xảy ra', 'error');
                            }
                        } catch (e) {
                            // Nếu response có chứa HTML error, hiển thị thông báo chung
                            if (response.includes('<br />') || response.includes('<b>')) {
                                Swal.fire('Lỗi', 'Có lỗi xảy ra trên server, vui lòng thử lại sau!', 'error');
                            } else {
                                Swal.fire('Lỗi', 'Có lỗi xảy ra khi xử lý phản hồi từ server', 'error');
                            }
                        }
                        // Re-enable anti-debug
                        window.adminActionInProgress = false;
                    },
                    error: function(xhr, status, error) {
                        // Kiểm tra status code
                        if (xhr.status === 403) {
                            Swal.fire('Lỗi', 'Bạn không có quyền thực hiện thao tác này!', 'error');
                        } else if (xhr.status === 404) {
                            Swal.fire('Lỗi', 'Không tìm thấy API endpoint!', 'error');
                        } else if (xhr.status === 500) {
                            Swal.fire('Lỗi', 'Lỗi server, vui lòng thử lại sau!', 'error');
                        } else {
                            Swal.fire('Lỗi', 'Có lỗi xảy ra, vui lòng thử lại!', 'error');
                        }
                        // Re-enable anti-debug
                        window.adminActionInProgress = false;
                    }
                });
            } else {
                // Re-enable anti-debug nếu user cancel
                window.adminActionInProgress = false;
            }
        });
    }
    </script>
