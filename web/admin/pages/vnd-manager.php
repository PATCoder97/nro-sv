<?php
// Kiểm tra quyền truy cập
if (!$user['is_admin']) {
    echo '<div class="alert alert-danger">Bạn không có quyền truy cập trang này!</div>';
    exit;
}

// Kiểm tra quyền VND Manager
if (!$user['is_super_admin'] && empty($user['perm_vnd_manager'])) {
    echo '<div class="alert alert-danger">Bạn không có quyền quản lý VND!</div>';
    exit;
}

$kmess = 20;
$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
$start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = "WHERE 1=1";
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($CVH->connect_db(), $search);
    $where_clause .= " AND (username LIKE '%$search_escaped%' OR email LIKE '%$search_escaped%' OR id = '$search_escaped')";
}

// Lấy danh sách tài khoản
$result = mysqli_query($CVH->connect_db(), "
    SELECT id, username, email, vnd, last_time_login, create_time
    FROM account 
    $where_clause
    ORDER BY vnd DESC, id DESC
    LIMIT $start, $kmess
");

$total_query = mysqli_query($CVH->connect_db(), "
    SELECT COUNT(*) as total 
    FROM account 
    $where_clause
");
$total = mysqli_fetch_assoc($total_query)['total'];

// Thống kê VND
$stats_query = mysqli_query($CVH->connect_db(), "
    SELECT 
        COUNT(*) as total_accounts,
        SUM(vnd) as total_vnd,
        AVG(vnd) as avg_vnd,
        MAX(vnd) as max_vnd,
        MIN(vnd) as min_vnd
    FROM account
");
$stats = mysqli_fetch_assoc($stats_query);

// Chuẩn hóa & chống hiển thị âm
$stats['total_accounts'] = isset($stats['total_accounts']) ? (int)$stats['total_accounts'] : 0;
$stats['total_vnd'] = isset($stats['total_vnd']) ? (int)$stats['total_vnd'] : 0;
$stats['avg_vnd'] = isset($stats['avg_vnd']) ? (int)round($stats['avg_vnd']) : 0;
$stats['max_vnd'] = isset($stats['max_vnd']) ? (int)$stats['max_vnd'] : 0;
$stats['min_vnd'] = isset($stats['min_vnd']) ? max(0, (int)$stats['min_vnd']) : 0;

// Tạo CSRF token
$csrf_token = $CVH->getCSRFToken($user['id']);
?>

<div id="content" class="app-content">
    <div class="row">
        <div class="col-xl-12 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-coins me-2"></i>
                        Quản lý VND - Buff/Trừ tiền tài khoản
                    </h4>
                </div>
                <div class="card-body">
                    <style>
                    .kpi-card { position: relative; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,.06); overflow: hidden; }
                    .kpi-card .card-body { padding: 16px 18px; }
                    .kpi-value { font-size: clamp(20px, 3.2vw, 36px); font-weight: 700; letter-spacing: .2px; line-height: 1.1; white-space: nowrap; overflow: visible; text-overflow: clip; }
                    .kpi-sub { color:#f8f9fa; opacity:.85; font-size: 13px; }
                    .kpi-icon { position:absolute; right:12px; top:50%; transform:translateY(-50%); width:56px; height:56px; background:rgba(255,255,255,.18); border-radius:50%; display:flex; align-items:center; justify-content:center; pointer-events:none; }
                    .kpi-icon i{ opacity:.9; }
                    .kpi-text{ flex:1; min-width:0; padding-right:86px; }
                    @media (max-width: 576px){ .kpi-value { font-size: 22px; } }
                    </style>
                    <!-- Thống kê tổng quan -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-2">
                            <div class="card kpi-card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="kpi-text">
                                            <h6 class="card-title mb-1">Tổng tài khoản</h6>
                                            <div class="kpi-value mb-0"><?php echo number_format($stats['total_accounts']); ?></div>
                                        </div>
                                        <div class="align-self-center kpi-icon"><i class="fas fa-users"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card kpi-card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="kpi-text">
                                            <h6 class="card-title mb-1">Tổng VND</h6>
                                            <div class="kpi-value mb-0" data-raw-vnd="<?php echo (int)$stats['total_vnd']; ?>" title="<?php echo number_format($stats['total_vnd']); ?> đ" data-full-value="<?php echo number_format($stats['total_vnd']); ?> đ"><?php echo number_format($stats['total_vnd']); ?> đ</div>
                                        </div>
                                        <div class="align-self-center kpi-icon"><i class="fas fa-coins"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card kpi-card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="kpi-text">
                                            <h6 class="card-title mb-1">VND trung bình</h6>
                                            <div class="kpi-value mb-0" data-raw-vnd="<?php echo (int)$stats['avg_vnd']; ?>" title="<?php echo number_format($stats['avg_vnd']); ?> đ" data-full-value="<?php echo number_format($stats['avg_vnd']); ?> đ"><?php echo number_format($stats['avg_vnd']); ?> đ</div>
                                        </div>
                                        <div class="align-self-center kpi-icon"><i class="fas fa-chart-line"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card kpi-card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="kpi-text">
                                            <h6 class="card-title mb-1">VND cao nhất</h6>
                                            <div class="kpi-value mb-0" data-raw-vnd="<?php echo (int)$stats['max_vnd']; ?>" title="<?php echo number_format($stats['max_vnd']); ?> đ" data-full-value="<?php echo number_format($stats['max_vnd']); ?> đ"><?php echo number_format($stats['max_vnd']); ?> đ</div>
                                        </div>
                                        <div class="align-self-center kpi-icon"><i class="fas fa-arrow-up"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card kpi-card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="kpi-text">
                                            <h6 class="card-title mb-1">VND thấp nhất</h6>
                                            <div class="kpi-value mb-0" data-raw-vnd="<?php echo (int)$stats['min_vnd']; ?>" title="<?php echo number_format($stats['min_vnd']); ?> đ" data-full-value="<?php echo number_format($stats['min_vnd']); ?> đ"><?php echo number_format($stats['min_vnd']); ?> đ</div>
                                        </div>
                                        <div class="align-self-center kpi-icon"><i class="fas fa-arrow-down"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card kpi-card bg-secondary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title mb-1">Tài khoản hiển thị</h6>
                                            <div class="kpi-value mb-0"><?php echo number_format($total); ?></div>
                                        </div>
                                        <div class="align-self-center kpi-icon"><i class="fas fa-eye"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tìm kiếm -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput"
                                       placeholder="Tìm theo ID, username hoặc email...">
                                <button class="btn btn-primary" type="button" id="searchBtn">
                                    <i class="fas fa-search"></i> Tìm
                                </button>
                                <button class="btn btn-secondary" type="button" id="clearSearch">
                                    <i class="fas fa-times"></i> Xóa
                                </button>
                            </div>
                            <div id="searchHint" class="form-text">Gõ để tìm kiếm nhanh. Enter để tìm ngay.</div>
                        </div>
                    </div>

                    <!-- Bảng danh sách tài khoản -->
                    <div class="table-responsive" id="accountsTable">
                        <table class="table table-hover" id="accountsTBody">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>VND hiện tại</th>
                                    <th>Đăng nhập cuối</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="listBody">
                                <?php
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $id = $row['id'];
                                        $username = $row['username'];
                                        $email = $row['email'];
                                        // Chuẩn hóa email: nếu cột lưu JSON {"email":"","verify":"false"...} thì chỉ lấy trường email
                                        $emailDisplay = '';
                                        if (!empty($email)) {
                                            $decoded = json_decode($email, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                $em = isset($decoded['email']) ? trim((string)$decoded['email']) : '';
                                                if ($em !== '') { $emailDisplay = $em; }
                                            } else {
                                                $emailDisplay = trim($email);
                                            }
                                        }
                                        if ($emailDisplay === '') {
                                            $emailDisplay = 'Chưa xác minh email';
                                        }
                                        $vnd = $row['vnd'];
                                        $last_login = $row['last_time_login'];
                                        $create_time = $row['create_time'];

                                        // Format thời gian
                                        $last_login_time = null;
                                        if ($last_login) {
                                            if (is_numeric($last_login)) {
                                                $last_login_time = $last_login;
                                            } else {
                                                $timestamp = strtotime($last_login);
                                                if ($timestamp !== false) {
                                                    $last_login_time = $timestamp;
                                                }
                                            }
                                        }
                                        $time_ago = $last_login_time ? $CVH->time_ago($last_login_time) : 'Chưa đăng nhập';
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $id; ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($username); ?></strong>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($emailDisplay); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success fs-6"><?php echo number_format($vnd); ?> VND</span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo $time_ago; ?></small>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo date('d/m/Y', strtotime($create_time)); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="showVNDModal(<?php echo $id; ?>, '<?php echo htmlspecialchars($username); ?>', <?php echo $vnd; ?>, 'buff')">
                                                        <i class="fas fa-plus"></i> Buff
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="showVNDModal(<?php echo $id; ?>, '<?php echo htmlspecialchars($username); ?>', <?php echo $vnd; ?>, 'deduct')">
                                                        <i class="fas fa-minus"></i> Trừ
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="showVNDModal(<?php echo $id; ?>, '<?php echo htmlspecialchars($username); ?>', <?php echo $vnd; ?>, 'set')">
                                                        <i class="fas fa-edit"></i> Sửa
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="7" class="text-center">Không có dữ liệu tài khoản nào</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <?php if ($total > $kmess): ?>
                    <div class="d-flex justify-content-center mt-3">
                        <?php 
                        $pagination_url = 'vnd-manager?';
                        if (!empty($search)) {
                            $pagination_url .= 'search=' . urlencode($search) . '&';
                        }
                        echo $CVH->phantrang($pagination_url, $start, $total, $kmess); 
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thao tác VND -->
<div class="modal fade" id="vndModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vndModalTitle">Thao tác VND</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="vndForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" id="accountId" name="account_id">
                    <input type="hidden" id="actionType" name="action_type">
                    
                    <div class="mb-3">
                        <label class="form-label">Tài khoản:</label>
                        <input type="text" class="form-control" id="accountUsername" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">VND hiện tại:</label>
                        <input type="text" class="form-control" id="currentVND" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" id="amountLabel">Số tiền:</label>
                        <input type="number" class="form-control" id="amount" name="amount" 
                               min="1" step="1" required placeholder="Nhập số tiền...">
                        <div class="form-text" id="amountHelp"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Lý do:</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" 
                                  placeholder="Nhập lý do thao tác..." required></textarea>
                    </div>
                    
                    <div class="mb-3" id="previewSection" style="display: none;">
                        <div class="alert alert-info">
                            <strong>Xem trước:</strong>
                            <div id="vndPreview"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="submitVNDAction()">
                    <i class="fas fa-save"></i> Thực hiện
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Debounce helper
function debounce(fn, delay) {
    let t; return function() { clearTimeout(t); const args = arguments; t = setTimeout(()=>fn.apply(this,args), delay); };
}

// Render rows into table body
function renderRows(items){
    const tbody = document.getElementById('listBody');
    if(!tbody) return;
    if(!items || items.length===0){
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">Không có kết quả</td></tr>';
        return;
    }
    let html = '';
    items.forEach(function(row){
        const last = row.last_time_login ? row.last_time_login : 'Chưa đăng nhập';
        html += `
        <tr>
            <td><span class="badge bg-secondary">${row.id}</span></td>
            <td><strong>${row.username}</strong></td>
            <td><small>${row.email||''}</small></td>
            <td><span class="badge bg-success fs-6">${Number(row.vnd).toLocaleString()} VND</span></td>
            <td><small class="text-muted">${last}</small></td>
            <td><small class="text-muted">${row.create_time ? new Date(row.create_time).toLocaleDateString('vi-VN') : ''}</small></td>
            <td>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-success" onclick="showVNDModal(${row.id}, '${row.username.replace(/'/g,"&#39;")}', ${row.vnd}, 'buff')"><i class="fas fa-plus"></i> Buff</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="showVNDModal(${row.id}, '${row.username.replace(/'/g,"&#39;")}', ${row.vnd}, 'deduct')"><i class="fas fa-minus"></i> Trừ</button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="showVNDModal(${row.id}, '${row.username.replace(/'/g,"&#39;")}', ${row.vnd}, 'set')"><i class="fas fa-edit"></i> Sửa</button>
                </div>
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

// Perform ajax search
function doSearch(keyword){
    if(!keyword){ renderRows([]); return; }
    fetch('/ajax/admin/vnd/search.php?q=' + encodeURIComponent(keyword))
        .then(r=>r.json())
        .then(json=>{
            if(json && json.status){ renderRows(json.items); }
            else { renderRows([]); }
        })
        .catch(()=> renderRows([]));
}

// Bind inputs
const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const clearBtn = document.getElementById('clearSearch');

if(searchInput){
    searchInput.addEventListener('input', debounce(function(){ doSearch(this.value.trim()); }, 300));
    searchInput.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); doSearch(this.value.trim()); }});
}
if(searchBtn){ searchBtn.addEventListener('click', function(){ doSearch(searchInput.value.trim()); }); }
if(clearBtn){ clearBtn.addEventListener('click', function(){ searchInput.value=''; renderRows([]); }); }
// Hiển thị modal thao tác VND
function showVNDModal(accountId, username, currentVND, actionType) {
    $('#accountId').val(accountId);
    $('#accountUsername').val(username);
    $('#currentVND').val(currentVND.toLocaleString() + ' VND');
    $('#actionType').val(actionType);
    $('#amount').val('');
    $('#reason').val('');
    $('#previewSection').hide();
    
    // Cập nhật tiêu đề và label
    let title = '';
    let amountLabel = '';
    let amountHelp = '';
    
    switch(actionType) {
        case 'buff':
            title = 'Buff VND cho ' + username;
            amountLabel = 'Số tiền buff:';
            amountHelp = 'Số tiền sẽ được cộng vào VND hiện tại';
            break;
        case 'deduct':
            title = 'Trừ VND của ' + username;
            amountLabel = 'Số tiền trừ:';
            amountHelp = 'Số tiền sẽ được trừ từ VND hiện tại';
            break;
        case 'set':
            title = 'Đặt VND cho ' + username;
            amountLabel = 'Số tiền mới:';
            amountHelp = 'VND sẽ được đặt thành số tiền này';
            break;
    }
    
    $('#vndModalTitle').text(title);
    $('#amountLabel').text(amountLabel);
    $('#amountHelp').text(amountHelp);
    
    $('#vndModal').modal('show');
}

// Xem trước thao tác VND
$('#amount').on('input', function() {
    const amount = parseInt($(this).val()) || 0;
    const currentVND = parseInt($('#currentVND').val().replace(/[^\d]/g, '')) || 0;
    const actionType = $('#actionType').val();
    
    if (amount > 0) {
        let newVND = 0;
        let operation = '';
        
        switch(actionType) {
            case 'buff':
                newVND = currentVND + amount;
                operation = currentVND.toLocaleString() + ' + ' + amount.toLocaleString() + ' = ' + newVND.toLocaleString();
                break;
            case 'deduct':
                newVND = Math.max(0, currentVND - amount);
                operation = currentVND.toLocaleString() + ' - ' + amount.toLocaleString() + ' = ' + newVND.toLocaleString();
                break;
            case 'set':
                newVND = amount;
                operation = 'Đặt thành: ' + newVND.toLocaleString();
                break;
        }
        
        $('#vndPreview').html(`
            <strong>Thao tác:</strong> ${operation}<br>
            <strong>VND sau thao tác:</strong> ${newVND.toLocaleString()} VND
        `);
        $('#previewSection').show();
    } else {
        $('#previewSection').hide();
    }
});

// Thực hiện thao tác VND
function submitVNDAction() {
    const formData = new FormData(document.getElementById('vndForm'));
    
    if (!formData.get('amount') || !formData.get('reason')) {
        Swal.fire('Lỗi', 'Vui lòng điền đầy đủ thông tin!', 'error');
        return;
    }
    
    // Tạm thời disable anti-debug
    window.adminActionInProgress = true;
    
    $.ajax({
        url: '/ajax/admin/vnd/update.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response && response.status) {
                Swal.fire('Thành công', response.message, 'success').then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Lỗi', (response && response.message) ? response.message : 'Có lỗi xảy ra', 'error');
            }
            window.adminActionInProgress = false;
        },
        error: function(xhr, status, error) {
            var msg = 'Có lỗi xảy ra, vui lòng thử lại!';
            if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
            else if (xhr.responseText) {
                try { var j = JSON.parse(xhr.responseText); if (j.message) msg = j.message; } catch(e) {}
            }
            if (xhr.status === 403) msg = 'Bạn không có quyền thực hiện thao tác này!';
            if (xhr.status === 404) msg = 'Không tìm thấy API endpoint!';
            if (xhr.status === 500) msg = 'Lỗi server, vui lòng thử lại sau!';
            Swal.fire('Lỗi', msg, 'error');
            window.adminActionInProgress = false;
        }
    });
}
</script>
<script>
// Tự động co chữ để luôn thấy đủ số trong khung (không xuống dòng)
function fitKPIValues(){
  document.querySelectorAll('.kpi-text .kpi-value').forEach(function(el){
    const container = el.parentElement; // kpi-text
    if(!container) return;
    // reset cỡ chữ theo CSS trước khi tính
    el.style.fontSize = '';
    // lấy cỡ hiện tại
    let fontSize = parseFloat(getComputedStyle(el).fontSize);
    const min = 14;
    // giảm dần cho tới khi vừa
    while(el.scrollWidth > container.clientWidth && fontSize > min){
      fontSize -= 1;
      el.style.fontSize = fontSize + 'px';
    }
    // set title để xem đầy đủ khi hover (native)
    const full = el.getAttribute('data-full-value');
    if(full) el.title = full;
  });
}

window.addEventListener('load', fitKPIValues);
window.addEventListener('resize', function(){
  // debounce nhẹ
  clearTimeout(window.__fitKPITimer);
  window.__fitKPITimer = setTimeout(fitKPIValues, 100);
});

// Rút gọn rất ngắn gọn: 1,700,000 => "1 triệu 7"; 515,630,730 => "515 triệu 6"; 871,079,091 => "871 triệu"
function formatVNDCompactVi(n){
  n = Number(n||0);
  if(n < 1_000) return n.toString();
  if(n >= 1_000_000_000){
    const ty = Math.floor(n/1_000_000_000);
    const tramTrieuDigit = Math.floor((n%1_000_000_000)/100_000_000); // 0..9
    return ty + ' tỷ' + (tramTrieuDigit>0 ? ' ' + tramTrieuDigit : '');
  }
  if(n >= 1_000_000){
    const trieu = Math.floor(n/1_000_000);
    const tramNghinDigit = Math.floor((n%1_000_000)/100_000); // 0..9
    return trieu + ' triệu' + (tramNghinDigit>0 ? ' ' + tramNghinDigit : '');
  }
  const nghin = Math.floor(n/1_000);
  const tramDigit = Math.floor((n%1_000)/100);
  return nghin + ' nghìn' + (tramDigit>0 ? ' ' + tramDigit : '');
}

// Áp dụng định dạng chữ rút gọn lên KPI và giữ nguyên số đầy đủ khi click để đổi
function applyCompactVND(){
  document.querySelectorAll('.kpi-value[data-raw-vnd]').forEach(function(el){
    const raw = Number(el.getAttribute('data-raw-vnd')) || 0;
    const compact = formatVNDCompactVi(raw);
    el.dataset.fullText = el.textContent; // lưu bản đầy đủ
    el.textContent = compact;
    el.style.whiteSpace = 'nowrap';
  });
}

window.addEventListener('load', applyCompactVND);
</script>
