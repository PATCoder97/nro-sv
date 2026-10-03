<?php
if (!$user['is_admin']) { echo '<div class="alert alert-danger">Bạn không có quyền truy cập!</div>'; exit; }
if (!$user['is_super_admin'] && empty($user['perm_buff_item_manager'])) { echo '<div class="alert alert-danger">Bạn không có quyền buff item!</div>'; exit; }
$csrf_token = $CVH->getCSRFToken($user['id']);
?>

<style>
/* Toast notification styles */
.toast-notification {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  background: white;
  border: 1px solid #d4edda;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  padding: 12px 16px;
  min-width: 300px;
  animation: slideInRight 0.3s ease-out;
}

.toast-content {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #155724;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.toast-notification.fade-out {
  animation: slideOutRight 0.3s ease-in;
}

@keyframes slideOutRight {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(100%);
    opacity: 0;
  }
}
</style>
<div id="content" class="app-content">
  <div class="row">
    <div class="col-xl-6 mb-3">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title mb-0"><i class="fas fa-magic me-2"></i>Buff Item</h4>
        </div>
        <div class="card-body">
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Lưu ý:</strong> Item sẽ được gửi khi player online. Hệ thống sẽ tự động xử lý trong 5-10 giây.
          </div>
          
          <form id="buffForm">
            <div class="mb-3">
              <label class="form-label">Tên người chơi <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="text" class="form-control" id="playerName" name="player_name" required placeholder="Nhập tên người chơi">
                <button type="button" class="btn btn-outline-secondary" id="searchPlayer">
                  <i class="fas fa-search"></i>
                </button>
              </div>
              <input type="hidden" id="playerId" name="player_id" required>
              <div id="playerSearchResults" class="mt-2" style="display:none;"></div>
              <div id="selectedPlayer" class="mt-2" style="display:none;">
                <div class="alert alert-info">
                  <strong>Player đã chọn:</strong> <span id="selectedPlayerName"></span> (ID: <span id="selectedPlayerId"></span>)
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Item <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="text" class="form-control" id="itemSearch" placeholder="Tìm kiếm item theo ID hoặc tên">
                <button type="button" class="btn btn-outline-secondary" id="searchItem">
                  <i class="fas fa-search"></i>
                </button>
              </div>
              <input type="hidden" id="itemId" name="item_id" required>
              <div id="itemSearchResults" class="mt-2" style="display:none;"></div>
              <div id="selectedItem" class="mt-2" style="display:none;">
                <div class="alert alert-info">
                  <strong>Item đã chọn:</strong> <span id="selectedItemName"></span> (ID: <span id="selectedItemId"></span>)
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Options (tùy chọn)</label>
              <div class="input-group">
                <input type="text" class="form-control" id="optionSearch" placeholder="Tìm kiếm option">
                <button type="button" class="btn btn-outline-secondary" id="searchOption">
                  <i class="fas fa-search"></i>
                </button>
              </div>
              <input type="text" class="form-control mt-2" id="options" name="options" placeholder="50-20;30-1;77-15">
              <small class="form-text text-muted">Format: optionId-param;optionId-param</small>
              <div id="optionSearchResults" class="mt-2" style="display:none;"></div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Số lượng</label>
              <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="100">
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
              <i class="fas fa-magic me-2"></i>Gửi yêu cầu buff
            </button>
          </form>
        </div>
      </div>
    </div>
    
    <div class="col-xl-6 mb-3">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title mb-0"><i class="fas fa-clock me-2"></i>Yêu cầu đang chờ</h4>
          <button class="btn btn-sm btn-outline-primary" onclick="loadPendingRequests()">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
        <div class="card-body">
          <div id="pendingRequests">
            <div class="text-center text-muted">
              <i class="fas fa-spinner fa-spin"></i> Đang tải...
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title mb-0"><i class="fas fa-history me-2"></i>Lịch sử buff</h4>
          <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
              <option value="">Tất cả</option>
              <option value="pending">Đang chờ</option>
              <option value="completed">Thành công</option>
              <option value="failed">Thất bại</option>
            </select>
            <button class="btn btn-sm btn-outline-primary" onclick="loadBuffHistory()">
              <i class="fas fa-sync-alt"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Player</th>
                  <th>Item ID</th>
                  <th>Options</th>
                  <th>Số lượng</th>
                  <th>Trạng thái</th>
                  <th>Admin</th>
                  <th>Thời gian</th>
                  <th>Kết quả</th>
                </tr>
              </thead>
              <tbody id="buffHistory">
                <tr>
                  <td colspan="9" class="text-center text-muted">
                    <i class="fas fa-spinner fa-spin"></i> Đang tải...
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  loadPendingRequests();
  loadBuffHistory();
  
  // Auto refresh mỗi 30 giây
  setInterval(loadPendingRequests, 30000);
  
  // Tìm kiếm player với debounce
  var searchPlayers = debounce(function(searchTerm) {
    const container = document.getElementById('playerSearchResults');
    container.innerHTML = '<div class="list-group-item text-center"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</div>';
    container.style.display = 'block';
    
    fetch(`/ajax/admin/buff/search_player.php?q=${encodeURIComponent(searchTerm)}`)
    .then(r => r.json())
    .then(data => {
      if(data.status && data.data.length > 0) {
        let html = '<div class="list-group">';
        data.data.forEach(player => {
          html += `
            <a href="#" class="list-group-item list-group-item-action" onclick="selectPlayer(${player.id}, '${player.name}')">
              <strong>${player.display_name}</strong> (ID: ${player.display_id}) - Level: ${player.level || 'N/A'}
            </a>
          `;
        });
        html += '</div>';
        container.innerHTML = html;
      } else {
        container.innerHTML = '<div class="alert alert-warning">Không tìm thấy player</div>';
      }
    })
    .catch(() => {
      container.innerHTML = '<div class="alert alert-danger">Lỗi tìm kiếm</div>';
    });
  }, 300);

  // Tìm kiếm player
  document.getElementById('searchPlayer').addEventListener('click', function(){
    const searchTerm = document.getElementById('playerName').value.trim();
    searchPlayers(searchTerm);
  });

  // Tìm kiếm khi gõ
  document.getElementById('playerName').addEventListener('input', function(){
    const searchTerm = this.value.trim();
    if(searchTerm.length > 0) {
      searchPlayers(searchTerm);
    } else {
      document.getElementById('playerSearchResults').style.display = 'none';
    }
  });
  
  // Debounce function để tránh gọi AJAX quá nhiều
  function debounce(func, wait) {
    var timeout;
    return function executedFunction(...args) {
      var later = function() {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // Tìm kiếm item với debounce
  var searchItems = debounce(function(searchTerm) {
    const container = document.getElementById('itemSearchResults');
    container.innerHTML = '<div class="list-group-item text-center"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</div>';
    container.style.display = 'block';
    
    fetch(`/ajax/admin/buff/search_item.php?q=${encodeURIComponent(searchTerm)}`)
    .then(r => r.json())
    .then(data => {
      if(data.status && data.data.length > 0) {
        let html = '<div class="list-group">';
        data.data.forEach(item => {
          html += `
            <a href="#" class="list-group-item list-group-item-action" onclick="selectItem(${item.id}, '${item.name}')">
              <strong>${item.display_name}</strong> (ID: ${item.display_id}) - Type: ${item.type || 'N/A'}
            </a>
          `;
        });
        html += '</div>';
        container.innerHTML = html;
      } else {
        container.innerHTML = '<div class="alert alert-warning">Không tìm thấy item</div>';
      }
    })
    .catch(() => {
      container.innerHTML = '<div class="alert alert-danger">Lỗi tìm kiếm</div>';
    });
  }, 300);

  // Tìm kiếm item
  document.getElementById('searchItem').addEventListener('click', function(){
    const searchTerm = document.getElementById('itemSearch').value.trim();
    searchItems(searchTerm);
  });

  // Tìm kiếm khi gõ
  document.getElementById('itemSearch').addEventListener('input', function(){
    const searchTerm = this.value.trim();
    if(searchTerm.length > 0) {
      searchItems(searchTerm);
    } else {
      document.getElementById('itemSearchResults').style.display = 'none';
    }
  });
  
  // Tìm kiếm option với debounce
  var searchOptions = debounce(function(searchTerm) {
    const container = document.getElementById('optionSearchResults');
    container.innerHTML = '<div class="list-group-item text-center"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</div>';
    container.style.display = 'block';
    
    fetch(`/ajax/admin/buff/search_option.php?q=${encodeURIComponent(searchTerm)}`)
    .then(r => r.json())
    .then(data => {
      if(data.status && data.data.length > 0) {
        let html = '<div class="list-group">';
        data.data.forEach(option => {
          html += `
            <a href="#" class="list-group-item list-group-item-action" onclick="addOption(${option.id}, '${option.name}')">
              <strong>${option.display_name}</strong> (ID: ${option.display_id})
            </a>
          `;
        });
        html += '</div>';
        container.innerHTML = html;
      } else {
        container.innerHTML = '<div class="alert alert-warning">Không tìm thấy option</div>';
      }
    })
    .catch(() => {
      container.innerHTML = '<div class="alert alert-danger">Lỗi tìm kiếm</div>';
    });
  }, 300);

  // Tìm kiếm option
  document.getElementById('searchOption').addEventListener('click', function(){
    const searchTerm = document.getElementById('optionSearch').value.trim();
    searchOptions(searchTerm);
  });

  // Tìm kiếm khi gõ
  document.getElementById('optionSearch').addEventListener('input', function(){
    const searchTerm = this.value.trim();
    if(searchTerm.length > 0) {
      searchOptions(searchTerm);
    } else {
      document.getElementById('optionSearchResults').style.display = 'none';
    }
  });
  
  // Form submit
  document.getElementById('buffForm').addEventListener('submit', function(e){
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('csrf_token', '<?php echo htmlspecialchars($csrf_token); ?>');
    formData.append('player_id', document.getElementById('playerId').value);
    formData.append('player_name', document.getElementById('playerName').value);
    formData.append('item_id', document.getElementById('itemId').value);
    formData.append('options', document.getElementById('options').value);
    formData.append('quantity', document.getElementById('quantity').value);
    
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang gửi...';
    
    fetch('/ajax/admin/buff/send.php', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if(data.status) {
        Swal.fire('Thành công', data.message, 'success');
        document.getElementById('buffForm').reset();
        document.getElementById('selectedItem').style.display = 'none';
        document.getElementById('selectedPlayer').style.display = 'none';
        document.getElementById('itemSearchResults').style.display = 'none';
        document.getElementById('playerSearchResults').style.display = 'none';
        document.getElementById('optionSearchResults').style.display = 'none';
        loadPendingRequests();
        loadBuffHistory();
      } else {
        Swal.fire('Lỗi', data.message, 'error');
      }
    })
    .catch(() => {
      Swal.fire('Lỗi', 'Không thể kết nối', 'error');
    })
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-magic me-2"></i>Gửi yêu cầu buff';
    });
  });
  
  // Status filter
  document.getElementById('statusFilter').addEventListener('change', loadBuffHistory);
});

