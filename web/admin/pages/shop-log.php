<?php
// Kiểm tra quyền
if (!$adminPerms->checkPageAccess('shop_log')) {
    echo '<script>window.showPermissionDeniedNeeded = "Xem log shop";</script>';
    return;
}

// Xử lý filter
$filter_admin = isset($_GET['admin']) ? intval($_GET['admin']) : 0;
$filter_planet = isset($_GET['planet']) ? $_GET['planet'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Xây dựng query
$where_conditions = [];
$params = [];

if ($filter_admin > 0) {
    $where_conditions[] = "sl.admin_id = ?";
    $params[] = $filter_admin;
}

if ($filter_planet !== '') {
    $where_conditions[] = "sl.planet = " . intval($filter_planet);
}

if ($filter_status) {
    if ($filter_status == 'active') {
        $where_conditions[] = "s.id IS NOT NULL";
    } elseif ($filter_status == 'deleted') {
        $where_conditions[] = "s.id IS NULL";
    }
}

if ($filter_date_from) {
    $where_conditions[] = "DATE(sl.created_at) >= ?";
    $params[] = $filter_date_from;
}

if ($filter_date_to) {
    $where_conditions[] = "DATE(sl.created_at) <= ?";
    $params[] = $filter_date_to;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Lấy danh sách admin
$admins = $CVH->get_list("SELECT id, username FROM account WHERE is_admin = 1 ORDER BY username");
$admin_list = [];
foreach ($admins as $admin) {
    $admin_list[$admin['id']] = $admin['username'];
}

// Lấy danh sách hành tinh
$planets = $CVH->get_list("SELECT DISTINCT planet FROM shop_log ORDER BY planet");
$planet_list = [];
$planet_names = [
    0 => 'Trái Đất',
    1 => 'Namek',
    2 => 'Xayda'
];
foreach ($planets as $planet) {
    $planet_list[$planet['planet']] = $planet_names[$planet['planet']] ?? 'Hành tinh ' . $planet['planet'];
}

// Lấy dữ liệu log
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) as total FROM shop_log sl $where_clause";
$total_records = $CVH->get_row($count_sql)['total'];
$total_pages = ceil($total_records / $limit);

$sql = "SELECT sl.*, 
        CASE 
            WHEN s.id IS NOT NULL THEN 'active'
            ELSE 'deleted'
        END as current_status
        FROM shop_log sl 
        LEFT JOIN cvh_sell_item s ON sl.item_id = s.id
        $where_clause 
        ORDER BY sl.created_at DESC 
        LIMIT $limit OFFSET $offset";
?>

<div id="content" class="app-content">
    <div class="d-flex align-items-center mb-3">
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin">Trang chủ</a></li>
                <li class="breadcrumb-item active">Log Shop</li>
            </ol>
            <h1 class="page-header mb-0">📊 Log Quản Lý Shop</h1>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fa fa-filter"></i> Bộ lọc
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="request" value="shop-log">
                
                <div class="col-md-2">
                    <label class="form-label">Admin</label>
                    <select name="admin" class="form-select">
                        <option value="">Tất cả</option>
                        <?php foreach ($admin_list as $id => $username): ?>
                        <option value="<?php echo $id; ?>" <?php echo $filter_admin == $id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($username); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Hành tinh</label>
                    <select name="planet" class="form-select">
                        <option value="">Tất cả</option>
                        <?php foreach ($planet_list as $planet_id => $planet_name): ?>
                        <option value="<?php echo $planet_id; ?>" <?php echo $filter_planet == $planet_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($planet_name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Còn hoạt động</option>
                        <option value="deleted" <?php echo $filter_status == 'deleted' ? 'selected' : ''; ?>>Đã xóa</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $filter_date_from; ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $filter_date_to; ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> Lọc
                        </button>
                        <a href="?request=shop-log" class="btn btn-secondary">
                            <i class="fa fa-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($total_records); ?></h4>
                            <small>Tổng số log</small>
                        </div>
                        <div class="ms-auto">
                            <i class="fa fa-list fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <?php 
                                $active_count = $CVH->get_row("SELECT COUNT(*) as count FROM shop_log sl LEFT JOIN cvh_sell_item s ON sl.item_id = s.id WHERE s.id IS NOT NULL")['count'];
                                echo number_format($active_count);
                                ?>
                            </h4>
                            <small>Item còn hoạt động</small>
                        </div>
                        <div class="ms-auto">
                            <i class="fa fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <?php 
                                $deleted_count = $CVH->get_row("SELECT COUNT(*) as count FROM shop_log sl LEFT JOIN cvh_sell_item s ON sl.item_id = s.id WHERE s.id IS NULL")['count'];
                                echo number_format($deleted_count);
                                ?>
                            </h4>
                            <small>Item đã xóa</small>
                        </div>
                        <div class="ms-auto">
                            <i class="fa fa-trash fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <?php 
                                $admin_count = $CVH->get_row("SELECT COUNT(DISTINCT admin_id) as count FROM shop_log")['count'];
                                echo number_format($admin_count);
                                ?>
                            </h4>
                            <small>Admin đã thêm item</small>
                        </div>
                        <div class="ms-auto">
                            <i class="fa fa-users fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fa fa-history"></i> Lịch sử thêm item
            </h5>
        </div>
        <div class="card-body">
            <?php if ($total_records > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Admin</th>
                            <th>Item</th>
                            <th>Hành tinh</th>
                            <th>Giá</th>
                            <th>Options</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $logs = $CVH->get_list($sql);
                        if ($logs):
                        foreach ($logs as $log): 
                        ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary">#<?php echo $log['item_id']; ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <i class="fa fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($log['admin_username']); ?></div>
                                        <small class="text-muted">ID: <?php echo $log['admin_id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($log['icon_id']): ?>
                                    <img src="<?php echo getItemIcon($log['icon_id']); ?>" alt="" class="me-2" style="width: 32px; height: 32px;">
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($log['item_name']); ?></div>
                                        <small class="text-muted">Icon ID: <?php echo $log['icon_id'] ?: 'N/A'; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $planet_names = [
                                    0 => 'Trái Đất',
                                    1 => 'Namek',
                                    2 => 'Xayda'
                                ];
                                $planet_name = $planet_names[$log['planet']] ?? 'Hành tinh ' . $log['planet'];
                                ?>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($planet_name); ?></span>
                            </td>
                            <td>
                                <span class="fw-bold text-success"><?php echo number_format($log['price']); ?>₫</span>
                            </td>
                            <td>
                                <?php 
                                $options = json_decode($log['options'], true);
                                if (is_array($options) && count($options) > 0):
                                ?>
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#optionsModal<?php echo $log['id']; ?>">
                                    <i class="fa fa-eye"></i> Xem (<?php echo count($options); ?>)
                                </button>
                                
                                <!-- Options Modal -->
                                <div class="modal fade" id="optionsModal<?php echo $log['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Options của <?php echo htmlspecialchars($log['item_name']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Param</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($options as $option): ?>
                                                            <tr>
                                                                <td><span class="badge bg-secondary"><?php echo $option['id']; ?></span></td>
                                                                <td><?php echo htmlspecialchars($option['param']); ?></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">Không có</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <div><?php echo date('d/m/Y', strtotime($log['created_at'])); ?></div>
                                    <small class="text-muted"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></small>
                                </div>
                            </td>
                            <td>
                                <?php if ($log['current_status'] == 'active'): ?>
                                <span class="badge bg-success">
                                    <i class="fa fa-check"></i> Hoạt động
                                </span>
                                <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="fa fa-trash"></i> Đã xóa
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $log['id']; ?>">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                                
                                <!-- Detail Modal -->
                                <div class="modal fade" id="detailModal<?php echo $log['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Chi tiết log #<?php echo $log['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Thông tin Admin</h6>
                                                        <p><strong>ID:</strong> <?php echo $log['admin_id']; ?></p>
                                                        <p><strong>Username:</strong> <?php echo htmlspecialchars($log['admin_username']); ?></p>
                                                        <p><strong>Thời gian:</strong> <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Thông tin Item</h6>
                                                        <p><strong>Tên:</strong> <?php echo htmlspecialchars($log['item_name']); ?></p>
                                                        <p><strong>Hành tinh:</strong> <?php echo htmlspecialchars($planet_name); ?></p>
                                                        <p><strong>Giá:</strong> <?php echo number_format($log['price']); ?>₫</p>
                                                        <p><strong>Icon ID:</strong> <?php echo $log['icon_id'] ?: 'N/A'; ?></p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <h6>Options (JSON)</h6>
                                                <pre class="bg-light p-3 rounded"><code><?php echo htmlspecialchars(json_encode(json_decode($log['options'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></code></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?request=shop-log&page=<?php echo $i; ?>&admin=<?php echo $filter_admin; ?>&planet=<?php echo urlencode($filter_planet); ?>&status=<?php echo urlencode($filter_status); ?>&date_from=<?php echo urlencode($filter_date_from); ?>&date_to=<?php echo urlencode($filter_date_to); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Không có dữ liệu</h5>
                <p class="text-muted">Chưa có log nào được ghi nhận.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
