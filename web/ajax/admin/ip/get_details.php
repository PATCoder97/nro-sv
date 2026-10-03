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

// Lấy thống kê chi tiết IP
$stats_query = mysqli_query($CVH->connect_db(), "
    SELECT 
        COUNT(*) as total_accounts,
        SUM(CASE WHEN ban = 1 THEN 1 ELSE 0 END) as banned_accounts,
        SUM(CASE WHEN ban = 0 THEN 1 ELSE 0 END) as active_accounts,
        MIN(create_time) as first_account,
        MAX(update_time) as last_account,
        MAX(last_time_login) as last_activity,
        GROUP_CONCAT(DISTINCT username ORDER BY username SEPARATOR ', ') as all_usernames
    FROM account 
    WHERE ip_address = '" . mysqli_real_escape_string($CVH->connect_db(), $ip) . "'
");

$stats = mysqli_fetch_assoc($stats_query);

// Lấy danh sách tài khoản gần đây
$recent_query = mysqli_query($CVH->connect_db(), "
    SELECT username, ban, last_time_login, create_time, update_time
    FROM account 
    WHERE ip_address = '" . mysqli_real_escape_string($CVH->connect_db(), $ip) . "'
    ORDER BY last_time_login DESC
    LIMIT 5
");

// Xử lý last_activity cho thống kê
$last_activity_time = null;
if ($stats['last_activity']) {
    if (is_numeric($stats['last_activity'])) {
        $last_activity_time = $stats['last_activity'];
    } else {
        $timestamp = strtotime($stats['last_activity']);
        if ($timestamp !== false) {
            $last_activity_time = $timestamp;
        }
    }
}
$last_activity_display = $last_activity_time ? $CVH->time_ago($last_activity_time) : 'Chưa đăng nhập';

// Tính điểm bảo mật
$security_score = 100;
$security_issues = [];

if ($stats['total_accounts'] > 5) {
    $security_score -= 30;
    $security_issues[] = 'Nhiều tài khoản trên cùng IP (có thể là bot/spam)';
}

if ($stats['banned_accounts'] > 0) {
    $security_score -= 20;
    $security_issues[] = 'Có tài khoản bị ban';
}

if ($stats['total_accounts'] > 10) {
    $security_score -= 40;
    $security_issues[] = 'Rất nhiều tài khoản (có thể là tấn công)';
}

$security_score = max(0, $security_score);

if ($security_score >= 80) {
    $score_class = 'success';
    $score_text = 'An toàn';
    $score_icon = 'shield-check';
} elseif ($security_score >= 60) {
    $score_class = 'warning';
    $score_text = 'Cần theo dõi';
    $score_icon = 'exclamation-triangle';
} else {
    $score_class = 'danger';
    $score_text = 'Nguy hiểm';
    $score_icon = 'shield-alt';
}

?>

<!-- Header với IP và nút hành động -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-network-wired text-primary me-2"></i>Chi tiết IP</h4>
        <p class="text-muted mb-0"><?php echo htmlspecialchars($ip); ?></p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="banIP('<?php echo htmlspecialchars($ip); ?>')">
            <i class="fas fa-ban me-1"></i>Ban IP
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="unbanIP('<?php echo htmlspecialchars($ip); ?>')">
            <i class="fas fa-check me-1"></i>Unban IP
        </button>
    </div>
</div>

<!-- Thống kê chính -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-center text-white">
                <div class="mb-2">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
                <h3 class="mb-1"><?php echo $stats['total_accounts']; ?></h3>
                <small class="opacity-75">Tổng tài khoản</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <div class="card-body text-center text-white">
                <div class="mb-2">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
                <h3 class="mb-1"><?php echo $stats['active_accounts']; ?></h3>
                <small class="opacity-75">Hoạt động</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);">
            <div class="card-body text-center text-white">
                <div class="mb-2">
                    <i class="fas fa-ban fa-2x opacity-75"></i>
                </div>
                <h3 class="mb-1"><?php echo $stats['banned_accounts']; ?></h3>
                <small class="opacity-75">Bị ban</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-center text-white">
                <div class="mb-2">
                    <i class="fas fa-<?php echo $score_icon; ?> fa-2x opacity-75"></i>
                </div>
                <h3 class="mb-1"><?php echo $security_score; ?></h3>
                <small class="opacity-75">Điểm bảo mật</small>
            </div>
        </div>
    </div>
</div>

<!-- Thông tin thời gian và bảo mật -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light border-0">
                <h6 class="mb-0"><i class="fas fa-clock text-primary me-2"></i>Thông tin thời gian</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-calendar-plus text-primary"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Tài khoản đầu tiên</small>
                                <strong><?php echo ($stats['first_account'] ? date('d/m/Y H:i', strtotime($stats['first_account'])) : 'N/A'); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-calendar-check text-success"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Tài khoản cuối cùng</small>
                                <strong><?php echo ($stats['last_account'] ? date('d/m/Y H:i', strtotime($stats['last_account'])) : 'N/A'); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="fas fa-clock text-info"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Hoạt động cuối</small>
                        <strong><?php echo $last_activity_display; ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light border-0">
                <h6 class="mb-0"><i class="fas fa-shield-alt text-<?php echo $score_class; ?> me-2"></i>Đánh giá bảo mật</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="position-relative d-inline-block">
                        <div class="progress-circle" style="width: 80px; height: 80px; border-radius: 50%; border: 8px solid #e9ecef; border-top: 8px solid var(--bs-<?php echo $score_class; ?>); transform: rotate(-90deg); display: inline-block;">
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(90deg);">
                                <h4 class="mb-0 text-<?php echo $score_class; ?>"><?php echo $security_score; ?></h4>
                                <small class="text-muted"><?php echo $score_text; ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (empty($security_issues)): ?>
                    <div class="text-center">
                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                        <p class="text-success mb-0">IP này không có vấn đề bảo mật</p>
                    </div>
                <?php else: ?>
                    <div>
                        <h6 class="text-danger mb-2"><i class="fas fa-exclamation-triangle me-1"></i>Vấn đề bảo mật:</h6>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($security_issues as $issue): ?>
                                <li class="text-danger mb-1">
                                    <i class="fas fa-times-circle me-2"></i><?php echo htmlspecialchars($issue); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Danh sách tài khoản -->
<div class="row">
    <div class="col-md-8 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-users text-primary me-2"></i>Danh sách tài khoản</h6>
                <span class="badge bg-primary"><?php echo $stats['total_accounts']; ?> tài khoản</span>
            </div>
            <div class="card-body">
                <?php if ($stats['all_usernames']): ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php 
                        $usernames = explode(', ', $stats['all_usernames']);
                        foreach ($usernames as $username): 
                        ?>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($username); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                        <p>Không có tài khoản nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light border-0">
                <h6 class="mb-0"><i class="fas fa-history text-info me-2"></i>Tài khoản gần đây</h6>
            </div>
            <div class="card-body p-0">
                <?php if (mysqli_num_rows($recent_query) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php while ($row = mysqli_fetch_assoc($recent_query)): 
                            $status_class = $row['ban'] ? 'text-danger' : 'text-success';
                            $status_icon = $row['ban'] ? 'ban' : 'check-circle';
                            
                            // Xử lý last_time_login - có thể là timestamp hoặc datetime string
                            $last_login_time = null;
                            if ($row['last_time_login']) {
                                if (is_numeric($row['last_time_login'])) {
                                    $last_login_time = $row['last_time_login'];
                                } else {
                                    $timestamp = strtotime($row['last_time_login']);
                                    if ($timestamp !== false) {
                                        $last_login_time = $timestamp;
                                    }
                                }
                            }
                            $last_login = $last_login_time ? $CVH->time_ago($last_login_time) : 'Chưa đăng nhập';
                        ?>
                            <div class="list-group-item border-0 d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-2 me-3">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block"><?php echo htmlspecialchars($row['username']); ?></strong>
                                        <small class="text-muted"><?php echo $last_login; ?></small>
                                    </div>
                                </div>
                                <div>
                                    <i class="fas fa-<?php echo $status_icon; ?> <?php echo $status_class; ?>"></i>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-history fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0">Không có dữ liệu</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Footer với thông tin bổ sung -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Thông tin được cập nhật lần cuối: <?php echo date('d/m/Y H:i:s'); ?>
                </small>
            </div>
        </div>
    </div>
</div>