function selectPlayer(id, name) {
  document.getElementById('playerId').value = id;
  document.getElementById('playerName').value = name;
  document.getElementById('selectedPlayerId').textContent = id;
  document.getElementById('selectedPlayerName').textContent = name;
  document.getElementById('selectedPlayer').style.display = 'block';
  document.getElementById('playerSearchResults').style.display = 'none';
}

function selectItem(id, name) {
  document.getElementById('itemId').value = id;
  document.getElementById('selectedItemId').textContent = id;
  document.getElementById('selectedItemName').textContent = name;
  document.getElementById('selectedItem').style.display = 'block';
  document.getElementById('itemSearchResults').style.display = 'none';
}

function addOption(id, name) {
  const currentOptions = document.getElementById('options').value;
  const newOption = `${id}-1`; // Default param = 1
  
  if(currentOptions) {
    document.getElementById('options').value = currentOptions + ';' + newOption;
  } else {
    document.getElementById('options').value = newOption;
  }
  
  document.getElementById('optionSearchResults').style.display = 'none';
  
  // Hiện thông báo nhỏ thay vì popup
  const toast = document.createElement('div');
  toast.className = 'toast-notification';
  toast.innerHTML = `
    <div class="toast-content">
      <i class="fas fa-check-circle text-success me-2"></i>
      Đã thêm option: ${name} (${id}-1)
    </div>
  `;
  document.body.appendChild(toast);
  
  // Tự động ẩn sau 3 giây
  setTimeout(() => {
    toast.remove();
  }, 3000);
}

