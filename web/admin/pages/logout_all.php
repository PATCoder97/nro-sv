<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php"; ?>
<div id="content" class="app-content">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title mb-0">
            <i class="fa fa-sign-out-alt me-2"></i>Quản lý phiên đăng nhập
          </h4>
        </div>
        <div class="card-body">
          <p class="text-muted mb-4">
            <i class="fa fa-info-circle me-2"></i>
            Thao tác này sẽ đăng xuất phiên theo lựa chọn dưới đây. Hãy cẩn thận khi sử dụng tính năng này.
          </p>
          
          <div class="row g-4">
            <!-- Đăng xuất TẤT CẢ tài khoản -->
            <div class="col-md-6">
              <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                  <h5 class="mb-0">
                    <i class="fa fa-exclamation-triangle me-2"></i>Đăng xuất TẤT CẢ tài khoản
                  </h5>
                </div>
                <div class="card-body">
                  <p class="text-muted small mb-3">
                    <i class="fa fa-warning me-1"></i>
                    Thao tác này sẽ đăng xuất tất cả phiên của mọi tài khoản trên hệ thống.
                  </p>
                  <button class="btn btn-danger w-100" id="logoutAllBtn">
                    <i class="fa fa-sign-out-alt me-2"></i>Đăng xuất tất cả
                  </button>
                </div>
              </div>
            </div>

            <!-- Đăng xuất theo tài khoản -->
            <div class="col-md-6">
              <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                  <h5 class="mb-0">
                    <i class="fa fa-user-times me-2"></i>Đăng xuất theo tài khoản
                  </h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label">
                      <i class="fa fa-user me-1"></i>Username
                    </label>
                    <input type="text" id="targetUsername" class="form-control" placeholder="Nhập username">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">
                      <i class="fa fa-id-card me-1"></i>User ID
                    </label>
                    <input type="number" id="targetUserId" class="form-control" placeholder="Hoặc nhập user ID">
                  </div>
                  <button class="btn btn-warning w-100" id="logoutByUserBtn">
                    <i class="fa fa-user-slash me-2"></i>Đăng xuất user
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Danh sách phiên đăng nhập -->
          <div class="mt-4">
            <div class="card border-primary">
              <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                  <i class="fa fa-users me-2"></i>Danh sách phiên đăng nhập
                </h6>
                <button class="btn btn-light btn-sm" onclick="loadSessions()">
                  <i class="fa fa-refresh me-1"></i>Làm mới
                </button>
              </div>
              <div class="card-body">
                <!-- Thống kê phiên -->
                <div class="row mb-3" id="sessionStats">
                  <div class="col-md-2">
                    <div class="text-center">
                      <h5 class="text-primary mb-1" id="totalSessions">-</h5>
                      <small class="text-muted">Tổng phiên</small>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="text-center">
                      <h5 class="text-success mb-1" id="activeSessions">-</h5>
                      <small class="text-muted">Phiên hoạt động</small>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="text-center">
                      <h5 class="text-warning mb-1" id="expiredSessions">-</h5>
                      <small class="text-muted">Phiên hết hạn</small>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="text-center">
                      <h5 class="text-info mb-1" id="onlineUsers">-</h5>
                      <small class="text-muted">Đang online</small>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="text-center">
                      <h5 class="text-danger mb-1" id="adminSessions">-</h5>
                      <small class="text-muted">Phiên admin</small>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="text-center">
                      <h5 class="text-secondary mb-1" id="uniqueUsers">-</h5>
                      <small class="text-muted">Tổng users</small>
                    </div>
                  </div>
                </div>
                
                <!-- Bộ lọc và tìm kiếm -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <select id="limitSelect" class="form-select form-select-sm" onchange="changeLimit()">
                      <option value="10">10 phiên/trang</option>
                      <option value="20" selected>20 phiên/trang</option>
                      <option value="50">50 phiên/trang</option>
                      <option value="100">100 phiên/trang</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Tìm kiếm username..." onkeyup="searchSessions()">
                  </div>
                  <div class="col-md-3">
                    <select id="statusFilter" class="form-select form-select-sm" onchange="filterByStatus()">
                      <option value="">Tất cả trạng thái</option>
                      <option value="active">Hoạt động</option>
                      <option value="expired">Hết hạn</option>
                      <option value="banned">Bị cấm</option>
                    </select>
                  </div>
                </div>

                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                  <table class="table table-hover table-sm">
                    <thead class="table-light sticky-top">
                      <tr>
                        <th style="width: 80px;">Session ID</th>
                        <th style="width: 80px;">User ID</th>
                        <th style="width: 150px;">Username</th>
                        <th style="width: 100px;">Trạng thái</th>
                        <th style="width: 120px;">Thời gian còn lại</th>
                        <th style="width: 120px;">Thao tác</th>
                      </tr>
                    </thead>
                    <tbody id="sessionsTable">
                      <tr>
                        <td colspan="6" class="text-center">
                          <i class="fa fa-spinner fa-spin me-2"></i>Đang tải...
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Phân trang -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                  <div class="text-muted small">
                    Hiển thị <span id="showingInfo">0-0</span> của <span id="totalRecords">0</span> phiên
                  </div>
                  <nav aria-label="Phân trang">
                    <ul class="pagination pagination-sm mb-0" id="pagination">
                      <!-- Phân trang sẽ được tạo bằng JavaScript -->
                    </ul>
                  </nav>
                </div>
              </div>
            </div>
          </div>

          <!-- Thống kê phiên hiện tại -->
          <div class="mt-4">
            <div class="card border-info">
              <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                  <i class="fa fa-chart-bar me-2"></i>Thống kê phiên hiện tại
                </h6>
              </div>
              <div class="card-body">
                <div class="row text-center">
                  <div class="col-md-4">
                    <div class="border-end">
                      <h4 class="text-primary mb-1" id="totalSessions">-</h4>
                      <small class="text-muted">Tổng phiên</small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="border-end">
                      <h4 class="text-success mb-1" id="onlineUsers">-</h4>
                      <small class="text-muted">Người dùng đang online</small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <h4 class="text-warning mb-1" id="adminSessions">-</h4>
                    <small class="text-muted">Phiên admin</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
  
  function post(url, data) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(data)
    }).then(r => r.json());
  }

  // Biến toàn cục cho phân trang
  let currentPage = 1;
  let currentLimit = 20;
  let allSessions = [];
  let filteredSessions = [];

  // Load thống kê và danh sách phiên đăng nhập khi trang tải
  document.addEventListener('DOMContentLoaded', function() {
    loadSessionStats();
    loadSessions();
  });

  function loadSessionStats() {
    // Khởi tạo thống kê với dấu "-" chờ dữ liệu từ API
    document.getElementById('totalSessions').textContent = '-';
    document.getElementById('activeSessions').textContent = '-';
    document.getElementById('expiredSessions').textContent = '-';
    document.getElementById('onlineUsers').textContent = '-';
    document.getElementById('adminSessions').textContent = '-';
    document.getElementById('uniqueUsers').textContent = '-';
  }

  function loadSessions(page = 1) {
    const tbody = document.getElementById('sessionsTable');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin me-2"></i>Đang tải...</td></tr>';
    
    currentPage = page;
    
    // Gọi API để lấy danh sách phiên đăng nhập với phân trang
    fetch(`/ajax/admin/get_accounts.php?csrf_token=${csrfToken}&page=${page}&limit=${currentLimit}`)
      .then(response => {
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        console.log('API Response:', data); // Debug log
        if (data.status && data.sessions) {
          allSessions = data.sessions;
          filteredSessions = data.sessions;
          displaySessions(data.sessions);
          updateSessionStats(data.stats);
          updatePagination(data.pagination);
          updateShowingInfo(data.pagination);
        } else {
          tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">' + (data.message || 'Không thể tải danh sách phiên đăng nhập') + '</td></tr>';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi kết nối: ' + error.message + '</td></tr>';
      });
  }

  function updateSessionStats(stats) {
    document.getElementById('totalSessions').textContent = stats.total_sessions;
    document.getElementById('activeSessions').textContent = stats.active_sessions;
    document.getElementById('expiredSessions').textContent = stats.expired_sessions;
    document.getElementById('onlineUsers').textContent = stats.online_users;
    document.getElementById('adminSessions').textContent = stats.admin_sessions;
    document.getElementById('uniqueUsers').textContent = stats.unique_users;
  }

  function displaySessions(sessions) {
    const tbody = document.getElementById('sessionsTable');
    if (sessions.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Không có phiên đăng nhập nào</td></tr>';
      return;
    }

    tbody.innerHTML = sessions.map(session => `
      <tr>
        <td><code class="small">${session.session_id}</code></td>
        <td><span class="small">${session.user_id}</span></td>
        <td>
          <div class="d-flex align-items-center">
            <strong class="small">${session.username}</strong>
            ${session.is_admin ? '<span class="badge bg-danger ms-1 small">Admin</span>' : ''}
          </div>
        </td>
        <td>
          <span class="badge bg-${session.status_color} small">${session.status_text}</span>
        </td>
        <td>
          <small class="text-muted">${session.time_left}</small>
        </td>
        <td>
          <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-warning btn-sm" onclick="logoutSession(${session.session_id}, '${session.username}')" title="Đăng xuất phiên này">
              <i class="fa fa-sign-out-alt"></i>
            </button>
            <button class="btn btn-outline-danger btn-sm" onclick="logoutUser(${session.user_id}, '${session.username}')" title="Đăng xuất tất cả phiên của user">
              <i class="fa fa-user-times"></i>
            </button>
            <button class="btn btn-outline-info btn-sm" onclick="viewSessionDetails(${session.session_id})" title="Xem chi tiết">
              <i class="fa fa-eye"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  function updatePagination(pagination) {
    const paginationContainer = document.getElementById('pagination');
    const { current_page, total_pages } = pagination;
    
    if (total_pages <= 1) {
      paginationContainer.innerHTML = '';
      return;
    }
    
    let paginationHTML = '';
    
    // Nút Previous
    if (current_page > 1) {
      paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadSessions(${current_page - 1})">‹</a></li>`;
    }
    
    // Các trang
    const startPage = Math.max(1, current_page - 2);
    const endPage = Math.min(total_pages, current_page + 2);
    
    if (startPage > 1) {
      paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadSessions(1)">1</a></li>`;
      if (startPage > 2) {
        paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      }
    }
    
    for (let i = startPage; i <= endPage; i++) {
      const activeClass = i === current_page ? 'active' : '';
      paginationHTML += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="loadSessions(${i})">${i}</a></li>`;
    }
    
    if (endPage < total_pages) {
      if (endPage < total_pages - 1) {
        paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      }
      paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadSessions(${total_pages})">${total_pages}</a></li>`;
    }
    
    // Nút Next
    if (current_page < total_pages) {
      paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadSessions(${current_page + 1})">›</a></li>`;
    }
    
    paginationContainer.innerHTML = paginationHTML;
  }

  function updateShowingInfo(pagination) {
    const { current_page, limit, total_records } = pagination;
    const start = (current_page - 1) * limit + 1;
    const end = Math.min(current_page * limit, total_records);
    
    document.getElementById('showingInfo').textContent = `${start}-${end}`;
    document.getElementById('totalRecords').textContent = total_records;
  }

  function changeLimit() {
    currentLimit = parseInt(document.getElementById('limitSelect').value);
    loadSessions(1);
  }

  function searchSessions() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    const filtered = allSessions.filter(session => {
      const matchesSearch = session.username.toLowerCase().includes(searchTerm) || 
                           session.user_id.toString().includes(searchTerm) ||
                           session.session_id.toString().includes(searchTerm);
      
      const matchesStatus = !statusFilter || session.status === statusFilter;
      
      return matchesSearch && matchesStatus;
    });
    
    filteredSessions = filtered;
    displaySessions(filtered);
  }

  function filterByStatus() {
    searchSessions(); // Tái sử dụng logic tìm kiếm
  }

  function logoutSession(sessionId, username) {
    Swal.fire({
      title: 'Xác nhận đăng xuất phiên',
      text: `Bạn có chắc chắn muốn đăng xuất phiên của ${username}?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#f39c12',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Đăng xuất phiên',
      cancelButtonText: 'Hủy bỏ'
    }).then((result) => {
      if (result.isConfirmed) {
        performLogoutSession(sessionId);
      }
    });
  }

  function logoutUser(userId, username) {
    Swal.fire({
      title: 'Xác nhận đăng xuất',
      text: `Bạn có chắc chắn muốn đăng xuất TẤT CẢ phiên của ${username}?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e74c3c',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Đăng xuất tất cả',
      cancelButtonText: 'Hủy bỏ'
    }).then((result) => {
      if (result.isConfirmed) {
        performLogout(null, userId);
      }
    });
  }

  function viewSessionDetails(sessionId) {
    Swal.fire({
      title: 'Chi tiết phiên đăng nhập',
      text: `Session ID: ${sessionId}\nĐang tải thông tin chi tiết...`,
      icon: 'info'
    });
  }

  function performLogoutSession(sessionId) {
    try {
      post('/ajax/admin/logout_all.php', { 
        session_id: sessionId, 
        csrf_token: csrfToken 
      }).then(res => {
        Swal.fire({
          title: res.status ? 'Thành công' : 'Lỗi',
          text: res.message || 'Xong',
          icon: res.status ? 'success' : 'error'
        });
        if (res.status) {
          loadSessionStats();
          loadSessions(); // Reload danh sách
        }
      });
    } catch (error) {
      Swal.fire('Lỗi', 'Không thể kết nối đến server', 'error');
    }
  }

  function performLogout(username, userId) {
    try {
      post('/ajax/admin/logout_all.php', { 
        username: username || '', 
        user_id: userId || '', 
        csrf_token: csrfToken 
      }).then(res => {
        Swal.fire({
          title: res.status ? 'Thành công' : 'Lỗi',
          text: res.message || 'Xong',
          icon: res.status ? 'success' : 'error'
        });
        if (res.status) {
          loadSessionStats();
          loadSessions(); // Reload danh sách
        }
      });
    } catch (error) {
      Swal.fire('Lỗi', 'Không thể kết nối đến server', 'error');
    }
  }

  document.getElementById('logoutAllBtn').addEventListener('click', async () => {
    const result = await Swal.fire({
      title: 'Xác nhận đăng xuất',
      text: 'Bạn có chắc chắn muốn đăng xuất TẤT CẢ tài khoản?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Đăng xuất tất cả',
      cancelButtonText: 'Hủy bỏ'
    });

    if (result.isConfirmed) {
      try {
        const res = await post('/ajax/admin/logout_all.php', { all: '1', csrf_token: csrfToken });
        Swal.fire({
          title: res.status ? 'Thành công' : 'Lỗi',
          text: res.message || 'Xong',
          icon: res.status ? 'success' : 'error'
        });
        if (res.status) {
          loadSessionStats();
          loadAccounts();
        }
      } catch (error) {
        Swal.fire('Lỗi', 'Không thể kết nối đến server', 'error');
      }
    }
  });

  document.getElementById('logoutByUserBtn').addEventListener('click', async () => {
    const username = document.getElementById('targetUsername').value.trim();
    const userId = document.getElementById('targetUserId').value.trim();
    
    if (!username && !userId) {
      Swal.fire('Lỗi', 'Vui lòng nhập username hoặc user ID', 'error');
      return;
    }

    const result = await Swal.fire({
      title: 'Xác nhận đăng xuất',
      text: `Bạn có chắc chắn muốn đăng xuất ${username || `user ID ${userId}`}?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#f39c12',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Đăng xuất',
      cancelButtonText: 'Hủy bỏ'
    });

    if (result.isConfirmed) {
      performLogout(username, userId);
      // Clear form
      document.getElementById('targetUsername').value = '';
      document.getElementById('targetUserId').value = '';
    }
  });
</script>