function loadPendingRequests() {
  fetch('/ajax/admin/buff/pending.php')
  .then(r => r.json())
  .then(data => {
    const container = document.getElementById('pendingRequests');
    if(data.status && data.data.length > 0) {
      let html = '';
      data.data.forEach(item => {
        html += `
          <div class="alert alert-warning mb-2">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <strong>${item.player_name}</strong> - Item ID: ${item.item_id}
                <br><small>Options: ${item.options_string || 'Không có'} | Số lượng: ${item.quantity}</small>
              </div>
              <small class="text-muted">${item.created_at}</small>
            </div>
          </div>
        `;
      });
      container.innerHTML = html;
    } else {
      container.innerHTML = '<div class="text-center text-muted">Không có yêu cầu nào đang chờ</div>';
    }
  })
  .catch(() => {
    document.getElementById('pendingRequests').innerHTML = '<div class="text-center text-danger">Lỗi tải dữ liệu</div>';
  });
}

function loadBuffHistory() {
  const status = document.getElementById('statusFilter').value;
  const url = status ? `/ajax/admin/buff/history.php?status=${status}` : '/ajax/admin/buff/history.php';
  
  fetch(url)
  .then(r => r.json())
  .then(data => {
    const tbody = document.getElementById('buffHistory');
    if(data.status && data.data.length > 0) {
      let html = '';
      data.data.forEach(item => {
        const statusClass = {
          'pending': 'warning',
          'completed': 'success', 
          'failed': 'danger'
        }[item.status] || 'secondary';
        
        html += `
          <tr>
            <td>${item.id}</td>
            <td>${item.player_name}</td>
            <td>${item.item_id}</td>
            <td>${item.options_string || '-'}</td>
            <td>${item.quantity}</td>
            <td><span class="badge bg-${statusClass}">${item.status}</span></td>
            <td>${item.admin_name}</td>
            <td>${item.created_at}</td>
            <td>${item.result_message || '-'}</td>
          </tr>
        `;
      });
      tbody.innerHTML = html;
    } else {
      tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Không có dữ liệu</td></tr>';
    }
  })
  .catch(() => {
    document.getElementById('buffHistory').innerHTML = '<tr><td colspan="9" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>';
  });
}
</script>
